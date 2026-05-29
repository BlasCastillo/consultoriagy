<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Gaceta;

class GacetaPorAprobarNotification extends Notification implements ShouldBroadcast
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
            'mensaje' => 'La Gaceta Nro. ' . $this->gaceta->numero . ' está lista para revisión final y publicación.',
            'url' => route('gacetas.aprobar', $this->gaceta->id)
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'gaceta_id' => $this->gaceta->id,
            'mensaje' => 'La Gaceta Nro. ' . $this->gaceta->numero . ' está lista para revisión final y publicación.',
            'url' => route('gacetas.aprobar', $this->gaceta->id)
        ]);
    }
}
