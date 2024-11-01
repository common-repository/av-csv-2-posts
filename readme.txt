=== Plugin Name ===
Contributors: xiaobai_wp
Tags: csv,bbdd,import,posts
Requires at least: 3.x
Tested up to: 3.1
Stable tag: trunk
Importar archivos CSV y convertirlos en Posts. Seleccionar campos, autor, post_type, imagen destacada, campos personalizados,  categor&iacute;as...

== Description ==

AV CSV 2 POST. Este plugin est&aacute; dise&ntilde;ado para importar archivos en formato CSV y convertirlos en Posts.

**Caracter&iacute;sticas:**

**Subida del archivo CSV:**

* Importar archivos CSV. 
* Cada archivo subido se almacena en una nueva tabla intermedia (avit) en la bbdd. 
* Cada línea del archivo csv se contempla como un registro y se almacena en otra tabla intermedia la bbdd (avit_datos).

**Creaci&oacute;n de entradas:**

* Crear entradas (Posts) de forma autom&aacute;tica. Recogiendo los datos de las tablas intermedias (procedentes de la subida de archivos csv).
* Asignar el contenido a un autor determinado. 
* Seleccionar el Tipo de contenido (Post_type).
* Asignar un estado predeterminado para el status de las entradas (publicado - borrador).
* Asignar un estado predeterminado para el status de los comentarios (permitir - no permitir).
* Establecer c&oacute;digo HTML delante y detr&aacute;s de cada campo.

* Seleccionar los campos a incluir en el t&iacute;tulo de las entradas.
* Seleccionar los campos a incluir en el contenido de las entradas (Indicar si se trada de texto o de im&aacute;gen).
* Establecer un campo como imagen destacada. 
* Crear campos personalizados (visible y ocultos). 
* Seleccionar campos para asignar categor&iacute;as a cada post (por valores distintos). 
* Establecer un orden de salida de los campos.
* Colocar la etiqueta more... en cualquier parte del post.

**Creaci&oacute;n de categor&iacute;s y etiquetas:**

* Crear categor&iacute;as y etiquetas de forma masiva (una categor&iacute;a o etiqueta para cada registro). En la creaci&oacute;n de etiquetas, se evitar&aacute;n los duplicados. 



== Installation ==


1. Subir el plugin a la carpeta /wp-content/plugins/
1. Activar el plugin desde la zona de administraci&oacute;n.
1. En el men&uacute; Herramientas aparece una opci&oacute;n de acceso al plugin. AV CSV 2 POST. 

== Frequently Asked Questions ==

= Como crear el archivo CSV? =
Con cualquier programa que nos permita exportar datos en este formato. 
Como este plugin est&aacute; pensado para convertir datos de una tabla de una BBDD a entradas normalizadas de Wordpress, se aconseja:
Usar el panel de administraci&oacute;n de phpMyAdmin para realizar la exportaci&oacute;n.
Activar la casilla de exportar como datos CSV.
Activar la casilla de incluir nombres de campos en primera fila.
Comprobar que la opci&oacute;n (campos terminados por) se corresponde al punto y coma.
Activar la casilla Enviar (para obtener el resultado en forma de archivo - descargar).
Desactivar la casilla que dice (recordar la plantilla), para no tener problemas de interpretaci&oacute;n entre valores num&eacute;ricos y de fecha.


= Una vez creadas las entradas, se puede invertir el proceso? =

No. El alcance de este plugin finaliza en el momento en que se crean las entradas.



== Screenshots ==

1. Subir archivo CSV paso 1. Seleccionar archivo, Delimitador de campo y primera fila como nombres de campo.
2. Subir archivo CSV paso 2. Configurar guardado de datos en las tablas intermedias.
3. Crear entradas paso 1. Seleccionar campos y configuraci&oacute;n inicial.
4. Crear entradas paso 2. Ordenar campos, configurar html, asignar categor&iacute;as, etc.
5. Crear categor&iacute;s de forma masiva.
6. Crear etiquetas de forma masiva.


== Changelog ==

1.0 Versi&oacute;n inicial