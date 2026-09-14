#!/usr/bin/env python3

import argparse
import json
import sys
import time

from zk import ZK
from zk.finger import Finger
from zk.exception import ZKError, ZKErrorResponse


# ============================================================
# SORTIE
# ============================================================

def output(data):
    print(json.dumps(data, ensure_ascii=False))


def progress(message):
    print(f"ENROLL_PROGRESS: {message}", file=sys.stderr, flush=True)


# ============================================================
# CONNEXION
# ============================================================

def connect_device(args):
    progress("Connexion au terminal...")

    zk = ZK(
        args.ip,
        port=args.port,
        timeout=args.timeout,
        password=0,
        force_udp=False,
        ommit_ping=False
    )

    conn = zk.connect()

    progress("Connexion réussie.")

    return conn


# ============================================================
# UTILISATEURS
# ============================================================

def get_users(conn):
    return conn.get_users()


def find_user(conn, user_id):
    users = get_users(conn)

    for user in users:
        if str(user.user_id) == str(user_id):
            return user

    return None


def find_user_by_uid(conn, uid):
    users = get_users(conn)

    for user in users:
        if int(user.uid) == int(uid):
            return user

    return None


def find_free_uid(conn):
    users = get_users(conn)
    used = {int(u.uid) for u in users}

    uid = 1

    while uid in used:
        uid += 1

    return uid

# ============================================================
# UTILISATEURS - LECTURE
# ============================================================

def list_users(args):
    conn = None

    try:
        conn = connect_device(args)

        users = get_users(conn)

        data = []

        for user in users:
            data.append({
                "uid": int(user.uid),
                "user_id": str(user.user_id),
                "name": str(user.name),
                "privilege": int(user.privilege),
                "card": int(user.card),
            })

        output({
            "success": True,
            "count": len(data),
            "users": data
        })

        return 0

    except Exception as e:
        output({
            "success": False,
            "count": 0,
            "users": [],
            "message": f"Erreur lecture utilisateurs : {e}"
        })

        return 1

    finally:
        if conn:
            try:
                conn.enable_device()
            except Exception:
                pass

            try:
                conn.disconnect()
            except Exception:
                pass
# ============================================================
# TEMPLATES
# ============================================================

def get_template_map(conn):
    templates = conn.get_templates()

    result = {}

    for template in templates:
        result[(int(template.uid), int(template.fid))] = template

    return result


def get_templates_for_uid(conn, uid):
    return [
        t for t in conn.get_templates()
        if int(t.uid) == int(uid)
    ]


# ============================================================
# TEST CONNEXION
# ============================================================

def test_connection(args):
    conn = None

    try:
        conn = connect_device(args)

        output({
            "success": True,
            "online": True,
            "message": f"Terminal connecté : {args.ip}",
            "diag": {
                "ip": args.ip,
                "port": args.port,
                "device": "ZKTeco",
                "transport": "TCP/pyzk"
            }
        })

        return 0

    except Exception as e:
        output({
            "success": False,
            "online": False,
            "message": str(e),
            "diag": {
                "ip": args.ip,
                "port": args.port,
                "device": "ZKTeco",
                "transport": "TCP/pyzk"
            }
        })

        return 1

    finally:
        if conn:
            try:
                conn.enable_device()
            except Exception:
                pass

            try:
                conn.disconnect()
            except Exception:
                pass


# ============================================================
# ENROLEMENT
# ============================================================

def enroll_user(args):
    conn = None

    target_uid = None
    temporary_uid = None

    try:
        conn = connect_device(args)

        # ----------------------------------------------------
        # 1. Chercher l'utilisateur cible
        # ----------------------------------------------------

        user = find_user(conn, args.user_id)

        if user:
            target_uid = int(user.uid)

            progress(
                f"Utilisateur existant : "
                f"UID={target_uid}, UserID={user.user_id}"
            )

        else:
            target_uid = find_free_uid(conn)

            progress(
                f"Création utilisateur : "
                f"UID={target_uid}, UserID={args.user_id}"
            )

            conn.set_user(
                uid=target_uid,
                name=args.name,
                privilege=0,
                password="",
                group_id="",
                user_id=args.user_id,
                card=0
            )

            user = find_user_by_uid(conn, target_uid)

            if not user:
                raise ZKErrorResponse(
                    f"Impossible de retrouver UID={target_uid}"
                )

        # ----------------------------------------------------
        # 2. Vérifier si le doigt existe déjà
        # ----------------------------------------------------

        existing_target_templates = get_templates_for_uid(
            conn,
            target_uid
        )

        for template in existing_target_templates:
            if int(template.fid) == int(args.finger_index):
                output({
                    "success": False,
                    "enrolled": False,
                    "message": (
                        f"Une empreinte existe déjà : "
                        f"UID={target_uid}, "
                        f"FID={args.finger_index}"
                    )
                })
                return 1

        progress(
            f"Préparation de l'enrôlement : "
            f"UID={target_uid}, "
            f"UserID={args.user_id}, "
            f"FID={args.finger_index}"
        )

        # ----------------------------------------------------
        # 3. État AVANT enrôlement
        # ----------------------------------------------------

        users_before = get_users(conn)
        user_uids_before = {
            int(u.uid) for u in users_before
        }

        templates_before = get_template_map(conn)
        template_keys_before = set(templates_before.keys())

        # ----------------------------------------------------
        # 4. Désactiver le terminal
        # ----------------------------------------------------

        conn.disable_device()

        progress("Terminal désactivé pendant l'enrôlement.")
        progress(
            "Placez le doigt sur le capteur "
            "(plusieurs captures seront demandées)."
        )

        # ----------------------------------------------------
        # 5. Lancer l'enrôlement
        #
        # IMPORTANT :
        # Sur ce ZK3969, enroll_user() peut expirer alors
        # que le terminal a quand même créé le template.
        # ----------------------------------------------------

        enroll_exception = None
        enroll_result = None

        try:
            enroll_result = conn.enroll_user(
                uid=target_uid,
                temp_id=args.finger_index,
                user_id=args.user_id
            )

        except (TimeoutError, ZKError, ZKErrorResponse) as e:
            enroll_exception = e

        # ----------------------------------------------------
        # 6. Réactiver le terminal avant inspection
        # ----------------------------------------------------

        try:
            conn.enable_device()
        except Exception:
            pass

        progress("Terminal réactivé.")

        # Petit délai pour laisser le terminal finaliser
        time.sleep(1)

        # ----------------------------------------------------
        # 7. Chercher le template réellement créé
        # ----------------------------------------------------

        templates_after = get_template_map(conn)

        new_templates = []

        for key, template in templates_after.items():
            if key not in template_keys_before:
                new_templates.append(template)

        # ----------------------------------------------------
        # 8. Cas idéal : le template est déjà sur UID cible
        # ----------------------------------------------------

        target_template = None

        for template in new_templates:
            if (
                int(template.uid) == target_uid
                and int(template.fid) == int(args.finger_index)
                and int(template.valid) == 1
            ):
                target_template = template
                break

        if target_template is None:
            # Vérification globale au cas où le template était
            # déjà apparu mais n'était pas dans la liste "new".
            for template in templates_after.values():
                if (
                    int(template.uid) == target_uid
                    and int(template.fid) == int(args.finger_index)
                    and int(template.valid) == 1
                ):
                    target_template = template
                    break

        # ----------------------------------------------------
        # 9. Si le firmware a créé un UID temporaire
        # ----------------------------------------------------

        if target_template is None:

            temporary_candidates = []

            for template in new_templates:

                uid = int(template.uid)
                fid = int(template.fid)

                if (
                    uid != target_uid
                    and fid == int(args.finger_index)
                    and int(template.valid) == 1
                ):
                    temporary_candidates.append(template)

            # Priorité à un UID qui n'existait pas avant.
            temporary_candidates = [
                t for t in temporary_candidates
                if int(t.uid) not in user_uids_before
            ]

            if len(temporary_candidates) == 1:

                temporary_template = temporary_candidates[0]
                temporary_uid = int(temporary_template.uid)

                progress(
                    f"Template temporaire détecté : "
                    f"UID={temporary_uid}, "
                    f"FID={temporary_template.fid}, "
                    f"Taille={len(temporary_template.template)}"
                )

                # --------------------------------------------
                # Construire le Finger avec l'UID cible
                # --------------------------------------------

                finger_target = Finger(
                    uid=target_uid,
                    fid=int(temporary_template.fid),
                    valid=int(temporary_template.valid),
                    template=temporary_template.template
                )

                # --------------------------------------------
                # Transfert vers l'utilisateur cible
                # --------------------------------------------

                progress(
                    f"Transfert du template "
                    f"UID={temporary_uid} → UID={target_uid}"
                )

                conn.save_user_template(
                    user,
                    [finger_target]
                )

                progress("Template transféré avec succès.")

                # --------------------------------------------
                # Vérification
                # --------------------------------------------

                templates_verify = get_template_map(conn)

                verified = None

                for template in templates_verify.values():
                    if (
                        int(template.uid) == target_uid
                        and int(template.fid) == int(args.finger_index)
                        and int(template.valid) == 1
                    ):
                        verified = template
                        break

                if verified is None:
                    raise ZKErrorResponse(
                        "Le template n'a pas pu être vérifié "
                        f"sur UID={target_uid}"
                    )

                progress(
                    f"Vérification OK : "
                    f"UID={target_uid}, "
                    f"FID={verified.fid}, "
                    f"Taille={len(verified.template)}"
                )

                # --------------------------------------------
                # Supprimer l'utilisateur temporaire
                # --------------------------------------------

                if temporary_uid != target_uid:

                    progress(
                        f"Suppression de l'utilisateur temporaire "
                        f"UID={temporary_uid}"
                    )

                    try:
                        conn.delete_user(uid=temporary_uid)
                    except Exception as e:
                        progress(
                            f"Avertissement suppression UID temporaire : {e}"
                        )

                    # Vérification suppression
                    remaining_user = find_user_by_uid(
                        conn,
                        temporary_uid
                    )

                    if remaining_user:
                        progress(
                            f"Avertissement : UID={temporary_uid} "
                            f"existe encore."
                        )
                    else:
                        progress(
                            f"UID temporaire {temporary_uid} supprimé."
                        )

                output({
                    "success": True,
                    "enrolled": True,
                    "uid": target_uid,
                    "user_id": str(args.user_id),
                    "name": str(user.name),
                    "finger_index": int(args.finger_index),
                    "template_size": len(verified.template),
                    "temporary_uid": temporary_uid,
                    "message": (
                        "Empreinte enregistrée avec succès."
                    )
                })

                return 0

            # ------------------------------------------------
            # Aucun template temporaire détecté
            # ------------------------------------------------

            message = (
                "L'enrôlement n'a produit aucun template identifiable."
            )

            if enroll_exception:
                message += f" Erreur du terminal : {enroll_exception}"

            output({
                "success": False,
                "enrolled": False,
                "uid": target_uid,
                "message": message
            })

            return 1

        # ----------------------------------------------------
        # 10. Template déjà sur UID cible
        # ----------------------------------------------------

        output({
            "success": True,
            "enrolled": True,
            "uid": target_uid,
            "user_id": str(args.user_id),
            "name": str(user.name),
            "finger_index": int(args.finger_index),
            "template_size": len(target_template.template),
            "temporary_uid": None,
            "message": "Empreinte enregistrée avec succès."
        })

        return 0

    except KeyboardInterrupt:

        output({
            "success": False,
            "enrolled": False,
            "message": "Opération interrompue."
        })

        return 130

    except Exception as e:

        output({
            "success": False,
            "enrolled": False,
            "message": f"Erreur : {e}"
        })

        return 1

    finally:

        if conn:

            try:
                conn.enable_device()
            except Exception:
                pass

            try:
                conn.disconnect()
            except Exception:
                pass


# ============================================================
# SUPPRESSION UTILISATEUR
# ============================================================

def delete_user(args):
    conn = None

    try:
        conn = connect_device(args)

        user = find_user(conn, args.user_id)

        if not user:
            output({
                "success": True,
                "deleted": False,
                "message": (
                    f"Utilisateur {args.user_id} introuvable."
                )
            })
            return 0

        uid = int(user.uid)

        progress(
            f"Suppression utilisateur : "
            f"UID={uid}, UserID={args.user_id}"
        )

        conn.delete_user(uid=uid)

        time.sleep(0.5)

        remaining = find_user_by_uid(conn, uid)

        if remaining:

            output({
                "success": False,
                "deleted": False,
                "uid": uid,
                "message": (
                    f"L'utilisateur UID={uid} existe encore "
                    "après suppression."
                )
            })

            return 1

        output({
            "success": True,
            "deleted": True,
            "uid": uid,
            "user_id": str(args.user_id),
            "message": "Utilisateur supprimé avec succès."
        })

        return 0

    except Exception as e:

        output({
            "success": False,
            "deleted": False,
            "message": f"Erreur : {e}"
        })

        return 1

    finally:

        if conn:

            try:
                conn.enable_device()
            except Exception:
                pass

            try:
                conn.disconnect()
            except Exception:
                pass


# ============================================================
# ATTENDANCE
# ============================================================

def attendance(args):
    conn = None

    try:
        conn = connect_device(args)

        records = conn.get_attendance()

        data = []

        for record in records:

            data.append({
                "user_id": str(record.user_id),
                "timestamp": (
                    record.timestamp.isoformat()
                    if record.timestamp
                    else None
                ),
                "status": int(record.status),
                "punch": int(record.punch)
            })

        output({
            "success": True,
            "count": len(data),
            "attendance": data
        })

        return 0

    except Exception as e:

        output({
            "success": False,
            "message": f"Erreur : {e}"
        })

        return 1

    finally:

        if conn:

            try:
                conn.enable_device()
            except Exception:
                pass

            try:
                conn.disconnect()
            except Exception:
                pass


# ============================================================
# ARGUMENTS
# ============================================================

def build_parser():

    parser = argparse.ArgumentParser(
        description="Gestion ZKTeco via pyzk"
    )

    parser.add_argument(
        "--ip",
        default="192.168.0.201"
    )

    parser.add_argument(
        "--port",
        type=int,
        default=4370
    )

    parser.add_argument(
        "--timeout",
        type=int,
        default=20
    )

    subparsers = parser.add_subparsers(
        dest="command",
        required=True
    )

    # --------------------------------------------------------
    # TEST
    # --------------------------------------------------------

    p_test = subparsers.add_parser("test")

    p_test.add_argument("--ip", default=argparse.SUPPRESS)
    p_test.add_argument("--port", type=int, default=argparse.SUPPRESS)
    p_test.add_argument("--timeout", type=int, default=argparse.SUPPRESS)

    # --------------------------------------------------------
    # ENROLL
    # --------------------------------------------------------

    p_enroll = subparsers.add_parser("enroll")

    # Permet aussi :
    # enroll --ip ... --port ...
    # afin de rester compatible avec Laravel.
    p_enroll.add_argument("--ip", default=argparse.SUPPRESS)
    p_enroll.add_argument("--port", type=int, default=argparse.SUPPRESS)
    p_enroll.add_argument("--timeout", type=int, default=argparse.SUPPRESS)

    p_enroll.add_argument(
        "--user-id",
        required=True
    )

    p_enroll.add_argument(
        "--name",
        required=True
    )

    p_enroll.add_argument(
        "--finger-index",
        type=int,
        default=0
    )

    # --------------------------------------------------------
    # DELETE
    # --------------------------------------------------------

    p_delete = subparsers.add_parser("delete")

    p_delete.add_argument("--ip", default=argparse.SUPPRESS)
    p_delete.add_argument("--port", type=int, default=argparse.SUPPRESS)
    p_delete.add_argument("--timeout", type=int, default=argparse.SUPPRESS)

    p_delete.add_argument(
        "--user-id",
        required=True
    )

    # --------------------------------------------------------
    # USERS
    # --------------------------------------------------------

    p_users = subparsers.add_parser("users")

    p_users.add_argument("--ip", default=argparse.SUPPRESS)
    p_users.add_argument("--port", type=int, default=argparse.SUPPRESS)
    p_users.add_argument("--timeout", type=int, default=argparse.SUPPRESS)

    # --------------------------------------------------------
    # ATTENDANCE
    # --------------------------------------------------------

    p_attendance = subparsers.add_parser("attendance")

    p_attendance.add_argument("--ip", default=argparse.SUPPRESS)
    p_attendance.add_argument("--port", type=int, default=argparse.SUPPRESS)
    p_attendance.add_argument("--timeout", type=int, default=argparse.SUPPRESS)

    return parser


# ============================================================
# MAIN
# ============================================================

def main():

    parser = build_parser()
    args = parser.parse_args()

    if args.command == "test":
        return test_connection(args)

    if args.command == "enroll":
        return enroll_user(args)

    if args.command == "delete":
        return delete_user(args)

    if args.command == "users":
        return list_users(args)

    if args.command == "attendance":
        return attendance(args)

    parser.print_help()
    return 1


if __name__ == "__main__":
    sys.exit(main())
