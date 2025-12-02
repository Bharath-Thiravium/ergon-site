@echo off
REM Comprehensive Windows batch script for ergon to ergon-site migration

echo 🔧 Starting comprehensive ergon to ergon-site migration...

REM Fix PHP files
echo 📝 Fixing PHP files...
powershell -Command "(Get-ChildItem -Path . -Filter *.php -Recurse | Where-Object {$_.FullName -notmatch 'vendor|node_modules'} | ForEach-Object {(Get-Content $_.FullName) -replace '/ergon/', '/ergon-site/' | Set-Content $_.FullName})"

REM Fix JavaScript files  
echo 🔧 Fixing JavaScript files...
powershell -Command "(Get-ChildItem -Path . -Filter *.js -Recurse | Where-Object {$_.FullName -notmatch 'vendor|node_modules'} | ForEach-Object {(Get-Content $_.FullName) -replace '/ergon/', '/ergon-site/' | Set-Content $_.FullName})"

REM Fix CSS files
echo 🎨 Fixing CSS files...
powershell -Command "(Get-ChildItem -Path . -Filter *.css -Recurse | Where-Object {$_.FullName -notmatch 'vendor|node_modules'} | ForEach-Object {(Get-Content $_.FullName) -replace '/ergon/', '/ergon-site/' | Set-Content $_.FullName})"

echo ✅ Migration complete! Summary:
echo - Updated all PHP files
echo - Updated all JavaScript files
echo - Updated all CSS files
echo.
echo 🔍 Please test the application and check for any remaining issues.
pause