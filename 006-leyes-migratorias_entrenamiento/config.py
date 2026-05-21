# ============================================================
# config.py - Configuracion central del ejercicio
# 006 - Entrenamiento IA: Leyes Migratorias en Espana
# ============================================================
# Todos los scripts importan desde aqui.
# Si algo cambia (modelo, ruta, parametros), solo lo tocas aqui.

# ------------------------------------------------------------
# OLLAMA LOCAL
# ------------------------------------------------------------
OLLAMA_BASE_URL = "http://localhost:11434"
OLLAMA_MODEL    = "llama3:latest"          # modelo ya instalado en local

# ------------------------------------------------------------
# RUTAS DE DATOS
# ------------------------------------------------------------
DATA_DIR   = "materiales"
MAIN_JSONL = "materiales/leyes_migratorias.jsonl"

# ------------------------------------------------------------
# FORMATO DE PROMPT
# Debe ser identico en entrenamiento y en inferencia.
# ------------------------------------------------------------
PROMPT_PREFIX = "### Pregunta:\n"
PROMPT_MIDDLE = "\n\n### Respuesta:\n"

# ------------------------------------------------------------
# PARAMETROS DE GENERACION (inferencia)
# ------------------------------------------------------------
MAX_NEW_TOKENS = 300
TEMPERATURE    = 0.1          # bajo = respuestas mas deterministas

# ------------------------------------------------------------
# FINE-TUNING CON LORA (scripts 004 y 005)
# Modelo base de HuggingFace - cabe en GTX 1650 (4 GB VRAM)
# ------------------------------------------------------------
HF_BASE_MODEL    = "Qwen/Qwen2.5-1.5B-Instruct"
LORA_OUTPUT_DIR  = "leyes-qwen25-1b-lora"
MERGED_OUTPUT_DIR = "leyes-qwen25-1b-fusionado"

# ------------------------------------------------------------
# PARAMETROS DE ENTRENAMIENTO
# ------------------------------------------------------------
SEED                        = 42
VAL_RATIO                   = 0.10    # 10% para validacion
NUM_TRAIN_EPOCHS            = 6
LEARNING_RATE               = 2e-4
PER_DEVICE_TRAIN_BATCH_SIZE = 1
GRADIENT_ACCUMULATION_STEPS = 8       # batch efectivo = 8
MAX_LENGTH                  = 512
LORA_R                      = 16
LORA_ALPHA                  = 32
LORA_DROPOUT                = 0.05
