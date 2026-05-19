import requests
from config import OLLAMA_BASE_URL, OLLAMA_MODEL

# Pregunta muy especializada para ver si el modelo general conoce
# datos academicos concretos sobre DAW sin entrenamiento extra.

try:
    response = requests.post(
        f"{OLLAMA_BASE_URL}/api/generate",
        json={
            "model": OLLAMA_MODEL,
            "prompt": (
                "Dame el temario de la asignatura Diseño de Interfaces Web "
                "de segundo curso de DAW. Si tienes la respuesta en tu base "
                "de datos, indicala, y si no la tienes, dilo también."
            ),
            "stream": False,
        },
        timeout=60,
    )
    response.raise_for_status()
    print(response.json()["response"])

except requests.exceptions.ConnectionError:
    print(f"[ERROR] No se puede conectar a Ollama en {OLLAMA_BASE_URL}")
except requests.exceptions.Timeout:
    print("[ERROR] Tiempo de espera agotado.")
except requests.exceptions.HTTPError as e:
    print(f"[ERROR] Respuesta HTTP inesperada: {e}")
