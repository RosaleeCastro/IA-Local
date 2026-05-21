# ==============================================
# verificar.py
# Comprueba cuántos fragmentos hay en la base
# y de qué documento viene cada uno
# ==============================================

import chromadb

DB_PATH   = "chromadb_leyes"
COLLECTION = "leyes_migracion"

# Conectar con la base existente
client     = chromadb.PersistentClient(path=DB_PATH)
collection = client.get_collection(name=COLLECTION)

# Total de fragmentos
total = collection.count()
print(f"Total de fragmentos en la base: {total}")
print("-" * 40)

# Contar por origen usando los metadatos
todos = collection.get(include=["metadatas"])

conteo = {}
for meta in todos["metadatas"]:
    origen = meta["origen"]
    conteo[origen] = conteo.get(origen, 0) + 1

print("Fragmentos por documento:")
for origen, cantidad in conteo.items():
    print(f"  {origen}: {cantidad} fragmentos")

print("-" * 40)
print("Ejemplo del primer fragmento:")
primer = collection.get(ids=["doc0_0"], include=["documents", "metadatas"])
if primer["documents"]:
    print(f"  Origen: {primer['metadatas'][0]['origen']}")
    print(f"  Texto:  {primer['documents'][0][:200]}...")