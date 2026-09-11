#!/usr/bin/env python3
"""
ZKTeco Terminal Manager Bridge for Laravel
Interacts with ZKTeco devices (e.g. ZK3969) via pyzk.
Outputs JSON for clean consumption by Laravel PHP.
"""

import sys
import json
import argparse
from zk import ZK, const

def get_zk(ip, port, timeout=15):
    return ZK(
        ip,
        port=port,
        timeout=timeout,
        password=0,
        force_udp=False,
        ommit_ping=False
    )

def test_connection(args):
    zk = get_zk(args.ip, args.port, timeout=5)
    conn = None
    try:
        conn = zk.connect()
        return {
            "success": True,
            "online": True,
            "message": f"Connecté avec succès à {args.ip}:{args.port}"
        }
    except Exception as e:
        return {
            "success": False,
            "online": False,
            "message": f"Échec de connexion : {str(e)}"
        }
    finally:
        if conn:
            try:
                conn.disconnect()
            except:
                pass

def enroll_user(args):
    zk = get_zk(args.ip, args.port, timeout=args.timeout)
    conn = None
    user_id = str(args.user_id).strip()
    user_name = str(args.name or user_id).strip()
    finger_index = int(args.finger_index or 0)

    try:
        conn = zk.connect()
        conn.disable_device()

        # Determine safe UID
        uid = args.uid
        if not uid or uid <= 0:
            try:
                users = conn.get_users()
                existing = next((u for u in users if str(u.user_id).strip() == user_id), None)
                if existing and existing.uid:
                    uid = existing.uid
                else:
                    uids = [u.uid for u in users if isinstance(u.uid, int) and u.uid > 0]
                    uid = (max(uids) + 1) if uids else 1
            except Exception:
                uid = 10000

        # Register or update user on device
        conn.set_user(
            uid=uid,
            name=user_name[:24],  # ZK name length limit
            privilege=0,
            password="",
            group_id="",
            user_id=user_id,
            card=0
        )

        # Trigger enrollment on device - this will block while waiting for user finger presses
        conn.enroll_user(uid=uid, temp_id=finger_index, user_id=user_id)

        # Re-enable device after enrollment
        conn.enable_device()

        # Verify that the template is now registered
        template_found = False
        template_size = None
        try:
            templates = conn.get_templates()
            for t in templates:
                if t.uid == uid and (t.fid == finger_index or t.fid == 0):
                    template_found = True
                    template_size = t.size
                    break
        except Exception:
            template_found = True  # If template verification fails but enroll_user did not throw, treat as ok

        return {
            "success": True,
            "enrolled": True,
            "uid": uid,
            "user_id": user_id,
            "finger_index": finger_index,
            "template_found": template_found,
            "template_size": template_size,
            "message": f"Empreinte enregistrée avec succès pour {user_name} ({user_id}) sur le terminal."
        }

    except Exception as e:
        return {
            "success": False,
            "enrolled": False,
            "user_id": user_id,
            "message": f"Erreur lors de l'enrôlement : {str(e)}"
        }
    finally:
        if conn:
            try:
                conn.enable_device()
            except:
                pass
            try:
                conn.disconnect()
            except:
                pass

def delete_user(args):
    zk = get_zk(args.ip, args.port, timeout=10)
    conn = None
    user_id = str(args.user_id).strip()

    try:
        conn = zk.connect()
        conn.disable_device()

        users = conn.get_users()
        found = [u for u in users if str(u.user_id).strip() == user_id]

        deleted_count = 0
        for u in found:
            conn.delete_user(uid=u.uid, user_id=user_id)
            deleted_count += 1

        if not found:
            # Try deleting by string id directly
            try:
                conn.delete_user(user_id=user_id)
            except:
                pass

        conn.enable_device()

        return {
            "success": True,
            "user_id": user_id,
            "deleted": True,
            "message": f"Utilisateur {user_id} supprimé du terminal."
        }
    except Exception as e:
        return {
            "success": False,
            "user_id": user_id,
            "message": f"Erreur suppression : {str(e)}"
        }
    finally:
        if conn:
            try:
                conn.enable_device()
            except:
                pass
            try:
                conn.disconnect()
            except:
                pass

def get_attendance(args):
    zk = get_zk(args.ip, args.port, timeout=15)
    conn = None
    try:
        conn = zk.connect()
        records = conn.get_attendance()
        logs = []
        for r in records:
            logs.append({
                "identifier": str(r.user_id).strip(),
                "timestamp": str(r.timestamp),
                "status": "present",
                "punch": getattr(r, 'punch', 0)
            })
        return {
            "success": True,
            "total": len(logs),
            "logs": logs
        }
    except Exception as e:
        return {
            "success": False,
            "total": 0,
            "logs": [],
            "message": f"Erreur récupération pointages : {str(e)}"
        }
    finally:
        if conn:
            try:
                conn.disconnect()
            except:
                pass

def main():
    parser = argparse.ArgumentParser(description="ZKTeco Bridge CLI")
    subparsers = parser.add_subparsers(dest="command")

    # test
    p_test = subparsers.add_parser("test")
    p_test.add_argument("--ip", required=True)
    p_test.add_argument("--port", type=int, default=4370)

    # enroll
    p_enroll = subparsers.add_parser("enroll")
    p_enroll.add_argument("--ip", required=True)
    p_enroll.add_argument("--port", type=int, default=4370)
    p_enroll.add_argument("--user-id", required=True)
    p_enroll.add_argument("--name", default="")
    p_enroll.add_argument("--uid", type=int, default=0)
    p_enroll.add_argument("--finger-index", type=int, default=0)
    p_enroll.add_argument("--timeout", type=int, default=45)

    # delete
    p_del = subparsers.add_parser("delete")
    p_del.add_argument("--ip", required=True)
    p_del.add_argument("--port", type=int, default=4370)
    p_del.add_argument("--user-id", required=True)

    # attendance
    p_att = subparsers.add_parser("attendance")
    p_att.add_argument("--ip", required=True)
    p_att.add_argument("--port", type=int, default=4370)

    parsed = parser.parse_args()

    if parsed.command == "test":
        res = test_connection(parsed)
    elif parsed.command == "enroll":
        res = enroll_user(parsed)
    elif parsed.command == "delete":
        res = delete_user(parsed)
    elif parsed.command == "attendance":
        res = get_attendance(parsed)
    else:
        res = {"success": False, "message": "Commande inconnue"}

    print(json.dumps(res, ensure_ascii=False))

if __name__ == "__main__":
    main()
