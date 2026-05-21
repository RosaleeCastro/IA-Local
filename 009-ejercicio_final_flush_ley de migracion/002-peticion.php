<?php
// ==============================================
// 002-peticion.php
// Primera petición simple desde PHP a Ollama
// Solo comprobamos que la conexión funciona
// ==============================================

// ----------------------------------------------
// CONFIGURACIÓN
// URL donde escucha Ollama en local
// ----------------------------------------------
$ollama_url = "http://localhost:11434/api/generate";
$modelo     = "qwen2.5:3b";

// ----------------------------------------------
// PREGUNTA DE PRUEBA
// Una consulta sencilla para verificar conexión
// ----------------------------------------------
$pregunta = "¿Qué es el arraigo en la ley de extranjería española? Responde en dos frases.";

// ----------------------------------------------
// PREPARAR LA PETICIÓN
// Ollama espera un JSON con model, prompt y stream
// stream:false significa que espera la respuesta
// completa antes de devolverla
// ----------------------------------------------
$datos = [
    "model"  => $modelo,
    "prompt" => $pregunta,
    "stream" => false
];

// ----------------------------------------------
// ENVIAR PETICIÓN CON CURL
// curl es la herramienta de PHP para hacer
// peticiones HTTP a otros servidores
// ----------------------------------------------
$ch = curl_init($ollama_url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,           // devuelve la respuesta como string
    CURLOPT_POST           => true,           // usamos método POST
    CURLOPT_HTTPHEADER     => [               // cabecera JSON
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS     => json_encode($datos), // convertimos array a JSON
    CURLOPT_TIMEOUT        => 120             // esperamos hasta 2 minutos
]);

// Ejecutar la petición
$respuesta = curl_exec($ch);

// Comprobar si hubo error de conexión
if ($respuesta === false) {
    die("Error de conexión con Ollama: " . curl_error($ch));
}

curl_close($ch);

// ----------------------------------------------
// PROCESAR RESPUESTA
// Ollama devuelve un JSON, lo convertimos a array
// y extraemos el campo "response"
// ----------------------------------------------
$resultado = json_decode($respuesta, true);
$texto     = $resultado["response"] ?? "Sin respuesta";

// ----------------------------------------------
// MOSTRAR RESULTADO
// Por ahora solo texto plano para verificar
// ----------------------------------------------
echo "<h2>Pregunta:</h2>";
echo "<p>" . htmlspecialchars($pregunta) . "</p>";

echo "<h2>Respuesta de Ollama:</h2>";
echo "<p>" . nl2br(htmlspecialchars($texto)) . "</p>";
?>