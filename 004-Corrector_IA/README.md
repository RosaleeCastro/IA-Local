# 004 - Corrector IA con Ollama local

Este proyecto permite corregir textos y redactar correos profesionales usando una IA local con Ollama.

La aplicacion funciona desde el navegador con XAMPP y se comunica con Ollama instalado en la misma maquina.

## 1. Requisitos

Necesitas tener:

- XAMPP iniciado.
- Apache activo en XAMPP.
- Ollama instalado y abierto.
- El modelo `llama3:latest` descargado en Ollama.

Para comprobar los modelos instalados:

```powershell
ollama list
```

Si no tienes `llama3:latest`, puedes instalarlo con:

```powershell
ollama pull llama3:latest
```

## 2. Abrir la aplicacion

Con Apache activo en XAMPP, abre en el navegador:

```text
http://localhost/Ia_VM/004-Corrector_IA/
```

La interfaz permite:

- Escribir o dictar texto.
- Corregir textos escritos o dictados por voz.
- Redactar correos profesionales.
- Copiar el resultado generado.

## 3. Comprobar Ollama local

El proyecto incluye un archivo de comprobacion:

```text
ping.php
```

Puedes abrirlo en el navegador:

```text
http://localhost/Ia_VM/004-Corrector_IA/ping.php
```

Si todo funciona, veras una respuesta JSON parecida a:

```json
{
  "ok": true,
  "host": "localhost:11434",
  "modelos": ["llama3:latest"]
}
```

## 4. Modelo usado

El backend usa por defecto:

```text
llama3:latest
```

La configuracion esta en:

```text
back.php
```

Linea principal:

```php
$modelo = getenv("OLLAMA_MODEL") ?: "llama3:latest";
```

La URL local de Ollama es:

```php
$ollamaUrl = getenv("OLLAMA_URL") ?: "http://localhost:11434/api/generate";
```

## 5. Como funciona

El flujo es:

1. El usuario escribe o dicta un texto en `index.html`.
2. El navegador envia el texto a `back.php`.
3. `back.php` prepara un prompt segun el modo:
   - Correccion de correo.
   - Redaccion de correo.
   - Correccion de texto general.
4. `back.php` llama a Ollama local.
5. Ollama genera la respuesta.
6. La interfaz muestra el texto corregido o redactado.

## 6. Archivos principales

```text
index.html
```

Interfaz visual del corrector.

```text
back.php
```

Backend que recibe el texto, llama a Ollama y devuelve la respuesta.

```text
ping.php
```

Comprueba que Ollama local esta activo y lista los modelos instalados.

```text
DECISIONES_TECNICAS.md
```

Documento con las decisiones tecnicas del proyecto.

## 7. Probar Ollama desde terminal

Si la aplicacion no responde, prueba primero Ollama directamente:

```powershell
ollama run llama3:latest "Corrige este texto: hola equipo necesito revisar informe"
```

Si Ollama responde en terminal, el modelo esta funcionando.

## 8. Problemas comunes

### La pagina dice que Ollama no esta disponible

Comprueba que Ollama este abierto.

Tambien puedes probar:

```powershell
ollama serve
```

### El modelo tarda mucho

La primera respuesta puede tardar porque Ollama carga el modelo en memoria.

Puedes precalentarlo con:

```powershell
ollama run llama3:latest "hola"
```

### El modelo no existe

Instalalo con:

```powershell
ollama pull llama3:latest
```

### Apache no abre la pagina

Comprueba que Apache este iniciado en XAMPP y que la carpeta este en:

```text
C:\xampp\htdocs\Ia_VM\004-Corrector_IA
```

## 9. Cambiar de modelo

Puedes cambiar el modelo editando `back.php`:

```php
$modelo = getenv("OLLAMA_MODEL") ?: "llama3:latest";
```

Por ejemplo, si tienes otro modelo instalado:

```php
$modelo = getenv("OLLAMA_MODEL") ?: "qwen2.5:3b";
```

Despues guarda el archivo y vuelve a probar la aplicacion.

## 10. Nota importante

Este proyecto ya no usa una VM.

Ahora usa Ollama local en:

```text
http://localhost:11434
```
