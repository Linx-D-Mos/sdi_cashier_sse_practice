<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio SSE - Alertas en Tiempo Real</title>
    <style>
        body {
            font-family: sans-serif;
            background: #1a202c;
            color: #fff;
            padding: 2rem;
        }

        #log-container {
            background: #2d3748;
            padding: 1rem;
            border-radius: 8px;
            font-family: monospace;
            max-height: 400px;
            overflow-y: auto;
        }

        .log-entry {
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            border-left: 4px solid #4ade80;
            background: #374151;
        }
    </style>
</head>

<body>
    <h1>🛰️ Panel de Control - Receptor de Alertas</h1>
    <p>Escuchando el canal de eventos del servidor...</p>

    <div id="log-container">
    </div>

    <script>
        // Instanciamos la API nativa del navegador para conectar al endpoint
        const eventSource = new EventSource('/api/alertas-stream');

        const logContainer = document.getElementById('log-container');

        // 2. ESCUCHADOR ESPECÍFICO: Nos sintonizamos explícitamente en el canal "alerta"
        eventSource.addEventListener('alert', function(event) {
            // Parseamos el JSON que viene dentro del text-stream
            const payload = JSON.parse(event.data);

            // Creamos y renderizamos el elemento en la interfaz
            const entry = document.createElement('div');
            entry.className = 'log-entry';
            entry.innerText = `[${new Date().toLocaleTimeString()}] Evento: ALERTA 🚨 | ${payload.message}`;

            logContainer.appendChild(entry);

            // Auto-scroll hacia abajo para ver las alertas nuevas
            logContainer.scrollTop = logContainer.scrollHeight;
        });

        // Monitoreo de errores de red (Se mantiene igual)
        eventSource.onerror = function(error) {
            console.log("El canal persistente se ha interrumpido. El navegador intentará reconectar solo...");
        };

    </script>
</body>

</html>
