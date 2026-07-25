<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrestamoArchivo;
use App\Models\User;
use App\Notifications\PrestamoVencidoNotification;
use Illuminate\Support\Facades\Notification;

class VerificarPrestamos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:verificar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica préstamos activos que superaron la fecha de devolución y emite alertas.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vencidos = PrestamoArchivo::where('estado_prestamo', 'Activo')
            ->whereDate('fecha_devolucion_estimada', '<', now()->toDateString())
            ->get();

        if ($vencidos->isEmpty()) {
            $this->info('Verificación completada. No se encontraron préstamos vencidos.');
            return;
        }

        $usuariosANotificar = User::role(['Super Admin', 'Coordinador General', 'Director de Consultoría Jurídica'])->get();

        foreach ($vencidos as $prestamo) {
            $prestamo->update(['estado_prestamo' => 'Vencido']);
            Notification::send($usuariosANotificar, new PrestamoVencidoNotification($prestamo));
        }

        $this->info('Verificación completada. ' . $vencidos->count() . ' préstamos vencidos detectados.');
    }
}
