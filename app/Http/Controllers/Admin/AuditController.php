<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminLog::with('admin');

        if ($admin = $request->get('admin_id')) {
            $query->where('admin_id', $admin);
        }

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        $logs = $query->orderByDesc('created_at')->paginate(50);

        $actions = AdminLog::select('action')->distinct()->pluck('action');

        return view('admin.audit.index', compact('logs', 'actions'));
    }
}