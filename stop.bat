@echo off
REM ==========================================================
REM  Nien Giam Luong - Stop server
REM  Dung Laravel server. KHONG dung MySQL (de tranh anh huong
REM  cac app khac dung chung XAMPP).
REM ==========================================================
chcp 65001 >NUL
title Nien Giam Luong - Dung server

echo.
echo  [.] Dang dung server...

REM Tim cua so chua artisan serve theo title
taskkill /F /FI "WINDOWTITLE eq Nien Giam Luong - Server*" /T >NUL 2>&1

REM Backup: kill bat ky tien trinh php artisan serve nao
for /f "tokens=2" %%P in ('tasklist /FI "IMAGENAME eq php.exe" /FO TABLE /NH 2^>NUL') do (
    wmic process where "ProcessId=%%P" get CommandLine 2>NUL | findstr /I "artisan serve" >NUL
    if not errorlevel 1 (
        taskkill /F /PID %%P >NUL 2>&1
    )
)

echo  [OK] Server da dung.
echo.
echo  Luu y: MySQL van dang chay (de cac app khac dung chung
echo  XAMPP khong bi anh huong). Neu muon dung MySQL, vao
echo  XAMPP Control Panel ho?c chay:
echo      taskkill /F /IM mysqld.exe
echo.
timeout /t 3 /nobreak >NUL
exit /b 0
