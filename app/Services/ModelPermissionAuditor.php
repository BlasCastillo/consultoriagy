<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModelPermissionAuditor
{
    /**
     * Escanea el directorio app/Models y devuelve una lista de nombres de modelos.
     *
     * @return array<string>
     */
    public static function getModels(): array
    {
        $modelsPath = app_path('Models');
        $models = [];

        if (File::exists($modelsPath)) {
            $files = File::allFiles($modelsPath);

            foreach ($files as $file) {
                // Obtener el nombre del archivo sin la extensión .php
                $modelName = $file->getFilenameWithoutExtension();
                $models[] = $modelName;
            }
        }

        return $models;
    }

    /**
     * Genera la matriz de permisos estándar agrupada por modelo.
     * Devuelve: ['User' => ['create User', 'read User', 'update User', 'delete User'], ...]
     *
     * @return array<string, array<string>>
     */
    public static function getPermissionsMatrix(): array
    {
        $models = static::getModels();
        $matrix = [];

        foreach ($models as $model) {
            $matrix[$model] = [
                "read {$model}",
                "create {$model}",
                "update {$model}",
                "delete {$model}",
            ];
        }

        return $matrix;
    }
}
