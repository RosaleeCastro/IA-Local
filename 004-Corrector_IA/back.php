<?php
header("Content-Type: application/json; charset=UTF-8");
ini_set("display_errors", "0");

set_error_handler(function ($severity, $message, $file, $line) {
    http_response_code(500);
    echo json_encode([
        "ok"     => false,
        "error"  => "PHP ha generado un error.",
        "detail" => $message . " en " . basename($file) . ":" . $line
    ]);
    exit;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error === null) return;
    $fatales = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (!in_array($error["type"], $fatales, true)) return;
    if (!headers_sent()) {
        http_response_code(500);
        header("Content-Type: application/json; charset=UTF-8");
    }
    echo json_encode([
        "ok"     => false,
        "error"  => "PHP ha terminado con un error fatal.",
        "detail" => $error["message"] . " en " . basename($error["file"]) . ":" . $error["line"]
    ]);
});

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["ok" => false, "error" => "Metodo no permitido. Usa POST."]);
    exit;
}

$mensaje = isset($_POST["mensaje"]) ? trim($_POST["mensaje"]) : "";
$modo    = isset($_POST["modo"])    ? trim($_POST["modo"])    : "correo";
$modo    = in_array($modo, ["correo", "texto"], true) ? $modo : "correo";

if ($mensaje === "") {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "No se ha recibido ningun mensaje."]);
    exit;
}

// ─── Configuracion ────────────────────────────────────────────────────────────
$ollamaUrl          = getenv("OLLAMA_URL")              ?: "http://192.168.1.43:11434/api/generate";
// MEJORA: llama3.2:3b o mistral:7b producen correos mucho mejores que deepseek-r1:1.5b
$modelo = getenv("OLLAMA_MODEL") ?: "llama3.2:3b";
$maxIntentos        = 6;
$pausaMicrosegundos = 2_000_000;
$timeoutGeneracion  = (int)(getenv("OLLAMA_TIMEOUT")         ?: 300);
$tiempoPausas       = (int)ceil((($maxIntentos - 1) * $pausaMicrosegundos) / 1_000_000);
$tiempoMaximoScript = (int)(getenv("PHP_MAX_EXECUTION_TIME") ?: (($maxIntentos * $timeoutGeneracion) + $tiempoPausas + 15));

ini_set("max_execution_time", (string)$tiempoMaximoScript);
set_time_limit($tiempoMaximoScript);

// ─── Limpieza de la respuesta ─────────────────────────────────────────────────
function limpiarRespuestaModelo(string $texto): string
{
    $texto = trim($texto);

    // Extraer contenido entre etiquetas <correo>…</correo> o <texto>…</texto>
    if (preg_match('/<(?:correo|texto)>(.*?)<\/(?:correo|texto)>/is', $texto, $m)) {
        return trim($m[1]);
    }

    // Eliminar bloques de razonamiento <think>…</think> (deepseek-r1 y similares)
    $texto = preg_replace('/<think>.*?<\/think>/is', '', $texto);

    // Eliminar líneas que son solo metadatos o artefactos del modelo
    $patronesMeta = [
        '/^(aqui|aquí)\s+(esta|está|tienes|te dejo)/i',
        '/^(hola[!,. ]|estimado modelo|claro[,!])/i',
        '/^(correo\s+(electronico|electrónico|final|redactado|listo)[: ]*$)/i',
        '/^(resultado|borrador|version|versión)[: ]*$/i',
        '/^-{3,}$/',
        '/^\*{3,}$/',
        '/^(here is|next[: ]|finally[,: ]|your welcome)/i',
        '/^(note|nota)[: ]/i',
    ];

    $lineas = preg_split("/\r\n|\n|\r/", $texto);
    $filtradas = [];

    foreach ($lineas as $linea) {
        $limpia = trim($linea);
        if ($limpia === '') {
            $filtradas[] = '';
            continue;
        }
        $esMeta = false;
        foreach ($patronesMeta as $patron) {
            if (preg_match($patron, $limpia)) {
                $esMeta = true;
                break;
            }
        }
        if ($esMeta) continue;
        // Quitar negritas markdown residuales al inicio/fin de línea
        $filtradas[] = preg_replace('/^\*\*|\*\*$/', '', $limpia);
    }

    // Colapsar más de dos saltos de línea seguidos
    return trim(preg_replace("/\n{3,}/", "\n\n", implode("\n", $filtradas)));
}

// ─── Detectar intención: ¿redactar desde cero o solo corregir? ───────────────
// MEJORA: detección más amplia para cubrir variaciones de dictado por voz
function detectarIntencion(string $texto): string
{
    $norm = mb_strtolower(trim($texto), 'UTF-8');

    $palabrasClave = [
        'genera',    'genérame', 'generame',
        'redacta',   'redáctame', 'redactame',
        'escribe',   'crea',     'prepara',
        'hazme',     'haz',      'construye',
        'necesito',  'quiero',
    ];

    $objetivos = [
        'correo',    'email',    'mail',
        'mensaje',   'nota',     'carta',
    ];

    foreach ($palabrasClave as $pc) {
        foreach ($objetivos as $obj) {
            if (
                strpos($norm, $pc . ' ' . $obj) !== false ||
                strpos($norm, $pc . ' un ' . $obj) !== false ||
                strpos($norm, $pc . ' una ' . $obj) !== false ||
                strpos($norm, $pc . 'me un ' . $obj) !== false ||
                strpos($norm, $pc . 'me una ' . $obj) !== false
            ) {
                return 'redactar';
            }
        }
    }

    return 'corregir';
}

$intencion = detectarIntencion($mensaje);

// ─── Prompts del sistema ──────────────────────────────────────────────────────
if ($modo === 'texto') {

    $sistema = <<<'PROMPT'
Eres un editor de textos profesionales en espanol especializado en corregir dictados por voz.
El usuario te da texto dictado con errores tipicos: muletillas, frases cortadas, palabras mal reconocidas por el microfono.
Tu unica tarea: devolver el texto mejorado, claro y coherente.

Reglas estrictas:
- Responde SOLO con el texto mejorado, sin explicaciones ni comentarios.
- Usa etiquetas <texto> y </texto> para envolverlo.
- PROHIBIDO inventar, ampliar o anadir contenido que no este en el original.
- Respeta la longitud y el alcance del original: una instruccion corta se devuelve corta, no como parrafo largo.
- Elimina muletillas de dictado: "eh", "este", "bueno", "o sea", "mmm", "yyy".
- Convierte numeros dictados en letra al digito: "el uno" → 1, "el dos" → 2, etc.
- Si el microfono genero frases rotas o sin sentido, interpretalas por el contexto mas logico.
- LISTA: Si el texto contiene varias acciones o ideas encadenadas (verbos como mejorar, reducir,
  ampliar, revisar, anadir, hacer, crear, enviar, corregir, etc.), formatealas como lista numerada.
  Reglas de la lista:
  a) Si hay una oracion introductoria antes de los items, termina esa oracion con ":" y coloca
     la lista a continuacion. Cada item empieza en mayuscula y termina en punto.
  b) Si hay una oracion de cierre despues de los items, ponla como parrafo separado tras la lista.
  c) Separa intro, lista y cierre con una linea en blanco entre cada bloque.
- Corrige puntuacion, tildes y mejora el estilo sin cambiar el significado.
- Mantén el tono original (formal o informal).
- Idioma: espanol. Nunca traduzcas.

Ejemplo:
Dictado: "los objetivos son mejorar el servicio reducir costes y ampliar la cartera se recomienda revisar los resultados el proximo mes"
Salida:
<texto>
Los objetivos son:

1. Mejorar el servicio.
2. Reducir costes.
3. Ampliar la cartera.

Se recomienda revisar los resultados el próximo mes.
</texto>

Texto del usuario:
PROMPT;

} elseif ($intencion === 'redactar') {

    $sistema = <<<'PROMPT'
Eres un redactor de correos electronicos profesionales en espanol.
El usuario te da una instruccion breve (puede venir de un dictado por voz con errores).
Tu unica tarea: redactar el correo completo listo para enviar.

Reglas:
1. Responde SOLO con el correo dentro de <correo> y </correo>. Nada mas.
2. PROHIBIDO inventar informacion que no este en la instruccion del usuario.
3. PROHIBIDO repetir informacion ni resumir al final lo que ya dijiste en el correo.
4. PROHIBIDO frases de cortesia no solicitadas: "Espero que estes bien", "Ha sido un placer", "Quedo a tu disposicion", "No dudes en contactarme" y similares.
5. SALUDO: una sola linea breve: "Hola," o "Buenos dias,". Sin mas.
6. FIRMA: solo esto al final:
   Saludos,
   [Tu nombre]
7. LISTA: Si la instruccion menciona varios puntos o acciones (anadir, hacer, enviar, revisar,
   preparar, corregir, actualizar, crear, confirmar, llamar, incluir, adjuntar),
   presentalos como lista numerada. Sin introduccion redundante antes de la lista.
8. Elimina muletillas de dictado: "eh", "este", "bueno", "o sea".
9. Convierte numeros dictados en letra al digito: "el uno" → 1, "el dos" → 2, etc.
10. Si faltan datos imprescindibles usa marcadores: [nombre], [fecha].
11. Tono: profesional y directo. Idioma: espanol. Nunca traduzcas.

Instruccion del usuario:
PROMPT;

} else {

    $sistema = <<<'PROMPT'
Eres un corrector de textos dictados por voz en espanol. Conviertes el dictado en un correo profesional limpio y listo para enviar.

Reglas estrictas (sin excepciones):
1. Responde SOLO con el correo dentro de <correo> y </correo>. Nada mas.
2. PROHIBIDO inventar o ampliar contenido que no este en el dictado.
3. PROHIBIDO repetir informacion ni anadir resumen al final.
4. PROHIBIDO frases de cortesia no solicitadas: "Espero que se encuentre bien", "Ha sido un placer", "Quedo a su disposicion", "No dude en contactarme" y similares.
5. SALUDO: una sola linea: "Hola," o "Buenos dias,". Sin mas.
6. FIRMA: solo esto:
   Saludos,
   [Tu nombre]
7. LISTA: Detecta y formatea como lista numerada cuando el dictado contenga:
   a) Enumeraciones: "el uno... el dos... el tres...", "primero... segundo..."
   b) Varias acciones con verbos: anadir, hacer, enviar, revisar, preparar, corregir,
      actualizar, crear, modificar, confirmar, adjuntar, incluir, verificar, contactar.
   c) Patrones: "tenemos las siguientes tareas:", "hay que:", "lo siguiente:"
   IMPORTANTE: Si hay una oracion introductoria antes de "hay que" o antes de la lista de acciones,
   esa oracion se convierte en el parrafo de apertura terminado en ":" y las acciones forman la lista.
   Cada item de la lista empieza en mayuscula y termina en punto.
8. Elimina muletillas de voz: "eh", "este", "bueno", "o sea", "mmm", "yyy".
9. Convierte numeros dictados en letra al digito: "el uno" → 1, "el dos" → 2, "el tres" → 3.
10. Corrige frases mal reconocidas por el microfono segun el contexto mas logico.
11. Corrige puntuacion y tildes. Tono directo y profesional. Idioma: espanol. Nunca traduzcas.

Ejemplos de referencia:

Dictado: "corrige estos datos el uno el dos y el tres"
Salida:
<correo>
Hola,

Corrige estos datos: 1, 2 y 3.

Saludos,
[Tu nombre]
</correo>

Dictado: "tenemos las siguientes tareas anadir el logo hacer el informe y revisar los datos"
Salida:
<correo>
Hola,

Tenemos las siguientes tareas:
1. Anadir el logo.
2. Hacer el informe.
3. Revisar los datos.

Saludos,
[Tu nombre]
</correo>

Dictado: "necesito que el equipo lo tenga todo listo para la presentacion con el cliente del viernes hay que revisar las diapositivas eh corregir los errores del presupuesto anadir los logos de la empresa y confirmar la hora de la reunion con secretaria"
Salida:
<correo>
Hola,

Necesito que el equipo lo tenga todo listo para la presentacion con el cliente del viernes:

1. Revisar las diapositivas.
2. Corregir los errores del presupuesto.
3. Anadir los logos de la empresa.
4. Confirmar la hora de la reunion con secretaria.

Saludos,
[Tu nombre]
</correo>

Borrador dictado:
PROMPT;

}

// ─── Llamada a Ollama con reintentos ─────────────────────────────────────────
$respuestaRaw    = false;
$codigoHttp      = 0;
$datos           = null;
$detalleConexion = null;

$payload = json_encode([
    "model"   => $modelo,
    "prompt"  => $sistema . "\n" . $mensaje,
    "stream"  => true,
    "options" => [
        "temperature"    => 0.3,
        "top_p"          => 0.92,
        "num_predict"    => 450,
        "repeat_penalty" => 1.1,
    ]
], JSON_UNESCAPED_UNICODE);

for ($intento = 1; $intento <= $maxIntentos; $intento++) {

    $textoAcumulado  = "";
    $bufferLinea     = "";
    $errorStream     = null;
    $abortado        = false;

    $ch = curl_init($ollamaUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => $timeoutGeneracion,
        CURLOPT_WRITEFUNCTION  => function ($ch, $chunk) use (&$textoAcumulado, &$bufferLinea, &$errorStream, &$abortado) {
            $bufferLinea .= $chunk;
            $lineas = explode("\n", $bufferLinea);
            $bufferLinea = array_pop($lineas); // último fragmento incompleto
            foreach ($lineas as $linea) {
                $linea = trim($linea);
                if ($linea === "") continue;
                $obj = json_decode($linea, true);
                if (!is_array($obj)) continue;
                if (isset($obj["error"])) {
                    $errorStream = $obj["error"];
                    $abortado = true;
                    return 0; // aborta la transferencia cURL
                }
                if (isset($obj["response"])) {
                    $textoAcumulado .= $obj["response"];
                }
            }
            return strlen($chunk);
        },
    ]);

    $curlOk = curl_exec($ch);

    if ($curlOk === false && !$abortado) {
        $detalleConexion = curl_error($ch);
        curl_close($ch);
        break;
    }

    $codigoHttp = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    // Reintentar solo si Ollama está cargando el modelo
    $modeloCargando =
        $errorStream !== null &&
        strpos($errorStream, "loading model") !== false;

    if (!$modeloCargando) break;
    if ($intento < $maxIntentos) usleep($pausaMicrosegundos);
}

// ─── Respuesta al cliente ─────────────────────────────────────────────────────
if ($detalleConexion !== null) {
    // Timeout pero con texto parcial: devolver lo generado es mejor que un error
    if ($textoAcumulado !== "" && str_contains($detalleConexion, "timed out")) {
        echo json_encode([
            "ok"       => true,
            "response" => limpiarRespuestaModelo($textoAcumulado),
            "modelo"   => $modelo,
            "modo"     => $modo,
            "intencion"=> $intencion,
            "aviso"    => "Respuesta incompleta: el modelo superó el tiempo límite.",
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(502);

    $pasos = match(true) {
        str_contains($detalleConexion, "timed out")           => "Ollama tardó demasiado. Ejecuta en la VM: ollama run {$modelo} \"hola\" para precalentar el modelo.",
        str_contains($detalleConexion, "Connection refused")  => "Ollama no está corriendo. En la VM ejecuta: ollama serve",
        str_contains($detalleConexion, "Could not resolve")   => "No se resuelve la IP de la VM. Verifica OLLAMA_URL en back.php.",
        str_contains($detalleConexion, "Network unreachable") => "La VM no es accesible. Comprueba que está encendida y en la misma red.",
        default                                               => "Comprueba: 1) VM encendida  2) ollama serve corriendo  3) IP correcta en back.php",
    };

    echo json_encode([
        "ok"     => false,
        "error"  => "No se pudo conectar con Ollama.",
        "detail" => $detalleConexion,
        "hint"   => $pasos,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($errorStream !== null) {
    http_response_code(502);
    echo json_encode([
        "ok"     => false,
        "error"  => "Ollama devolvió un error.",
        "detail" => $errorStream,
        "hint"   => strpos($errorStream, "model") !== false
            ? "Verifica que el modelo existe: ollama list"
            : "Reinicia Ollama en la VM: ollama serve",
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($codigoHttp >= 400) {
    http_response_code(502);
    echo json_encode([
        "ok"     => false,
        "error"  => "Ollama devolvió HTTP " . $codigoHttp . ".",
        "hint"   => "Verifica que el modelo existe en Ollama: ollama list",
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($textoAcumulado === "") {
    http_response_code(502);
    echo json_encode([
        "ok"     => false,
        "error"  => "Ollama no devolvió contenido.",
        "hint"   => "El modelo puede estar descargado de memoria. Ejecuta en la VM: ollama run {$modelo} \"hola\"",
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    "ok"       => true,
    "response" => limpiarRespuestaModelo($textoAcumulado),
    "modelo"   => $modelo,
    "modo"     => $modo,
    "intencion"=> $intencion,
], JSON_UNESCAPED_UNICODE);