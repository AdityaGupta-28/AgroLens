<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Farmer;
use App\Models\LandHolding;
use App\Models\Region;
use App\Models\Survey;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'stats' => [
                'users' => User::count(),
                'superadmins' => User::where('role', UserRole::SuperAdmin)->count(),
                'officers' => User::where('role', UserRole::GovernmentOfficer)->count(),
                'viewers' => User::where('role', UserRole::PublicViewer)->count(),
                'farmers' => Farmer::count(),
                'districts' => Region::districts()->count(),
                'holdings' => LandHolding::count(),
                'surveys' => Survey::where('is_active', true)->count(),
                'api_keys' => User::whereNotNull('api_token')->count(),
            ],
            'recentActivity' => ActivityLog::query()
                ->with('user:id,name,email')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
