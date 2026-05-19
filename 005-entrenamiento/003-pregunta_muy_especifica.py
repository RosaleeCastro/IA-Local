import requests

# Este archivo prueba una pregunta todavia mas especializada.
# La idea es ver si el modelo general conoce un dato academico muy concreto.

response = requests.post(
    "http://localhost:11434/api/generate",
    json={
        "model": "qwen2.5:3b-instruct",
        # Pregunta especifica sobre una asignatura concreta de DAW.
        "prompt": "Dame el temario de la asignatura Diseño de Interfaces Web de segundo curso de DAW. Si tienes la respuesta en tu base de datos, indicala, y si no la tienes, dilo tambien.",
        "stream": False
    }
)

# Se imprime unicamente el texto que genera el modelo.
print(response.json()["response"])
