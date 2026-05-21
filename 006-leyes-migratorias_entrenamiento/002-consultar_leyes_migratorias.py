from config import MAIN_JSONL
from motor_leyes import answer_question, load_jsonl

# ============================================================
# 002-consultar_leyes_migratorias.py
# ============================================================
# Ejercicio basado en 005-entrenamiento/008-pruebadura.py
#
# Sistema de consulta en dos capas:
# 1. Busca en el JSONL una pregunta parecida.
# 2. Si no hay una coincidencia clara, pregunta a Ollama usando
#    los fragmentos mas parecidos como contexto.
# ============================================================

def main():
    dataset = load_jsonl(MAIN_JSONL)
    print(f"Dataset cargado: {len(dataset)} ejemplos.")
    print("Escribe 'salir' para terminar.\n")

    while True:
        try:
            question = input("Tu: ").strip()
        except EOFError:
            print("\nFin de entrada.")
            break

        if question.lower() in ["salir", "exit", "quit"]:
            break

        if not question:
            continue

        result = answer_question(question, dataset)

        if result["source"] == "JSONL":
            print(f"\nModelo (JSONL, similitud {result['similarity']:.2f}):")
        else:
            print(f"\n[similitud max {result['similarity']:.2f} - usando Ollama con contexto]")
            print("Modelo (Ollama):")

        print(result["answer"])

        print()


if __name__ == "__main__":
    main()
