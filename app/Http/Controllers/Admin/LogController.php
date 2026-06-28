<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProxyUsage;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = ProxyUsage::with('user');

        if ($model = $request->get('model')) {
            $query->where('model', $model);
        }

        if ($status = $request->get('status')) {
            if ($status === 'success') {
                $query->whereBetween('status_code', [200, 299]);
            } else {
                $query->where('status_code', '>=', 400);
            }
        }

        $logs = $query->orderByDesc('created_at')->paginate(100)->withQueryString();

        $models = ProxyUsage::select('model')->distinct()->orderBy('model')->pluck('model');

        return view('admin.logs.index', compact('logs', 'models'));
    }
}