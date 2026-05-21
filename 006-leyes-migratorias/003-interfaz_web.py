import json
import mimetypes
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import unquote

from config import MAIN_JSONL, OLLAMA_MODEL
from motor_leyes import answer_question, load_jsonl

# ============================================================
# 003-interfaz_web.py
# ============================================================
# Interfaz visual local para el ejercicio 006.
#
# Ejecuta:
#   python 003-interfaz_web.py
#
# Abre:
#   http://localhost:8060
# ============================================================

HOST = "localhost"
PORT = 8060
BASE_DIR = Path(__file__).resolve().parent
WEB_DIR = BASE_DIR / "web"

DATASET = load_jsonl(MAIN_JSONL)


class AppHandler(BaseHTTPRequestHandler):
    def do_GET(self):
        path = unquote(self.path.split("?", 1)[0])
        if path == "/":
            path = "/index.html"

        file_path = (WEB_DIR / path.lstrip("/")).resolve()
        if not str(file_path).startswith(str(WEB_DIR.resolve())):
            self.send_error(403)
            return

        if not file_path.is_file():
            self.send_error(404)
            return

        content_type = mimetypes.guess_type(file_path.name)[0] or "application/octet-stream"
        body = file_path.read_bytes()
        self.send_response(200)
        self.send_header("Content-Type", content_type)
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def do_POST(self):
        if self.path != "/api/ask":
            self.send_error(404)
            return

        length = int(self.headers.get("Content-Length", "0"))
        raw_body = self.rfile.read(length)

        try:
            payload = json.loads(raw_body.decode("utf-8"))
            question = str(payload.get("question", "")).strip()
        except json.JSONDecodeError:
            self.send_json({"error": "JSON invalido"}, status=400)
            return

        if not question:
            self.send_json({"error": "La pregunta no puede estar vacia"}, status=400)
            return

        result = answer_question(question, DATASET)
        matches = [
            {
                "similarity": round(score, 2),
                "question": item["question"],
            }
            for score, item in result["matches"]
        ]

        self.send_json({
            "answer": result["answer"],
            "source": result["source"],
            "similarity": round(result["similarity"], 2),
            "matches": matches,
            "model": OLLAMA_MODEL,
            "datasetSize": len(DATASET),
        })

    def send_json(self, payload, status=200):
        body = json.dumps(payload, ensure_ascii=False).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def log_message(self, format, *args):
        print(f"[web] {self.address_string()} - {format % args}")


def main():
    server = ThreadingHTTPServer((HOST, PORT), AppHandler)
    print(f"Interfaz lista: http://{HOST}:{PORT}")
    print(f"Dataset cargado: {len(DATASET)} ejemplos")
    print("Pulsa Ctrl+C para detener.")
    server.serve_forever()


if __name__ == "__main__":
    main()
