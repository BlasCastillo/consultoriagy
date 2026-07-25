<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Gaceta;

class MovimientoArchivoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $gaceta;
    public $accion;
    public $usuarioResponsable;

    /**
     * Create a new notification instance.
     */
    public function __construct(Gaceta $gaceta, string $accion, string $usuarioResponsable)
    {
        $this->gaceta = $gaceta;
        $this->accion = $accion;
        $this->usuarioResponsable = $usuarioResponsable;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'titulo' => "Movimiento de Archivo: {$this->accion}",
            'mensaje' => "El usuario {$this->usuarioResponsable} ha registrado un(a) {$this->accion} para la Gaceta {$this->gaceta->numero} / {$this->gaceta->anio}.",
            'gaceta_id' => $this->gaceta->id,
            'tipo' => $this->accion == 'Préstamo' ? 'warning' : 'success'
        ];
    }
}
