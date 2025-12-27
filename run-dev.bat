@echo off
echo ========================================
echo Starting Development Server
echo ========================================
echo.
echo Frontend assets will be compiled with hot reload
echo Server will start at http://localhost:8000
echo.
echo Press Ctrl+C to stop the servers
echo.

start "Vite Dev Server" cmd /k "npm run dev"
timeout /t 3 /nobreak > nul
start "Laravel Server" cmd /k "php artisan serve"

echo.
echo Both servers are starting in separate windows...
echo.
pause
