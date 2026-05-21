import requests

OLLAMA_URL = "http://localhost:11434/api/embeddings"
MODEL = "nomic-embed-text:v1.5"

def get_embedding(texto):
    response = requests.post(OLLAMA_URL, json={
        "model": MODEL,
        "prompt": texto
    })
    response.raise_for_status()
    return response.json()["embedding"]

# Probamos con tres palabras
palabras = ["hombre", "mujer", "infante"]

for palabra in palabras:
    embedding = get_embedding(palabra)
    print(f"Palabra: {palabra}")
    print(f"Primeros 5 números: {embedding[:5]}")
    print(f"Total de números: {len(embedding)}")
    print()