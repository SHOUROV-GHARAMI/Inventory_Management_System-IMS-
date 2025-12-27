@extends('layouts.dashboard')

@section('title', 'Low Stock Alerts')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">
        <i class="fas fa-exclamation-triangle mr-3"></i>Low Stock Alerts
    </h1>
    <p class="text-gray-600 mt-1">Monitor low stock items</p>
</div>

<div class="bg-white rounded-xl shadow-lg p-8">
    <p class="text-gray-600">This page is under construction. Use the API endpoints for now.</p>
    <p class="text-sm text-gray-500 mt-2">API: GET /api/low-stock-alerts</p>
</div>
@endsection
