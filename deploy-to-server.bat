@echo off
title Update Moris Server
cd /d "%~dp0"

echo ==============================================
echo   Updating Moris Live Server (morispt.com)
echo ==============================================
echo.

set KEY_PATH=C:\Users\vatan\Desktop\Mori\Mori.pem
set SERVER_IP=15.134.224.105
set SERVER_USER=ubuntu

if not exist "%KEY_PATH%" (
    echo [ERROR] Private key not found at: %KEY_PATH%
    pause
    exit /b 1
)

echo Connecting to server and updating code from GitHub...
ssh -i "%KEY_PATH%" %SERVER_USER%@%SERVER_IP% "bash /home/ubuntu/update.sh"

echo.
echo ==============================================
echo   Done! Check site: https://morispt.com
echo ==============================================
echo.
pause
