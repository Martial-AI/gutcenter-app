"""
Passerelle Réseau Local ZKTeco (ZK3969) -> GUT Center
Synchronise automatiquement les empreintes digitales pointées avec l'application locale.
"""

import sys
import time
import json
import urllib.request
import urllib.error

# Configuration
DEVICE_IP = "192.168.1.201"  # Modifiez ici avec l'adresse IP de votre pointeuse ZKTeco
DEVICE_PORT = 4370
DEVICE_NAME = "ZKTeco-ZK3969-Ethernet"
API_URL = "http://127.0.0.1:8000/api/biometric/punch"  # URL de l'API locale
POLL_INTERVAL_SECONDS = 5

print("=" * 60)
print(f" Démarrage de la synchronisation ZKTeco Réseau Local ({DEVICE_IP}:{DEVICE_PORT})")
print(f" Transmission vers : {API_URL}")
print("=" * 60)

try:
    from zk import ZK
    zk_available = True
except ImportError:
    zk_available = False
    print("\n[INFO] La bibliothèque 'pyzk' n'est pas installée.")
    print("Pour l'installer rapidement : pip install pyzk\n")


def send_punch_to_laravel(user_id, timestamp=None, status="present"):
    payload = {
        "identifier": str(user_id).strip(),
        "device_identifier": DEVICE_NAME,
        "occurred_at": str(timestamp) if timestamp else time.strftime("%Y-%m-%d %H:%M:%S"),
        "status": status
    }
    
    data = json.dumps(payload).encode("utf-8")
    req = urllib.request.Request(
        API_URL,
        data=data,
        headers={"Content-Type": "application/json", "Accept": "application/json"}
    )
    
    try:
        with urllib.request.urlopen(req, timeout=5) as response:
            res_body = response.read().decode("utf-8")
            res_json = json.loads(res_body)
            print(f"[OK] Pointage enregistré pour ID {user_id}: {res_json.get('message', 'Succès')}")
            return True
    except urllib.error.HTTPError as e:
        print(f"[ERREUR API {e.code}] ID {user_id}: {e.read().decode('utf-8')}")
    except Exception as e:
        print(f"[ERREUR DE CONNEXION API] {e}")
    return False


def run_pyzk_sync():
    conn = None
    zk = ZK(DEVICE_IP, port=DEVICE_PORT, timeout=5, password=0, force_udp=False, ommit_ping=False)
    
    synced_cache = set()
    
    while True:
        try:
            print(f"Connexion à la pointeuse ZKTeco ({DEVICE_IP})...")
            conn = zk.connect()
            conn.disable_device()
            
            print("[CONNECTÉ] Récupération des pointages...")
            attendances = conn.get_attendance()
            
            new_count = 0
            for att in attendances:
                key = f"{att.user_id}_{att.timestamp}"
                if key not in synced_cache:
                    success = send_punch_to_laravel(att.user_id, att.timestamp)
                    if success:
                        synced_cache.add(key)
                        new_count += 1
            
            if new_count > 0:
                print(f"[SYNCHRO] {new_count} nouveau(x) pointage(s) synchronisé(s).")
            
            conn.enable_device()
            conn.disconnect()
            conn = None
            
        except Exception as e:
            print(f"[ATTENTE] Connexion pointeuse en cours : {e}")
            if conn:
                try:
                    conn.enable_device()
                    conn.disconnect()
                except:
                    pass
                conn = None
        
        time.sleep(POLL_INTERVAL_SECONDS)


if __name__ == "__main__":
    if zk_available:
        run_pyzk_sync()
    else:
        print("Veuillez installer pyzk via : pip install pyzk")
        print("Puis relancez : python zk_sync.py")
