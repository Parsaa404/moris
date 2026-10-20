@echo off
title Moris Website Server
cd /d "%~dp0"

echo ==============================================
echo   Moris Website - Local Windows Server
echo ==============================================
echo.
echo   PUBLIC (English):  http://localhost:8080/en/
echo   PUBLIC (Persian):   http://localhost:8080/fa/
echo   ADMIN PANEL:        http://localhost:8080/admin/login.php
echo.
echo   NOTE: Please make sure MySQL in XAMPP is running.
echo   Press Ctrl+C to stop the server.
echo ==============================================
echo.

start http://localhost:8080/en/
php -S localhost:8080 router.php
pause
