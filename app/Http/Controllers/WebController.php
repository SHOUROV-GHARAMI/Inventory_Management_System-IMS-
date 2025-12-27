<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class WebController extends Controller
{
    public function login()
    {
        // If already authenticated, redirect to dashboard
        if (session()->has('auth_token')) {
            $roleSlug = session('user_role_slug', 'staff');
            return redirect('/' . $roleSlug . '/dashboard');
        }
        
        return view('auth.login');
    }

    public function handleLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Invalid email or password');
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Get user role
        $role = $user->roles()->first()->name ?? 'Guest';
        $roleSlug = strtolower(str_replace(' ', '-', $role));

        // Store in session
        session([
            'auth_token' => $token,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $role,
            'user_role_slug' => $roleSlug
        ]);

        return redirect('/' . $roleSlug . '/dashboard');
    }

    public function dashboard(Request $request)
    {
        // Check if authenticated
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }

        return view('dashboard.index');
    }

    public function logout(Request $request)
    {
        // Clear session
        $request->session()->flush();
        
        return redirect('/login')->with('success', 'You have been logged out successfully');
    }

    // Products page
    public function products()
    {
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return view('pages.products');
    }

    // Categories page
    public function categories()
    {
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return view('pages.categories');
    }

    // Inventory page
    public function inventory()
    {
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return view('pages.inventory');
    }

    // Suppliers page
    public function suppliers()
    {
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return view('pages.suppliers');
    }

    // Purchase Orders page
    public function purchaseOrders()
    {
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return view('pages.purchase-orders');
    }

    // Sales page
    public function sales()
    {
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return view('pages.sales');
    }

    // Reports page
    public function reports()
    {
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return view('pages.reports');
    }

    // Alerts page
    public function alerts()
    {
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return view('pages.alerts');
    }

    // Users page
    public function users()
    {
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return view('pages.users');
    }

    // Settings page (Super Admin only)
    public function settings()
    {
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return view('pages.settings');
    }

    // Backups page (Super Admin only)
    public function backups()
    {
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return view('pages.backups');
    }

    // Audit Logs page (Super Admin only)
    public function auditLogs()
    {
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return view('pages.audit-logs');
    }

    // Profile page
    public function profile()
    {
        if (!session()->has('auth_token')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }
        return view('pages.profile');
    }
}
