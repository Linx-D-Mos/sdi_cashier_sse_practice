<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlertaStreamController extends Controller
{
    public function stream(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ];

        return response()->stream(function () use ($request) {
            // 1. Desactivar el límite de tiempo de ejecución de PHP para esta petición
            set_time_limit(0);

            // 1. Restricción de resilencia: ¿El navegador viene de una reconexión?
            // El navegador envía 'Last-Event-ID'. Si no existe, buscamos la cabecera común por si acaso.

            $lastEventId = $request->header('Last-Event-ID')
                ?? $request->server('HTTP_LAST_EVENT_ID')
                ?? null;

            if (is_null($lastEventId)) {
                $lastEventId = Alert::max('id') ?? 0;
                $contexto = "Conexión Limpia inicializada en ID: {$lastEventId}";
            } else {
                $lastEventId = Alert::max('id') ?? 0;
                $contexto = "Reconexión exitosa. Recuperando datos desde el ID: {$lastEventId}";
            }

            echo "event: conecta\n";
            echo "data: " . json_encode(["message" => $contexto]) . "\n\n";

            if (ob_get_level() > 0) ob_flush();
            flush();

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                $alertasNuevas = Alert::where('id', '>', $lastEventId)
                    ->orderBy('id', 'asc')
                    ->get();

                if ($alertasNuevas->isNotEmpty()) {
                    foreach ($alertasNuevas as $alerta) {
                        echo "event: alert\n";
                        echo "data: " . json_encode([
                            'id' => $alerta->id,
                            'message' => $alerta->message,
                            'tipo' => $alerta->tipo,
                            'timestamp' => $alerta->created_at->toTimeString()
                        ]) . "\n\n";

                        $lastEventId = $alerta->id;
                    }
                    if (ob_get_level() > 0) ob_flush();
                    flush();
                }
                sleep(2);
            }
        }, 200, $headers);
    }
}
