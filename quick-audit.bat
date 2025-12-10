@echo off
echo 🚀 QUICK AUDIT - Ergon Task Management
echo =====================================
echo.

echo ✅ 1. Running Tests...
php vendor\phpunit\phpunit\phpunit --testdox --colors=never
echo.

echo 🔒 2. Security Check...
php security-audit.php | findstr /C:"Issues found" /C:"HIGH:" /C:"MEDIUM:"
echo.

echo 📊 3. Audit Summary Complete!
echo.
echo 💡 For full audit run: audit.bat
echo 🔧 To install static analysis: composer install
pause