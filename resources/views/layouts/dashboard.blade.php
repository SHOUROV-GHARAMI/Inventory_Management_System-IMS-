<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Inventory Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-gradient-to-b from-blue-900 to-blue-800 text-white shadow-xl transition-all duration-300">
            <div class="p-6">
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-boxes mr-3"></i>
                    <span>IMS</span>
                </h1>
                <p class="text-blue-200 text-sm mt-1">Inventory System</p>
            </div>
            
            <nav class="mt-6 px-3">
                @php
                    $roleSlug = session('user_role_slug', 'staff');
                @endphp
                
                <a href="/{{ $roleSlug }}/dashboard" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-blue-700 transition @if(request()->is('*/dashboard')) bg-blue-700 @endif">
                    <i class="fas fa-home w-6"></i>
                    <span>Dashboard</span>
                </a>
                
                @if(session('user_role') === 'Super Admin' || session('user_role') === 'Admin' || session('user_role') === 'Manager' || session('user_role') === 'Staff')
                <a href="/{{ $roleSlug }}/products" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-box w-6"></i>
                    <span>Products</span>
                </a>
                <a href="/{{ $roleSlug }}/categories" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-tags w-6"></i>
                    <span>Categories</span>
                </a>
                <a href="/{{ $roleSlug }}/inventory" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-warehouse w-6"></i>
                    <span>Inventory</span>
                </a>
                @endif
                
                @if(session('user_role') === 'Super Admin' || session('user_role') === 'Admin' || session('user_role') === 'Manager')
                <a href="/{{ $roleSlug }}/suppliers" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-truck w-6"></i>
                    <span>Suppliers</span>
                </a>
                <a href="/{{ $roleSlug }}/purchase-orders" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-shopping-cart w-6"></i>
                    <span>Purchase Orders</span>
                </a>
                @endif
                
                @if(session('user_role') === 'Super Admin' || session('user_role') === 'Admin' || session('user_role') === 'Manager' || session('user_role') === 'Staff')
                <a href="/{{ $roleSlug }}/sales" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-dollar-sign w-6"></i>
                    <span>Sales</span>
                </a>
                @endif
                
                @if(session('user_role') === 'Super Admin' || session('user_role') === 'Admin' || session('user_role') === 'Manager')
                <a href="/{{ $roleSlug }}/reports" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-chart-line w-6"></i>
                    <span>Reports</span>
                </a>
                <a href="/{{ $roleSlug }}/alerts" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-exclamation-triangle w-6"></i>
                    <span>Low Stock Alerts</span>
                </a>
                @endif
                
                @if(session('user_role') === 'Super Admin' || session('user_role') === 'Admin')
                <a href="/{{ $roleSlug }}/users" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-users w-6"></i>
                    <span>Users</span>
                </a>
                @endif
                
                @if(session('user_role') === 'Super Admin')
                <div class="mt-4 pt-4 border-t border-blue-700">
                    <p class="text-xs text-blue-300 px-4 mb-2">SUPER ADMIN</p>
                    <a href="/{{ $roleSlug }}/settings" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-cog w-6"></i>
                        <span>Settings</span>
                    </a>
                    <a href="/{{ $roleSlug }}/backups" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-database w-6"></i>
                        <span>Backups</span>
                    </a>
                    <a href="/{{ $roleSlug }}/audit-logs" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-history w-6"></i>
                        <span>Audit Logs</span>
                    </a>
                </div>
                @endif
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white shadow-md">
                <div class="flex items-center justify-between px-6 py-4">
                    <button id="sidebarToggle" class="text-gray-600 hover:text-gray-800 lg:hidden">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <div class="flex items-center space-x-4">
                        <div class="text-right mr-4">
                            <p class="text-sm font-semibold text-gray-800">{{ session('user_name', 'User') }}</p>
                            <p class="text-xs text-gray-500">{{ session('user_role', 'Guest') }}</p>
                        </div>
                        
                        <div class="relative">
                            <button id="profileBtn" class="flex items-center space-x-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-user-circle"></i>
                                <span class="hidden md:inline">Profile</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            
                            <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50">
                                <a href="/{{ session('user_role_slug', 'staff') }}/profile" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i> My Profile
                                </a>
                                <form action="/logout" method="POST" class="block">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
                @endif
                
                @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                </div>
                @endif
                
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4 px-6">
                <div class="flex justify-between items-center text-sm text-gray-600">
                    <p>&copy; 2025 Inventory Management System. All rights reserved.</p>
                    <p>Version 1.0.1</p>
                </div>
            </footer>
        </div>
    </div>

    <script>

        @if(session('auth_token'))
            localStorage.setItem('auth_token', '{{ session("auth_token") }}');
        @endif

        document.getElementById('profileBtn').addEventListener('click', function() {
            document.getElementById('profileMenu').classList.toggle('hidden');
        });

        document.addEventListener('click', function(event) {
            const profileBtn = document.getElementById('profileBtn');
            const profileMenu = document.getElementById('profileMenu');
            if (!profileBtn.contains(event.target) && !profileMenu.contains(event.target)) {
                profileMenu.classList.add('hidden');
            }
        });

        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
