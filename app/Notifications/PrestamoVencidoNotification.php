<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PrestamoArchivo;

class PrestamoVencidoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $prestamo;

    /**
     * Create a new notification instance.
     */
    public function __construct(PrestamoArchivo $prestamo)
    {
        $this->prestamo = $prestamo;
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
            'titulo' => 'Préstamo Vencido: Gaceta ' . $this->prestamo->gaceta->numero,
            'mensaje' => "El solicitante {$this->prestamo->solicitante_nombres} (Contacto: {$this->prestamo->telefono}) no ha devuelto la gaceta. Fecha límite era: " . $this->prestamo->fecha_devolucion_estimada->format('d/m/Y'),
            'prestamo_id' => $this->prestamo->id,
            'tipo' => 'danger'
        ];
    }
}
