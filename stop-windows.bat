@echo off
setlocal
cd /d "%~dp0"
docker compose -f docker-compose.full.yml down
echo TechMind dihentikan.
pause
