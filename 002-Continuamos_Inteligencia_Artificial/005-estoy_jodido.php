<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

$modelo = "deepseek-r1:1.5b";
$url = "http://192.168.1.38:11434/api/generate";

$pregunta = $_POST['pregunta'] ?? '';
$respuestaIA = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pregunta = trim($pregunta);

    if ($pregunta === '') {
        $error = "Debes escribir una pregunta.";
    } else {

        // Prompt mejorado: aquí damos contexto e instrucciones a la IA.
        $promptFinal = "
        Actúa como un asistente técnico especializado en desarrollo web.
        Responde siempre en español.
        Explica de forma clara, breve y ordenada.
        No uses código salvo que sea necesario.

        Pregunta del usuario:
        " . $pregunta;

        $data = [
            "model"  => $modelo,
            "prompt" => $promptFinal,
            "stream" => false
        ];

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 300,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = "Error de cURL: " . curl_error($ch);
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result = json_decode($response, true);

            if ($httpCode === 200 && isset($result["response"])) {
                $respuestaIA = $result["response"];
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
    <title>Asistente técnico con IA</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f8;
            margin: 0;
            padding: 40px;
            color: #222;
        }

        .contenedor {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 28px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
            color: #2c2c54;
        }

        .info {
            color: #555;
            font-size: 15px;
            margin-bottom: 25px;
        }

        textarea {
            width: 100%;
            min-height: 150px;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 16px;
            resize: vertical;
        }

        button {
            margin-top: 15px;
            padding: 12px 24px;
            background: #6c5ce7;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #5a4bd1;
        }

        .respuesta {
            margin-top: 25px;
            padding: 20px;
            background: #eefaf1;
            border-left: 5px solid #2ecc71;
            border-radius: 10px;
            white-space: pre-wrap;
        }

        .error {
            margin-top: 25px;
            padding: 20px;
            background: #fff0f0;
            border-left: 5px solid #e74c3c;
            border-radius: 10px;
        }

        .detalle {
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>

<main class="contenedor">

    <h1>Asistente técnico con IA</h1>

    <p class="info">
        Esta página usa PHP en XAMPP para enviar una pregunta a Ollama,
        instalado en una VM Ubuntu Server. En este ejercicio se mejora el prompt
        para que la IA responda como asistente técnico.
    </p>

    <p class="detalle">
        <strong>Modelo usado:</strong> <?= htmlspecialchars($modelo) ?>
    </p>

    <form method="POST">
        <label for="pregunta"><strong>Escribe tu consulta técnica:</strong></label>
        <br><br>

        <textarea 
            name="pregunta" 
            id="pregunta"
            placeholder="Ejemplo: Explícame qué diferencia hay entre HTML y CSS."
        ><?= htmlspecialchars($pregunta) ?></textarea>

        <br>

        <button type="submit">Enviar consulta</button>
    </form>

    <?php if (!empty($error)): ?>
        <div class="error">
            <strong>Error:</strong><br>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($respuestaIA)): ?>
        <div class="respuesta">
            <strong>Respuesta de la IA:</strong><br><br>
            <?= htmlspecialchars($respuestaIA) ?>
        </div>
    <?php endif; ?>

</main>

</body>
</html>