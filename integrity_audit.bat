@echo off
echo =======================================================
echo          POST-MOVE INTEGRITY AUDIT - BEGIN
echo =======================================================

echo.
echo Checking if core directories still exist...
echo.

for %%D in (
app
app\controllers
app\models
app\routes
app\views
public
public\assets
public\js
public\css
core
vendor
config
database
resources
storage
bootstrap
) do (
    if exist "%%D" (
        echo [OK]   %%D found.
    ) else (
        echo [ERROR] MISSING: %%D           !! CRITICAL !!
    )
)

echo.
echo =======================================================
echo Checking PHP entry-points still untouched...
echo =======================================================

for %%F in (
index.php
router.php
autoload.php
composer.json
composer.lock
.htaccess
) do (
    if exist "%%F" (
        echo [OK]   %%F exists.
    ) else (
        echo [ERROR] MISSING: %%F           !! CRITICAL !!
    )
)

echo.
echo =======================================================
echo Checking no controllers were accidentally moved...
echo =======================================================

set missingController=0
for /f "delims=" %%F in ('dir /b /s app\controllers\*.php') do (
    echo [OK] Controller: %%~nxF
)

echo.
echo =======================================================
echo Checking no routes were accidentally moved...
echo =======================================================

for /f "delims=" %%F in ('dir /b /s app\routes\*.php') do (
    echo [OK] Route: %%~nxF
)

echo.
echo =======================================================
echo Checking no views were accidentally moved...
echo =======================================================

for /f "delims=" %%F in ('dir /b /s app\views\*') do (
    echo [OK] View: %%~nxF
)

echo.
echo =======================================================
echo Checking no JS files in public/js were touched...
echo =======================================================

for /f "delims=" %%F in ('dir /b /s public\js\*.js') do (
    echo [OK] JS: %%~nxF
)

echo.
echo =======================================================
echo Searching for missing known-critical files...
echo =======================================================

set flagCriticalMissing=0
for %%F in (
app\controllers\FinanceController.php
app\controllers\PrefixFallback.php
app\routes\api.php
app\routes\web.php
core\Database.php
core\App.php
core\Request.php
) do (
    if exist "%%F" (
        echo [OK] Critical file %%F exists.
    ) else (
        echo [ERROR] CRITICAL FILE MISSING: %%F
        set flagCriticalMissing=1
    )
)

echo.
echo =======================================================
echo  Checking archive folder integrity...
echo =======================================================

for %%D in (
archive\documentation
archive\debug_scripts
archive\test_scripts
archive\fix_scripts
archive\sql_dumps
archive\legacy_unused
archive\runtime_misc
) do (
    if exist "%%D" (
        echo [OK] Archive folder %%D exists.
    ) else (
        echo [ERROR] MISSING ARCHIVE FOLDER: %%D
    )
)

echo.
echo =======================================================
echo DONE — SCAN COMPLETE.
echo =======================================================

echo.
if %flagCriticalMissing%==1 (
    echo ⚠️  CRITICAL ERRORS FOUND — CHECK ABOVE LIST!
) else (
    echo ✅ ALL GOOD — No critical files were moved or deleted.
)

echo.
pause