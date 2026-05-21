# ⚖️ Asistente Legal · Leyes de Extranjería España

Sistema RAG (Retrieval-Augmented Generation) entrenado con la Ley Orgánica 4/2000 sobre derechos y libertades de los extranjeros en España.

---

## ¿Qué hace este proyecto?

Permite hacer preguntas en lenguaje natural sobre leyes migratorias españolas y recibir respuestas basadas en los artículos reales de la ley, con interfaz web visual.

```
Pregunta del usuario
       ↓
Convertir pregunta en embedding (nomic-embed-text)
       ↓
Buscar los 3 artículos más similares en ChromaDB
       ↓
Enviar artículos + pregunta a qwen2.5:3b como contexto
       ↓
Respuesta en lenguaje natural con base legal real
```

---

## Estructura de archivos

```
007-Ejercicio_RAG/
│
├── Jefatura_del_Estado.txt      # Ley Orgánica 4/2000 (fuente de datos)
│
├── 001-busqueda-literal.py      # Paso 1: búsqueda por texto exacto
├── 002-embeddings.py            # Paso 2: conversión de texto a vectores
├── 003-similitud.py             # Paso 3: comparación semántica
├── 004-guardar-chromadb.py      # Paso 4: base vectorial con palabras
├── 005-buscador.py              # Paso 5: búsqueda semántica básica
│
├── 006-cargar-documento.py      # Indexa los 109 artículos de la ley
├── 007-buscador-migracion.py    # Buscador por terminal
│
├── 008-demo-migracion.py        # Servidor web con IA generativa
├── 008-demo-interface.html      # Interfaz visual de la demo
│
├── chromadb_migracion/          # Base de datos vectorial (generada)
└── mi_base_vectorial/           # Base de datos de prueba (generada)
```

---

## Requisitos

### Software

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

### 1. Clonar o descargar el proyecto

```bash
cd 007-Ejercicio_RAG
```

### 2. Indexar el documento legal

Ejecuta este paso solo la primera vez o cuando cambies el documento fuente:

```bash
python 006-cargar-documento.py
```

Salida esperada:

```
Parrafos encontrados: 109
Guardado articulo 1/109
Guardado articulo 2/109
...
Base de datos creada con 109 articulos.
```

### 3. Lanzar la demo web

```bash
python 008-demo-migracion.py
```

Salida esperada:

```
Servidor iniciado en http://localhost:8080
Abre tu navegador en http://localhost:8080
Pulsa Ctrl+C para detener
```

### 4. Abrir el navegador

Navega a: **http://localhost:8080**

---

## Ejemplos de preguntas

| Pregunta                                        | Artículos relevantes |
| ----------------------------------------------- | -------------------- |
| ¿Qué derechos tienen los extranjeros en España? | Art. 3, 4, 5         |
| ¿Cómo puedo obtener la residencia temporal?     | Art. 30 bis, 31      |
| ¿Qué pasa si un menor llega solo a España?      | Art. 35, 35 bis      |
| ¿Cuáles son las infracciones graves?            | Art. 53, 54          |
| ¿Puedo trabajar siendo extranjero?              | Art. 36, 37, 38      |
| ¿Qué es la reagrupación familiar?               | Art. 16, 17, 18      |

---

## Progresión didáctica

Este proyecto sigue una progresión desde búsqueda simple hasta RAG completo:

| Archivo | Concepto           | Descripción                      |
| ------- | ------------------ | -------------------------------- |
| `001`   | Búsqueda literal   | Compara letras, no significados  |
| `002`   | Embeddings         | Convierte texto en 768 números   |
| `003`   | Similitud coseno   | Compara vectores matemáticamente |
| `004`   | ChromaDB           | Guarda vectores en disco         |
| `005`   | Buscador semántico | Encuentra por significado        |
| `006`   | Indexar documento  | Procesa la ley en 109 artículos  |
| `007`   | Buscador terminal  | RAG básico por consola           |
| `008`   | Demo completa      | RAG + LLM + interfaz web         |

---

## Tecnologías

| Tecnología                                                      | Uso                                        |
| --------------------------------------------------------------- | ------------------------------------------ |
| [ChromaDB](https://www.trychroma.com)                           | Base de datos vectorial local              |
| [Ollama](https://ollama.com)                                    | Servidor de modelos de IA local            |
| [nomic-embed-text](https://ollama.com/library/nomic-embed-text) | Modelo de embeddings (768 dimensiones)     |
| [qwen2.5:3b](https://ollama.com/library/qwen2.5)                | Modelo de lenguaje para respuestas         |
| Python `http.server`                                            | Servidor web ligero sin dependencias extra |

---

## Fuente legal

**Ley Orgánica 4/2000**, de 11 de enero, sobre derechos y libertades de los extranjeros en España y su integración social.

- BOE núm. 10, de 12 de enero de 2000
- Referencia: BOE-A-2000-544
- Última modificación: 19 de marzo de 2025

---

## Notas

- Toda la inferencia es **100% local**, sin APIs externas ni costes.
- El sistema responde solo con base en los artículos indexados.
- Para actualizar el documento fuente, edita `Jefatura_del_Estado.txt` y vuelve a ejecutar `006-cargar-documento.py`.
