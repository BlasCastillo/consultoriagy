<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Solicitud;

class SolicitudRechazadaNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public $solicitud;
    public $motivo_rechazo;

    /**
     * Create a new notification instance.
     */
    public function __construct(Solicitud $solicitud, $motivo_rechazo = 'No especificado')
    {
        $this->solicitud = $solicitud;
        $this->motivo_rechazo = $motivo_rechazo;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'mensaje' => "Su solicitud '{$this->solicitud->titulo_acto}' ha sido devuelta.",
            'motivo' => $this->motivo_rechazo,
            'url' => route('solicitudes.show', $this->solicitud->id, false)
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'mensaje' => "Su solicitud '{$this->solicitud->titulo_acto}' ha sido devuelta.",
            'motivo' => $this->motivo_rechazo,
            'url' => route('solicitudes.show', $this->solicitud->id, false)
        ]);
    }
}
