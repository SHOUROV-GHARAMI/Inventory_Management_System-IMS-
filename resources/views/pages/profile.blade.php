@extends('layouts.dashboard')

@section('title', 'My Profile')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">
        <i class="fas fa-user-circle mr-3"></i>My Profile
    </h1>
    <p class="text-gray-600 mt-1">Manage your account settings</p>
</div>

<div class="bg-white rounded-xl shadow-lg p-8">
    <div class="max-w-2xl">
        <div class="mb-6 pb-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Profile Information</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                    <input type="text" value="{{ session('user_name') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" value="{{ session('user_email') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <input type="text" value="{{ session('user_role') }}" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>
            </div>
        </div>
        
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Change Password</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                    <input type="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input type="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
        <div class="flex space-x-4">
            <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-save mr-2"></i>Save Changes
            </button>
            <button class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300 transition">
                Cancel
            </button>
        </div>
    </div>
</div>
@endsection
