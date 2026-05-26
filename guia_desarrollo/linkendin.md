# Posts para LinkedIn - Proyecto IA local

Contenido basado en `guia_desarrollo/desarrollo.md`.

Cada bloque está pensado como una publicación independiente.

---

# Post 1 - Introducción a IA local con Ollama

Hoy empecé construyendo la base de un entorno de inteligencia artificial local.

El objetivo era claro: ejecutar modelos de IA directamente en mi ordenador, sin depender de una API externa y entendiendo qué ocurre por debajo.

Para ello instalé y probé **Ollama**, una herramienta que permite descargar, ejecutar y gestionar modelos de lenguaje en local.

En esta primera parte trabajé con varios modelos:

- `llama3:latest`, para generación y corrección de texto.
- `nomic-embed-text:v1.5`, para crear embeddings.
- `qwen2.5:3b`, para generar respuestas dentro de sistemas RAG.

Algunos comandos básicos usados:

```powershell
ollama list
ollama pull llama3:latest
ollama pull nomic-embed-text:v1.5
ollama pull qwen2.5:3b
ollama run llama3:latest "Responde solo: listo"
```

Lo más importante de esta fase fue entender que Ollama funciona como un servidor local de modelos. Si no está activo, las aplicaciones que dependen de IA no pueden responder.

Aprendizaje clave:

La IA local no empieza en la interfaz, empieza en preparar bien el entorno.

#IA #Ollama #InteligenciaArtificial #DesarrolloLocal #Python #ModelosLocales

---

# Post 2 - MicroChatGPT con PHP

Después de preparar Ollama, el siguiente paso fue crear una primera aplicación web conversacional: un pequeño MicroChatGPT con PHP.

La idea era sencilla, pero muy importante para entender el flujo real de una aplicación de IA:

```text
Usuario escribe una pregunta
        ↓
Interfaz web
        ↓
Backend en PHP
        ↓
Ollama local
        ↓
Respuesta en pantalla
```

El proyecto usado fue:

```text
C:\xampp\htdocs\Ia_VM\004-Corrector_IA
```

La aplicación se apoya en tres piezas principales:

- `index.html`, como interfaz del usuario.
- `back.php`, como backend que recibe la petición.
- Ollama, como motor local de IA.

Lo interesante es que el navegador no habla directamente con el modelo. Primero envía la petición a PHP, y PHP se encarga de preparar el prompt y llamar a Ollama mediante HTTP.

Prueba rápida:

```text
Explícame en tres frases qué es una inteligencia artificial local.
```

Aprendizaje clave:

Una aplicación de IA no es solo el modelo. También necesita una interfaz, un backend y una comunicación bien organizada entre todas las partes.

#PHP #Ollama #IA #XAMPP #Backend #DesarrolloWeb

---

# Post 3 - Repaso de pregunta y respuesta con IA

Una vez creada la conexión entre PHP y Ollama, trabajé el patrón más básico de una aplicación conversacional: pregunta y respuesta.

Este patrón parece simple, pero es la base de muchas herramientas actuales de IA.

El flujo técnico fue:

```text
Usuario escribe una pregunta
        ↓
El navegador envía los datos
        ↓
PHP recibe la petición
        ↓
PHP llama a Ollama
        ↓
La respuesta vuelve a la interfaz
```

Proyecto usado:

```text
C:\xampp\htdocs\Ia_VM\004-Corrector_IA
```

Un ejemplo de pregunta general sería:

```text
¿Qué ventajas tiene usar IA local en vez de una API externa?
```

Pero la calidad mejora mucho cuando la instrucción es más precisa:

```text
Responde con una lista de 3 ventajas de usar IA local.
```

Aquí aprendí algo importante: no basta con preguntar, también hay que saber pedir.

Un prompt claro puede indicar:

- Tema.
- Formato.
- Longitud.
- Nivel de detalle.
- Tono de respuesta.

Aprendizaje clave:

El prompt es parte del desarrollo. Una buena instrucción puede mejorar mucho el resultado sin tocar el código.

#PromptEngineering #IA #PHP #Ollama #DesarrolloWeb #Aprendizaje

---

# Post 4 - Corrector de correos con IA

En esta fase convertí la IA local en una herramienta práctica: un corrector y redactor de correos.

El objetivo era aplicar la inteligencia artificial a una necesidad real: mejorar textos, corregir errores y dar un tono más profesional.

Proyecto usado:

```text
C:\xampp\htdocs\Ia_VM\004-Corrector_IA
```

Texto de prueba:

```text
hola necesito que me envies los documentos rapido porque tengo que cerrar el informe y no me da tiempo gracias
```

Resultado esperado:

```text
Hola:

Necesito que me envíes los documentos lo antes posible para poder cerrar el informe a tiempo.

Gracias.
```

La parte importante está en el prompt del backend. El sistema debe pedir a la IA que:

- Corrija ortografía.
- Mejore la redacción.
- Mantenga el significado original.
- Use un tono profesional.
- No invente información nueva.

Este caso muestra muy bien cómo una aplicación sencilla puede convertirse en una herramienta útil para productividad, comunicación y atención al cliente.

Aprendizaje clave:

La IA aporta más valor cuando resuelve una tarea concreta y bien definida.

#IA #Productividad #Ollama #PHP #CorreccionDeTexto #Automatizacion

---

# Post 5 - Entrenamiento con JSONL: preguntas y respuestas propias

El siguiente paso fue pasar de una IA general a una IA con una pequeña base de conocimiento propia.

Para ello trabajé con un archivo JSONL de preguntas y respuestas sobre leyes migratorias.

Proyecto usado:

```text
C:\xampp\htdocs\Ia_VM\006-leyes-migratorias_entrenamiento
```

Archivo de datos:

```text
materiales\leyes_migratorias.jsonl
```

Cada línea del archivo contiene una pregunta y una respuesta:

```json
{"question":"¿Qué es el NIE?","answer":"El NIE es el número de identidad de extranjero."}
```

La aplicación compara la pregunta del usuario con las preguntas guardadas. Si encuentra una coincidencia clara, responde desde el JSONL. Si no, puede usar Ollama con contexto.

Para arrancar la interfaz:

```powershell
cd C:\xampp\htdocs\Ia_VM\006-leyes-migratorias_entrenamiento
python 003-interfaz_web.py
```

URL:

```text
http://localhost:8060
```

Pregunta de prueba:

```text
¿Qué es el NIE?
```

Aprendizaje clave:

Antes de construir sistemas complejos, una base JSONL permite crear conocimiento propio de forma clara, editable y fácil de probar.

#Python #JSONL #IA #Ollama #Datos #LegalTech

---

# Post 6 - RAG y bases de datos vectoriales

Después de trabajar con JSONL, avancé hacia un sistema más potente: RAG.

RAG significa generación aumentada por recuperación. En lugar de pedirle al modelo que responda solo con su entrenamiento general, primero se buscan documentos relevantes y luego se le entregan como contexto.

Proyecto usado:

```text
C:\xampp\htdocs\Ia_VM\007-Ejercicio_RAG
```

El flujo fue:

```text
Pregunta del usuario
        ↓
Embedding con nomic-embed-text
        ↓
Búsqueda semántica en ChromaDB
        ↓
Fragmentos legales relevantes
        ↓
Respuesta generada con qwen2.5:3b
```

Tecnologías usadas:

- `nomic-embed-text:v1.5`, para convertir texto en vectores.
- `ChromaDB`, para guardar y buscar información semántica.
- `qwen2.5:3b`, para generar respuestas.
- Python, para coordinar el proceso.

Arranque de la demo:

```powershell
cd C:\xampp\htdocs\Ia_VM\007-Ejercicio_RAG
python 008-demo-migracion.py
```

URL:

```text
http://localhost:8080
```

Pregunta de prueba:

```text
¿Qué derechos tienen los extranjeros en España?
```

Aprendizaje clave:

RAG mejora la precisión porque la respuesta se apoya en documentos reales recuperados antes de generar texto.

#RAG #ChromaDB #Embeddings #Python #Ollama #IA #LegalTech

---

# Post 7 - Stack mini con PHP, Python, ChromaDB y Ollama

La última fase fue integrar todo en una aplicación más completa.

Aquí ya no se trata solo de llamar a un modelo. El objetivo fue conectar varias tecnologías para crear una demo final funcional.

Proyecto usado:

```text
C:\xampp\htdocs\Ia_VM\009-ejercicio_final_flush_ley de migracion
```

Stack utilizado:

```text
PHP
Python
ChromaDB
Ollama
XAMPP
JavaScript
```

Flujo de la aplicación:

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
        ↓
Respuesta con fuentes legales
```

PHP gestiona la interfaz y la petición web. Python se encarga de la búsqueda semántica. ChromaDB recupera los fragmentos relevantes. Ollama genera la respuesta final usando ese contexto.

Comandos de preparación:

```powershell
cd "C:\xampp\htdocs\Ia_VM\009-ejercicio_final_flush_ley de migracion"
python 001-indexar.py
python verificar.py
```

URL de la demo:

```text
http://localhost/Ia_VM/009-ejercicio_final_flush_ley%20de%20migracion/005-demo.php
```

Pregunta de prueba:

```text
¿Qué ocurre con los menores extranjeros no acompañados?
```

Aprendizaje clave:

El valor del proyecto está en la integración: una interfaz web, un backend PHP, scripts Python, búsqueda vectorial y generación local con IA.

#PHP #Python #RAG #ChromaDB #Ollama #XAMPP #IA #FullStack

---

# Post resumen - Evolución completa del proyecto

Durante este recorrido construí una evolución completa de aplicaciones con inteligencia artificial local.

El camino fue progresivo:

```text
1. Instalación de Ollama y modelos locales
2. MicroChatGPT con PHP
3. Pregunta y respuesta con prompts claros
4. Corrector de correos con IA
5. Base de conocimiento con JSONL
6. RAG con ChromaDB
7. Integración final con PHP + Python + Ollama
```

Lo más interesante fue ver cómo cada fase añade una capa nueva:

- Primero, el modelo local.
- Después, la interfaz web.
- Luego, el backend.
- Más tarde, datos propios.
- Finalmente, búsqueda semántica y RAG.

Este proyecto me permitió practicar desarrollo web, backend, Python, PHP, bases vectoriales y modelos locales en un mismo recorrido.

Aprendizaje final:

La IA no es solo escribir prompts. También es arquitectura, datos, integración, pruebas y diseño de una experiencia útil para el usuario.

#InteligenciaArtificial #Ollama #Python #PHP #RAG #ChromaDB #DesarrolloWeb #IAlocal
