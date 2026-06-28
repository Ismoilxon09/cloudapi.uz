<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ProxyUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $query = ProxyUsage::where('user_id', Auth::id())->orderByDesc('created_at');

        // Filter by model
        if ($model = $request->get('model')) {
            $query->where('model', $model);
        }

        // Filter by status
        if ($status = $request->get('status')) {
            if ($status === 'success') {
                $query->whereBetween('status_code', [200, 299]);
            } elseif ($status === 'error') {
                $query->where('status_code', '>=', 400);
            }
        }

        $logs = $query->paginate(50)->withQueryString();

        // Available models for filter
        $models = ProxyUsage::where('user_id', Auth::id())
            ->select('model')
            ->distinct()
            ->orderBy('model')
            ->pluck('model');

        return view('dashboard.logs.index', compact('logs', 'models'));
    }
}