import requests
import chromadb
from http.server import HTTPServer, BaseHTTPRequestHandler
import json
import urllib.parse

OLLAMA_URL = "http://localhost:11434/api/embeddings"
OLLAMA_GENERATE = "http://localhost:11434/api/generate"
MODEL_EMBED = "nomic-embed-text:v1.5"  
MODEL_LLM = "qwen2.5:3b"
DB_PATH = "chromadb_migracion"
COLLECTION_NAME = "leyes_migracion"
TOP_K = 3
MAX_CONTEXT_CHARS = 8000
MAX_RESPONSE_TOKENS = 900

session = requests.Session()

client = chromadb.PersistentClient(path=DB_PATH)
collection = client.get_collection(name=COLLECTION_NAME)

def get_embedding(texto):
    response = session.post(OLLAMA_URL, json={
        "model": MODEL_EMBED,
        "prompt": texto,
        "keep_alive": "10m"
    }, timeout=30)
    response.raise_for_status()
    return response.json()["embedding"]

def buscar_articulos(consulta, n=TOP_K):
    embedding = get_embedding(consulta)
    resultados = collection.query(
        query_embeddings=[embedding],
        n_results=n
    )
    return resultados["documents"][0], resultados["distances"][0]

def preparar_contexto(docs):
    contexto = "\n\n".join(docs)
    if len(contexto) <= MAX_CONTEXT_CHARS:
        return contexto
    return contexto[:MAX_CONTEXT_CHARS] + "\n\n[Contexto recortado por longitud]"

def generar_respuesta(consulta, contexto):
    prompt = f"""Eres un asistente legal especializado en leyes de extranjería e inmigración en España.
Responde en español de forma clara y concisa basándote SOLO en el siguiente contexto legal.
Si la información no está en el contexto, indícalo.

Responde con todos los puntos relevantes que aparezcan en el contexto.
No cortes frases ni termines con una enumeracion incompleta.

CONTEXTO LEGAL:
{contexto}

PREGUNTA: {consulta}

RESPUESTA:"""

    response = session.post(OLLAMA_GENERATE, json={
        "model": MODEL_LLM,
        "prompt": prompt,
        "stream": False,
        "keep_alive": "10m",
        "options": {
            "temperature": 0.2,
            "num_ctx": 4096,
            "num_predict": MAX_RESPONSE_TOKENS
        }
    }, timeout=120)
    response.raise_for_status()
    return response.json()["response"].strip()

class Handler(BaseHTTPRequestHandler):
    def log_message(self, format, *args):
        pass

    def do_GET(self):
        with open("008-demo-interface.html", "r", encoding="utf-8") as archivo:
            html = archivo.read()

        self.send_response(200)
        self.send_header("Content-Type", "text/html; charset=utf-8")
        self.send_header("Cache-Control", "no-store")
        self.end_headers()
        self.wfile.write(html.encode("utf-8"))

    def do_POST(self):
        length = int(self.headers["Content-Length"])
        body = self.rfile.read(length)
        data = json.loads(body)
        consulta = data.get("consulta", "").strip()

        if not consulta:
            self._json({"error": "Consulta vacía"})
            return

        try:
            docs, dists = buscar_articulos(consulta)
            contexto = preparar_contexto(docs)
            respuesta = generar_respuesta(consulta, contexto)

            articulos = [
                {"texto": doc[:400] + "..." if len(doc) > 400 else doc,
                 "distancia": round(dist, 0)}
                for doc, dist in zip(docs, dists)
            ]

            self._json({
                "respuesta": respuesta,
                "articulos": articulos
            })
        except Exception as e:
            self._json({"error": str(e)})

    def _json(self, data):
        self.send_response(200)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.end_headers()
        self.wfile.write(json.dumps(data, ensure_ascii=False).encode("utf-8"))

print("Servidor iniciado en http://localhost:8080")
print("Abre tu navegador en http://localhost:8080")
print("Pulsa Ctrl+C para detener")
HTTPServer(("localhost", 8080), Handler).serve_forever()
