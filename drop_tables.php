<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    Schema::table('gacetas', function(Blueprint $table){
        $table->dropForeign(['gobernador_id']);
    });
} catch(\Exception $e){}

try {
    Schema::table('gacetas', function(Blueprint $table){
        $table->dropColumn('gobernador_id');
    });
} catch(\Exception $e){}

Schema::dropIfExists('gobernadores');
Schema::dropIfExists('titulos');
echo "Tables dropped successfully\n";
