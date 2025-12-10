@echo off
echo ========================================
echo   ERGON TASK MANAGEMENT - AUDIT SUITE
echo ========================================
echo.

echo 🧪 Running PHPUnit Tests...
call vendor\bin\phpunit --colors=always
if %errorlevel% neq 0 (
    echo ❌ Tests failed!
    pause
    exit /b 1
)
echo ✅ Tests passed!
echo.

echo 🔍 Running PHPStan Static Analysis...
call vendor\bin\phpstan analyse --no-progress
if %errorlevel% neq 0 (
    echo ⚠️  PHPStan found issues
) else (
    echo ✅ PHPStan analysis clean!
)
echo.

echo 🔒 Running Security Audit...
php security-audit.php
echo.

echo 📏 Running Code Style Check...
call vendor\bin\phpcs --colors
if %errorlevel% neq 0 (
    echo ⚠️  Code style issues found
    echo 🔧 Run 'composer fix-style' to auto-fix
) else (
    echo ✅ Code style is clean!
)
echo.

echo 📊 Audit Complete!
echo ========================================
pause