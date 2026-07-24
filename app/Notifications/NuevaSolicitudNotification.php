<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Solicitud;

class NuevaSolicitudNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public $solicitud;

    public function __construct(Solicitud $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'solicitud_id' => $this->solicitud->id,
            'mensaje' => "Nueva solicitud '{$this->solicitud->titulo_acto}' de {$this->solicitud->institucion->nombre}",
            'url' => route('solicitudes.show', $this->solicitud->id, false)
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'solicitud_id' => $this->solicitud->id,
            'mensaje' => "Nueva solicitud '{$this->solicitud->titulo_acto}' de {$this->solicitud->institucion->nombre}",
            'url' => route('solicitudes.show', $this->solicitud->id, false)
        ]);
    }
}
