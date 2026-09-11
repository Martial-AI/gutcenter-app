#!/usr/bin/env python3
"""
ZKTeco Terminal Manager Bridge for Laravel
Uses ONLY Python stdlib (socket, struct, time) — NO external libraries required.
Implements the ZKTeco binary protocol directly.
Outputs JSON for clean consumption by Laravel PHP.
"""

import sys
import json
import socket
import struct
import time
import argparse

# ---------------------------------------------------------------------------
# ZKTeco Protocol Constants
# ---------------------------------------------------------------------------
CMD_CONNECT        = 1000
CMD_EXIT           = 1001
CMD_ENABLEDEVICE   = 1002
CMD_DISABLEDEVICE  = 1003
CMD_ACK_OK         = 2000
CMD_ACK_ERROR      = 2001
CMD_PREPARE_DATA   = 1500
CMD_DATA           = 1501
CMD_USER_WRQ       = 72    # Write user
CMD_USER_RRQ       = 8     # Read users
CMD_STARTENROLL    = 61    # Start fingerprint enrollment
CMD_DELETE_USER    = 18    # Delete user
CMD_ATTLOG_RRQ     = 500   # Read attendance log

HEADER_SIZE = 8


# ---------------------------------------------------------------------------
# Low-level ZK packet helpers
# ---------------------------------------------------------------------------

def _checksum(data: bytes) -> int:
    s = 0
    n = len(data)
    for i in range(0, n - 1, 2):
        w = data[i] | (data[i + 1] << 8)
        s += w
    if n % 2:
        s += data[-1]
    while s >> 16:
        s = (s & 0xFFFF) + (s >> 16)
    return (~s) & 0xFFFF


def _build_packet(command: int, session_id: int, reply_id: int, payload: bytes = b'') -> bytes:
    header = struct.pack('<HHHH', command, 0, session_id, reply_id) + payload
    chk = _checksum(header)
    return struct.pack('<HHHH', command, chk, session_id, reply_id) + payload


def _parse_header(data: bytes) -> dict:
    if len(data) < HEADER_SIZE:
        return {}
    cmd, chk, sid, rid = struct.unpack('<HHHH', data[:HEADER_SIZE])
    return {'command': cmd, 'checksum': chk, 'session_id': sid, 'reply_id': rid,
            'payload': data[HEADER_SIZE:]}


def _decode_zk_time(t: int) -> str:
    try:
        sec    = t % 60;    t //= 60
        minute = t % 60;    t //= 60
        hour   = t % 24;    t //= 24
        day    = (t % 31) + 1; t //= 31
        month  = (t % 12) + 1; t //= 12
        year   = t + 2000
        if 2010 <= year <= 2040:
            return f"{year:04d}-{month:02d}-{day:02d} {hour:02d}:{minute:02d}:{sec:02d}"
    except Exception:
        pass
    return time.strftime('%Y-%m-%d %H:%M:%S')


# ---------------------------------------------------------------------------
# ZKDevice — manages a single UDP session
# ---------------------------------------------------------------------------

class ZKDevice:
    def __init__(self, ip: str, port: int = 4370, timeout: int = 10):
        self.ip = ip
        self.port = port
        self.timeout = timeout
        self.sock = None
        self.session_id = 0
        self.reply_id = 0

    def _send(self, data: bytes):
        self.sock.sendto(data, (self.ip, self.port))

    def _recv(self, size: int = 4096) -> bytes:
        try:
            data, _ = self.sock.recvfrom(size)
            return data
        except socket.timeout:
            return b''

    def connect(self) -> bool:
        self.sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        self.sock.settimeout(self.timeout)
        pkt = _build_packet(CMD_CONNECT, 0, 0)
        self._send(pkt)
        resp = self._recv()
        hdr = _parse_header(resp)
        if hdr.get('command') == CMD_ACK_OK:
            self.session_id = hdr['session_id']
            self.reply_id = 0
            return True
        return False

    def disconnect(self):
        if self.sock:
            try:
                pkt = _build_packet(CMD_EXIT, self.session_id, self.reply_id)
                self._send(pkt)
            except Exception:
                pass
            try:
                self.sock.close()
            except Exception:
                pass
            self.sock = None

    def _cmd(self, command: int, payload: bytes = b'') -> dict:
        self.reply_id += 1
        pkt = _build_packet(command, self.session_id, self.reply_id, payload)
        self._send(pkt)
        resp = self._recv()
        return _parse_header(resp)

    def disable_device(self):
        self._cmd(CMD_DISABLEDEVICE, b'\xff\xff\x00\x00')

    def enable_device(self):
        self._cmd(CMD_ENABLEDEVICE)

    def get_users(self) -> list:
        hdr = self._cmd(CMD_USER_RRQ)
        raw = hdr.get('payload', b'')

        if hdr.get('command') == CMD_PREPARE_DATA:
            total_size = struct.unpack('<I', raw[:4])[0] if len(raw) >= 4 else 0
            raw = b''
            while len(raw) < total_size:
                chunk = self._recv(65535)
                if not chunk:
                    break
                raw += _parse_header(chunk).get('payload', b'')

        users = []
        record_size = 72
        for i in range(0, len(raw) - record_size + 1, record_size):
            rec = raw[i:i + record_size]
            try:
                uid = struct.unpack('<H', rec[0:2])[0]
                user_id = rec[48:72].rstrip(b'\x00').decode('ascii', errors='ignore').strip()
                name = rec[11:35].rstrip(b'\x00').decode('utf-8', errors='ignore').strip()
                users.append({'uid': uid, 'user_id': user_id, 'name': name})
            except Exception:
                continue
        return users

    def set_user(self, uid: int, user_id: str, name: str, privilege: int = 0, password: str = '', card: int = 0):
        uid_b     = struct.pack('<H', uid & 0xFFFF)
        priv_b    = struct.pack('<B', privilege)
        pw_b      = password.encode('ascii', errors='replace')[:8].ljust(8, b'\x00')
        name_b    = name.encode('utf-8', errors='replace')[:24].ljust(24, b'\x00')
        card_b    = struct.pack('<I', card)
        group_b   = b'\x00' * 9
        uid2_b    = struct.pack('<H', uid & 0xFFFF)
        uid3_b    = b'\x00' * 4
        user_id_b = user_id.encode('ascii', errors='replace')[:24].ljust(24, b'\x00')

        payload = (uid_b + priv_b + pw_b + name_b + card_b + group_b + uid2_b + uid3_b + user_id_b)[:72].ljust(72, b'\x00')
        hdr = self._cmd(CMD_USER_WRQ, payload)
        return hdr.get('command') == CMD_ACK_OK

    def delete_user(self, uid: int, user_id: str) -> bool:
        payload = struct.pack('<H', uid & 0xFFFF)
        hdr = self._cmd(CMD_DELETE_USER, payload)
        return hdr.get('command') == CMD_ACK_OK

    def enroll_fingerprint(self, uid: int, finger_index: int = 0, timeout: int = 60) -> bool:
        """
        Sends CMD_STARTENROLL and polls until ACK_OK (all 3 finger presses done) or timeout.
        The device display will guide the user interactively.
        """
        payload = struct.pack('<HBB', uid & 0xFFFF, finger_index, 1)
        self.reply_id += 1
        pkt = _build_packet(CMD_STARTENROLL, self.session_id, self.reply_id, payload)
        self._send(pkt)

        deadline = time.time() + timeout
        last_print = 0

        while time.time() < deadline:
            resp = self._recv(1024)
            if resp:
                hdr = _parse_header(resp)
                cmd = hdr.get('command', 0)
                if cmd == CMD_ACK_OK:
                    return True
                if cmd == CMD_ACK_ERROR:
                    return False

            now = time.time()
            if now - last_print >= 5:
                remaining = int(deadline - now)
                print(f"ENROLL_PROGRESS: waiting for finger... {remaining}s remaining", flush=True)
                last_print = now

            time.sleep(0.5)

        return False

    def get_attendance(self) -> list:
        hdr = self._cmd(CMD_ATTLOG_RRQ)
        raw = hdr.get('payload', b'')

        if hdr.get('command') == CMD_PREPARE_DATA:
            total_size = struct.unpack('<I', raw[:4])[0] if len(raw) >= 4 else 0
            raw = b''
            while len(raw) < total_size:
                chunk = self._recv(65535)
                if not chunk:
                    break
                raw += _parse_header(chunk).get('payload', b'')

        logs = []
        record_size = 40
        for i in range(0, len(raw) - record_size + 1, record_size):
            rec = raw[i:i + record_size]
            try:
                user_id = rec[0:24].rstrip(b'\x00').decode('ascii', errors='ignore').strip()
                t = struct.unpack('<I', rec[24:28])[0]
                ts = _decode_zk_time(t)
                punch = rec[28] if len(rec) > 28 else 0
                if user_id:
                    logs.append({'identifier': user_id, 'timestamp': ts, 'status': 'present', 'punch': punch})
            except Exception:
                continue
        return logs


# ---------------------------------------------------------------------------
# High-level command functions (use ZKDevice — no external libs)
# ---------------------------------------------------------------------------

def test_connection(args) -> dict:
    zk = ZKDevice(args.ip, args.port, timeout=5)
    try:
        if zk.connect():
            zk.disconnect()
            return {'success': True, 'online': True,
                    'message': f'Connecté avec succès à {args.ip}:{args.port}'}
        return {'success': False, 'online': False,
                'message': f'Terminal injoignable ({args.ip}:{args.port})'}
    except Exception as e:
        return {'success': False, 'online': False, 'message': f'Échec de connexion : {str(e)}'}


def enroll_user(args) -> dict:
    user_id      = str(args.user_id).strip()
    user_name    = str(args.name or user_id).strip()
    finger_index = int(args.finger_index or 0)
    timeout      = int(args.timeout or 60)

    zk = ZKDevice(args.ip, args.port, timeout=max(15, timeout))
    try:
        if not zk.connect():
            return {'success': False, 'enrolled': False, 'user_id': user_id,
                    'message': f'Impossible de se connecter au terminal ({args.ip}:{args.port})'}

        zk.disable_device()

        uid = int(args.uid or 0)
        if uid <= 0:
            users = zk.get_users()
            existing = next((u for u in users if u['user_id'] == user_id), None)
            if existing:
                uid = existing['uid']
            else:
                uids = [u['uid'] for u in users if isinstance(u['uid'], int) and u['uid'] > 0]
                uid = (max(uids) + 1) if uids else 1

        zk.set_user(uid=uid, user_id=user_id, name=user_name[:24], privilege=0)

        print(f"ENROLL_START: uid={uid} user_id={user_id} finger={finger_index}", flush=True)

        success = zk.enroll_fingerprint(uid=uid, finger_index=finger_index, timeout=timeout)

        zk.enable_device()

        if success:
            return {
                'success': True, 'enrolled': True,
                'uid': uid, 'user_id': user_id, 'finger_index': finger_index,
                'message': f'Empreinte enregistrée avec succès pour {user_name} ({user_id}).'
            }
        return {
            'success': False, 'enrolled': False, 'uid': uid, 'user_id': user_id,
            'message': "Délai dépassé ou erreur hardware — le doigt n'a pas été posé à temps."
        }

    except Exception as e:
        return {'success': False, 'enrolled': False, 'user_id': user_id,
                'message': f"Erreur lors de l'enrôlement : {str(e)}"}
    finally:
        try:
            zk.enable_device()
        except Exception:
            pass
        zk.disconnect()


def delete_user(args) -> dict:
    user_id = str(args.user_id).strip()
    zk = ZKDevice(args.ip, args.port, timeout=10)
    try:
        if not zk.connect():
            return {'success': False, 'user_id': user_id,
                    'message': 'Impossible de se connecter au terminal.'}

        zk.disable_device()
        users = zk.get_users()
        found = [u for u in users if u['user_id'] == user_id]

        deleted = 0
        for u in found:
            if zk.delete_user(u['uid'], user_id):
                deleted += 1

        zk.enable_device()

        return {
            'success': True, 'user_id': user_id, 'deleted': True,
            'message': f'Utilisateur {user_id} supprimé du terminal.' if deleted > 0
                       else f'Utilisateur {user_id} introuvable sur le terminal (déjà supprimé?).'
        }
    except Exception as e:
        return {'success': False, 'user_id': user_id, 'message': f'Erreur suppression : {str(e)}'}
    finally:
        try:
            zk.enable_device()
        except Exception:
            pass
        zk.disconnect()


def get_attendance(args) -> dict:
    zk = ZKDevice(args.ip, args.port, timeout=15)
    try:
        if not zk.connect():
            return {'success': False, 'total': 0, 'logs': [],
                    'message': 'Impossible de se connecter au terminal.'}
        logs = zk.get_attendance()
        return {'success': True, 'total': len(logs), 'logs': logs}
    except Exception as e:
        return {'success': False, 'total': 0, 'logs': [],
                'message': f'Erreur récupération pointages : {str(e)}'}
    finally:
        zk.disconnect()


# ---------------------------------------------------------------------------
# CLI entry point
# ---------------------------------------------------------------------------

def main():
    parser = argparse.ArgumentParser(description="ZKTeco Bridge CLI (no external deps)")
    sub = parser.add_subparsers(dest='command')

    p = sub.add_parser('test')
    p.add_argument('--ip', required=True)
    p.add_argument('--port', type=int, default=4370)

    p = sub.add_parser('enroll')
    p.add_argument('--ip', required=True)
    p.add_argument('--port', type=int, default=4370)
    p.add_argument('--user-id', required=True)
    p.add_argument('--name', default='')
    p.add_argument('--uid', type=int, default=0)
    p.add_argument('--finger-index', type=int, default=0)
    p.add_argument('--timeout', type=int, default=60)

    p = sub.add_parser('delete')
    p.add_argument('--ip', required=True)
    p.add_argument('--port', type=int, default=4370)
    p.add_argument('--user-id', required=True)

    p = sub.add_parser('attendance')
    p.add_argument('--ip', required=True)
    p.add_argument('--port', type=int, default=4370)

    parsed = parser.parse_args()

    dispatch = {
        'test':       test_connection,
        'enroll':     enroll_user,
        'delete':     delete_user,
        'attendance': get_attendance,
    }

    fn = dispatch.get(parsed.command)
    result = fn(parsed) if fn else {'success': False, 'message': 'Commande inconnue'}

    # JSON result is always the LAST line (progress lines are prefixed with ENROLL_PROGRESS:)
    print(json.dumps(result, ensure_ascii=False))


if __name__ == '__main__':
    main()
