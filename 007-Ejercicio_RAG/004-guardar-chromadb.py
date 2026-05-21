import requests
import chromadb

OLLAMA_URL = "http://localhost:11434/api/embeddings"
MODEL = "nomic-embed-text:v1.5"
DB_PATH = "mi_base_vectorial"
COLLECTION_NAME = "palabras"

def get_embedding(texto):
    response = requests.post(OLLAMA_URL, json={
        "model": MODEL,
        "prompt": texto
    })
    response.raise_for_status()
    return response.json()["embedding"]

# Conectamos con ChromaDB y creamos una coleccion
client = chromadb.PersistentClient(path=DB_PATH)
collection = client.get_or_create_collection(name=COLLECTION_NAME)

# Palabras que vamos a guardar
palabras = ["gato", "perro", "camion", "ira", "ciudad", "España"]

# Guardamos cada palabra con su embedding
for i, palabra in enumerate(palabras):
    embedding = get_embedding(palabra)
    collection.add(
        ids=[str(i)],
        documents=[palabra],
        embeddings=[embedding]
    )
    print(f"Guardado: {palabra}")

print("\nBase vectorial creada correctamente.")
print(f"Total de elementos guardados: {collection.count()}")