@echo off
REM ==========================================================
REM  Niên Giám Lương — NativePHP dev mode (Electron + SQLite)
REM
REM  IMPORTANT: NativePHP at runtime overrides DB_CONNECTION to
REM  use database/nativephp.sqlite (see NativeServiceProvider::
REM  rewriteDatabase). The DB_DATABASE in .env.nativephp is
REM  IGNORED by the Electron window — so we must use
REM  `native:migrate` / `native:migrate:fresh` (NOT plain
REM  `migrate`) to target the actual app database.
REM
REM  Usage:
REM    dev-native.bat         — start dev server (incremental migrate)
REM    dev-native.bat fresh   — DROP all tables in nativephp.sqlite
REM                             and re-seed sample data, then start
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

if /I "%~1"=="fresh" (
    echo  [+] FRESH mode: dropping all tables in database/nativephp.sqlite and re-seeding
    php artisan native:migrate:fresh --seed --force --no-interaction
) else (
    echo  [+] Running NativePHP migrations on database/nativephp.sqlite
    REM This is the database Electron actually reads at runtime.
    REM (The auto-seed in NativeAppServiceProvider only runs when the DB is empty.)
    php artisan native:migrate --force --no-interaction
)

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
