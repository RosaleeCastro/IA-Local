# Decisiones Tecnicas

## Objetivo del proyecto

Esta aplicacion convierte texto escrito o dictado por voz en un mensaje mejor formateado con ayuda de una IA ejecutada en Ollama local.

El flujo principal es:

1. La persona usuaria escribe o dicta un texto.
2. El navegador muestra el contenido en tiempo real en el area de entrada.
3. El `frontend` envia el texto completo al `backend`.
4. `back.php` reenvia la peticion a Ollama local.
5. La respuesta corregida se muestra en pantalla y se puede copiar.

## Decisiones actuales

### Arquitectura simple de dos archivos

- `index.html` contiene la interfaz, estilos y logica del navegador.
- `back.php` actua como proxy entre la pagina y Ollama.

Motivo:
Se prioriza simplicidad para aprendizaje, despliegue facil en XAMPP y cambios rapidos.

Consecuencia:
El proyecto es facil de entender, pero conviene mantener el JavaScript ordenado porque toda la UI vive en un solo archivo.

### Backend como intermediario

No se llama a Ollama directamente desde el navegador.

Motivo:
- Evita problemas de CORS.
- Permite ocultar detalles de conexion de Ollama.
- Facilita cambiar URL, modelo, timeouts y validaciones sin tocar el `frontend`.

### Ollama local

- Host actual por defecto: `localhost`
- Endpoint actual: `/api/generate`
- Modelo por defecto: `llama3:latest`

Motivo:
La IA vive en la misma maquina que XAMPP, asi que se reduce la dependencia de red y se simplifica el arranque del proyecto.

### Modelo por defecto para correccion y redaccion

Se usa `llama3:latest` como modelo por defecto.

Motivo:
Es un modelo local adecuado para corregir textos, redactar correos y seguir instrucciones de formato con una calidad razonable.

Consecuencia:
Se prioriza una buena calidad de redaccion frente al modelo mas pequeno posible.

### Envio manual en vez de autoenvio constante

La correccion se dispara de forma manual con boton o cuando termina el dictado.

Motivo:
Evita saturar Ollama mientras la persona esta escribiendo o hablando.

Consecuencia:
Menos carga para Ollama local y menos errores por peticiones simultaneas.

### Doble modo de IA segun la intencion del texto

El backend distingue entre dos tipos de entrada:

- `modo correccion`: cuando el usuario ya ha dictado o escrito un correo y solo quiere pulirlo
- `modo redaccion`: cuando el usuario pide expresamente generar o redactar un correo nuevo

Motivo:
Un mismo prompt no resuelve bien ambos casos. Si no se separan, el modelo puede responder con instrucciones, traducciones o texto incompleto.

Consecuencia:
La aplicacion puede comportarse mejor tanto como corrector de dictado como asistente de redaccion.

### Dictado en tiempo real

El texto reconocido se refleja en el `textarea` mientras la persona habla.

Motivo:
Da retroalimentacion inmediata y permite corregir si el reconocimiento falla.

### Accesibilidad como requisito del producto

La interfaz debe ser util para personas con baja vision o fatiga visual.

Motivo:
El caso de uso esta muy ligado al dictado por voz, asi que la accesibilidad no es opcional sino parte del valor central.

Lineas guia:
- buen contraste
- botones grandes
- estados visibles y tambien anunciables
- acciones comprensibles sin jerga tecnica
- foco de teclado claro

## Problemas detectados durante el proceso

### Microfono

Hubo fallos que no dependian del proyecto sino del sistema operativo o del navegador.

Aprendizaje:
Antes de depurar `SpeechRecognition`, conviene confirmar que el microfono funciona en otras aplicaciones.

### Configuracion local de Ollama

La aplicacion ya no depende de una IP de VM. Por defecto usa `http://localhost:11434/api/generate`.

Aprendizaje:
Conviene poder configurar la URL por entorno si se quiere usar otro host en el futuro.

### Tiempos de espera

Los tiempos de carga del modelo pueden superar limites por defecto de PHP.

Aprendizaje:
El tiempo maximo del script debe estar alineado con el timeout real de Ollama y con los reintentos.

## Criterios de diseño acordados

### Interfaz

- lenguaje claro y humano
- boton principal centrado en la tarea de voz
- estados faciles de entender
- sensacion de asistente de redaccion, no de herramienta tecnica

### Accesibilidad

- uso correcto de `label`
- soporte para teclado
- mensajes de estado con `aria-live`
- no depender solo del color para indicar estado

### Evolucion futura

Mejoras recomendadas en orden:

1. Rediseño visual y accesibilidad base.
2. Selector de modelo en la interfaz.
3. Boton para escuchar el resultado.
4. Historial local de mensajes.
5. Preferencias persistentes con `localStorage`.

## Regla de trabajo para siguientes cambios

Cuando se propongan cambios, evaluar siempre:

1. Si mejora la claridad para quien usa voz.
2. Si reduce carga innecesaria sobre Ollama.
3. Si mantiene o mejora accesibilidad.
4. Si puede entenderse facilmente por una persona que esta aprendiendo.
