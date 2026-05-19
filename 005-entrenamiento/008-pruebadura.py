import json
import difflib
import requests
from config import OLLAMA_BASE_URL, OLLAMA_MODEL, MAIN_JSONL

# Sistema de respuesta en dos capas:
# 1. Busca en el JSONL si hay una pregunta parecida (similitud de texto).
# 2. Si no hay coincidencia clara, manda la pregunta directamente a Ollama
#    con el contexto de los fragmentos mas parecidos (RAG simple).

MIN_SIMILARITY = 0.60   # umbral para respuesta directa desde JSONL
RAG_TOP_K = 3           # cuantos fragmentos incluir como contexto para Ollama


def load_jsonl(path):
    rows = []
    with open(path, "r", encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line:
                continue
            item = json.loads(line)
            rows.append({
                "question": item["question"].strip(),
                "answer": item["answer"].strip(),
            })
    return rows


def normalize(text):
    return " ".join(text.strip().lower().split())


def find_top_matches(user_question, dataset, top_k=RAG_TOP_K):
    user_q = normalize(user_question)
    scored = []
    for item in dataset:
        score = difflib.SequenceMatcher(None, user_q, normalize(item["question"])).ratio()
        scored.append((score, item))
    scored.sort(key=lambda x: x[0], reverse=True)
    return scored[:top_k]


def ask_ollama(question: str, context_fragments: list[dict]) -> str:
    # Construye un prompt RAG: contexto + pregunta del usuario.
    context_text = "\n\n".join(
        f"Pregunta de referencia: {f['question']}\nRespuesta de referencia: {f['answer']}"
        for f in context_fragments
    )
    prompt = (
        "Usa los siguientes fragmentos de referencia para responder la pregunta. "
        "Si los fragmentos no son suficientes, responde con tu conocimiento general.\n\n"
        f"{context_text}\n\n"
        f"Pregunta del usuario: {question}\n\n"
        "Respuesta:"
    )

    try:
        resp = requests.post(
            f"{OLLAMA_BASE_URL}/api/generate",
            json={"model": OLLAMA_MODEL, "prompt": prompt, "stream": False},
            timeout=60,
        )
        resp.raise_for_status()
        return resp.json()["response"].strip()
    except requests.exceptions.ConnectionError:
        return f"[ERROR] No se puede conectar a Ollama en {OLLAMA_BASE_URL}"
    except requests.exceptions.Timeout:
        return "[ERROR] Tiempo de espera agotado esperando respuesta de Ollama."
    except Exception as e:
        return f"[ERROR] {e}"


def main():
    dataset = load_jsonl(MAIN_JSONL)
    print(f"Dataset cargado: {len(dataset)} ejemplos.")
    print("Escribe 'salir' para terminar.\n")

    while True:
        pregunta = input("Tú: ").strip()
        if pregunta.lower() in ["salir", "exit", "quit"]:
            break
        if not pregunta:
            continue

        matches = find_top_matches(pregunta, dataset)
        best_score, best_item = matches[0]

        if best_score >= MIN_SIMILARITY:
            # Respuesta directa desde el JSONL entrenado
            print(f"\nModelo (JSONL, similitud {best_score:.2f}):", best_item["answer"])
        else:
            # Fallback: Ollama con los fragmentos mas parecidos como contexto
            context = [item for _, item in matches]
            print(f"\n[similitud max {best_score:.2f} — usando Ollama con contexto]")
            answer = ask_ollama(pregunta, context)
            print("Modelo (Ollama):", answer)

        print()


if __name__ == "__main__":
    main()
