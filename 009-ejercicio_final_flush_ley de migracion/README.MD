# ⚖️ Asistente Legal · Extranjería España

Sistema RAG completo con interfaz web construido con PHP, Python y modelos
de IA locales. Permite consultar leyes migratorias españolas en lenguaje
natural y recibir respuestas fundamentadas en artículos reales de la ley.

---

## ¿Qué hace este proyecto?

Pregunta del usuario (navegador)
↓
005-demo.php recibe la petición AJAX
↓
003-funcion.php llama a buscador.py
↓
buscador.py busca en ChromaDB los 4 fragmentos más similares
↓
003-funcion.php envía fragmentos + pregunta a Ollama
↓
qwen2.5:3b genera respuesta basada en los artículos reales
↓
Navegador muestra respuesta + fuentes legales consultadas

---

## Estructura de archivos

009-ejercicio_final_flush_ley de migracion/
│
├── Jefatura_del_Estado.txt Ley Orgánica 4/2000 (fuente 1)
├── regularizacion_masiva.txt Real Decreto 316/2026 (fuente 2)
├── preguntas.txt Preguntas predefinidas del modo automático
│
├── 001-indexar.py Lee los dos documentos e indexa en ChromaDB
├── verificar.py Comprueba cuántos fragmentos hay en la base
├── buscador.py Busca en ChromaDB y devuelve JSON a PHP
│
├── 002-peticion.php Paso 1: petición simple a Ollama sin contexto
├── 003-funcion.php Paso 2: funciones reutilizables con RAG
├── 005-demo.php Paso 3: interfaz completa con AJAX
│
└── chromadb_leyes/ Base de datos vectorial (generada automáticamente)

---

## Progresión didáctica

Este proyecto replica la estructura del archivo `007-flush.php`
de la carpeta `009-multimodelo`, adaptado a búsqueda semántica con RAG:

| Archivo            | Equivalente 009-multimodelo | Descripción                   |
| ------------------ | --------------------------- | ----------------------------- |
| `001-indexar.py`   | —                           | Indexa documentos en ChromaDB |
| `002-peticion.php` | `001-peticion sencilla.php` | Petición simple sin contexto  |
| `003-funcion.php`  | `003-funcion.php`           | Funciones reutilizables       |
| `buscador.py`      | —                           | Puente PHP ↔ ChromaDB         |
| `005-demo.php`     | `007-flush.php`             | Interfaz completa con AJAX    |

La diferencia clave: aquí el modelo no responde desde su entrenamiento
general sino desde documentos legales reales indexados en ChromaDB.

---

## Requisitos

### Software

- PHP 8.x con XAMPP
- Python 3.10 o superior
- [Ollama](https://ollama.com) instalado y en ejecución

### Modelos Ollama necesarios

```bash
ollama pull nomic-embed-text:v1.5
ollama pull qwen2.5:3b
```

### Librerías Python

```bash
pip install chromadb requests
```

---

## Instalación y uso

### 1. Colocar los archivos en XAMPP

C:\xampp\htdocs\009-ejercicio_final_flush_ley de migracion\

### 2. Indexar los documentos

Solo la primera vez o cuando cambies los documentos fuente:

```bash
python 001-indexar.py
```

Salida esperada:
Procesando: Jefatura_del_Estado.txt → 130 fragmentos
Procesando: regularizacion_masiva.txt → 27 fragmentos
Total de fragmentos indexados: 157

### 3. Verificar la base de datos

```bash
python verificar.py
```

### 4. Abrir la interfaz web

Con XAMPP corriendo:

http://localhost/009-ejercicio_final_flush_ley de migracion/005-demo.php

---

## Dos modos de uso

### Modo automático (panel izquierdo)

- **▶ Ejecutar todas** procesa las 8 preguntas de `preguntas.txt` secuencialmente
- Clic en cualquier pregunta individual para lanzarla sola

### Modo libre (panel derecho)

- Escribe cualquier pregunta en el textarea
- **Enter** para enviar · **Shift+Enter** para salto de línea

---

## Diferencia entre RAG y sin RAG

|                  | Sin RAG (002-peticion.php) | Con RAG (005-demo.php)    |
| ---------------- | -------------------------- | ------------------------- |
| Fuente           | Entrenamiento general      | Tus documentos reales     |
| Precisión        | Media, puede inventar      | Alta, basada en artículos |
| Actualización    | Corte de entrenamiento     | Tus TXT de 2026           |
| Fuentes visibles | No                         | Sí, con cada respuesta    |

---

## Tecnologías

| Tecnología           | Uso                                |
| -------------------- | ---------------------------------- |
| PHP + XAMPP          | Servidor web y lógica backend      |
| Python               | Búsqueda semántica en ChromaDB     |
| ChromaDB             | Base de datos vectorial local      |
| Ollama               | Servidor de modelos IA local       |
| nomic-embed-text     | Embeddings 768 dimensiones         |
| qwen2.5:3b           | Modelo de lenguaje para respuestas |
| JavaScript Fetch API | Peticiones AJAX sin recargar       |

---

## Fuentes legales

**Ley Orgánica 4/2000**, de 11 de enero
Derechos y libertades de los extranjeros en España
BOE núm. 10 · BOE-A-2000-544

**Real Decreto 316/2026**, de 14 de abril
Modificación del Reglamento de la Ley Orgánica 4/2000
BOE núm. 92 · BOE-A-2026-8284

---

## Notas

- Inferencia **100% local**, sin APIs externas ni costes
- Para añadir documentos edita `001-indexar.py` y vuelve a indexar
- Para cambiar preguntas predefinidas edita `preguntas.txt`
- Para cambiar el modelo edita `$modelo` en `003-funcion.php`
