<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModelsController extends Controller
{
    public function index(Request $request)
    {
        // Faqat noyob slug'lar — har slug uchun eng yuqori priority (eng kichik raqam)
        $query = AiModel::where('active', true)
            ->whereIn('id', function($q) {
                $q->selectRaw('id')
                    ->from('ai_models as inner_m')
                    ->where('active', true)
                    ->whereRaw('inner_m.priority = (
                        SELECT MIN(priority) FROM ai_models
                        WHERE slug = inner_m.slug AND active = 1
                    )')
                    ->groupBy('slug', 'id');
            });

        // Search
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('model_id', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Provider filter
        if ($provider = $request->get('provider')) {
            $query->where('model_id', 'like', "{$provider}/%");
        }

        // Category filter
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        // Capability filter
        if ($capability = $request->get('capability')) {
            $query->whereJsonContains('capabilities', $capability);
        }

        // Free filter
        if ($request->boolean('free')) {
            $query->where('is_free', true);
        }

        // Featured filter
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Sort
        $sort = $request->get('sort', 'featured');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('cost_input_usd', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('cost_input_usd', 'desc');
                break;
            case 'context':
                $query->orderByDesc('context_length');
                break;
            case 'name':
                $query->orderBy('display_name');
                break;
            case 'featured':
            default:
                $query->orderByDesc('is_featured')
                      ->orderBy('sort_order')
                      ->orderBy('display_name');
                break;
        }

        $models = $query->paginate(24)->withQueryString();

        // Filter options
        $providers = AiModel::where('active', true)
            ->selectRaw("SUBSTRING_INDEX(model_id, '/', 1) as provider, COUNT(*) as count")
            ->groupBy(DB::raw("SUBSTRING_INDEX(model_id, '/', 1)"))
            ->orderByDesc('count')
            ->get();

        $categories = AiModel::where('active', true)
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();

        $totalCount = AiModel::where('active', true)->count();
        $freeCount = AiModel::where('active', true)->where('is_free', true)->count();

        return view('models.index', compact(
            'models', 'providers', 'categories', 'totalCount', 'freeCount'
        ));
    }

    public function show(string $modelId)
    {
        // Allow slash in URL: openai/gpt-4o
        $model = AiModel::where('model_id', $modelId)->firstOrFail();

        // Related models from same provider
        $provider = explode('/', $modelId)[0];
        $related = AiModel::where('active', true)
            ->where('model_id', '!=', $modelId)
            ->where('model_id', 'like', "{$provider}/%")
            ->limit(6)
            ->get();

        return view('models.show', compact('model', 'related'));
    }
}