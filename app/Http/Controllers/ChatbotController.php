<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Gaceta;
use App\Models\Solicitud;
use App\Models\ActoAdministrativo;
use App\Models\Institucion;
use App\Models\Gobernador;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = trim($request->input('message'));
        $msgLower = mb_strtolower($userMessage, 'UTF-8');
        $user = auth()->user();
        $esEnteExterno = $user && !empty($user->institucion_id);
        $institucionUsuario = ($esEnteExterno && $user->institucion) ? $user->institucion : null;

        // 1. Bloqueo estricto de exposición de datos de usuarios, cuentas o correos
        if (preg_match('/\b(usuarios?|correos?|emails?|cuentas?|contraseñas?|claves?|logins?)\b/iu', $userMessage)) {
            return response()->json([
                'reply' => 'Por políticas de seguridad de la Consultoría Jurídica, el asistente no suministra datos de cuentas, correos electrónicos ni usuarios de ninguna institución.'
            ]);
        }

        // 2. Extracción de palabras clave relevantes del mensaje
        $palabrasIgnoradas = [
            'gaceta', 'gacetas', 'oficial', 'oficiales', 'ley', 'leyes', 'decreto', 'decretos',
            'resolucion', 'resolución', 'resoluciones', 'solicitud', 'solicitudes', 'tramite',
            'trámite', 'tramites', 'trámites', 'acto', 'actos', 'administrativo', 'administrativos',
            'institucion', 'institución', 'instituciones', 'ente', 'entes', 'sistema', 'consulta',
            'consultar', 'cuantas', 'cuántas', 'cuantos', 'cuántos', 'tiene', 'tienen', 'hay',
            'cual', 'cuál', 'cuales', 'cuáles', 'quien', 'quién', 'como', 'cómo', 'donde', 'dónde',
            'cuando', 'cuándo', 'actual', 'presidente', 'director', 'gobernador', 'estado', 'estatus',
            'yaracuy', 'para', 'por', 'con', 'sin', 'sobre', 'entre', 'hacia', 'desde', 'hasta',
            'del', 'las', 'los', 'una', 'uno', 'unos', 'unas', 'este', 'esta', 'estos', 'estas',
            'ese', 'esa', 'esos', 'esas', 'busco', 'necesito', 'quiero', 'saber', 'dime', 'mostrar',
            'ver', 'informacion', 'información', 'numero', 'número', 'año', 'anio', 'publicada', 'publicadas'
        ];

        $tokens = preg_split('/[^\p{L}\p{N}\-]+/u', $msgLower, -1, PREG_SPLIT_NO_EMPTY);
        $keywords = [];
        foreach ($tokens as $tok) {
            if (mb_strlen($tok, 'UTF-8') >= 2 && !in_array($tok, $palabrasIgnoradas, true)) {
                $keywords[] = $tok;
            }
        }
        $keywords = array_values(array_unique($keywords));

        $contextoSecciones = [];
        $gacetasRelevantesParaPdf = collect();

        // Contexto del usuario autenticado
        if ($esEnteExterno && $institucionUsuario) {
            $contextoSecciones[] = "PERFIL DEL USUARIO AUTENTICADO:\n- Rol: Representante Institucional de {$institucionUsuario->nombre} (" . ($institucionUsuario->siglas ?: 'Sin siglas') . ").\n- Restricción de Privacidad: Para solicitudes en trámite (no publicadas), SOLO tiene acceso a las solicitudes de su propia institución ({$institucionUsuario->nombre}). Las Gacetas Oficiales Publicadas, sus Actos Administrativos, conteo de gacetas por institución y Gobernadores son públicos.";
        } else {
            $contextoSecciones[] = "PERFIL DEL USUARIO AUTENTICADO:\n- Personal Interno de la Consultoría Jurídica con acceso de consulta a gacetas y solicitudes institucionales (sin exposición de cuentas de usuario).";
        }

        // 2.5. Estadísticas Globales del Sistema y Catálogo Completo de Instituciones
        $todasInstituciones = Institucion::with('tipoInstitucion')->orderBy('nombre')->get();
        $totalInstituciones = $todasInstituciones->count();
        $institucionesActivas = \App\Models\User::whereNotNull('institucion_id')->distinct('institucion_id')->count('institucion_id');
        $totalGacetasPublicadas = Gaceta::where('estado', Gaceta::ESTADO_PUBLICADA)->count();

        $resumenGlobal = "RESUMEN ESTADÍSTICO OFICIAL DEL SISTEMA SGCJ:\n"
            . "- Total de Instituciones registradas en el sistema: {$totalInstituciones} instituciones.\n"
            . "- Instituciones activas (con representante asignado): {$institucionesActivas} instituciones.\n"
            . "- Total de Gacetas Oficiales Publicadas en el sistema: {$totalGacetasPublicadas} gacetas.\n";

        if (!$esEnteExterno) {
            $totalSolicitudesAtendidas = Solicitud::where('estado', 'Publicada')->count();
            $totalSolicitudesPendientes = Solicitud::where('estado', 'Pendiente')->count();
            $totalPoasAnio = \App\Models\Poa::where('anio', now()->year)->count();
            $totalPrestamosActivos = \App\Models\PrestamoArchivo::where('estado_prestamo', '!=', 'Devuelto')->count();
            $resumenGlobal .= "- Solicitudes atendidas (publicadas): {$totalSolicitudesAtendidas} | Solicitudes pendientes: {$totalSolicitudesPendientes}.\n"
                . "- POAs del año " . now()->year . ": {$totalPoasAnio} | Préstamos físicos activos en Archivo: {$totalPrestamosActivos}.\n";
        }

        // Si el usuario pregunta por instituciones, entes, cuántos o cuáles están registrados, inyectar el catálogo completo
        $preguntaPorInstituciones = (bool) preg_match('/\b(institucion|instituciones|ente|entes|organismo|organismos|fundacion|fundaciones|registrad[oa]s?|cuant[oa]s?|cuales|listado|lista|total)\b/iu', $userMessage);
        if ($preguntaPorInstituciones && $todasInstituciones->isNotEmpty()) {
            $resumenGlobal .= "\nCATÁLOGO COMPLETO DE LAS {$totalInstituciones} INSTITUCIONES REGISTRADAS EN EL ESTADO YARACUY:\n";
            foreach ($todasInstituciones as $idx => $instCat) {
                $numLista = $idx + 1;
                $siglaTxt = $instCat->siglas ? " ({$instCat->siglas})" : "";
                $tipoTxt = $instCat->tipoInstitucion ? " | Tipo: {$instCat->tipoInstitucion->nombre}" : "";
                $estatusTxt = $instCat->estatus ? "Activa en catálogo" : "Inactiva";
                $resumenGlobal .= "{$numLista}. {$instCat->nombre}{$siglaTxt}{$tipoTxt} [{$estatusTxt}]\n";
            }
        }
        $contextoSecciones[] = $resumenGlobal;

        // 3. Detección de Instituciones específicas mencionadas en el mensaje
        $institucionesDetectadas = collect();

        foreach ($todasInstituciones as $inst) {
            $siglasLower = mb_strtolower(trim((string) $inst->siglas), 'UTF-8');
            $nombreLower = mb_strtolower(trim((string) $inst->nombre), 'UTF-8');

            if ($siglasLower !== '' && mb_strlen($siglasLower, 'UTF-8') >= 2 && Str::contains($msgLower, $siglasLower)) {
                $institucionesDetectadas->push($inst);
                continue;
            }

            if ($nombreLower !== '' && Str::contains($msgLower, $nombreLower)) {
                $institucionesDetectadas->push($inst);
                continue;
            }

            foreach ($keywords as $kw) {
                if (mb_strlen($kw, 'UTF-8') >= 4 && ($siglasLower === $kw || Str::contains($nombreLower, $kw))) {
                    $institucionesDetectadas->push($inst);
                    break;
                }
            }
        }
        $institucionesDetectadas = $institucionesDetectadas->unique('id');

        // Si el usuario externo pregunta por solicitudes en trámite de otra institución
        if ($esEnteExterno && $institucionesDetectadas->isNotEmpty()) {
            $otrasInstituciones = $institucionesDetectadas->filter(fn($i) => $i->id !== $user->institucion_id);
            if ($otrasInstituciones->isNotEmpty() && preg_match('/\b(solicitud|solicitudes|tr[aá]mite|estatus|pendiente|rechazada)\b/iu', $userMessage)) {
                $nombresOtras = $otrasInstituciones->map(fn($i) => $i->siglas ?: $i->nombre)->implode(', ');
                $contextoSecciones[] = "AVISO DE PRIVACIDAD DE TRÁMITES: El usuario preguntó por solicitudes/trámites de ({$nombresOtras}), pero al ser representante de {$institucionUsuario?->nombre} SOLO puede consultar el estatus de solicitudes en trámite de su propio ente. Sin embargo, sí puede consultar las Gacetas Oficiales Publicadas de {$nombresOtras}.";
            }
        }

        // Para cada institución detectada, calcular el conteo exacto de Gacetas Publicadas y listar sus gacetas y actos
        if ($institucionesDetectadas->isNotEmpty()) {
            $infoInst = "INFORMACIÓN OFICIAL DE INSTITUCIONES DETECTADAS EN LA CONSULTA:\n";
            foreach ($institucionesDetectadas as $inst) {
                $siglas = trim((string) $inst->siglas);
                $nombre = trim((string) $inst->nombre);

                $gacetasInst = Gaceta::with(['gobernador.titulo', 'solicitudes.institucion', 'solicitudes.actos'])
                    ->where('estado', Gaceta::ESTADO_PUBLICADA)
                    ->where(function ($q) use ($inst, $siglas, $nombre) {
                        $q->whereHas('solicitudes', function ($sq) use ($inst, $siglas, $nombre) {
                            $sq->where('institucion_id', $inst->id);
                            if ($siglas !== '') {
                                $sq->orWhere('titulo_acto', 'ILIKE', "%{$siglas}%");
                            }
                            if ($nombre !== '') {
                                $sq->orWhere('titulo_acto', 'ILIKE', "%{$nombre}%");
                            }
                        })->orWhereHas('solicitudes.actos', function ($aq) use ($siglas, $nombre) {
                            $aq->whereRaw('1 = 0');
                            if ($siglas !== '') {
                                $aq->orWhere('sumario', 'ILIKE', "%{$siglas}%");
                            }
                            if ($nombre !== '') {
                                $aq->orWhere('sumario', 'ILIKE', "%{$nombre}%");
                            }
                        });
                    })
                    ->orderByDesc('fecha_publicacion')
                    ->orderByDesc('id')
                    ->get();

                $totalPublicadas = $gacetasInst->count();
                $infoInst .= "- Institución: {$inst->nombre}" . ($siglas ? " ({$siglas})" : "") . " | Total de Gacetas Oficiales Publicadas: {$totalPublicadas}.\n";

                foreach ($gacetasInst->take(10) as $g) {
                    $gacetasRelevantesParaPdf->push($g);
                    $fechaPub = $g->fecha_publicacion ? $g->fecha_publicacion->format('d/m/Y') : 'S/F';
                    $infoInst .= "  * Gaceta Oficial N° {$g->numero} ({$g->tipo}) | Fecha de Publicación: {$fechaPub} | Año: {$g->anio} | Estado: {$g->estado}\n";

                    foreach ($g->solicitudes as $sol) {
                        foreach ($sol->actos as $acto) {
                            $infoInst .= "    - Acto: {$acto->tipo_acto} N° {$acto->numero} | Sumario: {$acto->sumario}\n";
                        }
                    }
                }
            }
            $contextoSecciones[] = $infoInst;
        }

        // 4. Búsqueda en Gacetas Publicadas y Actos Administrativos por palabras clave (o las más recientes)
        $gacetasQuery = Gaceta::with(['gobernador.titulo', 'solicitudes.institucion', 'solicitudes.actos'])
            ->where('estado', Gaceta::ESTADO_PUBLICADA);

        if (!empty($keywords)) {
            $gacetasQuery->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('numero', 'ILIKE', "%{$kw}%")
                      ->orWhere('tipo', 'ILIKE', "%{$kw}%");
                    if (is_numeric($kw)) {
                        $q->orWhere('anio', (int) $kw);
                    }
                    $q->orWhereHas('solicitudes', function ($sq) use ($kw) {
                        $sq->where('titulo_acto', 'ILIKE', "%{$kw}%")
                           ->orWhereHas('institucion', function ($iq) use ($kw) {
                               $iq->where('nombre', 'ILIKE', "%{$kw}%")
                                  ->orWhere('siglas', 'ILIKE', "%{$kw}%");
                           });
                    })->orWhereHas('solicitudes.actos', function ($aq) use ($kw) {
                        $aq->where('tipo_acto', 'ILIKE', "%{$kw}%")
                           ->orWhere('numero', 'ILIKE', "%{$kw}%")
                           ->orWhere('sumario', 'ILIKE', "%{$kw}%");
                    });
                }
            });
        }

        $gacetasEncontradas = $gacetasQuery->orderByDesc('fecha_publicacion')->orderByDesc('id')->limit(8)->get();

        // Si no hubo coincidencia por keyword pero preguntan por gacetas/decretos/actos, traer las últimas publicadas
        if ($gacetasEncontradas->isEmpty()) {
            $gacetasEncontradas = Gaceta::with(['gobernador.titulo', 'solicitudes.institucion', 'solicitudes.actos'])
                ->where('estado', Gaceta::ESTADO_PUBLICADA)
                ->orderByDesc('fecha_publicacion')
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        }

        if ($gacetasEncontradas->isNotEmpty()) {
            $infoGacetas = "GACETAS OFICIALES PUBLICADAS Y SUS ACTOS ADMINISTRATIVOS (ORDENADAS DE LA MÁS RECIENTE A LA MÁS ANTIGUA):\n";
            foreach ($gacetasEncontradas as $g) {
                $gacetasRelevantesParaPdf->push($g);
                $fechaPub = $g->fecha_publicacion ? $g->fecha_publicacion->format('d/m/Y') : 'S/F';
                $gobNombre = $g->gobernador ? trim(($g->gobernador->titulo->abreviatura ?? '') . ' ' . $g->gobernador->nombres . ' ' . $g->gobernador->apellidos) : 'N/A';
                $infoGacetas .= "- Gaceta N° {$g->numero} ({$g->tipo}) | Fecha Publicación: {$fechaPub} | Año: {$g->anio} | Gobernador: {$gobNombre} | Estado: {$g->estado}\n";

                foreach ($g->solicitudes as $sol) {
                    $enteNombre = $sol->institucion ? ($sol->institucion->siglas ? "{$sol->institucion->nombre} ({$sol->institucion->siglas})" : $sol->institucion->nombre) : 'Ente no especificado';
                    $infoGacetas .= "  * Apartado/Solicitud: {$sol->titulo_acto} | Institución: {$enteNombre}\n";
                    foreach ($sol->actos as $acto) {
                        $infoGacetas .= "    - [{$acto->tipo_acto} N° {$acto->numero}]: {$acto->sumario}\n";
                    }
                }
            }
            $contextoSecciones[] = $infoGacetas;
        }

        // 5. Búsqueda directa en ActoAdministrativo (de Gacetas Publicadas)
        if (!empty($keywords)) {
            $actosDirectos = ActoAdministrativo::with(['solicitud.gaceta', 'solicitud.institucion'])
                ->whereHas('solicitud.gaceta', function ($gq) {
                    $gq->where('estado', Gaceta::ESTADO_PUBLICADA);
                })
                ->where(function ($q) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $q->orWhere('tipo_acto', 'ILIKE', "%{$kw}%")
                          ->orWhere('numero', 'ILIKE', "%{$kw}%")
                          ->orWhere('sumario', 'ILIKE', "%{$kw}%");
                    }
                })
                ->limit(10)
                ->get()
                ->sortByDesc(fn($a) => optional(optional($a->solicitud)->gaceta)->fecha_publicacion);

            if ($actosDirectos->isNotEmpty()) {
                $infoActos = "COINCIDENCIAS ESPECÍFICAS EN ACTOS ADMINISTRATIVOS PUBLICADOS:\n";
                foreach ($actosDirectos as $acto) {
                    $g = $acto->solicitud?->gaceta;
                    if ($g) {
                        $gacetasRelevantesParaPdf->push($g);
                    }
                    $inst = $acto->solicitud?->institucion;
                    $fechaG = ($g && $g->fecha_publicacion) ? $g->fecha_publicacion->format('d/m/Y') : 'S/F';
                    $infoActos .= "- {$acto->tipo_acto} N° {$acto->numero} | Gaceta N° " . ($g?->numero ?? 'N/A') . " ({$fechaG}) | Institución: " . ($inst?->nombre ?? 'N/A') . " (" . ($inst?->siglas ?? '') . ") | Sumario: {$acto->sumario}\n";
                }
                $contextoSecciones[] = $infoActos;
            }
        }

        // 6. Consulta de Gobernadores (Información Pública)
        $tieneDecretoNombramiento = Schema::hasColumn('gobernadores', 'decreto_nombramiento');
        $gobernadoresQuery = Gobernador::with('titulo');
        if (!empty($keywords)) {
            $gobernadoresQuery->where(function ($q) use ($keywords, $tieneDecretoNombramiento) {
                foreach ($keywords as $kw) {
                    $q->orWhere('nombres', 'ILIKE', "%{$kw}%")
                      ->orWhere('apellidos', 'ILIKE', "%{$kw}%");
                    if ($tieneDecretoNombramiento) {
                        $q->orWhere('decreto_nombramiento', 'ILIKE', "%{$kw}%");
                    }
                }
            });
        }
        $gobernadores = $gobernadoresQuery->get();
        if ($gobernadores->isEmpty()) {
            $gobernadores = Gobernador::with('titulo')->orderByDesc('estado')->limit(5)->get();
        }
        if ($gobernadores->isNotEmpty()) {
            $infoGob = "GOBERNADORES REGISTRADOS EN EL SISTEMA:\n";
            foreach ($gobernadores as $gob) {
                $abrev = $gob->titulo?->abreviatura ? $gob->titulo->abreviatura . ' ' : '';
                $estatusGob = $gob->estado ? 'En Ejercicio (Activo)' : 'Histórico / Inactivo';
                $decretoInfo = ($tieneDecretoNombramiento && !empty($gob->decreto_nombramiento)) ? " | Decreto: {$gob->decreto_nombramiento}" : "";
                $infoGob .= "- {$abrev}{$gob->nombres} {$gob->apellidos} | Estatus: {$estatusGob}{$decretoInfo}\n";
            }
            $contextoSecciones[] = $infoGob;
        }

        // 7. Consulta de Estatus de Solicitudes de Gaceta (Aislada por Institución si es Ente Externo)
        $solicitudesQuery = Solicitud::with(['institucion', 'gaceta', 'actos']);
        if ($esEnteExterno) {
            // Aislamiento estricto: SOLO las solicitudes de su propia institución
            $solicitudesQuery->where('institucion_id', $user->institucion_id);
        } elseif (!empty($keywords)) {
            $solicitudesQuery->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    if (is_numeric($kw)) {
                        $q->orWhere('id', (int) $kw);
                    }
                    $q->orWhere('titulo_acto', 'ILIKE', "%{$kw}%")
                      ->orWhere('estado', 'ILIKE', "%{$kw}%")
                      ->orWhereHas('institucion', function ($iq) use ($kw) {
                          $iq->where('nombre', 'ILIKE', "%{$kw}%")
                             ->orWhere('siglas', 'ILIKE', "%{$kw}%");
                      });
                }
            });
        }

        $solicitudesEstatus = $solicitudesQuery->latest()->limit(10)->get();
        if ($solicitudesEstatus->isNotEmpty()) {
            $tituloSol = $esEnteExterno
                ? "ESTATUS DE SOLICITUDES DE SU INSTITUCIÓN ({$institucionUsuario?->nombre}):\n"
                : "ESTATUS DE SOLICITUDES DE GACETA (RECIENTES / RELACIONADAS):\n";

            foreach ($solicitudesEstatus as $sol) {
                $enteSol = $sol->institucion ? ($sol->institucion->siglas ?: $sol->institucion->nombre) : 'N/A';
                $numGaceta = $sol->gaceta ? "Gaceta N° {$sol->gaceta->numero}" : 'Sin gaceta asignada aún';
                $motivoRechazo = $sol->motivo_rechazo ? " | Motivo de Rechazo: {$sol->motivo_rechazo}" : "";
                $tituloSol .= "- Solicitud ID #{$sol->id} | Título: {$sol->titulo_acto} | Institución: {$enteSol} | Estado: {$sol->estado} | {$numGaceta}{$motivoRechazo}\n";
            }
            $contextoSecciones[] = $tituloSol;
        }

        // 8. Localizar archivos PDF físicos de las 1 o 2 Gacetas Publicadas más recientes relevantes para lectura nativa Multimodal
        $pdfAttachments = [];
        $gacetasUnicasParaPdf = $gacetasRelevantesParaPdf
            ->unique('id')
            ->sortByDesc(fn($g) => $g->fecha_publicacion ? $g->fecha_publicacion->timestamp : 0)
            ->values()
            ->take(2);

        foreach ($gacetasUnicasParaPdf as $gacetaPdf) {
            $fullPath = $this->resolverRutaFisicaPdfGaceta($gacetaPdf);
            if ($fullPath && is_file($fullPath) && is_readable($fullPath)) {
                $fileSize = @filesize($fullPath);
                // Límite de 15 MB para inline_data
                if ($fileSize !== false && $fileSize > 0 && $fileSize <= 15 * 1024 * 1024) {
                    $contenidoBinario = @file_get_contents($fullPath);
                    if ($contenidoBinario !== false) {
                        $pdfAttachments[] = [
                            'numero_gaceta' => $gacetaPdf->numero,
                            'part' => [
                                'inline_data' => [
                                    'mime_type' => 'application/pdf',
                                    'data' => base64_encode($contenidoBinario),
                                ],
                            ],
                        ];
                    }
                }
            }
        }

        if (!empty($pdfAttachments)) {
            $numsAdjuntos = collect($pdfAttachments)->pluck('numero_gaceta')->implode(', ');
            $contextoSecciones[] = "ARCHIVOS PDF ADJUNTOS EN ESTA CONSULTA MULTIMODAL: Se han adjuntado directamente los documentos PDF completos de la(s) Gaceta(s) Oficial(es) N° {$numsAdjuntos}. Lee el texto interno e imágenes escaneadas del PDF adjunto para responder con exactitud quién fue nombrado presidente/director, qué dispone un decreto, resolución o cualquier artículo consultado, priorizando la Gaceta de fecha más reciente cuando se pregunte por el titular o estado actual.";
        }

        $contextoFinal = implode("\n\n", $contextoSecciones);

        // 9. Construcción del Prompt Unificado
        $promptFinal = "Instrucción del Sistema: Eres el Asistente Jurídico Inteligente Oficial del Sistema de Gestión de Control Jurídico (SGCJ) de la Consultoría Jurídica del Estado Yaracuy.\n"
            . "Reglas obligatorias:\n"
            . "1. Responde siempre en español con un tono formal, jurídico, preciso y resolutivo.\n"
            . "2. NUNCA expongas datos de usuarios, cuentas ni correos electrónicos.\n"
            . "3. Si el usuario es un Representante Institucional externo, para solicitudes en trámite (no publicadas) solo puede conocer el estatus de las solicitudes de su propia institución. En cambio, todas las Gacetas Oficiales Publicadas, sus Actos Administrativos, el conteo de gacetas por institución y los Gobernadores son públicos.\n"
            . "4. Cuando se pregunte cuántas gacetas tiene una institución (por ejemplo FUNDAY) o cuáles son, utiliza el conteo exacto y el listado proporcionado en el contexto.\n"
            . "5. Cuando se pregunte quién es el actual presidente/director de un ente o qué dice un decreto/acto, revisa tanto los sumarios como el contenido íntegro de los PDFs adjuntos, dando prioridad a la Gaceta Oficial más reciente por fecha de publicación.\n\n"
            . "=== CONTEXTO OFICIAL DE LA BASE DE DATOS SGCJ ===\n"
            . ($contextoFinal ?: "No se encontró contexto adicional en la base de datos.")
            . "\n=================================================\n\n"
            . "Pregunta del usuario: " . $userMessage;

        // 10. Consumo de la API de Gemini con soporte Multimodal (inline_data) y Fallback a solo-texto
        $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json(['reply' => 'Error Crítico del Sistema: Variable GEMINI_API_KEY ausente en la configuración del servidor.'], 500);
        }

        $primaryModel = config('services.gemini.model') ?? 'gemini-2.5-flash';
        $fallbackModel = config('services.gemini.fallback_model') ?? 'gemini-2.0-flash';
        $modelsToTry = array_values(array_unique(array_filter([$primaryModel, $fallbackModel, 'gemini-2.5-flash'])));

        $partsConPdf = [["text" => $promptFinal]];
        foreach ($pdfAttachments as $att) {
            $partsConPdf[] = $att['part'];
        }

        $partsSoloTexto = [["text" => $promptFinal]];

        // Intentamos primero con PDF adjunto (si existen PDFs), y si falla hacemos fallback automático a solo-texto
        $modosIntento = !empty($pdfAttachments) ? ['multimodal', 'solo_texto'] : ['solo_texto'];
        $lastError = null;

        foreach ($modosIntento as $modo) {
            $partsActuales = ($modo === 'multimodal') ? $partsConPdf : $partsSoloTexto;
            $payload = [
                "contents" => [
                    [
                        "parts" => $partsActuales
                    ]
                ],
                "generationConfig" => [
                    "temperature" => 0.2
                ]
            ];

            foreach ($modelsToTry as $model) {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

                try {
                    $response = Http::withoutVerifying()
                        ->timeout($modo === 'multimodal' ? 40 : 25)
                        ->withHeaders([
                            'Content-Type' => 'application/json',
                            'x-goog-api-key' => $apiKey,
                        ])
                        ->post($url, $payload);

                    if ($response->successful()) {
                        $data = $response->json();
                        $parts = $data['candidates'][0]['content']['parts'] ?? [];
                        $reply = '';
                        foreach ($parts as $part) {
                            if (!empty($part['text']) && empty($part['thought'])) {
                                $reply .= $part['text'];
                            }
                        }

                        if (empty($reply) && !empty($parts[0]['text'])) {
                            $reply = $parts[0]['text'];
                        }

                        if (!empty($reply)) {
                            return response()->json(['reply' => $reply]);
                        }
                    } else {
                        $lastError = "Fallo modelo {$model} ({$modo}, HTTP {$response->status()}): " . $response->body();
                        Log::warning($lastError);
                    }
                } catch (\Exception $e) {
                    $lastError = "Excepción modelo {$model} ({$modo}): " . $e->getMessage();
                    Log::warning($lastError);
                }
            }
        }

        Log::error('Agotados todos los modelos de Gemini en ChatbotController. Último error: ' . $lastError);
        return response()->json(['reply' => 'El núcleo de IA no pudo procesar la solicitud en este momento. Intenta de nuevo en unos instantes.'], 500);
    }

    /**
     * Localiza la ruta física en disco del archivo PDF de una Gaceta Publicada
     * o de sus actos/solicitudes asociados en storage/app/public/.
     */
    private function resolverRutaFisicaPdfGaceta(Gaceta $gaceta): ?string
    {
        $disk = Storage::disk('public');

        $candidatos = array_filter([
            $gaceta->pdf_final ?? null,
            $gaceta->archivo_pdf_completo ?? null,
            $gaceta->pdf_url ?? null,
        ]);

        foreach ($gaceta->solicitudes as $sol) {
            foreach ($sol->actos as $acto) {
                if (!empty($acto->archivo_pdf)) {
                    $candidatos[] = $acto->archivo_pdf;
                }
            }
            if (!empty($sol->archivo_pdf_path)) {
                $candidatos[] = $sol->archivo_pdf_path;
            }
            if (!empty($sol->archivo_pdf)) {
                $candidatos[] = $sol->archivo_pdf;
            }
        }

        foreach ($candidatos as $relPath) {
            $limpio = ltrim(str_replace(['/storage/', 'storage/'], '', (string) $relPath), '/\\');
            if ($limpio !== '' && $disk->exists($limpio)) {
                return $disk->path($limpio);
            }
            $directo = storage_path('app/public/' . $limpio);
            if ($limpio !== '' && is_file($directo)) {
                return $directo;
            }
        }

        if (!empty($gaceta->ruta_archivo)) {
            $legacy = storage_path('app/SGCJ/' . $gaceta->ruta_archivo);
            if (is_file($legacy)) {
                return $legacy;
            }
        }

        return null;
    }
}
