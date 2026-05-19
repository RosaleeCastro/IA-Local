import requests

# Este archivo hace la prueba mas simple posible contra Ollama:
# envia un texto a un modelo local y muestra la respuesta en pantalla.

response = requests.post(
    "http://localhost:11434/api/generate",
    json={
        # Nombre del modelo que Ollama debe usar para responder.
        "model": "qwen2.5:3b-instruct",
        # Texto que se le manda al modelo.
        "prompt": "Hola, ¿qué puedes hacer?",
        # Si es False, esperamos la respuesta completa de una sola vez.
        "stream": False
    }
)

# La API devuelve un diccionario JSON. Aqui extraemos solo el texto generado.
print(response.json()["response"])
