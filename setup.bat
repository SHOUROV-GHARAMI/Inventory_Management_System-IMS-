@echo off
echo ========================================
echo Inventory Management System Setup
echo ========================================
echo.

echo Step 1: Installing Composer dependencies...
call composer install
if errorlevel 1 (
    echo ERROR: Failed to install Composer dependencies
    pause
    exit /b 1
)
echo Composer dependencies installed successfully!
echo.

echo Step 2: Installing Node.js dependencies...
call npm install
if errorlevel 1 (
    echo ERROR: Failed to install Node dependencies
    pause
    exit /b 1
)
echo Node dependencies installed successfully!
echo.

echo Step 3: Generating application key...
php artisan key:generate
if errorlevel 1 (
    echo ERROR: Failed to generate application key
    pause
    exit /b 1
)
echo Application key generated successfully!
echo.

echo Step 4: Creating storage link...
php artisan storage:link
echo.

echo ========================================
echo Setup completed successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Create a MySQL database named 'inventorysystem'
echo 2. Update .env file with your database credentials
echo 3. Run: php artisan migrate
echo 4. Run: npm run dev (in one terminal)
echo 5. Run: php artisan serve (in another terminal)
echo.
pause
