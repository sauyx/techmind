@echo off
setlocal
cd /d "%~dp0"

echo Memastikan Docker Desktop berjalan...
docker info >nul 2>&1
if errorlevel 1 (
    echo Docker Desktop belum berjalan. Buka Docker Desktop lalu coba lagi.
    pause
    exit /b 1
)

echo Menjalankan TechMind (build pertama bisa beberapa menit)...
docker compose -f docker-compose.full.yml up -d --build
if errorlevel 1 (
    echo Gagal menjalankan Docker Compose.
    pause
    exit /b 1
)

echo Menunggu aplikasi siap...
timeout /t 15 /nobreak >nul

start "" "http://localhost:8000"
start "" "http://localhost:8080"

echo TechMind berjalan:
echo   Aplikasi : http://localhost:8000
echo   phpMyAdmin: http://localhost:8080
echo Login admin default: admin@techmind.local / change-me
echo Tutup jendela ini tidak mematikan container. Gunakan stop-windows.bat untuk berhenti.
pause
