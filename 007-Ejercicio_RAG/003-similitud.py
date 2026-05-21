import requests
import numpy as np
import itertools

OLLAMA_URL = "http://localhost:11434/api/embeddings"
MODEL = "nomic-embed-text:v1.5"

def get_embedding(texto):
    response = requests.post(OLLAMA_URL, json={
        "model": MODEL,
        "prompt": texto
    })
    response.raise_for_status()
    return np.array(response.json()["embedding"])

def similitud_coseno(vec1, vec2):
    dot = np.dot(vec1, vec2)
    norma1 = np.linalg.norm(vec1)
    norma2 = np.linalg.norm(vec2)
    return dot / (norma1 * norma2)

palabras = ["gato", "perro", "camion", "ira"]

embeddings = {}
for palabra in palabras:
    print(f"Calculando embedding: {palabra}")
    embeddings[palabra] = get_embedding(palabra)

resultados = []
for p1, p2 in itertools.combinations(palabras, 2):
    sim = similitud_coseno(embeddings[p1], embeddings[p2])
    resultados.append((p1, p2, sim))

resultados.sort(key=lambda x: x[2], reverse=True)

print("\nSimilitud coseno (1.0 = identicos, 0.0 = sin relacion):\n")
for p1, p2, sim in resultados:
    print(f"{p1:>8}  vs  {p2:<8} -> {sim:.4f}")