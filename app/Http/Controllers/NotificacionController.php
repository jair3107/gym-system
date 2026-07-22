<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    /**
     * Centro de notificaciones del usuario.
     */
    public function index(Request $request): View
    {
        $notificaciones = $request->user()
            ->notifications()
            ->paginate(20);

        return view('notificaciones.index', [
            'notificaciones' => $notificaciones,
        ]);
    }

    /**
     * Marcar una notificación como leída.
     */
    public function marcarLeida(Request $request, string $id): RedirectResponse
    {
        $notificacion = $request->user()->notifications()->findOrFail($id);
        $notificacion->markAsRead();

        return back();
    }

    /**
     * Marcar todas como leídas.
     */
    public function marcarTodas(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'Notificaciones marcadas como leídas.');
    }
}
