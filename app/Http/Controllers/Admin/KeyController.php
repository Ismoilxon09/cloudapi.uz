<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProxyKey;
use Illuminate\Http\Request;

class KeyController extends Controller
{
    public function index(Request $request)
    {
        $query = ProxyKey::with('user');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('q')) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $keys = $query->orderByDesc('created_at')->paginate(50)->withQueryString();

        return view('admin.keys.index', compact('keys'));
    }
}