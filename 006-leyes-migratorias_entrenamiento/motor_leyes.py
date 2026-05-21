import json
import difflib
import requests
from config import OLLAMA_BASE_URL, OLLAMA_MODEL

MIN_SIMILARITY = 0.60
RAG_TOP_K = 3


def load_jsonl(path):
    rows = []
    with open(path, "r", encoding="utf-8") as f:
        for line_number, line in enumerate(f, start=1):
            line = line.strip()
            if not line:
                continue

            try:
                item = json.loads(line)
            except json.JSONDecodeError as e:
                print(f"[AVISO] JSON invalido en linea {line_number}: {e}")
                continue

            question = str(item.get("question", "")).strip()
            answer = str(item.get("answer", "")).strip()

            if not question or not answer:
                print(f"[AVISO] Registro incompleto en linea {line_number}")
                continue

            rows.append({
                "question": question,
                "answer": answer,
            })

    if not rows:
        raise ValueError(f"No se cargaron ejemplos validos desde {path}")

    return rows


def normalize(text):
    return " ".join(text.strip().lower().split())


def find_top_matches(user_question, dataset, top_k=RAG_TOP_K):
    user_q = normalize(user_question)
    scored = []

    for item in dataset:
        reference_q = normalize(item["question"])
        score = difflib.SequenceMatcher(None, user_q, reference_q).ratio()
        scored.append((score, item))

    scored.sort(key=lambda x: x[0], reverse=True)
    return scored[:top_k]


def ask_ollama(question, context_fragments):
    context_text = "\n\n".join(
        f"Pregunta de referencia: {item['question']}\n"
        f"Respuesta de referencia: {item['answer']}"
        for item in context_fragments
    )

    prompt = (
        "Eres un asistente educativo sobre leyes migratorias en Espana. "
        "Responde de forma clara, breve y prudente. "
        "Usa solo los fragmentos de referencia para dar informacion concreta. "
        "No cites articulos, plazos, importes ni requisitos concretos si no aparecen "
        "en los fragmentos de referencia. "
        "No inventes nombres de organismos ni tramites. "
        "Si los fragmentos no bastan, dilo claramente y recomienda consultar "
        "fuentes oficiales o asesoramiento profesional.\n\n"
        f"Fragmentos de referencia:\n{context_text}\n\n"
        f"Pregunta del usuario: {question}\n\n"
        "Respuesta:"
    )

    try:
        response = requests.post(
            f"{OLLAMA_BASE_URL}/api/generate",
            json={
                "model": OLLAMA_MODEL,
                "prompt": prompt,
                "stream": False,
            },
            timeout=60,
        )
        response.raise_for_status()
        return response.json().get("response", "").strip()
    except requests.exceptions.ConnectionError:
        return f"[ERROR] No se puede conectar a Ollama en {OLLAMA_BASE_URL}"
    except requests.exceptions.Timeout:
        return "[ERROR] Tiempo de espera agotado esperando respuesta de Ollama."
    except requests.exceptions.HTTPError as e:
        return f"[ERROR] Respuesta HTTP inesperada: {e}"
    except Exception as e:
        return f"[ERROR] {e}"


def answer_question(question, dataset):
    matches = find_top_matches(question, dataset)
    best_score, best_item = matches[0]

    if best_score >= MIN_SIMILARITY:
        return {
            "answer": best_item["answer"],
            "source": "JSONL",
            "similarity": best_score,
            "matches": matches,
        }

    context = [item for _, item in matches]
    answer = ask_ollama(question, context)
    return {
        "answer": answer,
        "source": "Ollama",
        "similarity": best_score,
        "matches": matches,
    }
