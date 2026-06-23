<?php

namespace App\Http\Controllers;

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

            // Mensaje inicial de bienvenida
            echo "data: {\"status\": \"connected\", \"message\": \"Laboratorio de alertas activado\"}\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            // 2. El bucle de vida de la conexión.
            while (true) {

            // 3. Salvaguarda crítica: ¿El usuario cerró la pestaña?
                if (connection_aborted()) {
                    // Aqui podriamos meter un log si quisieramos
                    break;
                }

                // 4. Estructura del Protocolo SSE (Simulación de alerta)
                // Nota: Más adelante buscaremos alertas reales en la Base de Datos.
                $timestamp = now()->toTimeString();
                echo "event: alert\n";
                echo "data: {\"status\": \"live\", \"message\": \"Latido de telemetria a las {$timestamp} \"} \n\n";

                // 5. Forzar el envío inmediato de los bytes recopilados en este ciclo
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

                // 6. Protección de CPU: Pausa del hilo por 3 segundos antes de la siguiente vuelta
                sleep(3);
            }
        }, 200, $headers);
    }
}
