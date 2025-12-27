<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    
    public function check()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $healthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
            'version' => '1.0.0',
            'environment' => app()->environment()
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            $count = DB::table('users')->count();
            return [
                'status' => 'ok',
                'message' => 'Database is accessible',
                'users_count' => $count
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    private function checkCache()
    {
        try {
            Cache::put('health_check', 'ok', 10);
            $value = Cache::get('health_check');
            
            return [
                'status' => $value === 'ok' ? 'ok' : 'error',
                'message' => $value === 'ok' ? 'Cache is working' : 'Cache test failed'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache failed: ' . $e->getMessage()
            ];
        }
    }

    private function checkStorage()
    {
        try {
            $path = storage_path('app');
            $writable = is_writable($path);
            
            return [
                'status' => $writable ? 'ok' : 'error',
                'message' => $writable ? 'Storage is writable' : 'Storage is not writable',
                'path' => $path
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage check failed: ' . $e->getMessage()
            ];
        }
    }

    public function info()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'version' => '1.0.0',
                'environment' => app()->environment(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timezone' => config('app.timezone'),
                'debug_mode' => config('app.debug'),
                'cache_driver' => config('cache.default'),
                'database_driver' => config('database.default'),
                'uptime' => $this->getUptime()
            ]
        ]);
    }

    private function getUptime()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 'Not available on Windows';
        }
        
        try {
            $uptime = shell_exec('uptime -p');
            return trim($uptime);
        } catch (\Exception $e) {
            return 'Unable to determine';
        }
    }
}
