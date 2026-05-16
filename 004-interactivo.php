<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

$respuestaIA = '';
$error = '';
$prompt = $_POST['prompt'] ?? '';

$modelo = "deepseek-r1:1.5b";
$url = "http://192.168.1.38:11434/api/generate";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $prompt = trim($prompt);

    if ($prompt === '') {
        $error = "Debes escribir una pregunta antes de consultar la IA.";
    } else {

        $data = [
            "model"  => $modelo,
            "prompt" => $prompt,
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
    <title>IA interactiva con PHP y Ollama</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #222;
            margin: 0;
            padding: 40px;
        }

        .contenedor {
            max-width: 850px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
            color: #2c2c54;
        }

        textarea {
            width: 100%;
            min-height: 140px;
            padding: 12px;
            font-size: 16px;
            border-radius: 10px;
            border: 1px solid #ccc;
            resize: vertical;
        }

        button {
            margin-top: 15px;
            padding: 12px 22px;
            border: none;
            border-radius: 10px;
            background: #6c5ce7;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #5a4bd1;
        }

        .respuesta,
        .error {
            margin-top: 25px;
            padding: 18px;
            border-radius: 10px;
            white-space: pre-wrap;
        }

        .respuesta {
            background: #eefaf1;
            border-left: 5px solid #2ecc71;
        }

        .error {
            background: #fff0f0;
            border-left: 5px solid #e74c3c;
        }

        .info {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <main class="contenedor">

        <h1>IA interactiva con PHP y Ollama</h1>

        <p class="info">
            Este formulario se ejecuta en XAMPP, pero consulta el modelo
            <strong><?= htmlspecialchars($modelo) ?></strong>
            instalado en la VM Ubuntu Server mediante Ollama.
        </p>

        <form method="POST">
            <label for="prompt"><strong>Escribe tu pregunta:</strong></label>
            <br><br>

            <textarea 
                name="prompt" 
                id="prompt" 
                placeholder="Ejemplo: Explícame qué es HTML en un solo párrafo."
            ><?= htmlspecialchars($prompt) ?></textarea>

            <br>

            <button type="submit">Consultar IA</button>
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