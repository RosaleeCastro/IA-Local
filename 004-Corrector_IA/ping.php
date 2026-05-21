<?php
header("Content-Type: application/json; charset=UTF-8");
ini_set("display_errors", "0");

$urlBase = getenv("OLLAMA_URL") ?: "http://localhost:11434/api/generate";
$scheme  = parse_url($urlBase, PHP_URL_SCHEME) ?: "http";
$host    = parse_url($urlBase, PHP_URL_HOST)   ?: "localhost";
$port    = parse_url($urlBase, PHP_URL_PORT)   ?: 11434;
$check   = "{$scheme}://{$host}:{$port}/api/tags";

$ch = curl_init($check);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 4,
    CURLOPT_TIMEOUT        => 6,
]);
$resp = curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
$cerr = curl_error($ch);
curl_close($ch);

if ($resp !== false && $code === 200) {
    $data    = json_decode($resp, true);
    $modelos = array_column($data["models"] ?? [], "name");
    echo json_encode([
        "ok"      => true,
        "host"    => "{$host}:{$port}",
        "modelos" => $modelos,
    ], JSON_UNESCAPED_UNICODE);
} else {
    $motivo = match(true) {
        str_contains($cerr, "Connection refused")   => "Ollama local no está ejecutándose. Abre Ollama o ejecuta: ollama serve",
        str_contains($cerr, "timed out")            => "Ollama local tarda en responder. Puede estar cargando el modelo.",
        str_contains($cerr, "Could not resolve")    => "No se puede resolver el host. Verifica OLLAMA_URL.",
        str_contains($cerr, "Network unreachable")  => "Ollama local no es accesible.",
        default                                     => $cerr ?: "HTTP {$code}",
    };
    echo json_encode([
        "ok"     => false,
        "host"   => "{$host}:{$port}",
        "error"  => $motivo,
        "raw"    => $cerr,
    ], JSON_UNESCAPED_UNICODE);
}
