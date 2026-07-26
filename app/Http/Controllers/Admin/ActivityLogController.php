<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AdminActivityLog::query()->latest('created_at');

        // Filter berdasarkan aksi
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        // Filter berdasarkan pencarian (nama user / deskripsi)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('admin.activity-logs.index', compact('logs'));
    }
}
