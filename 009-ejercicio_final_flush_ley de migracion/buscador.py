# ==============================================
# buscador.py
# Script que recibe una pregunta por argumento,
# busca en ChromaDB y devuelve JSON con resultados
# PHP lo llama con shell_exec()
# VERSIÓN CORREGIDA: fuerza UTF-8 en la salida
# ==============================================

import sys
import io
import json
import requests
import chromadb

# ----------------------------------------------
# FORZAR UTF-8 EN LA SALIDA
# Sin esto Windows corrompe los acentos y la ñ
# cuando PHP lee la salida del script
# ----------------------------------------------
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

# ----------------------------------------------
# CONFIGURACIÓN
# ----------------------------------------------
OLLAMA_URL  = "http://localhost:11434/api/embeddings"
MODEL_EMBED = "nomic-embed-text:v1.5"
DB_PATH     = "chromadb_leyes"
COLLECTION  = "leyes_migracion"

# ----------------------------------------------
# LEER LA PREGUNTA
# PHP nos pasa la pregunta como argumento
# sys.argv[0] es el nombre del script
# sys.argv[1] es la pregunta
# ----------------------------------------------
if len(sys.argv) < 2:
    # Si no hay pregunta devolvemos error en JSON
    print(json.dumps({
        "error": "No se recibió ninguna pregunta"
    }, ensure_ascii=False))
    sys.exit(1)

# Reconstruir la pregunta completa
# (puede tener espacios y venir separada en varios argumentos)
pregunta = " ".join(sys.argv[1:])

# ----------------------------------------------
# FUNCIÓN: obtener embedding de la pregunta
# Convertimos la pregunta en vector numérico
# para poder comparar con los fragmentos guardados
# ----------------------------------------------
def get_embedding(texto):
    response = requests.post(OLLAMA_URL, json={
        "model":  MODEL_EMBED,
        "prompt": texto
    })
    response.raise_for_status()
    return response.json()["embedding"]

# ----------------------------------------------
# BUSCAR EN CHROMADB
# Calculamos el embedding de la pregunta
# y buscamos los 4 fragmentos más similares
# ----------------------------------------------
try:
    # Conectar con la base de datos existente
    client     = chromadb.PersistentClient(path=DB_PATH)
    collection = client.get_collection(name=COLLECTION)

    # Convertir pregunta a vector numérico
    embedding = get_embedding(pregunta)

    # Buscar los 4 fragmentos más similares
    resultados = collection.query(
        query_embeddings=[embedding],
        n_results=4,
        include=["documents", "metadatas", "distances"]
    )

    # ----------------------------------------------
    # PREPARAR RESPUESTA
    # Construimos una lista con los fragmentos
    # incluyendo texto, origen y distancia
    # ----------------------------------------------
    fragmentos = []
    docs       = resultados["documents"][0]
    metas      = resultados["metadatas"][0]
    dists      = resultados["distances"][0]

    for doc, meta, dist in zip(docs, metas, dists):
        fragmentos.append({
            "texto":     doc,
            "origen":    meta["origen"],
            "distancia": round(dist, 2)
        })

    # Devolver JSON limpio para que PHP lo lea
    # ensure_ascii=False preserva acentos y ñ
    print(json.dumps({
        "ok":         True,
        "pregunta":   pregunta,
        "fragmentos": fragmentos
    }, ensure_ascii=False))

except Exception as e:
    # Si algo falla devolvemos el error en JSON
    print(json.dumps({
        "error": str(e)
    }, ensure_ascii=False))
    sys.exit(1)