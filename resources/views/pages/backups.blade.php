@extends('layouts.dashboard')

@section('title', 'Backups')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">
        <i class="fas fa-database mr-3 text-purple-600"></i>Database Backups
    </h1>
    <p class="text-gray-600 mt-1">Backup and restore database (Super Admin only)</p>
</div>

<div class="bg-white rounded-xl shadow-lg p-8">
    <p class="text-gray-600">This page is under construction. Use the API endpoints for now.</p>
    <p class="text-sm text-gray-500 mt-2">API: GET /api/backups</p>
</div>
@endsection
