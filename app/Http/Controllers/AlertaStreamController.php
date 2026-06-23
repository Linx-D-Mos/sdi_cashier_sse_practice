<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlertaStreamController extends Controller
{
    public function stream(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ];

        return response()->stream(function () {
            // 1. Desactivar el límite de tiempo de ejecución de PHP para esta petición
            set_time_limit(0);

            // 1. Inicialización del puntero
            // Al conectar, el usuario solo escuchará las alertas que se creen a partir de este momento.
            $lastEventId = Alert::max('id') ?? 0;

            echo "event: conecta\n";
            echo "data: {\"message\": \"sintonizado con PostgresSQL con puntero inicial ID: {$lastEventId}\"}\n\n";

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
                    if(ob_get_level() > 0) ob_flush();
                    flush();
                }
                sleep(2);
            }
        }, 200, $headers);
    }
}
