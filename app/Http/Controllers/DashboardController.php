<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\UserGroupAssignment;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        $activeGroupAssignments = UserGroupAssignment::with('deviceGroup')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        return view('dashboard', [
            'user' => $user,
            'role' => $user->role,
            'isAdmin' => $user->isAdmin(),
            'isManager' => $user->isManager(),
            'isUser' => $user->isUser(),
            'activeGroupAssignments' => $activeGroupAssignments,
        ]);
    }

    /**
     * Admin dashboard
     */
    public function admin(): View
    {
        return view('dashboard.admin');
    }

    /**
     * Manager dashboard
     */
    public function manager(): View
    {
        return view('dashboard.manager');
    }

    /**
     * User dashboard
     */
    public function user(): View
    {
        return view('dashboard.user');
    }
}
