import requests
from config import OLLAMA_BASE_URL, OLLAMA_MODEL

# Pregunta mas concreta para medir hasta donde llega el modelo base
# sin ningun entrenamiento adicional sobre formacion profesional.

try:
    response = requests.post(c
        f"{OLLAMA_BASE_URL}/api/generate",
        json={
            "model": OLLAMA_MODEL,
            "prompt": (
                "¿Qué es un ciclo formativo de formación profesional? "
                "Si tienes la respuesta en tu base de datos, indicala, "
                "y si no la tienes, dilo también."
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
