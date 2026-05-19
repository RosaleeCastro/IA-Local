import requests
from config import OLLAMA_BASE_URL, OLLAMA_MODEL

# Prueba mas simple posible contra Ollama:
# envia un texto al modelo en la VM y muestra la respuesta.

try:
    response = requests.post(
        f"{OLLAMA_BASE_URL}/api/generate",
        json={
            "model": OLLAMA_MODEL,
            "prompt": "Hola, ¿qué puedes hacer?",
            "stream": False,
        },
        timeout=60,
    )
    response.raise_for_status()
    print(response.json()["response"])

except requests.exceptions.ConnectionError:
    print(f"[ERROR] No se puede conectar a Ollama en {OLLAMA_BASE_URL}")
    print("Comprueba que la VM esta encendida y que Ollama escucha en 0.0.0.0.")
except requests.exceptions.Timeout:
    print("[ERROR] Tiempo de espera agotado. El modelo puede estar cargandose.")
except requests.exceptions.HTTPError as e:
    print(f"[ERROR] Respuesta HTTP inesperada: {e}")
except KeyError:
    print("[ERROR] La respuesta de Ollama no contiene el campo 'response'.")
    print("Respuesta completa:", response.json())
