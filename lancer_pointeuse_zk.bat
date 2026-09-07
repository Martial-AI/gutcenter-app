@echo off
title Synchroniseur Reseau Local ZKTeco (ZK3969)
echo ======================================================
echo    Synchroniseur Direct Reseau ZKTeco -> GUT Center
echo ======================================================
echo.
echo Verification des dependances Python...
python -m pip install pyzk --quiet

echo.
echo Lancement de l'ecoute en direct de la pointeuse...
python zk_sync.py
pause
