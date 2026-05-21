# 006 - Leyes migratorias con IA local

Este ejercicio usa una base de conocimiento en formato JSONL y Ollama para responder preguntas sobre leyes migratorias en Espana.

El sistema puede funcionar de dos formas:

- Por consola.
- Desde una interfaz visual en el navegador.

## 1. Verificar que Ollama funciona

Antes de usar el ejercicio, abre una terminal en esta carpeta:

```powershell
cd C:\xampp\htdocs\Ia_VM\006-leyes-migratorias
```

Ejecuta:

```powershell
python 001-verificar.py
```

Este script comprueba:

- Que Ollama esta activo.
- Que el modelo configurado existe.
- Que el modelo puede responder.

El modelo configurado esta en `config.py`:

```python
OLLAMA_MODEL = "llama3:latest"
```

## 2. Usar el ejercicio en consola

Ejecuta:

```powershell
python 002-consultar_leyes_migratorias.py
```

Veras algo parecido a:

```text
Dataset cargado: 6 ejemplos.
Escribe 'salir' para terminar.

Tu:
```

Ahora puedes preguntar, por ejemplo:

```text
¿Qué es el NIE?
```

O:

```text
¿Qué es el arraigo social?
```

### Como salir del modelo en la terminal

Para salir del chat de consola, escribe:

```text
salir
```

Tambien funcionan:

```text
exit
quit
```

Si el programa se queda esperando o quieres cortar la ejecucion directamente, pulsa:

```text
Ctrl+C
```

## 3. Usar la interfaz visual en el navegador

Desde la misma carpeta:

```powershell
cd C:\xampp\htdocs\Ia_VM\006-leyes-migratorias
```

Ejecuta:

```powershell
python 003-interfaz_web.py
```

Cuando el servidor arranque, abre el navegador en:

```text
http://localhost:8060
```

Desde ahi puedes escribir preguntas en la caja de texto y pulsar `Enviar`.

Tambien puedes usar las preguntas rapidas del panel lateral.

### Como cerrar la interfaz web

La pagina del navegador no detiene el servidor por si sola.

Para cerrar el servidor, vuelve a la terminal donde ejecutaste:

```powershell
python 003-interfaz_web.py
```

Y pulsa:

```text
Ctrl+C
```

Despues puedes cerrar la pestana del navegador.

## 4. Donde estan los datos

Los datos estan en:

```text
materiales/leyes_migratorias.jsonl
```

Cada linea tiene este formato:

```json
{"question":"Pregunta","answer":"Respuesta"}
```

Puedes anadir nuevas preguntas y respuestas copiando ese formato.

## 5. Como decide la respuesta

El motor esta en:

```text
motor_leyes.py
```

El flujo es:

1. Lee el archivo `leyes_migratorias.jsonl`.
2. Compara tu pregunta con las preguntas guardadas.
3. Si encuentra una pregunta muy parecida, responde directamente desde el JSONL.
4. Si no encuentra coincidencia clara, manda a Ollama los fragmentos mas parecidos como contexto.
5. Ollama genera una respuesta prudente usando ese contexto.

## 6. Archivos principales

```text
001-verificar.py
```

Comprueba Ollama y el modelo.

```text
002-consultar_leyes_migratorias.py
```

Chat por consola.

```text
003-interfaz_web.py
```

Servidor local para usar la interfaz web.

```text
motor_leyes.py
```

Motor comun usado por la consola y la web.

```text
web/index.html
web/styles.css
web/app.js
```

Interfaz visual del navegador.
