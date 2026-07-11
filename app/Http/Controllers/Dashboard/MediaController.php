<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ChatAttachment;
use Illuminate\Http\Request;

/**
 * Media kutubxonasi — foydalanuvchi chatda yaratgan rasm/video/audiolar.
 */
class MediaController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type'); // image | video | audio | null(all)

        $query = ChatAttachment::where('user_id', auth()->id())
            ->whereHas('message', fn ($q) => $q->where('role', 'assistant'))
            ->with('message:id,model_id,session_id')
            ->orderByDesc('id');

        if (in_array($type, ['image', 'video', 'audio'], true)) {
            $query->where('type', $type);
        }

        $media = $query->paginate(48)->withQueryString();

        $counts = [
            'all'   => ChatAttachment::where('user_id', auth()->id())->whereHas('message', fn ($q) => $q->where('role', 'assistant'))->count(),
            'image' => ChatAttachment::where('user_id', auth()->id())->where('type', 'image')->whereHas('message', fn ($q) => $q->where('role', 'assistant'))->count(),
            'video' => ChatAttachment::where('user_id', auth()->id())->where('type', 'video')->whereHas('message', fn ($q) => $q->where('role', 'assistant'))->count(),
            'audio' => ChatAttachment::where('user_id', auth()->id())->where('type', 'audio')->whereHas('message', fn ($q) => $q->where('role', 'assistant'))->count(),
        ];

        return view('dashboard.media.index', compact('media', 'counts', 'type'));
    }
}
