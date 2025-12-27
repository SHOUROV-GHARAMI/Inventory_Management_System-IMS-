@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">
        <i class="fas fa-chart-line mr-3"></i>Dashboard
    </h1>
    <p class="text-gray-600 mt-1">Welcome back, {{ session('user_name') }}!</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Products -->
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-medium">Total Products</p>
                <h3 class="text-3xl font-bold mt-2" id="totalProducts">Loading...</h3>
            </div>
            <div class="bg-white bg-opacity-20 p-4 rounded-full">
                <i class="fas fa-box text-3xl"></i>
            </div>
        </div>
    </div>

    <!-- Low Stock Items -->
    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-red-100 text-sm font-medium">Low Stock Items</p>
                <h3 class="text-3xl font-bold mt-2" id="lowStockItems">Loading...</h3>
            </div>
            <div class="bg-white bg-opacity-20 p-4 rounded-full">
                <i class="fas fa-exclamation-triangle text-3xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Sales -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm font-medium">Total Sales</p>
                <h3 class="text-3xl font-bold mt-2" id="totalSales">Loading...</h3>
            </div>
            <div class="bg-white bg-opacity-20 p-4 rounded-full">
                <i class="fas fa-dollar-sign text-3xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Value -->
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm font-medium">Inventory Value</p>
                <h3 class="text-3xl font-bold mt-2" id="inventoryValue">Loading...</h3>
            </div>
            <div class="bg-white bg-opacity-20 p-4 rounded-full">
                <i class="fas fa-warehouse text-3xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-xl shadow-lg p-6 mb-8">
    <h2 class="text-xl font-bold text-gray-800 mb-4">
        <i class="fas fa-bolt mr-2 text-yellow-500"></i>Quick Actions
    </h2>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @php
            $roleSlug = session('user_role_slug', 'staff');
        @endphp
        
        @if(session('user_role') !== 'Staff')
        <a href="/{{ $roleSlug }}/products?action=create" class="flex flex-col items-center justify-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
            <i class="fas fa-plus-circle text-3xl text-blue-600 mb-2"></i>
            <span class="text-sm font-medium text-gray-700">Add Product</span>
        </a>
        @endif
        
        <a href="/{{ $roleSlug }}/sales?action=create" class="flex flex-col items-center justify-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
            <i class="fas fa-cash-register text-3xl text-green-600 mb-2"></i>
            <span class="text-sm font-medium text-gray-700">New Sale</span>
        </a>
        
        @if(session('user_role') !== 'Staff')
        <a href="/{{ $roleSlug }}/purchase-orders?action=create" class="flex flex-col items-center justify-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition">
            <i class="fas fa-shopping-cart text-3xl text-orange-600 mb-2"></i>
            <span class="text-sm font-medium text-gray-700">Purchase Order</span>
        </a>
        @endif
        
        <a href="/{{ $roleSlug }}/inventory" class="flex flex-col items-center justify-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
            <i class="fas fa-boxes text-3xl text-purple-600 mb-2"></i>
            <span class="text-sm font-medium text-gray-700">View Inventory</span>
        </a>
        
        @if(session('user_role') !== 'Staff')
        <a href="/{{ $roleSlug }}/reports" class="flex flex-col items-center justify-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
            <i class="fas fa-chart-bar text-3xl text-indigo-600 mb-2"></i>
            <span class="text-sm font-medium text-gray-700">Reports</span>
        </a>
        @endif
        
        <a href="/{{ $roleSlug }}/alerts" class="flex flex-col items-center justify-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition">
            <i class="fas fa-bell text-3xl text-red-600 mb-2"></i>
            <span class="text-sm font-medium text-gray-700">Alerts</span>
        </a>
    </div>
</div>

<!-- Recent Activity & Low Stock Alerts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Activity -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-history mr-2 text-gray-600"></i>Recent Activity
        </h2>
        <div id="recentActivity" class="space-y-3">
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-spinner fa-spin text-3xl mb-3"></i>
                <p>Loading activities...</p>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-exclamation-circle mr-2 text-red-600"></i>Low Stock Alerts
        </h2>
        <div id="lowStockAlerts" class="space-y-3">
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-spinner fa-spin text-3xl mb-3"></i>
                <p>Loading alerts...</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>

const authToken = localStorage.getItem('auth_token');

if (!authToken) {
    window.location.href = '/login';
}

async function loadDashboardStats() {
    try {
        const response = await fetch('/api/reports/dashboard', {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            const stats = data.data;
            
            document.getElementById('totalProducts').textContent = stats.total_products || 0;
            document.getElementById('lowStockItems').textContent = stats.low_stock_items || 0;
            document.getElementById('totalSales').textContent = '$' + (stats.total_sales_value || 0).toLocaleString();
            document.getElementById('inventoryValue').textContent = '$' + (stats.total_inventory_value || 0).toLocaleString();
        }
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
}

async function loadLowStockAlerts() {
    try {
        const response = await fetch('/api/low-stock-alerts?limit=5', {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            const alerts = data.data;
            
            const alertsHtml = alerts.length > 0 ? alerts.map(alert => `
                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                    <div>
                        <p class="font-medium text-gray-800">${alert.product_name}</p>
                        <p class="text-sm text-gray-600">Current: ${alert.current_quantity} | Min: ${alert.minimum_stock_level}</p>
                    </div>
                    <span class="px-3 py-1 bg-red-200 text-red-800 text-xs font-semibold rounded-full">${alert.status}</span>
                </div>
            `).join('') : '<p class="text-center text-gray-500 py-4">No low stock alerts</p>';
            
            document.getElementById('lowStockAlerts').innerHTML = alertsHtml;
        }
    } catch (error) {
        console.error('Error loading alerts:', error);
        document.getElementById('lowStockAlerts').innerHTML = '<p class="text-center text-red-500">Failed to load alerts</p>';
    }
}

loadDashboardStats();
loadLowStockAlerts();

document.getElementById('recentActivity').innerHTML = `
    <div class="flex items-start p-3 bg-gray-50 rounded-lg">
        <i class="fas fa-box text-blue-500 mt-1 mr-3"></i>
        <div>
            <p class="font-medium text-gray-800">Product Added</p>
            <p class="text-sm text-gray-600">New product added to inventory</p>
            <p class="text-xs text-gray-500 mt-1">Just now</p>
        </div>
    </div>
    <div class="flex items-start p-3 bg-gray-50 rounded-lg">
        <i class="fas fa-dollar-sign text-green-500 mt-1 mr-3"></i>
        <div>
            <p class="font-medium text-gray-800">Sale Completed</p>
            <p class="text-sm text-gray-600">Sale #12345 processed successfully</p>
            <p class="text-xs text-gray-500 mt-1">5 minutes ago</p>
        </div>
    </div>
`;
</script>
@endpush
@endsection
