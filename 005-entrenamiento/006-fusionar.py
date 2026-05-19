import os
import torch
from peft import PeftModel
from transformers import AutoModelForCausalLM, AutoTokenizer
from config import HF_BASE_MODEL, LORA_OUTPUT_DIR, MERGED_OUTPUT_DIR

# Une el modelo base con el adaptador LoRA entrenado.
# El resultado es un modelo completo que se puede cargar sin PEFT.

# =========================================================
# CONFIGURATION
# =========================================================
BASE_MODEL = HF_BASE_MODEL                    # meta-llama/Meta-Llama-3-8B-Instruct
ADAPTER_PATH = f"./{LORA_OUTPUT_DIR}"         # ./boe-llama3-8b-lora
OUTPUT_PATH = f"./{MERGED_OUTPUT_DIR}"        # ./boe-llama3-8b-fusionado


def main():
    # Primero comprobamos que exista la carpeta donde se guardo el adaptador.
    if not os.path.isdir(ADAPTER_PATH):
        raise FileNotFoundError(f"No existe la carpeta del adaptador: {ADAPTER_PATH}")

    print("Loading tokenizer...")
    # El tokenizador se guarda junto al modelo final para poder usarlo despues.
    tokenizer = AutoTokenizer.from_pretrained(
        BASE_MODEL,
        trust_remote_code=True
    )

    print("Loading base model...")
    # Cargamos el modelo original, es decir, el que aun no tiene el ajuste LoRA.
    base_model = AutoModelForCausalLM.from_pretrained(
        BASE_MODEL,
        torch_dtype=torch.float16 if torch.cuda.is_available() else torch.float32,
        device_map="auto" if torch.cuda.is_available() else None,
        trust_remote_code=True
    )

    print("Loading adapter...")
    # Encima del modelo base montamos el adaptador entrenado.
    model = PeftModel.from_pretrained(base_model, ADAPTER_PATH)

    print("Merging adapter into base model...")
    # Esta operacion copia el conocimiento del adaptador dentro del modelo.
    merged_model = model.merge_and_unload()

    print(f"Saving merged model to: {OUTPUT_PATH}")
    os.makedirs(OUTPUT_PATH, exist_ok=True)

    # Se guarda el modelo ya fusionado y tambien su tokenizador.
    merged_model.save_pretrained(OUTPUT_PATH)
    tokenizer.save_pretrained(OUTPUT_PATH)

    print("Done.")
    print(f"Merged model saved in: {OUTPUT_PATH}")


if __name__ == "__main__":
    main()
