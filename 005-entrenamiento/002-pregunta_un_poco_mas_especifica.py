import requests

# Esta prueba es parecida a la anterior, pero con una pregunta mas concreta.
# Sirve para comprobar hasta donde llega el modelo base sin entrenamiento extra.

response = requests.post(
    "http://localhost:11434/api/generate",
    json={
        "model": "qwen2.5:3b-instruct",
        # Se le pide al modelo una respuesta sobre formacion profesional.
        # El texto tambien le pide que diga si no conoce la respuesta.
        "prompt": "¿Qué es un ciclo formativo de formación profesional? Si tienes la respuesta en tu base de datos, indicala, y si no la tienes, dilo tambien.",
        "stream": False
    }
)

# Mostramos en consola solo el texto de respuesta.
print(response.json()["response"])
