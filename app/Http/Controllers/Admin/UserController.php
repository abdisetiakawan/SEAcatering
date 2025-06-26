<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['subscriptions.mealPlan', 'addresses']);

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        // Filter by subscription status
        if ($request->filled('subscription_status')) {
            if ($request->subscription_status === 'active') {
                $query->whereHas('subscriptions', function ($q) {
                    $q->where('status', 'active');
                });
            } elseif ($request->subscription_status === 'inactive') {
                $query->whereDoesntHave('subscriptions', function ($q) {
                    $q->where('status', 'active');
                });
            }
        }

        // Filter by admin status
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }


        // Filter by email verification
        if ($request->filled('email_verified')) {
            if ($request->email_verified === 'verified') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total_users' => User::count(),
            'active_subscribers' => User::whereHas('subscriptions', function ($q) {
                $q->where('status', 'active');
            })->count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'unverified_users' => User::whereNull('email_verified_at')->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
        ];

        return Inertia::render('Admin/UserManagement', [
            'users' => $users,
            'filters' => $request->only(['search', 'subscription_status', 'role', 'email_verified']),
            'stats' => $stats
        ]);
    }

    public function show(User $user)
    {
        $user->load([
            'subscriptions.mealPlan',
            'addresses',
            'orders.orderItems.menuItem',
            'reviews.menuItem'
        ]);

        $userStats = [
            'total_orders' => $user->orders()->count(),
            'total_spent' => $user->orders()->sum('total_amount'),
            'active_subscriptions' => $user->subscriptions()->where('status', 'active')->count(),
            'average_rating' => $user->reviews()->avg('rating'),
        ];

        return Inertia::render('Admin/UserDetail', [
            'user' => $user,
            'userStats' => $userStats
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
            'email_verified_at' => 'nullable|date',
        ]);


        $validated['password'] = Hash::make($validated['password']);

        if ($request->email_verified_at) {
            $validated['email_verified_at'] = now();
        }

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
        ]);


        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->subscriptions()->where('status', 'active')->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete user with active subscriptions.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleAdmin(User $user)
    {
        $user->update([
            'role' => $user->role === 'admin' ? 'user' : 'admin',
        ]);


        return redirect()->back()
            ->with('success', 'User admin status updated successfully.');
    }

    public function verifyEmail(User $user)
    {
        $user->update([
            'email_verified_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'User email verified successfully.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'action' => 'required|string|in:verify_email,make_admin,remove_admin,delete'
        ]);

        $users = User::whereIn('id', $validated['user_ids']);

        switch ($validated['action']) {
            case 'verify_email':
                $users->update(['email_verified_at' => now()]);
                $message = 'Users verified successfully.';
                break;
            case 'make_admin':
                $users->update(['role' => 'admin']);
                break;
            case 'remove_admin':
                $users->update(['role' => 'user']);
                break;
            case 'delete':
                $users->delete();
                $message = 'Users deleted successfully.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }
}
