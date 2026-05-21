import re
import chromadb
import requests

OLLAMA_URL = "http://localhost:11434/api/embeddings"
MODEL = "nomic-embed-text:v1.5"
TXT_PATH = "Jefatura_del_Estado.txt"
DB_PATH = "chromadb_migracion"
COLLECTION_NAME = "leyes_migracion"

def get_embedding(texto):
    response = requests.post(OLLAMA_URL, json={
        "model": MODEL,
        "prompt": texto
    })
    response.raise_for_status()
    return response.json()["embedding"]

# Leer el archivo
with open(TXT_PATH, "r", encoding="utf-8") as f:
    lineas = f.readlines()

# Dividir por articulos buscando lineas que empiecen con "Articulo"
parrafos = []
parrafo_actual = []

for linea in lineas:
    linea_limpia = linea.strip()
    if re.match(r"^Art[ií]culo\s+\d+", linea_limpia) and parrafo_actual:
        texto = " ".join(parrafo_actual).strip()
        if len(texto.split()) >= 15:
            parrafos.append(texto)
        parrafo_actual = [linea_limpia]
    else:
        if linea_limpia:
            parrafo_actual.append(linea_limpia)

# Agregar el ultimo parrafo
if parrafo_actual:
    texto = " ".join(parrafo_actual).strip()
    if len(texto.split()) >= 15:
        parrafos.append(texto)

print(f"Parrafos encontrados: {len(parrafos)}")
print(f"Ejemplo del primero:\n{parrafos[0][:200]}\n")

# Crear base de datos
client = chromadb.PersistentClient(path=DB_PATH)

try:
    client.delete_collection(COLLECTION_NAME)
except:
    pass

collection = client.create_collection(name=COLLECTION_NAME)

# Guardar cada parrafo con su embedding
for i, parrafo in enumerate(parrafos):
    # Limitar a 500 palabras por si algun articulo es muy largo
    palabras = parrafo.split()
    if len(palabras) > 500:
        parrafo = " ".join(palabras[:500])

    embedding = get_embedding(parrafo)
    collection.add(
        ids=[f"parrafo_{i}"],
        documents=[parrafo],
        embeddings=[embedding],
        metadatas=[{"indice": i}]
    )
    print(f"Guardado articulo {i+1}/{len(parrafos)}")

print(f"\nBase de datos creada con {collection.count()} articulos.")