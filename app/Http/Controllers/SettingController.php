<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Setting::query();

        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        $settings = $query->orderBy('group')->orderBy('key')->get();

        return response()->json([
            'success' => true,
            'data' => $settings->groupBy('group')
        ]);
    }

    public function show($key)
    {
        $value = Setting::get($key);

        if ($value === null) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $key,
                'value' => $value
            ]
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
            'settings.*.type' => 'nullable|string|in:string,integer,boolean,array,json',
            'settings.*.group' => 'nullable|string',
            'settings.*.description' => 'nullable|string'
        ]);

        $updated = [];

        foreach ($request->settings as $settingData) {
            $old = Setting::where('key', $settingData['key'])->first();
            
            $setting = Setting::set(
                $settingData['key'],
                $settingData['value'],
                $settingData['type'] ?? 'string',
                $settingData['group'] ?? 'general',
                $settingData['description'] ?? null
            );

            AuditLog::logAction(
                'updated',
                $setting,
                $old ? $old->toArray() : null,
                $setting->toArray()
            );

            $updated[] = $setting;
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => $updated
        ]);
    }

    public function destroy($key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found'
            ], 404);
        }

        AuditLog::logAction('deleted', $setting, $setting->toArray(), null);
        
        $setting->delete();
        Setting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully'
        ]);
    }

    public function initializeDefaults()
    {
        $defaults = [

            ['key' => 'app_name', 'value' => 'Inventory Management System', 'type' => 'string', 'group' => 'general'],
            ['key' => 'app_timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'general'],
            ['key' => 'currency', 'value' => 'USD', 'type' => 'string', 'group' => 'general'],
            ['key' => 'currency_symbol', 'value' => '$', 'type' => 'string', 'group' => 'general'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'group' => 'general'],

            ['key' => 'low_stock_threshold', 'value' => '10', 'type' => 'integer', 'group' => 'inventory'],
            ['key' => 'allow_negative_stock', 'value' => 'false', 'type' => 'boolean', 'group' => 'inventory'],
            ['key' => 'auto_generate_sku', 'value' => 'true', 'type' => 'boolean', 'group' => 'inventory'],
            ['key' => 'sku_prefix', 'value' => 'PRD', 'type' => 'string', 'group' => 'inventory'],

            ['key' => 'enable_low_stock_alerts', 'value' => 'true', 'type' => 'boolean', 'group' => 'notifications'],
            ['key' => 'low_stock_alert_emails', 'value' => [], 'type' => 'array', 'group' => 'notifications'],
            ['key' => 'enable_email_notifications', 'value' => 'false', 'type' => 'boolean', 'group' => 'notifications'],

            ['key' => 'invoice_prefix', 'value' => 'INV', 'type' => 'string', 'group' => 'sales'],
            ['key' => 'tax_rate', 'value' => '10', 'type' => 'integer', 'group' => 'sales'],
            ['key' => 'enable_discount', 'value' => 'true', 'type' => 'boolean', 'group' => 'sales'],

            ['key' => 'po_prefix', 'value' => 'PO', 'type' => 'string', 'group' => 'purchase'],
            ['key' => 'po_approval_required', 'value' => 'true', 'type' => 'boolean', 'group' => 'purchase'],
            ['key' => 'auto_receive_approved_po', 'value' => 'false', 'type' => 'boolean', 'group' => 'purchase'],
        ];

        foreach ($defaults as $default) {
            Setting::firstOrCreate(
                ['key' => $default['key']],
                $default
            );
        }

        Setting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Default settings initialized successfully',
            'data' => Setting::all()->groupBy('group')
        ]);
    }
}
