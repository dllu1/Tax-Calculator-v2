@echo off
REM ==========================================================
REM  Niên Giám Lương — NativePHP dev mode (Electron + SQLite)
REM  Swap .env to use SQLite, run native:serve, restore on exit.
REM ==========================================================
chcp 65001 >NUL
title Niên Giám Lương — NativePHP dev

cd /d "%~dp0"

REM Unset ELECTRON_RUN_AS_NODE if it leaked into the shell env.
REM When set, Electron runs as plain Node and require('electron')
REM returns the binary path string instead of the API object,
REM causing the main process to crash with "electron.app undefined".
set "ELECTRON_RUN_AS_NODE="

if not exist ".env.nativephp" (
    echo  [!] Missing .env.nativephp - run phase 2 setup first.
    pause
    exit /b 1
)

echo  [+] Backing up current .env to .env.bak
copy /Y .env .env.bak >NUL

echo  [+] Swapping to .env.nativephp (SQLite)
copy /Y .env.nativephp .env >NUL

echo  [+] Ensuring database/database.sqlite exists
if not exist "database\database.sqlite" type nul > "database\database.sqlite"

echo  [+] Running SQLite migrations + seeds
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction 2>NUL

echo  [+] Starting NativePHP dev (Electron window will open)
echo      Press Ctrl+C to stop. The original .env will be restored automatically.
echo.

php artisan native:serve

echo.
echo  [+] Restoring original .env (MySQL)
copy /Y .env.bak .env >NUL
del /Q .env.bak

echo  [OK] Done.
pause
