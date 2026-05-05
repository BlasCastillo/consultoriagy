<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    protected $disk = 'local';
    protected $backupName;

    public function __construct()
    {
        $this->backupName = env('APP_NAME', 'SGCJ');
    }

    public function index()
    {
        $directory = $this->backupName;
        $files = Storage::disk($this->disk)->files($directory);
        $backups = [];

        foreach ($files as $file) {
            if (str_ends_with($file, '.sql') || str_ends_with($file, '.zip')) {
                $backups[] = [
                    'file_path' => $file,
                    'file_name' => basename($file),
                    'file_size' => $this->humanFilesize(Storage::disk($this->disk)->size($file)),
                    'last_modified' => Carbon::createFromTimestamp(Storage::disk($this->disk)->lastModified($file))->format('d/m/Y H:i:s'),
                ];
            }
        }

        return view('admin.backups', ['backups' => array_reverse($backups)]);
    }
    public function create()
    {
        try {
            $storagePath = storage_path("app/{$this->backupName}");
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0777, true);
            }

            $fileName = "backup-" . date('Y-m-d_H-i-s') . ".sql";
            $fullOutputPath = $storagePath . DIRECTORY_SEPARATOR . $fileName;

            $dumpBinary = trim(str_replace(['"', "'"], '', env('DUMP_BINARY_PATH')));
            $pgDump = $dumpBinary . DIRECTORY_SEPARATOR . 'pg_dump.exe';

            // 3. COMANDO REFORZADO PARA CONTRASEÑAS CON CARACTERES ESPECIALES
            // Usamos comillas dobles para la variable de entorno PGPASSWORD
            $command = "set \"PGPASSWORD=" . env('DB_PASSWORD') . "\" && " .
                "\"{$pgDump}\" -U " . env('DB_USERNAME') . " -h " . env('DB_HOST', '127.0.0.1') . " -p " . env('DB_PORT', '5432') . " -f \"{$fullOutputPath}\" " . env('DB_DATABASE') . " 2>&1";

            exec($command, $output, $resultCode);

            if ($resultCode === 0) {
                return redirect()->route('backups.index')->with('success', '¡Sincronización exitosa! Backup generado.');
            }

            $error = implode("\n", $output);
            Log::error("Fallo de backup: " . $error);
            return redirect()->route('backups.index')->with('error', 'Error del sistema: ' . $error);

        }
        catch (\Exception $e) {
            return redirect()->route('backups.index')->with('error', 'Excepción: ' . $e->getMessage());
        }
    }
    public function download($fileName)
    {
        $file = $this->backupName . '/' . $fileName;
        if (Storage::disk($this->disk)->exists($file)) {
            return Storage::disk($this->disk)->download($file);
        }
        return redirect()->back()->with('error', 'Archivo no encontrado.');
    }

    public function destroy($fileName)
    {
        $file = $this->backupName . '/' . $fileName;
        if (Storage::disk($this->disk)->exists($file)) {
            Storage::disk($this->disk)->delete($file);
            return redirect()->back()->with('success', 'Respaldo eliminado.');
        }
        return redirect()->back()->with('error', 'No se pudo eliminar.');
    }

    private function humanFilesize($bytes, $decimals = 2)
    {
        $size = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . ($size[$factor] ?? 'B');
    }
}