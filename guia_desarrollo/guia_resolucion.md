# Guía para entregar las actividades correctamente

## Recibís el enunciado del ejercicio (previsiblemente adaptado a vuestro perfil)
Ejemplo:
- Crea un programa en JavaScript que represente el marcador de un partido de fútbol.
- Declara tres variables: una para los goles del equipo local, otra para los goles del equipo visitante y otra para el total de goles.
- Asigna a las variables de los equipos un número cualquiera de goles.
- Calcula el total de goles sumando los dos valores.
- Muestra por pantalla con document.write los goles de cada equipo y el total del partido.

## En primer lugar os vais al IDE que queráis, y resolvéis el ejercicio (y que funcione)

```
<script>
	/*
		Programa marcadores de futbol
		(c) 2025 Jose Vicente Carratala
		Este programa calcula el total de goles metidos en un partido
	*/

	var goles_equipo_local = 3;
	var goles_equipo_visitante = 2;
	var goles_totales = goles_equipo_local + goles_equipo_visitante;

	document.write("El equipo local ha marcado ",goles_equipo_local," goles<br>");
	document.write("El equipo visitante ha marcado ",goles_equipo_visitante," goles<br>");
	document.write("El total de goles marcados en el partido es de ",goles_totales ," goles<br>");
	
	
</script>
```

# Ahora resolvéis la rúbrica de evaluación

## Introducción breve y contextualización - 25% de la nota del ejercicio

En el ejemplo que se va a presentar a continuación, vamos a demostrar el uso de los elementos de un programa informático. Antes de crear programas más complejos, vamos a demostrar la utilidad de los elementos constructivos más sencillos que podemos encontrar en un lenguaje de programación.

## Desarrollo detallado y preciso - 25% de la nota del ejercicio

Para resolver este ejercicio que se presenta, vamos a usar recursos tales como el docstring, variables, operadores aritméticos, y salidas por documento o por consola.

Para crear un programa intentamos que tenga los siguientes bloques:
- Docstring
- Importaciones
- Declaración de variables globales
- Definición de funciones y clases
- Código de la función principal

## Aplicación práctica - 25% de la nota del ejercicio

Como una demostración extensa de los conceptos que se acaban de explicar, a continuación se muestra un programa de ejemplo que ilustra el esquema anteriormente mencionado:

```
<script>
	/*
		Programa marcadores de futbol
		(c) 2025 Jose Vicente Carratala
		Este programa calcula el total de goles metidos en un partido
	*/

	var goles_equipo_local = 3;
	var goles_equipo_visitante = 2;
	var goles_totales = goles_equipo_local + goles_equipo_visitante;

	document.write("El equipo local ha marcado ",goles_equipo_local," goles<br>");
	document.write("El equipo visitante ha marcado ",goles_equipo_visitante," goles<br>");
	document.write("El total de goles marcados en el partido es de ",goles_totales ," goles<br>");
	
	
</script>
```
Notas: No olvidemos que hay que utilizar la palabra var para declarar las variables.

## Conclusión breve - 25% de la nota del ejercicio

Siguiendo una estructura predefinida nos puede resultar más sencillo redactar programas informáticos, y crear herramientas que permitan resolver problemas reales.

# Respuesta del ejercicio:

Por lo tanto lo que me enviaréis en el ejercicio será la suma de las partes:

--- 

En el ejemplo que se va a presentar a continuación, vamos a demostrar el uso de los elementos de un programa informático. Antes de crear programas más complejos, vamos a demostrar la utilidad de los elementos constructivos más sencillos que podemos encontrar en un lenguaje de programación.

Para resolver este ejercicio que se presenta, vamos a usar recursos tales como el docstring, variables, operadores aritméticos, y salidas por documento o por consola.

Para crear un programa intentamos que tenga los siguientes bloques:
- Docstring
- Importaciones
- Declaración de variables globales
- Definición de funciones y clases
- Código de la función principal

Como una demostración extensa de los conceptos que se acaban de explicar, a continuación se muestra un programa de ejemplo que ilustra el esquema anteriormente mencionado:

```
<script>
	/*
		Programa marcadores de futbol
		(c) 2025 Jose Vicente Carratala
		Este programa calcula el total de goles metidos en un partido
	*/

	var goles_equipo_local = 3;
	var goles_equipo_visitante = 2;
	var goles_totales = goles_equipo_local + goles_equipo_visitante;

	document.write("El equipo local ha marcado ",goles_equipo_local," goles<br>");
	document.write("El equipo visitante ha marcado ",goles_equipo_visitante," goles<br>");
	document.write("El total de goles marcados en el partido es de ",goles_totales ," goles<br>");
	
	
</script>
```
Notas: No olvidemos que hay que utilizar la palabra var para declarar las variables.

Siguiendo una estructura predefinida nos puede resultar más sencillo redactar programas informáticos, y crear herramientas que permitan resolver problemas reales.




Ejemplo:

Un posible enunciado para el ejercicio:
- Un establo tiene varias cuadras y cada cuadra puede albergar 3 caballos.
- El usuario introduce el número total de caballos que quiere guardar.
- El programa debe calcular cuántas cuadras completas necesita, redondeando hacia arriba aunque la última no se llene.

# Resolución del ejercicio:
Paso 1:
Resolvéis el ejercicio en cuanto a código en el lenguaje que sea.
Ejemplo de ejercicio resuelto:

```
''' 
    Calculadora de cuadras
    v0.1 (c) 2025 Jose Vicente Carratalá
    Programa que calcula número de cuadras a partir de los caballos
'''

import math as matematicas

# Datos de inicio
caballos = 0
cuadras = 0
caballos_por_cuadra = 0

# Entrada de información
caballos_por_cuadra = int(input("Introduce el número de caballos por cuadra: "))
caballos = int(input("Introduce el número de caballos: "))

# Realización de cálculos
cuadras = caballos / caballos_por_cuadra
redondeoalza = matematicas.ceil(cuadras)

# Salida de resultados
print("Si tienes",caballos,"caballos")
print("Y te caben tres caballos por cuadra")
print("En ese caso necesitas",redondeoalza,"cuadras")


```

# Introducción del ejercicio según la rúbrica de evaluación:

## Introducción breve y contextualización - 25% de la nota del ejercicio
Vamos a realizar una demostración del uso de la librería matemática para realizar un redondeo al alza. La librería matemática sirve para tener acceso a realizar operaciones más avanzadas de las que permite el núcleo de Python.

## Desarrollo detallado y preciso - 25% de la nota del ejercicio
En Python, encontramos una librería llamada math, que tiene una serie de propiedades y métodos. Para usar esta librería, en primer lugar debemos importarla dentro de un programa, por ejemplo de esta forma:
```
import math
```

Hay que notar que es una buena práctica en Python trabajar con espacios de nombres (namespaces), por lo tanto sería más recomendable importar de esta forma:

```
import math as matematicas
```

Un pequeño ejemplo de uso de la librería sería este:

```
import math as matematicas
print(matematicas.pi)
```
El ejemplo anterior muestra el valor de la variable PI

## Aplicación práctica - 25% de la nota del ejercicio

A continuación se muestra un ejemplo de código completo en el que se ilustra el uso de la librería matemática

```
''' 
    Calculadora de cuadras
    v0.1 (c) 2025 Jose Vicente Carratalá
    Programa que calcula número de cuadras a partir de los caballos
'''

import math as matematicas

# Datos de inicio
caballos = 0
cuadras = 0
caballos_por_cuadra = 0

# Entrada de información
caballos_por_cuadra = int(input("Introduce el número de caballos por cuadra: "))
caballos = int(input("Introduce el número de caballos: "))

# Realización de cálculos
cuadras = caballos / caballos_por_cuadra
redondeoalza = matematicas.ceil(cuadras)

# Salida de resultados
print("Si tienes",caballos,"caballos")
print("Y te caben tres caballos por cuadra")
print("En ese caso necesitas",redondeoalza,"cuadras")


```

*Notas:*

Hay que tener cuidado en Python cuando introducimos un valor con input, porque por defecto se convierte en cadena de caracteres - usaremos conversión de tipo para evitar el error.

Hay que tener en cuenta que debemos cerrar tantos paréntesis como hayamos abierto

## Conclusión breve - 25% de la nota del ejercicio

Como hemos visto, la librería matemática nos va a ayudar a realizar operaciones complejas de forma sencilla y predecible.

Más adelante, podremos comprobar la utilidad de la librería matemática en programas más grandes.

# Entrega completa del ejercicio:

Por lo tanto, lo que entregaréis en la aplicación de subida, será lo siguiente (la suma de los cuatro apartados:

---
Vamos a realizar una demostración del uso de la librería matemática para realizar un redondeo al alza. La librería matemática sirve para tener acceso a realizar operaciones más avanzadas de las que permite el núcleo de Python.

En Python, encontramos una librería llamada math, que tiene una serie de propiedades y métodos. Para usar esta librería, en primer lugar debemos importarla dentro de un programa, por ejemplo de esta forma:
```
import math
```

Hay que notar que es una buena práctica en Python trabajar con espacios de nombres (namespaces), por lo tanto sería más recomendable importar de esta forma:

```
import math as matematicas
```

Un pequeño ejemplo de uso de la librería sería este:

```
import math as matematicas
print(matematicas.pi)
```
El ejemplo anterior muestra el valor de la variable PI

A continuación se muestra un ejemplo de código completo en el que se ilustra el uso de la librería matemática

```
''' 
    Calculadora de cuadras
    v0.1 (c) 2025 Jose Vicente Carratalá
    Programa que calcula número de cuadras a partir de los caballos
'''

import math as matematicas

# Datos de inicio
caballos = 0
cuadras = 0
caballos_por_cuadra = 0

# Entrada de información
caballos_por_cuadra = int(input("Introduce el número de caballos por cuadra: "))
caballos = int(input("Introduce el número de caballos: "))

# Realización de cálculos
cuadras = caballos / caballos_por_cuadra
redondeoalza = matematicas.ceil(cuadras)

# Salida de resultados
print("Si tienes",caballos,"caballos")
print("Y te caben tres caballos por cuadra")
print("En ese caso necesitas",redondeoalza,"cuadras")


```

*Notas:*

Hay que tener cuidado en Python cuando introducimos un valor con input, porque por defecto se convierte en cadena de caracteres - usaremos conversión de tipo para evitar el error.

Hay que tener en cuenta que debemos cerrar tantos paréntesis como hayamos abierto

Como hemos visto, la librería matemática nos va a ayudar a realizar operaciones complejas de forma sencilla y predecible.

Más adelante, podremos comprobar la utilidad de la librería matemática en programas más grandes.


---


