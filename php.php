<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

$prompt = "¿Qué es HTML? Responde con un solo párrafo y sin código.";

$data = [
    "model"  => "deepseek-r1:1.5b",
    "prompt" => $prompt,
    "stream" => false
];

$url = "http://192.168.1.38:11434/api/generate";

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
    die("Error de cURL: " . curl_error($ch));
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

$result = json_decode($response, true);

echo "<h1>Prueba PHP + Ollama</h1>";
echo "<p><strong>Modelo:</strong> deepseek-r1:1.5b</p>";
echo "<p><strong>Código HTTP:</strong> $httpCode</p>";

if (isset($result["response"])) {
    echo "<h2>Respuesta de la IA:</h2>";
    echo "<pre>" . htmlspecialchars($result["response"]) . "</pre>";
} else {
    echo "<h2>Respuesta completa recibida:</h2>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}