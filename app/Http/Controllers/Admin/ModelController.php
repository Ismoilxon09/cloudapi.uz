<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\AiModel;
use App\Services\OpenRouter\ModelSyncService;
use Illuminate\Http\Request;

class ModelController extends Controller
{
    public function index(Request $request)
    {
        $query = AiModel::query();

        if ($filter = $request->get('filter')) {
            match($filter) {
                'active' => $query->where('active', true),
                'inactive' => $query->where('active', false),
                'free' => $query->where('is_free', true),
                'featured' => $query->where('is_featured', true),
                default => null,
            };
        }

        if ($search = $request->get('q')) {
            $query->where(function($q) use ($search) {
                $q->where('model_id', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%");
            });
        }

        $models = $query->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('display_name')
            ->paginate(50)->withQueryString();

        $counts = [
            'all' => AiModel::count(),
            'active' => AiModel::where('active', true)->count(),
            'inactive' => AiModel::where('active', false)->count(),
            'free' => AiModel::where('is_free', true)->count(),
            'featured' => AiModel::where('is_featured', true)->count(),
        ];

        return view('admin.models.index', compact('models', 'counts'));
    }

    public function sync(ModelSyncService $service)
    {
        try {
            $result = $service->syncAll();

            AdminLog::record('models_synced', null, "OpenRouter sync: yangi {$result['created']}, yangilandi {$result['updated']}", $result);

            return back()->with('success',
                "Sync yakunlandi: yangi {$result['created']}, yangilandi {$result['updated']}, jami {$result['total']}"
            );
        } catch (\Exception $e) {
            return back()->with('error', "Sync xato: " . $e->getMessage());
        }
    }

    public function syncGroq()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('groq:sync');
            $output = \Illuminate\Support\Facades\Artisan::output();

            AdminLog::record('groq_synced', null, "Groq sync bajarildi", ['output' => substr($output, 0, 500)]);

            return back()->with('success', "Groq sync yakunlandi! /admin/models'da ko'rishingiz mumkin.");
        } catch (\Exception $e) {
            return back()->with('error', "Groq sync xato: " . $e->getMessage());
        }
    }

    public function toggle(AiModel $model)
    {
        $model->update(['active' => !$model->active]);
        AdminLog::record('model_toggled', $model, "{$model->display_name} → " . ($model->active ? 'faollashtirildi' : 'o\'chirildi'));
        return back();
    }

    public function feature(AiModel $model)
    {
        $model->update(['is_featured' => !$model->is_featured]);
        AdminLog::record('model_featured', $model, "{$model->display_name} → " . ($model->is_featured ? 'featured' : 'olib tashlandi'));
        return back();
    }

    public function updateMargin(Request $request, AiModel $model)
    {
        $validated = $request->validate([
            'margin_percent' => 'required|numeric|min:0|max:200',
        ]);

        $oldMargin = $model->margin_percent;
        $model->update(['margin_percent' => $validated['margin_percent']]);
        AdminLog::record('margin_updated', $model, "{$model->display_name}: {$oldMargin}% → {$validated['margin_percent']}%");

        return back()->with('success', "Marja yangilandi");
    }
}