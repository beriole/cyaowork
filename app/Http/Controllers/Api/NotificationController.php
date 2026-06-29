<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** GET /notifications — liste paginée (data + lu/non-lu). */
    public function index(Request $request)
    {
        $items = $request->user()->notifications()->paginate(20)->through(fn ($n) => [
            'id' => $n->id,
            'read' => $n->read_at !== null,
            'created_at' => $n->created_at,
            ...$n->data,
        ]);

        return response()->json($items);
    }

    /** GET /notifications/unread-count — pour le badge. */
    public function unreadCount(Request $request)
    {
        return response()->json(['count' => $request->user()->unreadNotifications()->count()]);
    }

    /** POST /notifications/{id}/read */
    public function markRead(Request $request, string $id)
    {
        $request->user()->notifications()->where('id', $id)->first()?->markAsRead();

        return response()->json(['ok' => true]);
    }

    /** POST /notifications/read-all */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}
