<?php

namespace App\Http\Controllers;

use App\Models\EducationProgram;
use App\Models\News;
use App\Models\SantriRegistration;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $newRegistrantsCount = SantriRegistration::where('status', 'pending')->count();
        $totalSantri         = SantriRegistration::where('status', 'accepted')->count();
        $activeProgramsCount = EducationProgram::where('is_active', true)->count();
        $totalNews           = News::count();

        $pendingRegistrants = SantriRegistration::where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // Semua pengguna admin untuk panel "Tim Admin" di dashboard
        $adminUsers = User::query()
            ->orderByRaw("CASE WHEN role = 'super_admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'avatar']);

        return view('dashboard.index', compact(
            'newRegistrantsCount',
            'totalSantri',
            'activeProgramsCount',
            'totalNews',
            'pendingRegistrants',
            'adminUsers'
        ));
    }
}
