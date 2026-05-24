<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DownloadLocalAssets extends Command
{
    protected $signature = 'app:download-assets';
    protected $description = 'Descarga todas las dependencias externas (CDNs) localmente a public/assets/vendor';

    public function handle()
    {
        $this->info('Iniciando descarga de recursos locales...');

        $baseDir = public_path('assets/vendor');
        $dirs = [
            'css' => $baseDir . '/css',
            'js' => $baseDir . '/js',
            'fonts' => $baseDir . '/fonts',
            'webfonts' => $baseDir . '/webfonts',
        ];

        foreach ($dirs as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
        }

        // 1. Chart.js
        $this->info('Descargando Chart.js...');
        $chartJsUrl = 'https://cdn.jsdelivr.net/npm/chart.js';
        $chartJsContent = @file_get_contents($chartJsUrl);
        if ($chartJsContent) {
            File::put($dirs['js'] . '/chart.min.js', $chartJsContent);
            $this->line('- chart.min.js guardado.');
        } else {
            $this->error('- Fallo al descargar Chart.js');
        }

        // 2. FontAwesome
        $this->info('Descargando FontAwesome 6.5.2...');
        $faCssUrl = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css';
        $faCssContent = @file_get_contents($faCssUrl);
        if ($faCssContent) {
            File::put($dirs['css'] . '/all.min.css', $faCssContent);
            $this->line('- all.min.css guardado.');
        } else {
            $this->error('- Fallo al descargar FontAwesome CSS');
        }

        // FontAwesome Webfonts
        $faWebfonts = [
            'fa-solid-900.woff2', 'fa-solid-900.ttf',
            'fa-regular-400.woff2', 'fa-regular-400.ttf',
            'fa-brands-400.woff2', 'fa-brands-400.ttf',
            'fa-v4compatibility.woff2', 'fa-v4compatibility.ttf'
        ];
        $faBaseUrl = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/';
        foreach ($faWebfonts as $font) {
            $fontContent = @file_get_contents($faBaseUrl . $font);
            if ($fontContent) {
                File::put($dirs['webfonts'] . '/' . $font, $fontContent);
                $this->line("- $font guardado.");
            }
        }

        // 3. Google Fonts / Bunny Fonts (Figtree)
        $this->info('Descargando Figtree Fonts...');
        $figtreeUrl = 'https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap';
        
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $figtreeCss = @file_get_contents($figtreeUrl, false, $context);
        
        if ($figtreeCss) {
            preg_match_all('/url\((https:\/\/[^)]+)\)/', $figtreeCss, $matches);
            $urls = $matches[1] ?? [];
            
            $i = 1;
            foreach ($urls as $url) {
                $fontContent = @file_get_contents($url, false, $context);
                if ($fontContent) {
                    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                    $ext = $ext ? $ext : 'woff2';
                    $fileName = "figtree-{$i}.{$ext}";
                    File::put($dirs['fonts'] . '/' . $fileName, $fontContent);
                    $figtreeCss = str_replace($url, "../fonts/$fileName", $figtreeCss);
                    $this->line("- $fileName guardado.");
                    $i++;
                }
            }
            File::put($dirs['css'] . '/figtree.css', $figtreeCss);
            $this->line('- figtree.css procesado y guardado.');
        } else {
            $this->error('- Fallo al descargar Figtree CSS');
        }

        $this->info('Descarga de recursos completada con éxito.');
    }
}
