@echo off
echo Starting AccountGo Build Wrapper...
powershell.exe -NoProfile -ExecutionPolicy Bypass -File ".\build_update.ps1"
if %ERRORLEVEL% NEQ 0 (
    echo Build failed with error code %ERRORLEVEL%
    pause
    exit /b %ERRORLEVEL%
)
echo Build wrapper finished.
pause
