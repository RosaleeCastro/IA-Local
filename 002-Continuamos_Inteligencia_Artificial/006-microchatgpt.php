<?php

session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

/*
    CONFIGURACIÓN PRINCIPAL
    PHP está en XAMPP.
    Ollama está en la VM Ubuntu Server.
*/
$modelo = "deepseek-r1:1.5b";
$urlOllama = "http://192.168.1.38:11434/api/generate";

/*
    Si no existe historial, lo creamos vacío.
*/
if (!isset($_SESSION['chat'])) {
    $_SESSION['chat'] = [];
}

/*
    Botón para limpiar la conversación.
*/
if (isset($_POST['limpiar'])) {
    $_SESSION['chat'] = [];
    header("Location: 006-microchatgpt.php");
    exit;
}

$error = "";

/*
    Procesamos el mensaje enviado por el usuario.
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {

    $mensajeUsuario = trim($_POST['mensaje']);

    if ($mensajeUsuario === '') {
        $error = "Debes escribir un mensaje.";
    } else {

        /*
            Guardamos el mensaje del usuario en el historial.
        */
        $_SESSION['chat'][] = [
            'rol' => 'usuario',
            'contenido' => $mensajeUsuario
        ];

        /*
            Construimos el contexto que recibirá la IA.
            Aquí le damos instrucciones y también el historial.
        */
        $prompt = "Actúa como un asistente técnico experto en desarrollo web.
Responde siempre en español.
Sé claro, breve y útil.
No inventes información.
Mantén el contexto de la conversación.

Historial de conversación:\n\n";

        /*
            Para no hacer el prompt infinito, usamos solo los últimos 8 mensajes.
        */
        $ultimosMensajes = array_slice($_SESSION['chat'], -8);

        foreach ($ultimosMensajes as $mensaje) {
            if ($mensaje['rol'] === 'usuario') {
                $prompt .= "Usuario: " . $mensaje['contenido'] . "\n";
            } else {
                $prompt .= "Asistente: " . $mensaje['contenido'] . "\n";
            }
        }

        $prompt .= "\nAsistente:";

        /*
            Datos enviados a Ollama.
        */
        $data = [
            "model" => $modelo,
            "prompt" => $prompt,
            "stream" => false,
            "options" => [
                "num_predict" => 400,
                "temperature" => 0.3
            ]
        ];

        /*
            Petición HTTP POST a Ollama.
        */
        $ch = curl_init($urlOllama);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 300,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = "Error de cURL: " . curl_error($ch);
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result = json_decode($response, true);

            if ($httpCode === 200 && isset($result['response'])) {

                $respuestaIA = trim($result['response']);

                /*
                    Guardamos la respuesta de la IA en el historial.
                */
                $_SESSION['chat'][] = [
                    'rol' => 'asistente',
                    'contenido' => $respuestaIA
                ];

            } else {
                $error = "Ollama respondió, pero no devolvió una respuesta válida.";
            }
        }

        curl_close($ch);
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Microchat IA con PHP y Ollama</title>

    <style>
        body {
            margin: 0;
            padding: 40px;
            font-family: Arial, sans-serif;
            background: #f3f4f8;
            color: #222;
        }

        .contenedor {
            max-width: 900px;
            margin: auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 28px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
            color: #2c2c54;
        }

        .info {
            color: #555;
            margin-bottom: 25px;
        }

        .chat {
            border: 1px solid #ddd;
            border-radius: 14px;
            padding: 20px;
            min-height: 280px;
            background: #fafafa;
            margin-bottom: 20px;
        }

        .mensaje {
            margin-bottom: 18px;
            padding: 14px;
            border-radius: 12px;
            line-height: 1.5;
            white-space: pre-wrap;
        }

        .usuario {
            background: #e8f0ff;
            border-left: 5px solid #4a90e2;
        }

        .asistente {
            background: #eefaf1;
            border-left: 5px solid #2ecc71;
        }

        .rol {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
        }

        textarea {
            width: 100%;
            min-height: 100px;
            padding: 14px;
            font-size: 16px;
            border-radius: 10px;
            border: 1px solid #ccc;
            resize: vertical;
            box-sizing: border-box;
        }

        .acciones {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        button {
            padding: 12px 22px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
        }

        .enviar {
            background: #6c5ce7;
            color: white;
        }

        .enviar:hover {
            background: #5a4bd1;
        }

        .limpiar {
            background: #e74c3c;
            color: white;
        }

        .limpiar:hover {
            background: #c0392b;
        }

        .error {
            margin-bottom: 20px;
            padding: 15px;
            background: #fff0f0;
            border-left: 5px solid #e74c3c;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<main class="contenedor">

    <h1>Microchat IA con PHP y Ollama</h1>

    <p class="info">
        Este microchat usa PHP en XAMPP como interfaz web y consulta el modelo
        <strong><?= htmlspecialchars($modelo) ?></strong>
        ejecutado con Ollama dentro de la VM Ubuntu Server.
    </p>

    <?php if (!empty($error)): ?>
        <div class="error">
            <strong>Error:</strong><br>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <section class="chat">

        <?php if (empty($_SESSION['chat'])): ?>

            <p>Todavía no hay mensajes. Escribe una consulta para empezar.</p>

        <?php else: ?>

            <?php foreach ($_SESSION['chat'] as $mensaje): ?>

                <div class="mensaje <?= $mensaje['rol'] === 'usuario' ? 'usuario' : 'asistente' ?>">
                    <span class="rol">
                        <?= $mensaje['rol'] === 'usuario' ? 'Tú' : 'IA' ?>
                    </span>
                    <?= htmlspecialchars($mensaje['contenido']) ?>
                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>

    <form method="POST">
        <label for="mensaje"><strong>Escribe tu mensaje:</strong></label>
        <br><br>

        <textarea 
            name="mensaje" 
            id="mensaje"
            placeholder="Ejemplo: Explícame qué es una API REST en pocas palabras."
        ></textarea>

        <div class="acciones">
            <button class="enviar" type="submit">Enviar</button>

            <button class="limpiar" type="submit" name="limpiar" value="1">
                Limpiar chat
            </button>
        </div>
    </form>

</main>

</body>
</html>