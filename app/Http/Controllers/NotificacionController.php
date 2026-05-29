<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function leer($id)
    {
        $notificacion = auth()->user()->notifications()->findOrFail($id);
        
        $notificacion->markAsRead();
        
        return redirect($notificacion->data['url'] ?? route('dashboard'));
    }
}
