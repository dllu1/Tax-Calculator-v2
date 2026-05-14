@echo off
REM ==========================================================
REM  Nien Giam Luong - Launcher
REM  Double-click to start MySQL + Laravel server + browser
REM ==========================================================
chcp 65001 >NUL
title Nien Giam Luong - Khoi dong...

cd /d "%~dp0"

set XAMPP_DIR=C:\xampp
set PHP_EXE=%XAMPP_DIR%\php\php.exe
set MYSQLD=%XAMPP_DIR%\mysql\bin\mysqld.exe
set MYSQL_INI=%XAMPP_DIR%\mysql\bin\my.ini
set PORT=8000
set URL=http://localhost:%PORT%

echo.
echo  ============================================
echo    NIEN GIAM LUONG - Tinh Thue TNCN
echo  ============================================
echo.

REM ---------- 1. Locate PHP ----------
if not exist "%PHP_EXE%" (
    echo  [!] Khong tim thay PHP tai: %PHP_EXE%
    where php >NUL 2>&1
    if errorlevel 1 (
        echo  [!] Cung khong co PHP trong PATH he thong.
        echo      Hay cai dat XAMPP vao C:\xampp hoac sua duong dan trong start.bat.
        echo.
        pause
        exit /b 1
    ) else (
        echo  [i] Su dung PHP tu PATH he thong.
        set PHP_EXE=php
    )
) else (
    echo  [OK] PHP: %PHP_EXE%
)

REM ---------- 2. Start MySQL if not running ----------
echo  [.] Kiem tra MySQL...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I "mysqld.exe" >NUL
if errorlevel 1 (
    if exist "%MYSQLD%" (
        echo  [+] Khoi dong MySQL...
        start "" /B "%MYSQLD%" --defaults-file="%MYSQL_INI%" --standalone
        REM cho MySQL san sang
        timeout /t 4 /nobreak >NUL
        echo  [OK] MySQL da khoi dong.
    ) else (
        echo  [!] Khong tim thay MySQL tai %MYSQLD%.
        echo      Neu ban dung MySQL khac, hay tu khoi dong truoc khi chay.
        echo.
        pause
    )
) else (
    echo  [OK] MySQL dang chay.
)

REM ---------- 3. Run pending migrations ----------
echo  [.] Kiem tra migration...
"%PHP_EXE%" artisan migrate --force >NUL 2>&1
if errorlevel 1 (
    echo  [!] Migration loi - hay chay thu cong: php artisan migrate
) else (
    echo  [OK] Database san sang.
)

REM ---------- 4. Start Laravel server in a new window ----------
echo  [+] Khoi dong web server tai %URL%
start "Nien Giam Luong - Server (port %PORT%)" cmd /k ""%PHP_EXE%" artisan serve --host=127.0.0.1 --port=%PORT%"

REM cho server kip lang nghe
timeout /t 3 /nobreak >NUL

REM ---------- 5. Open default browser ----------
echo  [+] Mo trinh duyet...
start "" "%URL%"

echo.
echo  ============================================
echo    Server dang chay: %URL%
echo    De dung: chay stop.bat hoac dong cua so
echo    "Nien Giam Luong - Server (port %PORT%)"
echo  ============================================
echo.

REM cua so nay tu dong dong sau 5s, server van chay o cua so khac
timeout /t 5 /nobreak >NUL
exit /b 0
