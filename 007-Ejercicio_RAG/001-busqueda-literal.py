documentos = [
    "Valencia es una ciudad situada en la costa este de España.",
    "Madrid es la capital de España y tiene muchos museos.",
    "Barcelona es conocida por su arquitectura y sus playas.",
    "Python es un lenguaje de programación usado en inteligencia artificial.",
    "La inteligencia artificial permite crear sistemas que aprenden de datos.",
    "Una base de datos guarda información para consultarla después."
]

consulta = input("Escribe una palabra para buscar: ")

print("\nResultados encontrados:\n")

encontrados = 0

for documento in documentos:
    if consulta.lower() in documento.lower():
        print("-", documento)
        encontrados += 1

if encontrados == 0:
    print("No se encontraron resultados.")