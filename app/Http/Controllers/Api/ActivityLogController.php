<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs (Super Admin only).
     */
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya Super Admin yang memiliki hak akses ini.'
            ], 403);
        }

        $logs = ActivityLog::with('user:id,name,username')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs
        ], 200);
    }
}
