# Ruta de demostracion IA local

Guia rapida para mostrar los ejercicios principales sin improvisar.

---

## 0. Preparacion antes de empezar

### Abrir XAMPP

1. Inicia **XAMPP**.
2. Activa **Apache**.
3. Deja Ollama abierto.

### Comprobar modelos

En PowerShell:

```powershell
ollama list
```

Modelos esperados:

```text
llama3:latest
nomic-embed-text:v1.5
qwen2.5:3b
```

Si falta alguno:

```powershell
ollama pull llama3:latest
ollama pull nomic-embed-text:v1.5
ollama pull qwen2.5:3b
```

### Precalentar modelos

Esto evita que la primera respuesta tarde demasiado:

```powershell
ollama run llama3:latest "Responde solo: listo"
ollama run qwen2.5:3b "Responde solo: listo"
```

---

# Orden recomendado de demo

```text
1. 004-Corrector_IA
   Texto simple -> IA corrige o redacta

2. 006-leyes-migratorias_entrenamiento
   JSONL -> busqueda por ejemplos -> respuesta local

3. 007-Ejercicio_RAG
   Texto legal real -> embeddings -> ChromaDB -> respuesta con contexto

4. 009-ejercicio_final_flush_ley de migracion
   Demo final PHP + Python + ChromaDB + Ollama + fuentes visibles
```

---

# 1. Demo 004 - Corrector IA

## Que demuestra

Una interfaz web en XAMPP que envia texto a PHP y PHP llama a Ollama local.

```text
Usuario escribe texto
        ↓
index.html
        ↓
back.php
        ↓
Ollama llama3:latest
        ↓
Respuesta corregida o redactada
```

## Ruta del proyecto

```text
C:\xampp\htdocs\Ia_VM\004-Corrector_IA
```

## Abrir en navegador

```text
http://localhost/Ia_VM/004-Corrector_IA/
```

## Comprobar conexion con Ollama

```text
http://localhost/Ia_VM/004-Corrector_IA/ping.php
```

Debe devolver algo parecido a:

```json
{
  "ok": true
}
```

## Prompt rapido para la demo

Pega este texto en la interfaz:

```text
hola equipo necesito que me mandeis los documentos antes del viernes porque si no no puedo terminar el informe gracias
```

## Que decir durante la demo

```text
Aqui la IA no esta en la nube. El navegador habla con PHP y PHP llama a Ollama local.
El objetivo es corregir o redactar textos profesionales desde una interfaz simple.
```

## Resultado esperado

Una version mas clara y profesional del mensaje, por ejemplo:

```text
Hola equipo:

Necesito que me envieis los documentos antes del viernes para poder finalizar el informe.

Gracias.
```

---

# 2. Demo 006 - Leyes migratorias con entrenamiento JSONL

## Que demuestra

Un asistente que primero busca respuestas parecidas en un archivo JSONL y, si no encuentra coincidencia suficiente, usa Ollama con contexto.

```text
Pregunta del usuario
        ↓
materiales/leyes_migratorias.jsonl
        ↓
motor_leyes.py compara similitud
        ↓
Si coincide: responde desde JSONL
Si no coincide: usa Ollama
        ↓
Interfaz web en localhost:8060
```

## Ruta del proyecto

```text
C:\xampp\htdocs\Ia_VM\006-leyes-migratorias_entrenamiento
```

## Arrancar servidor

En PowerShell:

```powershell
cd C:\xampp\htdocs\Ia_VM\006-leyes-migratorias_entrenamiento
python 003-interfaz_web.py
```

## Abrir en navegador

```text
http://localhost:8060
```

## Archivo de datos

```text
C:\xampp\htdocs\Ia_VM\006-leyes-migratorias_entrenamiento\materiales\leyes_migratorias.jsonl
```

Cada linea contiene:

```json
{"question":"Pregunta","answer":"Respuesta"}
```

## Prompt rapido para la demo

Usa primero una pregunta que seguramente existe:

```text
¿Qué es el NIE?
```

Luego una variacion:

```text
Explicame de forma sencilla que significa tener NIE en España.
```

## Que decir durante la demo

```text
Este ejercicio ya introduce una base de conocimiento propia.
No responde solo por entrenamiento general: primero compara mi pregunta con ejemplos guardados en JSONL.
```

## Resultado esperado

La interfaz debe mostrar:

```text
Respuesta
Fuente usada
Porcentaje de similitud
Referencias cercanas
```

## Cerrar servidor

En la terminal donde esta corriendo:

```text
Ctrl + C
```

---

# 3. Demo 007 - RAG con ChromaDB

## Que demuestra

Un sistema RAG real: convierte la pregunta en embedding, busca articulos similares en ChromaDB y genera una respuesta con contexto legal.

```text
Pregunta del usuario
        ↓
Embedding con nomic-embed-text
        ↓
Busqueda semantica en ChromaDB
        ↓
Contexto legal recuperado
        ↓
qwen2.5:3b genera respuesta
        ↓
Interfaz web con articulos consultados
```

## Ruta del proyecto

```text
C:\xampp\htdocs\Ia_VM\007-Ejercicio_RAG
```

## Indexar documento si es la primera vez

Solo si no existe la base `chromadb_migracion` o si cambiaste `Jefatura_del_Estado.txt`:

```powershell
cd C:\xampp\htdocs\Ia_VM\007-Ejercicio_RAG
python 006-cargar-documento.py
```

## Arrancar demo web

```powershell
cd C:\xampp\htdocs\Ia_VM\007-Ejercicio_RAG
python 008-demo-migracion.py
```

## Abrir en navegador

```text
http://localhost:8080
```

## Prompts rapidos para la demo

Pregunta inicial:

```text
¿Qué derechos tienen los extranjeros en España?
```

Pregunta mas concreta:

```text
¿Cómo puedo obtener la residencia temporal?
```

Pregunta para mostrar fuentes:

```text
¿Qué es la reagrupación familiar?
```

## Que decir durante la demo

```text
Aqui ya no buscamos solo coincidencias por texto.
El sistema convierte la pregunta en numeros, busca fragmentos parecidos en una base vectorial y entrega esos articulos al modelo para responder.
```

## Resultado esperado

La pantalla debe mostrar:

```text
Respuesta legal
Articulos consultados
Distancia o similitud de cada resultado
```

## Nota importante

No abras directamente:

```text
http://localhost/Ia_VM/007-Ejercicio_RAG/008-demo-interface.html
```

Esta demo necesita el servidor Python:

```text
http://localhost:8080
```

---

# 4. Demo 009 - Ejercicio final con PHP + Python + RAG

## Que demuestra

La version final integra XAMPP, PHP, Python, ChromaDB y Ollama en una sola interfaz.

```text
Navegador
        ↓
005-demo.php
        ↓
003-funcion.php
        ↓
buscador.py
        ↓
ChromaDB
        ↓
Ollama qwen2.5:3b
        ↓
Respuesta + fuentes legales
```

## Ruta del proyecto

```text
C:\xampp\htdocs\Ia_VM\009-ejercicio_final_flush_ley de migracion
```

## Indexar documentos si es la primera vez

```powershell
cd "C:\xampp\htdocs\Ia_VM\009-ejercicio_final_flush_ley de migracion"
python 001-indexar.py
```

## Verificar base vectorial

```powershell
python verificar.py
```

## Abrir en navegador con XAMPP

Usa esta URL:

```text
http://localhost/Ia_VM/009-ejercicio_final_flush_ley%20de%20migracion/005-demo.php
```

Si el navegador acepta espacios, tambien puede abrir:

```text
http://localhost/Ia_VM/009-ejercicio_final_flush_ley de migracion/005-demo.php
```

## Prompts rapidos para la demo

Pregunta legal general:

```text
¿Qué derechos tienen los extranjeros en España?
```

Pregunta sobre regularizacion:

```text
¿Qué requisitos se mencionan para regularizar a una persona extranjera?
```

Pregunta para mostrar busqueda por fuentes:

```text
¿Qué ocurre con los menores extranjeros no acompañados?
```

## Que decir durante la demo

```text
Esta es la integracion final.
PHP controla la interfaz y la peticion web, Python hace la busqueda semantica y Ollama genera la respuesta usando documentos legales reales.
```

## Resultado esperado

Debe verse:

```text
Respuesta generada
Fuentes consultadas
Fragmentos legales recuperados
Modo automatico con preguntas predefinidas
Modo libre para preguntas propias
```

---

# Guion corto para explicar toda la evolucion

```text
Primero mostramos una IA local sencilla que corrige texto.
Luego pasamos a una base JSONL con preguntas y respuestas legales.
Despues hacemos RAG real: embeddings, ChromaDB y respuesta con contexto.
Finalmente integramos todo en una demo web con PHP, Python, Ollama y fuentes visibles.
```

---

# Checklist antes de presentar

| Paso | Comprobacion | OK |
| --- | --- | --- |
| 1 | XAMPP abierto | [ ] |
| 2 | Apache iniciado | [ ] |
| 3 | Ollama abierto | [ ] |
| 4 | `ollama list` muestra los modelos | [ ] |
| 5 | `004-Corrector_IA/ping.php` responde OK | [ ] |
| 6 | `006` abre en `localhost:8060` | [ ] |
| 7 | `007` abre en `localhost:8080` | [ ] |
| 8 | `009` abre desde XAMPP | [ ] |

---

# Comandos rapidos agrupados

## 004

```text
http://localhost/Ia_VM/004-Corrector_IA/
```

## 006

```powershell
cd C:\xampp\htdocs\Ia_VM\006-leyes-migratorias_entrenamiento
python 003-interfaz_web.py
```

```text
http://localhost:8060
```

## 007

```powershell
cd C:\xampp\htdocs\Ia_VM\007-Ejercicio_RAG
python 008-demo-migracion.py
```

```text
http://localhost:8080
```

## 009

```text
http://localhost/Ia_VM/009-ejercicio_final_flush_ley%20de%20migracion/005-demo.php
```

---

# Si algo falla

## Ollama no responde

```powershell
ollama serve
```

En otra terminal:

```powershell
ollama run llama3:latest "hola"
```

## Python no encuentra librerias

```powershell
pip install chromadb requests
```

## La web 006 o 007 no carga

Revisa que la terminal siga abierta. Si cierras la terminal, se cierra el servidor.

## La web 004 o 009 no carga

Revisa que Apache este iniciado en XAMPP.

## La primera respuesta tarda

Es normal. El modelo se esta cargando en memoria.
