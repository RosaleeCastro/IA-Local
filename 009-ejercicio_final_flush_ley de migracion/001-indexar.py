# ==============================================
# 001-indexar.py
# Lee los dos documentos de ley y los guarda
# en ChromaDB con sus embeddings de Ollama
# ==============================================

import re
import requests
import chromadb

# ----------------------------------------------
# CONFIGURACIÓN
# Aquí definimos qué modelos y rutas usamos
# ----------------------------------------------
OLLAMA_URL    = "http://localhost:11434/api/embeddings"
MODEL_EMBED   = "nomic-embed-text:v1.5"
DB_PATH       = "chromadb_leyes"       # carpeta donde se guarda la base
COLLECTION    = "leyes_migracion"      # nombre de la colección

# Los dos documentos que vamos a indexar
DOCUMENTOS = [
    {
        "archivo": "Jefatura_del_Estado.txt",
        "origen":  "Ley Orgánica 4/2000"
    },
    {
        "archivo": "regularizacion_masiva.txt",
        "origen":  "Real Decreto 316/2026"
    }
]

# ----------------------------------------------
# FUNCIÓN: pedir embedding a Ollama
# Convierte un texto en una lista de 768 números
# ----------------------------------------------
def get_embedding(texto):
    response = requests.post(OLLAMA_URL, json={
        "model":  MODEL_EMBED,
        "prompt": texto
    })
    response.raise_for_status()
    return response.json()["embedding"]

# ----------------------------------------------
# FUNCIÓN: dividir texto en fragmentos por artículo
# Busca líneas que empiecen con "Artículo" o
# con palabras clave de disposiciones
# ----------------------------------------------
def dividir_en_fragmentos(contenido, origen):
    fragmentos = []
    fragmento_actual = []

    # Patrón que detecta inicio de artículo o disposición
    patron = re.compile(
        r"^(Art[ií]culo\s+\d+|Disposici[oó]n\s+(adicional|transitoria|derogatoria|final))",
        re.IGNORECASE
    )

    for linea in contenido.splitlines():
        linea_limpia = linea.strip()

        # Si encontramos inicio de nuevo artículo y ya tenemos texto
        if patron.match(linea_limpia) and fragmento_actual:
            texto = " ".join(fragmento_actual).strip()
            # Solo guardamos si tiene al menos 20 palabras
            if len(texto.split()) >= 20:
                fragmentos.append({
                    "texto":  texto,
                    "origen": origen
                })
            fragmento_actual = [linea_limpia]
        else:
            if linea_limpia:
                fragmento_actual.append(linea_limpia)

    # Guardar el último fragmento
    if fragmento_actual:
        texto = " ".join(fragmento_actual).strip()
        if len(texto.split()) >= 20:
            fragmentos.append({
                "texto":  texto,
                "origen": origen
            })

    return fragmentos

# ----------------------------------------------
# INICIALIZAR CHROMADB
# PersistentClient guarda los datos en disco
# para no tener que reindexar cada vez
# ----------------------------------------------
client = chromadb.PersistentClient(path=DB_PATH)

# Borramos la colección si ya existe para empezar limpio
try:
    client.delete_collection(COLLECTION)
    print("Colección anterior borrada")
except:
    pass

collection = client.create_collection(name=COLLECTION)

# ----------------------------------------------
# PROCESAR CADA DOCUMENTO
# ----------------------------------------------
total_guardados = 0

for doc in DOCUMENTOS:
    print(f"\nProcesando: {doc['archivo']}")
    print("-" * 40)

    # Leer el archivo
    with open(doc["archivo"], "r", encoding="utf-8") as f:
        contenido = f.read()

    # Dividir en fragmentos
    fragmentos = dividir_en_fragmentos(contenido, doc["origen"])
    print(f"Fragmentos encontrados: {len(fragmentos)}")

    # Guardar cada fragmento con su embedding
    for i, frag in enumerate(fragmentos):

        # Limitar a 400 palabras para no superar el límite del modelo
        palabras = frag["texto"].split()
        texto    = " ".join(palabras[:400])

        # Pedir embedding a Ollama
        embedding = get_embedding(texto)

        # Guardar en ChromaDB con metadatos
        # Los metadatos nos permiten saber de qué ley viene cada fragmento
        collection.add(
            ids=[f"doc{DOCUMENTOS.index(doc)}_{i}"],
            documents=[texto],
            embeddings=[embedding],
            metadatas=[{
                "origen": doc["origen"],
                "indice": i
            }]
        )

        print(f"  Guardado {i+1}/{len(fragmentos)}: {texto[:60]}...")
        total_guardados += 1

# ----------------------------------------------
# RESUMEN FINAL
# ----------------------------------------------
print("\n" + "=" * 40)
print(f"Base de datos creada correctamente")
print(f"Total de fragmentos indexados: {total_guardados}")
print(f"Ubicación: {DB_PATH}/")
print("=" * 40)
