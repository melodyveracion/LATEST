<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function indexForCurrentUser()
    {
        $user = Auth::user();

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->get();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $view = $user->role === 'bac'
            ? 'bac.notifications.index'
            : 'unit.notifications.index';

        return view($view, compact('notifications'));
    }
}
