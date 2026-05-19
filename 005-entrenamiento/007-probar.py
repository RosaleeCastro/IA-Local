import re
import torch
from transformers import AutoTokenizer, AutoModelForCausalLM

# Este script sirve para hablar por consola con el modelo ya entrenado.
# Flujo general:
# 1. Carga el modelo fusionado
# 2. Espera una pregunta del usuario
# 3. Construye el prompt con el formato usado en entrenamiento
# 4. Genera la respuesta
# 5. Limpia el texto y lo muestra

# =========================================================
# CONFIGURATION
# =========================================================
MODEL_PATH = "./boe-qwen35-4b-lora-fusionado"

MAX_NEW_TOKENS = 192
TEMPERATURE = 0.0
TOP_P = 1.0

PROMPT_PREFIX = "### Pregunta:\n"
PROMPT_MIDDLE = "\n\n### Respuesta:\n"


# =========================================================
# HELPERS
# =========================================================
def build_prompt(question: str) -> str:
    # Prepara la pregunta en el mismo formato con el que se entreno al modelo.
    question = question.strip()
    return f"{PROMPT_PREFIX}{question}{PROMPT_MIDDLE}"


def clean_response(text: str) -> str:
    # Algunos modelos repiten etiquetas como "Pregunta" o "Respuesta".
    # Esta funcion intenta dejar el texto limpio para que sea mas legible.
    text = text.strip()

    stops = [
        "\n### Pregunta:",
        "\n### Respuesta:",
        "\nPregunta:",
        "\nRespuesta:"
    ]
    for s in stops:
        if s in text:
            text = text.split(s)[0].strip()

    text = re.sub(r"\s+", " ", text).strip()
    return text


def load_model():
    print("Loading tokenizer...")
    # Cargamos el tokenizador desde la carpeta del modelo final.
    tokenizer = AutoTokenizer.from_pretrained(
        MODEL_PATH,
        trust_remote_code=True
    )

    print("Loading model...")
    # Cargamos el modelo ya fusionado, listo para inferencia.
    model = AutoModelForCausalLM.from_pretrained(
        MODEL_PATH,
        torch_dtype=torch.float16 if torch.cuda.is_available() else torch.float32,
        device_map="auto" if torch.cuda.is_available() else None,
        trust_remote_code=True
    )

    if tokenizer.pad_token is None:
        tokenizer.pad_token = tokenizer.eos_token

    # eval() pone el modelo en modo evaluacion.
    model.eval()
    return tokenizer, model


def answer_question(model, tokenizer, question: str) -> str:
    # Convierte la pregunta en un prompt del formato esperado por el modelo.
    prompt = build_prompt(question)

    # El tokenizador transforma texto en tensores numericos.
    inputs = tokenizer(prompt, return_tensors="pt")
    inputs = {k: v.to(model.device) for k, v in inputs.items()}

    with torch.no_grad():
        # generate() crea la continuacion del texto de entrada.
        outputs = model.generate(
            **inputs,
            max_new_tokens=MAX_NEW_TOKENS,
            do_sample=False if TEMPERATURE == 0 else True,
            temperature=None if TEMPERATURE == 0 else TEMPERATURE,
            top_p=TOP_P,
            pad_token_id=tokenizer.pad_token_id,
            eos_token_id=tokenizer.eos_token_id,
            use_cache=True,
            repetition_penalty=1.05,
        )

    # Nos quedamos solo con los tokens nuevos, es decir, la respuesta.
    new_tokens = outputs[0][inputs["input_ids"].shape[1]:]
    response = tokenizer.decode(new_tokens, skip_special_tokens=True)
    response = clean_response(response)

    if not response:
        return "No he podido generar una respuesta."

    return response


def main():
    # Paso 1: cargar modelo y tokenizador.
    tokenizer, model = load_model()

    print("Model loaded.")
    print("Write 'salir' to exit.\n")

    while True:
        # Paso 2: leer una pregunta del usuario.
        question = input("Tú: ").strip()
        if question.lower() in ["salir", "exit", "quit"]:
            break

        if not question:
            continue

        # Paso 3: generar y mostrar la respuesta.
        answer = answer_question(model, tokenizer, question)
        print("\nModelo:", answer)
        print()


if __name__ == "__main__":
    main()
