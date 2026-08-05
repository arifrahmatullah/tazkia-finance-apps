<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = auth()->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function recent()
    {
        $user = auth()->user();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $user->notifications()->latest()->limit(8)->get()->map(function ($n) {
                return [
                    'id'         => $n->id,
                    'read'       => !is_null($n->read_at),
                    'title'      => $n->data['title'] ?? '-',
                    'reference'  => $n->data['reference'] ?? null,
                    'url'        => $n->data['url'] ?? '#',
                    'type'       => $n->data['type'] ?? null,
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    public function markRead(string $notification)
    {
        $n = auth()->user()->notifications()->where('id', $notification)->firstOrFail();
        $n->markAsRead();

        return response()->json(['ok' => true, 'url' => $n->data['url'] ?? '/notifications']);
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }
}
