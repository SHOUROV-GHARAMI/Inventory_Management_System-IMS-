@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-900 via-blue-800 to-blue-600 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <div class="mx-auto h-16 w-16 bg-blue-600 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-boxes text-white text-3xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800">Inventory Management</h2>
                <p class="text-gray-600 mt-2">Sign in to your account</p>
            </div>

            @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
            @endif

            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
            @endif

            <form id="loginForm" method="POST" action="{{ route('login.post') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email Address
                    </label>
                    <input type="email" id="email" name="email" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="Enter your email">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <input type="password" id="password" name="password" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="Enter your password">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                    </div>
                </div>

                <button type="submit" id="loginBtn"
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition font-semibold">
                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                </button>
            </form>

            <div class="mt-8 border-t border-gray-200 pt-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Test Accounts:</h3>
                <div class="space-y-2 text-xs">
                    <div class="bg-purple-50 p-3 rounded-lg border border-purple-200">
                        <p class="font-bold text-purple-800"><i class="fas fa-crown mr-1"></i> Super Admin</p>
                        <p class="text-gray-600">superadmin@test.com / password</p>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                        <p class="font-bold text-blue-800"><i class="fas fa-user-shield mr-1"></i> Admin</p>
                        <p class="text-gray-600">admin@test.com / password</p>
                    </div>
                    <div class="bg-green-50 p-3 rounded-lg border border-green-200">
                        <p class="font-bold text-green-800"><i class="fas fa-user-tie mr-1"></i> Manager</p>
                        <p class="text-gray-600">manager@test.com / password</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <p class="font-bold text-gray-800"><i class="fas fa-user mr-1"></i> Staff</p>
                        <p class="text-gray-600">staff@test.com / password</p>
                    </div>
                </div>
            </div>
        </div>
        
        <p class="text-center text-white text-sm mt-6">
            &copy; 2025 Inventory Management System. All rights reserved.
        </p>
    </div>
</div>
@endsection
