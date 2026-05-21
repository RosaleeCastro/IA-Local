<?php
// ==============================================
// 005-demo.php
// Interfaz completa con dos modos:
// - Modo automático: procesa preguntas del archivo
// - Modo libre: el usuario escribe su pregunta
// DISEÑO: fondo blanco con acentos en morado
// ==============================================

header("Content-Type: text/html; charset=utf-8");

// Incluir funciones - ya NO ejecutan nada al cargarse
require_once __DIR__ . "/003-funcion.php";

// ----------------------------------------------
// MODO API: responde peticiones AJAX
// Cuando JS llama a ?api=consultar este bloque
// procesa la pregunta y devuelve JSON
// ----------------------------------------------
if (isset($_GET["api"]) && $_GET["api"] === "consultar") {

    header("Content-Type: application/json; charset=utf-8");

    $pregunta = trim($_POST["pregunta"] ?? "");

    if ($pregunta === "") {
        echo json_encode([
            "ok"    => false,
            "error" => "La pregunta está vacía"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Paso 1: buscar fragmentos en ChromaDB
    $resultado = buscarFragmentos($pregunta);

    if (isset($resultado["error"])) {
        echo json_encode([
            "ok"    => false,
            "error" => $resultado["error"]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Paso 2: generar respuesta con Ollama
    $respuesta = preguntarOllama($pregunta, $resultado["fragmentos"]);

    // Paso 3: devolver todo al frontend
    echo json_encode([
        "ok"         => true,
        "pregunta"   => $pregunta,
        "respuesta"  => $respuesta,
        "fragmentos" => $resultado["fragmentos"]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// ----------------------------------------------
// LEER PREGUNTAS DEL ARCHIVO
// ----------------------------------------------
$preguntas_archivo = file(
    __DIR__ . "/preguntas.txt",
    FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
) ?: [];

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Asistente Legal · Extranjería España</title>

<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<style>
/* ==============================================
   VARIABLES DE COLOR
   Fondo blanco con acentos en morado medio
   ============================================== */
:root {
    --morado:        #7c3aed;   /* color principal */
    --morado-suave:  #8b5cf6;   /* hover y acentos */
    --morado-claro:  #ede9fe;   /* fondos suaves */
    --morado-borde:  #ddd6fe;   /* bordes */
    --morado-texto:  #5b21b6;   /* textos en morado */
    --gris-texto:    #374151;   /* texto principal */
    --gris-suave:    #6b7280;   /* texto secundario */
    --gris-fondo:    #f9fafb;   /* fondo general */
    --blanco:        #ffffff;   /* cards y panels */
    --error:         #dc2626;
}

*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    /* Fondo casi blanco con levísimo toque morado */
    background: var(--gris-fondo);
    color: var(--gris-texto);
    font-family: 'DM Sans', sans-serif;
    min-height: 100vh;
}

/* ==============================================
   HEADER
   Blanco con línea morada inferior
   ============================================== */
header {
    background: var(--blanco);
    border-bottom: 3px solid var(--morado);
    padding: 0 2rem;
    height: 66px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 1px 8px rgba(124, 58, 237, 0.08);
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-logo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-icono {
    width: 38px;
    height: 38px;
    background: var(--morado-claro);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.header-titulo {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.25rem;
    font-weight: 700;
    /* Morado no tan oscuro para el título */
    color: var(--morado-texto);
}

.header-subtitulo {
    font-size: 11px;
    color: var(--gris-suave);
    letter-spacing: 0.03em;
}

.header-badge {
    font-size: 11px;
    font-weight: 500;
    color: var(--morado);
    background: var(--morado-claro);
    border: 1px solid var(--morado-borde);
    padding: 4px 12px;
    border-radius: 20px;
    letter-spacing: 0.04em;
}

/* ==============================================
   LAYOUT
   ============================================== */
.layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    min-height: calc(100vh - 66px);
}

/* ==============================================
   SIDEBAR
   Blanco con borde derecho morado suave
   ============================================== */
.sidebar {
    background: var(--blanco);
    border-right: 1px solid var(--morado-borde);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
    overflow-y: auto;
}

.sidebar-titulo {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--morado-texto);
}

.sidebar-desc {
    font-size: 12px;
    color: var(--gris-suave);
    line-height: 1.6;
    margin-top: 4px;
}

.divisor {
    height: 1px;
    background: var(--morado-borde);
}

.etiqueta {
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--morado-suave);
    margin-bottom: 8px;
}

/* Botón de pregunta predefinida */
.btn-pregunta {
    display: block;
    width: 100%;
    text-align: left;
    background: none;
    border: 1px solid var(--morado-borde);
    border-radius: 8px;
    padding: 9px 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    color: var(--gris-texto);
    cursor: pointer;
    transition: all 0.15s;
    margin-bottom: 6px;
    line-height: 1.5;
}

.btn-pregunta:hover {
    background: var(--morado-claro);
    border-color: var(--morado-suave);
    color: var(--morado-texto);
    transform: translateX(3px);
}

/* Botón ejecutar todas */
.btn-ejecutar-todas {
    width: 100%;
    background: var(--morado);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 11px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-ejecutar-todas:hover {
    background: var(--morado-suave);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.25);
}

.btn-ejecutar-todas:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Estadísticas */
.stats {
    background: var(--morado-claro);
    border-radius: 10px;
    padding: 12px 14px;
    margin-top: auto;
}

.stat-fila {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: var(--gris-suave);
    padding: 3px 0;
}

.stat-fila span:last-child {
    color: var(--morado-texto);
    font-weight: 500;
}

/* ==============================================
   CONTENIDO PRINCIPAL
   ============================================== */
.contenido {
    padding: 1.5rem 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
    max-width: 860px;
}

/* ==============================================
   TARJETA CONSULTA LIBRE
   ============================================== */
.tarjeta-consulta {
    background: var(--blanco);
    border: 1px solid var(--morado-borde);
    border-radius: 14px;
    padding: 1.3rem;
    box-shadow: 0 1px 6px rgba(124, 58, 237, 0.06);
}

textarea {
    width: 100%;
    background: var(--gris-fondo);
    border: 1px solid var(--morado-borde);
    border-radius: 10px;
    padding: 12px 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    color: var(--gris-texto);
    resize: none;
    height: 80px;
    outline: none;
    transition: border-color 0.2s;
    line-height: 1.6;
}

textarea:focus {
    border-color: var(--morado);
    background: var(--blanco);
}

textarea::placeholder {
    color: #9ca3af;
}

.fila-botones {
    display: flex;
    gap: 10px;
    margin-top: 10px;
    align-items: center;
}

.btn-enviar {
    background: var(--morado);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 22px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-enviar:hover {
    background: var(--morado-suave);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.25);
}

.btn-enviar:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.hint {
    font-size: 11px;
    color: var(--gris-suave);
}

/* ==============================================
   ESTADO DE PROGRESO
   ============================================== */
.estado {
    display: none;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: var(--morado-claro);
    border: 1px solid var(--morado-borde);
    border-radius: 10px;
    font-size: 13px;
    color: var(--morado-texto);
}

.spinner {
    width: 18px;
    height: 18px;
    border: 2px solid var(--morado-borde);
    border-top-color: var(--morado);
    border-radius: 50%;
    animation: girar 0.7s linear infinite;
    flex-shrink: 0;
}

@keyframes girar { to { transform: rotate(360deg); } }

/* ==============================================
   TARJETAS DE RESULTADO
   ============================================== */
.tarjeta-resultado {
    background: var(--blanco);
    border: 1px solid var(--morado-borde);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(124, 58, 237, 0.06);
    animation: aparecer 0.35s ease;
}

@keyframes aparecer {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Cabecera morada suave con la pregunta */
.tarjeta-cabecera {
    background: var(--morado-claro);
    padding: 12px 18px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    border-bottom: 1px solid var(--morado-borde);
}

.tarjeta-numero {
    min-width: 26px;
    height: 26px;
    background: var(--morado);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 500;
    color: white;
    flex-shrink: 0;
}

.tarjeta-pregunta {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--morado-texto);
    line-height: 1.4;
}

/* Respuesta en texto oscuro sobre blanco */
.tarjeta-respuesta {
    padding: 1.2rem 1.3rem 0.8rem;
    font-size: 14px;
    line-height: 1.85;
    color: var(--gris-texto);
}

.fuentes-label {
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--morado-suave);
    padding: 10px 1.3rem 8px;
    border-top: 1px solid var(--morado-borde);
    margin-top: 8px;
}

.fuentes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 8px;
    padding: 0 1.3rem 1.2rem;
}

.fuente-card {
    background: var(--gris-fondo);
    border: 1px solid var(--morado-borde);
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 11px;
    color: var(--gris-suave);
    line-height: 1.5;
}

.fuente-origen {
    display: inline-block;
    font-size: 9px;
    font-weight: 500;
    color: var(--morado-texto);
    background: var(--morado-claro);
    border-radius: 4px;
    padding: 2px 7px;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

/* ==============================================
   ESTADO VACÍO
   ============================================== */
.vacio {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--gris-suave);
}

.vacio-icono {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    opacity: 0.6;
}

.vacio h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.3rem;
    color: var(--morado-texto);
    margin-bottom: 8px;
}

.vacio p {
    font-size: 13px;
    line-height: 1.7;
    max-width: 340px;
    margin: 0 auto;
}
/* ==============================================
   MEJORAS DE LECTURA EN LA RESPUESTA
   ============================================== */

/* La respuesta ahora tiene márgenes y párrafos */
.tarjeta-respuesta {
    padding: 1.4rem 2rem;        /* más espacio lateral */
    font-size: 14px;
    line-height: 1.9;            /* más espacio entre líneas */
    color: var(--gris-texto);
    max-width: 720px;            /* limitar ancho para mejor lectura */
}

/* Párrafos dentro de la respuesta separados */
.tarjeta-respuesta p {
    margin-bottom: 1rem;
}

.tarjeta-respuesta p:last-child {
    margin-bottom: 0;
}

/* Números de lista con estilo */
.lista-num {
    display: inline-block;
    min-width: 24px;
    font-weight: 600;
    color: var(--morado);
}

/* Negritas en morado para destacar */
.tarjeta-respuesta strong {
    color: var(--morado-texto);
    font-weight: 500;
}

/* Fuentes con mejor tipografía */
.fuente-texto {
    font-size: 11px;
    color: var(--gris-suave);
    line-height: 1.6;
    margin-top: 5px;
    margin-bottom: 0;
}
</style>
</head>
<body>

<header>
    <div class="header-logo">
        <div class="header-icono">⚖️</div>
        <div>
            <div class="header-titulo">Asistente Legal · Extranjería</div>
            <div class="header-subtitulo">Ley Orgánica 4/2000 · Real Decreto 316/2026</div>
        </div>
    </div>
    <span class="header-badge">RAG · España · 2026</span>
</header>

<div class="layout">

    <aside class="sidebar">
        <div>
            <p class="sidebar-titulo">Consultas automáticas</p>
            <p class="sidebar-desc">
                Ejecuta todas las preguntas predefinidas o
                haz clic en una para lanzarla individualmente.
            </p>
        </div>

        <div class="divisor"></div>

        <button class="btn-ejecutar-todas" id="btnTodas" onclick="ejecutarTodas()">
            ▶ Ejecutar todas las preguntas
        </button>

        <div>
            <p class="etiqueta">Preguntas predefinidas</p>
            <?php foreach ($preguntas_archivo as $pregunta):
                $ph = htmlspecialchars($pregunta);
                $pj = htmlspecialchars($pregunta, ENT_QUOTES);
            ?>
                <button class="btn-pregunta" onclick="consultarPregunta('<?= $pj ?>')">
                    <?= $ph ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="divisor"></div>

        <div class="stats">
            <p class="etiqueta" style="margin-bottom:8px">Base de conocimiento</p>
            <div class="stat-fila"><span>Documentos</span><span>2 leyes</span></div>
            <div class="stat-fila"><span>Fragmentos</span><span>157</span></div>
            <div class="stat-fila"><span>Embeddings</span><span>nomic-embed</span></div>
            <div class="stat-fila"><span>Modelo LLM</span><span>qwen2.5:3b</span></div>
            <div class="stat-fila"><span>Base vectorial</span><span>ChromaDB</span></div>
        </div>
    </aside>

    <main class="contenido">

        <div class="tarjeta-consulta">
            <p class="etiqueta">Consulta libre</p>
            <textarea
                id="inputPregunta"
                placeholder="Escribe tu pregunta sobre extranjería en España..."
            ></textarea>
            <div class="fila-botones">
                <button class="btn-enviar" id="btnEnviar" onclick="enviarConsulta()">
                    Consultar →
                </button>
                <span class="hint">Enter para enviar · Shift+Enter para salto de línea</span>
            </div>
        </div>

        <div class="estado" id="estado">
            <div class="spinner"></div>
            <span id="estadoTexto">Procesando consulta...</span>
        </div>

        <div id="resultados"></div>

        <div class="vacio" id="estadoVacio">
            <div class="vacio-icono">📜</div>
            <h3>Haz tu primera consulta</h3>
            <p>
                Escribe una pregunta o elige una del panel izquierdo.
                El sistema buscará los artículos más relevantes y
                generará una respuesta fundamentada en la ley.
            </p>
        </div>

    </main>
</div>

<script>
// ==============================================
// JAVASCRIPT
// Maneja las peticiones AJAX y actualiza el DOM
// ==============================================

let contadorRespuestas = 0;
let consultando        = false;

// Escapar HTML para evitar inyección de código
function escapar(texto) {
    return texto
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

// Mostrar spinner con mensaje personalizado
function mostrarEstado(mensaje) {
    const estado = document.getElementById('estado');
    document.getElementById('estadoTexto').textContent = mensaje;
    estado.style.display = 'flex';
    estado.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function ocultarEstado() {
    document.getElementById('estado').style.display = 'none';
}

// ----------------------------------------------
// FUNCIÓN PRINCIPAL: consultar una pregunta
// Hace la petición AJAX y muestra el resultado
// ----------------------------------------------
async function consultarPregunta(pregunta) {

    if (consultando) return;
    consultando = true;

    // Ocultar estado vacío en la primera consulta
    document.getElementById('estadoVacio').style.display = 'none';
    document.getElementById('btnEnviar').disabled = true;
    document.getElementById('btnTodas').disabled  = true;

    mostrarEstado(`Consultando: "${pregunta.substring(0, 55)}..."`);

    try {
        // Petición POST a ?api=consultar
        const formData = new FormData();
        formData.append('pregunta', pregunta);

        const respuesta = await fetch('?api=consultar', {
            method: 'POST',
            body:   formData
        });

        const datos = await respuesta.json();
        ocultarEstado();

        if (!datos.ok) {
            mostrarError(pregunta, datos.error);
        } else {
            mostrarResultado(datos);
        }

    } catch (e) {
        ocultarEstado();
        mostrarError(pregunta, "Error de conexión con el servidor");
    }

    document.getElementById('btnEnviar').disabled = false;
    document.getElementById('btnTodas').disabled  = false;
    consultando = false;
}

/// ----------------------------------------------
// FUNCIÓN: construir y mostrar tarjeta resultado
// Formatea el texto de la respuesta convirtiendo
// markdown básico a HTML para mejor lectura
// ----------------------------------------------
function mostrarResultado(datos) {

    contadorRespuestas++;
    const zona = document.getElementById('resultados');

    // ----------------------------------------------
    // FORMATEAR LA RESPUESTA
    // El modelo devuelve texto plano con markdown
    // básico que convertimos a HTML para que
    // se vea con formato legible
    // ----------------------------------------------
    function formatearRespuesta(texto) {

        return texto
            // Negritas: **texto** → <strong>texto</strong>
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')

            // Saltos de línea dobles → párrafos separados
            .replace(/\n\n/g, '</p><p>')

            // Saltos de línea simples → <br>
            .replace(/\n/g, '<br>')

            // Listas numéricas: "1. texto" al inicio de línea
            // las envolvemos en un elemento con sangría
            .replace(/(\d+)\.\s+/g, '<span class="lista-num">$1.</span> ');
    }

    // Construir HTML de las fuentes legales
    let fuentesHTML = '';
    datos.fragmentos.forEach(frag => {
        const origen = escapar(frag.origen);
        const texto  = escapar(frag.texto.substring(0, 180)) + '...';
        fuentesHTML += `
            <div class="fuente-card">
                <span class="fuente-origen">${origen}</span>
                <p class="fuente-texto">${texto}</p>
            </div>
        `;
    });

    // Insertar al principio para ver lo más reciente arriba
    zona.insertAdjacentHTML('afterbegin', `
        <div class="tarjeta-resultado">
            <div class="tarjeta-cabecera">
                <div class="tarjeta-numero">${contadorRespuestas}</div>
                <div class="tarjeta-pregunta">${escapar(datos.pregunta)}</div>
            </div>
            <div class="tarjeta-respuesta">
                <p>${formatearRespuesta(escapar(datos.respuesta))}</p>
            </div>
            <p class="fuentes-label">Artículos consultados como fuente</p>
            <div class="fuentes-grid">${fuentesHTML}</div>
        </div>
    `);

    zona.firstElementChild.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

// Mostrar error en tarjeta roja
function mostrarError(pregunta, mensaje) {
    contadorRespuestas++;
    const zona = document.getElementById('resultados');
    zona.insertAdjacentHTML('afterbegin', `
        <div class="tarjeta-resultado" style="border-color:#fca5a5">
            <div class="tarjeta-cabecera" style="background:#fef2f2; border-color:#fca5a5">
                <div class="tarjeta-numero" style="background:#dc2626">${contadorRespuestas}</div>
                <div class="tarjeta-pregunta" style="color:#991b1b">${escapar(pregunta)}</div>
            </div>
            <div class="tarjeta-respuesta" style="color:#dc2626">
                ⚠ Error: ${escapar(mensaje)}
            </div>
        </div>
    `);
}

// Enviar consulta libre desde el textarea
function enviarConsulta() {
    const input    = document.getElementById('inputPregunta');
    const pregunta = input.value.trim();
    if (!pregunta) return;
    input.value = '';
    consultarPregunta(pregunta);
}

// Ejecutar todas las preguntas secuencialmente
// igual que el 007-flush.php original
async function ejecutarTodas() {

    const preguntas = <?= json_encode($preguntas_archivo, JSON_UNESCAPED_UNICODE) ?>;
    const btn       = document.getElementById('btnTodas');

    btn.disabled    = true;
    btn.textContent = '⏳ Procesando...';

    for (let i = 0; i < preguntas.length; i++) {
        mostrarEstado(`Pregunta ${i + 1} de ${preguntas.length}: ${preguntas[i].substring(0, 50)}...`);
        await consultarPregunta(preguntas[i]);
        // Pausa entre preguntas para no saturar Ollama
        await new Promise(r => setTimeout(r, 500));
    }

    ocultarEstado();
    btn.disabled    = false;
    btn.textContent = '▶ Ejecutar todas las preguntas';
}

// Enter envía, Shift+Enter hace salto de línea
document.getElementById('inputPregunta').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        enviarConsulta();
    }
});
</script>

</body>
</html>