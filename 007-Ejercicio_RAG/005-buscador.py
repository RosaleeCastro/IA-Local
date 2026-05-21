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

client = chromadb.PersistentClient(path=DB_PATH)
collection = client.get_collection(name=COLLECTION_NAME)

print(f"Base cargada con {collection.count()} elementos.")
print("=" * 45)

while True:
    consulta = input("\nEscribe una palabra o frase (o 'salir'): ")
    if consulta.lower() == "salir":
        break

    embedding_consulta = get_embedding(consulta)

    resultados = collection.query(
        query_embeddings=[embedding_consulta],
        n_results=3
    )

    documentos = resultados["documents"][0]
    distancias = resultados["distances"][0]

    print(f"\nResultados para '{consulta}':\n")
    for doc, dist in zip(documentos, distancias):
        mejor = " ← más similar" if dist == min(distancias) else ""
        print(f"  {doc:<12} distancia: {dist:>10.2f}{mejor}")