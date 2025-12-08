@echo off
echo ========================================
echo Attendance Table Structure Fix
echo ========================================
echo.

cd /d "%~dp0"

php run_attendance_fix.php

echo.
echo ========================================
echo Press any key to exit...
pause >nul
