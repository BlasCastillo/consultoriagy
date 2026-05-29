<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Gaceta;

class GacetaAsignadaNotification extends Notification implements ShouldBroadcast
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
            'mensaje' => 'Se te ha asignado la Gaceta Nro. ' . $this->gaceta->numero . ' para escaneo.',
            'url' => route('gacetas.digitalizador') // We will create this route
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'gaceta_id' => $this->gaceta->id,
            'mensaje' => 'Se te ha asignado la Gaceta Nro. ' . $this->gaceta->numero . ' para escaneo.',
            'url' => route('gacetas.digitalizador')
        ]);
    }
}
