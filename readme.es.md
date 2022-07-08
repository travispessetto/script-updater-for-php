[English](readme.md)

# Actualizador de Scripts para PHP

Actualizador de Scripts para PHP es un script de actualización que se puede usar con cualquier proyecto de PHP pata actualizar los archivos con un par de clics.
El script solamente requiere que puede leer un archivo que contiene la versión actual, sistema de archivos locales, y ubicación de archivos remotos. Se puede
usar Amazon S3 si uno quiere.

## Capturas de Pantalla

TODO

## Configurar el Script de actualización

Para usar este script de actualización se necesita crear un archivo que contiene los pasos para la actualización. Este archivo tiene que contener `version` y
`files`. Debajo `files` hay dos cosas adicionales, `add` y `delete`. La versión (`version`) tiene que ser un conjunto de números enteros separados con un punto (.)
donde el número más importante está más a la izquierda.

Los dos `add` (agregar) y `delete` (borrar) son secuenciás. Se asignan a todas los artículos un `local` (local) y un `remote` (remoto). `local` es donde quieres que vaya el archivo descargado y `remote` es donde se obtiene del servidor de actualización.

La sección de `script` define dos secciones `do` (hacer) y `undo` (deshacer). La sección do define lo que se ejecutará cuando se hayan añadido, actualizado o eliminado todos los archivos. Cada script en la sección do debe definir si debe ser borrado después de la ejecución, estableciendo `delete` como `true` (verdad) o `false` (falso). Vea el ejemplo de abajo si está confundido. En la sección de `undo` (deshacer) tiene scripts que necesitarán ser ejecutados si alguien elige revertir a una copia de seguridad. Esta sección requiere las secciones de `script`, `remote` (remoto) y `delete` (borrar). Estos scripts se añadirán al archivo de copia de seguridad y se recuperarán desde el remoto. La sección de `delete` (borrar) especifica si se borrará después de restaurar la copia de seguridad.

Si quieres que tus usuarios puedan hacer clic en un botón cuando terminen, puedes establecer `finishUrl` en el archivo YAML a una URL relativa. Cuando se haga clic en el botón irán a lo que se haya establecido. Si no existe, recibirán un mensaje de que no se ha encontrado un botón de finalización.

A continuación se muestra un ejemplo de archivo de actualización YAML:

```
version:    1.1.17
files:
        add:
            - {local: "foo/bar/writable/foobar.txt", remote: "files/foobar.txt"}
            - {local: "scripts/writefile.php", remote: "files/writefile.php.txt"}
        delete:
            - "foo/foo.txt"
scripts:
        do:
            - {script: "scripts/writefile.php", delete: true}
        undo:
            - {script: "scripts/deletefile.php", remote: "scripts/deletefile.php", delete: true}
finishUrl: "f