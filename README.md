# Cupon sample application #

Cupon is a sample application designed to learn Symfony2 development. It's a Groupon inspired clone, hence its name. Cupon application is explained in the upcoming book about Symfony2 written by Javier Eguiluz from [symfony.es](http://symfony.es).

If you find a bug, please fill in a bug report in the Github issues page.

## How to install ##

  1. `mkdir cupon`
  2. `git clone git@github.com:javiereguiluz/Cupon.git cupon`
  3. `cd cupon`
  4. `php bin/vendors install`
  5. `chmod -R 777 app/cache app/logs` (read [Setting up Permissions](http://symfony.com/doc/2.0/book/installation.html#configuration-and-setup) for a more elegant way to do this)
  6. Configure your web server
  7. Ensure that APC is installed and configured (it's used on the production environment)

## How to use ##

Before trying the application:

  1. Create a new sample database and configure its credentials in `app/config/parameters.ini` file
  2. Create the schema: `php app/console doctrine:schema:create`
  3. Initialize the ACL tables: `php app/console init:acl`
  4. Load data fixtures: `php app/console doctrine:fixtures:load` (if you get *Truncating table with foreign keys fails* exception, execute the following command: `php app/console doctrine:fixtures:load --append`)
  5. Dump web assets with Assetic: `php app/console assetic:dump --env=prod --no-debug`
  6. Ensure that `web/uploads/images/` directory has write permissions.

In case of error, don't forget to clear de cache:

  * Development environment: `php app/console cache:clear`
  * Production environment: `php app/console cache:clear --env=prod`

## How to test ##

Cupon application includes several unit and functional tests. In order to run the tests, you must have [PHPUnit](https://github.com/sebastianbergmann/phpunit/) installed on your machine. Then, execute the following command on the project's root directory:

~~~
$ phpunit -c app
~~~

If you don't want to run the full test suite, include an specific directory as argument:

~~~
$ phpunit -c app src/Cupon/OfertaBundle/
~~~

## Frontend ##

  * URL:
    * Development environment: `http://cupon/app_dev.php`
    * Production environment: `http://cupon/app.php`
  * User credentials:
    * Login: `usuarioN@localhost` being `N` an integer ranging from `1` to `500`
    * Password: `usuarioN` being `N` the number used in login

## Extranet ##

  * URL:
    * Development environment: `http://cupon/app_dev.php/extranet`
    * Production environment: `http://cupon/app.php/extranet`
  * User credentials:
    * Login: `tiendaN` being `N` an integer ranging from `1` to `80` approximately (the upper bound is randomly generated)
    * Password: same as login

## Backend ##

  * URL:
    * Development environment: `http://cupon/app_dev.php/backend`
    * Production environment: `http://cupon/app.php/backend`
  * User credentials:
    * Login: `admin`
    * Password: `1234`

# Aplicación de prueba Cupon #

Cupon es una aplicación de prueba desarrollada para aprender a programar con Symfony2. Se trata de un clon simplificado de Groupon, de ahí el nombre. Esta aplicación es la base del próximo libro sobre Symfony2 que publicará Javier Eguiluz.

Si descubres algún error, por favor utiliza la página de issues de Github para avisarnos.

## Instalación ##

  1. `mkdir cupon`
  2. `git clone git@github.com:javiereguiluz/Cupon.git cupon`
  3. `cd cupon`
  4. `php bin/vendors install`
  5. `chmod -R 777 app/cache app/logs` (lee la sección [Setting up Permissions](http://symfony.com/doc/2.0/book/installation.html#configuration-and-setup) para hacer esto de forma más elegante)
  6. Configura bien tu servidor web
  7. Asegúrate de que tienes APC instalado y configurado (se usa en el entorno de producción)

## Uso ##

Para poder probar bien la aplicación:

  1. Crea una nueva base de datos de prueba y configura sus datos de acceso en el archivo de configuración `app/config/parameters.ini`
  2. Crea el esquema de la base de datos con el comando: `php app/console doctrine:schema:create`
  3. Crea las tablas de la ACL: `php app/console init:acl`
  4. Carga los datos de pruebas con el siguiente comando: `php app/console doctrine:fixtures:load` (si se muestra una excepción de tipo *Truncating table with foreign keys fails* , ejecuta el siguiente comando: `php app/console doctrine:fixtures:load --append`)
  5. Genera los web assets con Assetic: `php app/console assetic:dump --env=prod --no-debug`
  6. Asegúrate de que el directorio `web/uploads/images/` tiene permisos de escritura.

Si tienes algún problema, limpia la cache:

  * Entorno de desarrollo: `php app/console cache:clear`
  * Entorno de producción: `php app/console cache:clear --env=prod`

## Test unitarios y funcionales ##

La aplicación incluye varios test unitarios y funcionales de ejemplo. Para ejecutarlos debes tener la herramienta [PHPUnit](https://github.com/sebastianbergmann/phpunit/) instalada. Después, ejecuta el siguiente comando en el directorio raíz del proyecto:

~~~
$ phpunit -c app
~~~

Si no quieres ejecutar todos los test, puedes indicar como argumento la ruta de un directorio para ejecutar solamente los test que se encuentren en esa ruta:

~~~
$ phpunit -c app src/Cupon/OfertaBundle/
~~~

## Frontend ##

  * URL:
    * Entorno de desarrollo: `http://cupon/app_dev.php`
    * Entorno de producción: `http://cupon/app.php`
  * Credenciales de usuarios:
    * Nombre de usuario: `usuarioN@localhost` siendo `N` un número entre `1` y `500`
    * Contraseña: `usuarioN` siendo `N` el mismo valor que el del nombre de usuario

## Extranet ##

  * URL:
    * Entorno de desarrollo: `http://cupon/app_dev.php/extranet`
    * Entorno de producción: `http://cupon/app.php/extranet`
  * Credenciales de usuarios:
    * Nombre de usuario: `tiendaN` siendo `N` un número entre `1` y `80` aproximadamente (el límite superior es aleatorio)
    * Contraseña: la misma que el nombre de usuario

## Backend ##

  * URL:
    * Entorno de desarrollo: `http://cupon/app_dev.php/backend`
    * Entorno de producción: `http://cupon/app.php/backend`
  * Credenciales de usuarios:
    * Nombre de usuario: `admin`
    * Contraseña: `1234`

