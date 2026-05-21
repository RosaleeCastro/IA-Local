import requests
import chromadb

OLLAMA_URL = "http://localhost:11434/api/embeddings"
MODEL = "nomic-embed-text:v1.5"
DB_PATH = "chromadb_migracion"
COLLECTION_NAME = "leyes_migracion"

def get_embedding(texto):
    response = requests.post(OLLAMA_URL, json={
        "model": MODEL,
        "prompt": texto
    })
    response.raise_for_status()
    return response.json()["embedding"]

# Conectar con la base existente
client = chromadb.PersistentClient(path=DB_PATH)
collection = client.get_collection(name=COLLECTION_NAME)

print("=" * 60)
print("  BUSCADOR DE LEYES MIGRATORIAS - España")
print("  Ley Organica 4/2000 - BOE 12 enero 2000")
print("=" * 60)
print(f"  Base cargada con {collection.count()} articulos indexados")
print("=" * 60)

while True:
    print()
    consulta = input("Tu pregunta (o 'salir'): ")
    if consulta.lower() == "salir":
        break

    embedding_consulta = get_embedding(consulta)

    resultados = collection.query(
        query_embeddings=[embedding_consulta],
        n_results=3
    )

    documentos = resultados["documents"][0]
    distancias = resultados["distances"][0]

    print(f"\nResultados para: '{consulta}'\n")
    print("-" * 60)

    for i, (doc, dist) in enumerate(zip(documentos, distancias)):
        print(f"Resultado {i+1}  (distancia: {dist:.0f})")
        print()
        # Mostrar hasta 600 caracteres del articulo
        texto = doc[:600]
        if len(doc) > 600:
            texto += "..."
        print(texto)
        print("-" * 60)