# Desarrollo de actividades según rúbrica

Este documento resume cada punto siguiendo las 4 fases de la rúbrica:

1. Introducción breve y contextualización.
2. Desarrollo detallado y preciso.
3. Aplicación práctica.
4. Conclusión breve.

---

# 1. Introducción a IA - Instalación de Ollama y modelos

## 1. Introducción breve y contextualización

La inteligencia artificial local permite ejecutar modelos en nuestro propio ordenador sin depender de servicios externos. En esta práctica usamos **Ollama** para instalar y ejecutar modelos de lenguaje de forma local.

## 2. Desarrollo detallado y preciso

Ollama funciona como un servidor local de modelos. Una vez instalado, permite descargar modelos con `ollama pull` y ejecutarlos con `ollama run`. Los modelos principales usados en el proyecto son:

- `llama3:latest`: modelo general para corrección y generación de texto.
- `nomic-embed-text:v1.5`: modelo para crear embeddings.
- `qwen2.5:3b`: modelo ligero para generar respuestas en RAG.

## 3. Aplicación práctica

Comandos básicos:

```powershell
ollama list
ollama pull llama3:latest
ollama pull nomic-embed-text:v1.5
ollama pull qwen2.5:3b
ollama run llama3:latest "Responde solo: listo"
```

Un error común es iniciar la aplicación web sin tener Ollama abierto. Para evitarlo, primero se comprueba que `localhost:11434` responde o se ejecuta `ollama list`.

## 4. Conclusión breve

Ollama es la base de todos los ejercicios porque permite usar IA local. A partir de esta instalación se construyen aplicaciones con PHP, Python, JSONL, embeddings y RAG.

---

# 2. Continuamos con IA - MicroChatGPT con PHP

Proyecto usado:

```text
C:\xampp\htdocs\Ia_VM\004-Corrector_IA
```

## 1. Introducción breve y contextualización

El MicroChatGPT con PHP demuestra cómo una página web puede enviar una pregunta a un backend y recibir una respuesta generada por IA local. Es una primera aproximación a una aplicación conversacional.

## 2. Desarrollo detallado y preciso

La aplicación usa tres piezas principales:

- `index.html`: interfaz del usuario.
- `back.php`: backend que recibe la petición.
- Ollama: motor local que genera la respuesta.

El navegador no llama directamente al modelo. Primero envía el texto a PHP, y PHP prepara el prompt y se comunica con Ollama mediante HTTP.

## 3. Aplicación práctica

Ruta de prueba:

```text
http://localhost/Ia_VM/004-Corrector_IA/
```

Prompt rápido:

```text
Explícame en tres frases qué es una inteligencia artificial local.
```

Comprobación de Ollama:

```text
http://localhost/Ia_VM/004-Corrector_IA/ping.php
```

Error común: abrir la web sin Apache activo en XAMPP. Para evitarlo, se debe iniciar Apache antes de entrar en la URL.

## 4. Conclusión breve

Este ejercicio muestra la estructura mínima de una aplicación de IA: interfaz, backend y modelo local. Sirve como base para las prácticas posteriores.

---

# 3. Repaso - Pregunta y respuesta

Proyecto usado:

```text
C:\xampp\htdocs\Ia_VM\004-Corrector_IA
```

## 1. Introducción breve y contextualización

El formato de pregunta y respuesta permite interactuar con la IA de manera directa. El usuario escribe una consulta y el sistema devuelve una respuesta generada por el modelo.

## 2. Desarrollo detallado y preciso

En este punto se repasa el flujo básico:

```text
Usuario escribe pregunta
        ↓
Navegador envía datos
        ↓
PHP recibe la petición
        ↓
PHP llama a Ollama
        ↓
La respuesta vuelve a la interfaz
```

La clave está en construir bien el prompt para que el modelo entienda qué debe responder y en mostrar la respuesta de forma clara al usuario.

## 3. Aplicación práctica

Ejemplo de pregunta:

```text
¿Qué ventajas tiene usar IA local en vez de una API externa?
```

Ejemplo de instrucción más precisa:

```text
Responde con una lista de 3 ventajas de usar IA local.
```

Error común: hacer preguntas demasiado ambiguas. Para evitarlo, conviene pedir formato, extensión y tema.

## 4. Conclusión breve

El patrón pregunta-respuesta es el núcleo de las aplicaciones conversacionales. Más adelante se mejora añadiendo datos propios y recuperación de información.

---

# 4. Corrector de correos con IA

Proyecto usado:

```text
C:\xampp\htdocs\Ia_VM\004-Corrector_IA
```

## 1. Introducción breve y contextualización

Un corrector de correos con IA aplica modelos de lenguaje a una tarea práctica: mejorar textos escritos, corregir errores y dar un tono más profesional.

## 2. Desarrollo detallado y preciso

La aplicación transforma un texto inicial en una versión más clara. Para ello, el backend crea un prompt con instrucciones como corregir ortografía, mejorar redacción y mantener el sentido original.

Este tipo de uso es útil en contextos de oficina, atención al cliente, formación y comunicación profesional.

## 3. Aplicación práctica

Texto para probar:

```text
hola necesito que me envies los documentos rapido porque tengo que cerrar el informe y no me da tiempo gracias
```

Resultado esperado:

```text
Hola:

Necesito que me envíes los documentos lo antes posible para poder cerrar el informe a tiempo.

Gracias.
```

Error común: pedir a la IA que cambie demasiado el contenido. Para evitarlo, se indica que debe conservar el significado original.

## 4. Conclusión breve

Esta práctica demuestra que la IA puede resolver una necesidad real de productividad. Además, reutiliza el mismo flujo técnico del MicroChatGPT.

---

# 5. Entrenamiento de IA - JSON con preguntas y respuestas

Proyecto usado:

```text
C:\xampp\htdocs\Ia_VM\006-leyes-migratorias_entrenamiento
```

## 1. Introducción breve y contextualización

El entrenamiento mediante JSONL permite crear una pequeña base de conocimiento con preguntas y respuestas propias. En este caso se aplica al tema de leyes migratorias.

## 2. Desarrollo detallado y preciso

El archivo principal de datos es:

```text
materiales\leyes_migratorias.jsonl
```

Cada línea contiene una pregunta y una respuesta:

```json
{"question":"¿Qué es el NIE?","answer":"El NIE es el número de identidad de extranjero."}
```

El motor compara la pregunta del usuario con las preguntas guardadas. Si encuentra una coincidencia clara, responde desde el JSONL. Si no, puede usar Ollama con contexto.

## 3. Aplicación práctica

Arrancar la interfaz:

```powershell
cd C:\xampp\htdocs\Ia_VM\006-leyes-migratorias_entrenamiento
python 003-interfaz_web.py
```

Abrir:

```text
http://localhost:8060
```

Pregunta de prueba:

```text
¿Qué es el NIE?
```

Error común: escribir JSON mal formado. Para evitarlo, cada línea debe ser un objeto JSON válido.

## 4. Conclusión breve

El JSONL permite introducir conocimiento propio de forma sencilla. Este paso prepara el camino hacia RAG, donde la búsqueda se vuelve semántica y más potente.

---

# 6. RAG - Bases de datos vectoriales

Proyecto usado:

```text
C:\xampp\htdocs\Ia_VM\007-Ejercicio_RAG
```

## 1. Introducción breve y contextualización

RAG significa generación aumentada por recuperación. Consiste en buscar información relevante en documentos propios y entregarla al modelo para que responda con más precisión.

## 2. Desarrollo detallado y preciso

El sistema usa:

- `nomic-embed-text:v1.5` para convertir texto en vectores.
- `ChromaDB` para guardar y buscar esos vectores.
- `qwen2.5:3b` para generar la respuesta final.

Flujo:

```text
Pregunta
  ↓
Embedding
  ↓
Búsqueda en ChromaDB
  ↓
Fragmentos legales relevantes
  ↓
Respuesta con Ollama
```

## 3. Aplicación práctica

Indexar si hace falta:

```powershell
cd C:\xampp\htdocs\Ia_VM\007-Ejercicio_RAG
python 006-cargar-documento.py
```

Arrancar demo:

```powershell
python 008-demo-migracion.py
```

Abrir:

```text
http://localhost:8080
```

Pregunta de prueba:

```text
¿Qué derechos tienen los extranjeros en España?
```

Error común: abrir directamente el HTML. Esta demo necesita el servidor Python en `localhost:8080`.

## 4. Conclusión breve

RAG mejora la calidad de las respuestas porque el modelo no depende solo de su entrenamiento general. Responde usando documentos reales recuperados desde una base vectorial.

---

# 7. Stack mini con PHP y Python

Proyecto usado:

```text
C:\xampp\htdocs\Ia_VM\009-ejercicio_final_flush_ley de migracion
```

## 1. Introducción breve y contextualización

Este ejercicio integra varios componentes en una aplicación final. PHP gestiona la web, Python realiza la búsqueda semántica y Ollama genera la respuesta.

## 2. Desarrollo detallado y preciso

El stack funciona así:

```text
005-demo.php
  ↓
003-funcion.php
  ↓
buscador.py
  ↓
ChromaDB
  ↓
Ollama
```

PHP y Python se comunican porque PHP ejecuta o llama al buscador en Python para obtener los fragmentos relevantes. Después PHP usa esos fragmentos como contexto para pedir la respuesta a Ollama.

## 3. Aplicación práctica

Indexar documentos:

```powershell
cd "C:\xampp\htdocs\Ia_VM\009-ejercicio_final_flush_ley de migracion"
python 001-indexar.py
```

Verificar:

```powershell
python verificar.py
```

Abrir demo:

```text
http://localhost/Ia_VM/009-ejercicio_final_flush_ley%20de%20migracion/005-demo.php
```

Pregunta de prueba:

```text
¿Qué ocurre con los menores extranjeros no acompañados?
```

Error común: no tener Apache activo o no haber indexado los documentos. Para evitarlo, se revisa XAMPP y se ejecuta `verificar.py`.

## 4. Conclusión breve

Este punto une todo lo aprendido: interfaz web, backend PHP, scripts Python, embeddings, base vectorial y modelo local. Es la demostración más completa del proyecto.
