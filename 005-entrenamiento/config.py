# Configuracion central del proyecto.
# Cambia OLLAMA_HOST con la IP real de tu VM Ubuntu Server.
# El resto de scripts importan desde aqui para no repetir valores.

OLLAMA_HOST = "http://192.168.1.43"   # <-- cambia por la IP de tu VM
OLLAMA_PORT = 11434
OLLAMA_BASE_URL = f"{OLLAMA_HOST}:{OLLAMA_PORT}"

# Modelo cargado en Ollama (ollama list para verificar el nombre exacto)
OLLAMA_MODEL = "qwen2.5:3b"

# Rutas de datos
DATA_DIR = "materiales"
MAIN_JSONL = "materiales/BOE-A-2023-13221.jsonl"

# Formato de prompt usado en entrenamiento y en inferencia
PROMPT_PREFIX = "### Pregunta:\n"
PROMPT_MIDDLE = "\n\n### Respuesta:\n"

# Parametros de generacion
MAX_NEW_TOKENS = 256
TEMPERATURE = 0.1

# Fine-tuning (scripts 005 y 006) — modelo base de HuggingFace (Apache 2.0, sin licencia)
HF_BASE_MODEL = "Qwen/Qwen2.5-3B-Instruct"
LORA_OUTPUT_DIR = "boe-qwen25-3b-lora"
MERGED_OUTPUT_DIR = "boe-qwen25-3b-fusionado"
