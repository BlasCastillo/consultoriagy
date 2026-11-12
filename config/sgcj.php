<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Límites de carga de archivos
    |--------------------------------------------------------------------------
    |
    | Valores en kilobytes (1 MB = 1024 KB) usados por la regla de validación
    | "max". Deben quedar siempre por debajo de upload_max_filesize y
    | post_max_size definidos en php.ini.
    |
    */

    'archivos' => [

        // PDFs de gacetas y actos administrativos (histórico incluido).
        'max_pdf_kb' => (int) env('SGCJ_MAX_PDF_KB', 512000), // 500 MB

        // Imágenes de fichas y evidencias.
        'max_imagen_kb' => (int) env('SGCJ_MAX_IMAGEN_KB', 10240), // 10 MB

    ],

];
