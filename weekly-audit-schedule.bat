@echo off
echo 📅 WEEKLY AUDIT SCHEDULE - Ergon Task Management
echo ===============================================
echo.

echo 🗓️  Setting up weekly audit schedule...
echo.

echo Creating scheduled task for weekly audits...
schtasks /create /tn "ErgonWeeklyAudit" /tr "C:\laragon\www\ergon-site\audit.bat" /sc weekly /d MON /st 09:00 /f

if %errorlevel% equ 0 (
    echo ✅ Weekly audit scheduled for every Monday at 9:00 AM
) else (
    echo ❌ Failed to create scheduled task
    echo 💡 You can manually run audit.bat weekly
)

echo.
echo 📋 Manual Schedule Reminder:
echo - Daily: Run quick-audit.bat before commits
echo - Weekly: Run audit.bat (every Monday)
echo - Before Production: Full security review
echo.

pause