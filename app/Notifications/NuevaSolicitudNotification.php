<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Gaceta;

class NuevaSolicitudNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $gaceta;

    public function __construct(Gaceta $gaceta)
    {
        $this->gaceta = $gaceta;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'gaceta_id' => $this->gaceta->id,
            'mensaje' => 'Nueva solicitud de Gaceta pendiente por evaluar.',
            'url' => route('gacetas.checklist', $this->gaceta->id)
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'gaceta_id' => $this->gaceta->id,
            'mensaje' => 'Nueva solicitud de Gaceta pendiente por evaluar.',
            'url' => route('gacetas.checklist', $this->gaceta->id)
        ]);
    }
}
