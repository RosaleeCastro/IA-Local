import requests
import sys
from config import OLLAMA_BASE_URL, OLLAMA_MODEL

# ============================================================
# 001-verificar.py
# ============================================================
# Comprueba que Ollama esta corriendo en local y que el modelo
# necesario para el ejercicio esta disponible.
#
# Ejecutar SIEMPRE antes de cualquier otro script.
# ============================================================

SEPARADOR = "=" * 55


def verificar_conexion():
    """Comprueba que el servidor Ollama responde."""
    print(f"\n1. Conexion a Ollama en {OLLAMA_BASE_URL} ...")
    try:
        r = requests.get(f"{OLLAMA_BASE_URL}/api/tags", timeout=8)
        r.raise_for_status()
        print("   OK - Ollama esta activo")
        return r.json().get("models", [])
    except requests.exceptions.ConnectionError:
        print("   ERROR - No se puede conectar a Ollama")
        print("   Solucion: abre Ollama (icono en la bandeja del sistema)")
        sys.exit(1)
    except requests.exceptions.Timeout:
        print("   ERROR - Timeout. Ollama tarda demasiado en responder")
        sys.exit(1)


def listar_modelos(modelos):
    """Muestra todos los modelos instalados con su tamanio."""
    print(f"\n2. Modelos instalados ({len(modelos)}):")
    if not modelos:
        print("   (ninguno)")
        return
    for m in modelos:
        nombre  = m.get("name", "?")
        size_gb = m.get("size", 0) / 1_073_741_824
        print(f"   - {nombre:<35} {size_gb:.1f} GB")


def verificar_modelo(modelos):
    """Comprueba que el modelo del ejercicio esta disponible."""
    print(f"\n3. Buscando modelo del ejercicio: {OLLAMA_MODEL} ...")
    nombres = [m.get("name", "") for m in modelos]
    if OLLAMA_MODEL in nombres:
        print(f"   OK - Modelo '{OLLAMA_MODEL}' encontrado")
        return True
    else:
        print(f"   AVISO - Modelo '{OLLAMA_MODEL}' NO encontrado")
        print(f"   Solucion: ejecuta en tu terminal:")
        print(f"   ollama pull {OLLAMA_MODEL}")
        return False


def verificar_respuesta():
    """Hace una peticion de prueba para confirmar que el modelo responde."""
    print(f"\n4. Prueba de respuesta del modelo ...")
    try:
        r = requests.post(
            f"{OLLAMA_BASE_URL}/api/generate",
            json={
                "model": OLLAMA_MODEL,
                "prompt": "Di solo la palabra: LISTO",
                "stream": False,
            },
            timeout=60,
        )
        r.raise_for_status()
        respuesta = r.json().get("response", "").strip()
        print(f"   OK - Respuesta recibida: '{respuesta[:60]}'")
        return True
    except requests.exceptions.Timeout:
        print("   AVISO - El modelo tarda en cargar. Vuelve a ejecutar en 30 segundos")
        return False
    except Exception as e:
        print(f"   ERROR - {e}")
        return False


def main():
    print(SEPARADOR)
    print("  VERIFICACION DEL ENTORNO - Ejercicio 006")
    print("  Leyes Migratorias en Espana")
    print(SEPARADOR)

    modelos         = verificar_conexion()
    listar_modelos(modelos)
    modelo_ok       = verificar_modelo(modelos)
    respuesta_ok    = verificar_respuesta() if modelo_ok else False

    print(f"\n{SEPARADOR}")
    if modelo_ok and respuesta_ok:
        print("  RESULTADO: TODO LISTO")
        print("  Puedes continuar con 002-antes_de_entrenar.py")
    else:
        print("  RESULTADO: REVISA LOS ERRORES ANTERIORES")
    print(SEPARADOR)


if __name__ == "__main__":
    main()
