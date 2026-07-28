<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Gaceta;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $userMessage = $request->input('message');
        $contexto = "";

        // 1. Motor RAG Ligero (Análisis de Intención de Búsqueda)
        if (Str::contains(strtolower($userMessage), ['gaceta', 'ley', 'decreto', 'resolucion', 'solicitud', 'trámite', 'usuario', 'institucion', 'institución', 'sistema'])) {
            
            // Extracción de palabras clave (Lematización simple manual)
            $palabrasIgnoradas = ['gaceta', 'ley', 'resolucion', 'solicitud', 'la', 'el', 'los', 'las', 'de', 'del', 'sobre', 'un', 'una', 'busco', 'necesito', 'quiero', 'saber', 'para', 'por', 'con'];
            $palabras = explode(' ', strtolower($userMessage));
            $keyword = null;
            
            foreach ($palabras as $palabra) {
                $palabraLimpia = preg_replace('/[^a-z0-9áéíóúñ]/i', '', $palabra);
                if (!in_array($palabraLimpia, $palabrasIgnoradas) && strlen($palabraLimpia) > 3) {
                    $keyword = $palabraLimpia;
                    break; // Tomamos la primera palabra clave fuerte
                }
            }

            if ($keyword) {
                // Consulta Forense (Basada en nuestra auditoría: Gaceta no tiene título, Solicitud sí)
                $gacetas = Gaceta::where('numero', 'ILIKE', '%' . $keyword . '%')
                    ->orWhereHas('solicitudes', function($q) use ($keyword) {
                        $q->where('titulo_acto', 'ILIKE', '%' . $keyword . '%');
                    })
                    ->with(['solicitudes' => function($q) use ($keyword) {
                        $q->where('titulo_acto', 'ILIKE', '%' . $keyword . '%');
                    }])
                    ->limit(5)
                    ->get();
                
                if ($gacetas->isNotEmpty()) {
                    $contexto .= "Base de datos del SGCJ - Coincidencias encontradas para '{$keyword}':\n";
                    foreach ($gacetas as $g) {
                        $titulosArray = $g->solicitudes->pluck('titulo_acto')->toArray();
                        $titulosStr = empty($titulosArray) ? 'Sin título descriptivo' : implode(" | ", $titulosArray);
                        
                        $contexto .= "- Gaceta Número: {$g->numero} | Año: {$g->anio} | Estado Actual: {$g->estado} | Actos/Asuntos: {$titulosStr}.\n";
                    }
                } else {
                    $contexto .= "El sistema buscó en la BD pero no encontró Gacetas ni Solicitudes relacionadas con '{$keyword}'.\n";
                }

                // Búsqueda de Usuarios e Instituciones
                $usuarios = \App\Models\User::where('name', 'ILIKE', '%' . $keyword . '%')
                    ->orWhere('email', 'ILIKE', '%' . $keyword . '%')
                    ->orWhereHas('institucion', function($q) use ($keyword) {
                        $q->where('nombre', 'ILIKE', '%' . $keyword . '%');
                    })
                    ->with('institucion')
                    ->limit(3)
                    ->get();

                if ($usuarios->isNotEmpty()) {
                    $contexto .= "\nBase de datos SGCJ - Usuarios/Instituciones encontradas para '{$keyword}':\n";
                    foreach ($usuarios as $u) {
                        $nombreInstitucion = $u->institucion ? $u->institucion->nombre : 'Ninguna (Interno)';
                        $contexto .= "- Usuario: {$u->name} | Email: {$u->email} | Pertenece a la Institución: {$nombreInstitucion}.\n";
                    }
                }
            }
        }

        // 2. Construcción del Prompt Unificado (Blindaje API)
        $promptFinal = "Instrucción: Eres el asistente inteligente oficial del Sistema de Gestión de Control Jurídico (SGCJ). Responde en español, usando un tono formal, amable y resolutivo. NO inventes Gacetas ni leyes. Usa estos datos reales del sistema si son relevantes para la pregunta (destaca el número de gaceta y su estado):\n\nContexto Vivo:\n" . 
        ($contexto ?: "No se extrajo contexto adicional.") . "\n\nPregunta del usuario: " . $userMessage;

        // 3. Payload Seguro y Simplificado
        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $promptFinal]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.2 // Muy estricto para evitar alucinaciones legales
            ]
        ];

        // 4. Consumo mediante Cliente HTTP Laravel
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json(['reply' => 'Error Crítico del Sistema: Variable GEMINI_API_KEY ausente en el archivo .env.'], 500);
        }

        // Se usa gemini-flash-latest para mayor compatibilidad
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}";

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                            ->withHeaders(['Content-Type' => 'application/json'])
                            ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'La respuesta generada está vacía. Reformula tu pregunta.';
                return response()->json(['reply' => $reply]);
            } else {
                \Illuminate\Support\Facades\Log::error('Fallo API Gemini: ' . $response->body());
                return response()->json(['reply' => 'El núcleo de IA no pudo procesar la solicitud en este momento.'], 500);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Caída API Gemini: ' . $e->getMessage());
            return response()->json(['reply' => 'Error de conectividad interna con el servicio de IA.'], 500);
        }
    }
}
