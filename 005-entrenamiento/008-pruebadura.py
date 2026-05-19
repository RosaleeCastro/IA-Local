import json
import difflib

# Este archivo NO usa un modelo generativo.
# En su lugar, compara la pregunta del usuario con preguntas ya guardadas
# en un archivo .jsonl y devuelve la respuesta de la mas parecida.
# Es una especie de buscador por similitud de texto.

# -----------------------------
# CONFIGURATION
# -----------------------------
DATA_FILE = "materiales/BOE-A-2023-13221.jsonl"
MIN_SIMILARITY = 0.90   # raise to 0.95 or 0.98 if you want it even stricter

def load_jsonl(path):
    # Lee el archivo .jsonl y lo convierte en una lista de diccionarios.
    rows = []
    with open(path, "r", encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line:
                continue
            item = json.loads(line)
            q = item["question"].strip()
            a = item["answer"].strip()
            rows.append({
                "question": q,
                "answer": a
            })
    return rows

def normalize(text):
    # Pasa el texto a minusculas y elimina espacios sobrantes.
    # Esto ayuda a comparar preguntas de forma mas consistente.
    return " ".join(text.strip().lower().split())

def find_best_match(user_question, dataset):
    # Busca la pregunta del dataset que mas se parece a la que ha escrito el usuario.
    user_q = normalize(user_question)

    best_item = None
    best_score = 0.0

    for item in dataset:
        trained_q = normalize(item["question"])
        # SequenceMatcher devuelve un numero entre 0 y 1.
        # Cuanto mas cerca de 1, mas parecidos son los textos.
        score = difflib.SequenceMatcher(None, user_q, trained_q).ratio()

        if score > best_score:
            best_score = score
            best_item = item

    return best_item, best_score

def main():
    # Paso 1: cargar todas las preguntas y respuestas del archivo.
    dataset = load_jsonl(DATA_FILE)

    print("Dataset loaded.")
    print("This system answers only with trained questions and answers.")
    print("Write 'salir' to exit.\n")

    while True:
        # Paso 2: pedir una pregunta al usuario.
        pregunta = input("Tú: ").strip()
        if pregunta.lower() in ["salir", "exit", "quit"]:
            break

        # Paso 3: buscar la coincidencia mas parecida.
        item, score = find_best_match(pregunta, dataset)

        if item is not None and score >= MIN_SIMILARITY:
            # Si la similitud es suficientemente alta, devolvemos la respuesta guardada.
            print("\nModelo:", item["answer"])
            print(f"(matched trained question with similarity {score:.4f})\n")
        else:
            # Si no hay una coincidencia clara, el sistema responde que no lo sabe.
            print("\nModelo: No lo sé según el material entrenado.\n")

if __name__ == "__main__":
    main()
