<?php
// ==============================================
// 003-funcion.php
// Contiene SOLO las dos funciones reutilizables:
// - buscarFragmentos(): busca en ChromaDB via Python
// - preguntarOllama(): genera respuesta con contexto
// Este archivo se incluye desde otros scripts,
// NO tiene código de ejecución directa
// ==============================================

// ----------------------------------------------
// FORZAR UTF-8 EN LA SALIDA
// ----------------------------------------------
header("Content-Type: text/html; charset=utf-8");


// ----------------------------------------------
// FUNCIÓN: buscar fragmentos relevantes
// Llama al script Python y devuelve los fragmentos
// más similares a la pregunta desde ChromaDB
// ----------------------------------------------
function buscarFragmentos($pregunta) {

    $python = "python";

    // Ruta al script buscador usando __DIR__
    // para que funcione desde cualquier ubicación
    $script = __DIR__ . "/buscador.py";

    // Escapar la pregunta para evitar problemas
    // con caracteres especiales en la terminal
    $pregunta_escapada = escapeshellarg($pregunta);

    // Forzar UTF-8 en la llamada a Python
    // PYTHONIOENCODING y PYTHONUTF8 evitan que
    // Windows corrompa los acentos y la ñ
    $comando = "set PYTHONIOENCODING=utf-8 && set PYTHONUTF8=1 && $python -X utf8 \"$script\" $pregunta_escapada 2>&1";

    // Ejecutar Python y capturar toda la salida
    $salida = shell_exec($comando);

    if (!$salida) {
        return ["error" => "No se obtuvo respuesta del buscador"];
    }

    // Limpiar la salida para asegurar UTF-8 válido
    $salida = mb_convert_encoding($salida, 'UTF-8', 'UTF-8');

    // Convertir JSON a array PHP
    $datos = json_decode($salida, true);

    if (!$datos) {
        return ["error" => "Respuesta inválida: $salida"];
    }

    return $datos;
}


// ----------------------------------------------
// FUNCIÓN: preguntar a Ollama con contexto
// Recibe la pregunta y los fragmentos de la ley
// y genera una respuesta fundamentada en ellos
// ----------------------------------------------
function preguntarOllama($pregunta, $fragmentos) {

    $ollama_url = "http://localhost:11434/api/generate";
    $modelo     = "qwen2.5:3b";

    // Construir el contexto juntando todos los
    // fragmentos con su origen indicado
    $contexto = "";
    foreach ($fragmentos as $i => $frag) {
        $num      = $i + 1;
        $origen   = $frag["origen"];
        $texto    = $frag["texto"];
        $contexto .= "[$num] ($origen)\n$texto\n\n";
    }

    // Prompt con rol de asistente legal y contexto
    // Un buen prompt mejora mucho la calidad
    $prompt = "Eres un asistente legal especializado en extranjería e inmigración en España.
Responde en español de forma clara y útil basándote ÚNICAMENTE en el siguiente contexto legal.
Si la información no está en el contexto, indícalo claramente.

CONTEXTO LEGAL:
$contexto

PREGUNTA: $pregunta

RESPUESTA:";

    $datos = [
        "model"  => $modelo,
        "prompt" => $prompt,
        "stream" => false
    ];

    $ch = curl_init($ollama_url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS     => json_encode($datos),
        CURLOPT_TIMEOUT        => 120
    ]);

    $respuesta = curl_exec($ch);

    if ($respuesta === false) {
        return "Error de conexión con Ollama: " . curl_error($ch);
    }

    curl_close($ch);

    $resultado = json_decode($respuesta, true);
    return $resultado["response"] ?? "Sin respuesta";
}