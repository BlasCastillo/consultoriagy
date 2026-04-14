<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupController extends Controller
{
    protected $disk = 'local';
    protected $backupName;

    public function __construct()
    {
        $this->backupName = env('APP_NAME', 'laravel-backup');
    }

    public function index()
    {
        $files = Storage::disk($this->disk)->files($this->backupName);
        $backups = [];

        foreach ($files as $file) {
            if (substr($file, -4) === '.zip') {
                $backups[] = [
                    'file_path' => $file,
                    'file_name' => str_replace($this->backupName . '/', '', $file),
                    'file_size' => $this->humanFilesize(Storage::disk($this->disk)->size($file)),
                    'last_modified' => Carbon::createFromTimestamp(Storage::disk($this->disk)->lastModified($file))->format('d/m/Y H:i:s'),
                ];
            }
        }

        // Ordenar más reciente primero
        $backups = array_reverse($backups);

        return view('admin.backups', compact('backups'));
    }

    public function create()
    {
        try {
            // 1. Limpieza explicita de carpeta temporal
            $tempDir = config('backup.backup.temporary_directory') ?? storage_path('app/backup-temp');
            if (\Illuminate\Support\Facades\File::exists($tempDir)) {
                \Illuminate\Support\Facades\File::cleanDirectory($tempDir);
            }

            // Guardar total de archivos antes de ejecutar
            $filesBefore = count(Storage::disk($this->disk)->files($this->backupName));

            // 2. Ejecutar el respaldo
            Artisan::call('backup:run'); 

            $output = Artisan::output();
            $filesAfter = count(Storage::disk($this->disk)->files($this->backupName));

            // 3. Verificar generación de nuevo archivo
            if ($filesAfter <= $filesBefore) {
                return redirect()->route('backups.index')->with('error', 'El proceso terminó pero el archivo ZIP no fue creado. Detalles: ' . substr($output, 0, 150));
            }
            
            return redirect()->route('backups.index')->with('success', 'Backup generado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('backups.index')->with('error', 'Error crítico al generar backup: ' . $e->getMessage());
        }
    }

    public function download($fileName)
    {
        $file = $this->backupName . '/' . $fileName;

        if (Storage::disk($this->disk)->exists($file)) {
            return Storage::disk($this->disk)->download($file);
        }

        return redirect()->route('backups.index')->with('error', 'El archivo no existe.');
    }

    public function destroy($fileName)
    {
        $file = $this->backupName . '/' . $fileName;

        if (Storage::disk($this->disk)->exists($file)) {
            Storage::disk($this->disk)->delete($file);
            return redirect()->route('backups.index')->with('success', 'Backup eliminado exitosamente.');
        }

        return redirect()->route('backups.index')->with('error', 'El archivo no existe o ya fue eliminado.');
    }

    private function humanFilesize($bytes, $decimals = 2)
    {
        $size = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }
}
