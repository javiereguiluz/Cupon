# Cupon sample application #

Cupon is a sample application designed to learn Symfony2 development. It's a Groupon inspired clone, hence its name. Cupon application is explained in the upcoming book about Symfony2 written by Javier Eguiluz from [symfony.es](http://symfony.es).

If you find a bug, please fill in a bug report in the Github issues page.

## How to install ##

  1. `mkdir cupon`
  2. `git clone git@github.com:javiereguiluz/Cupon.git cupon`
  3. `cd cupon`
  4. `php bin/vendors install`
  5. `chmod -R 777 app/cache app/logs`
  5. Configure your web server

## How to use ##

Before trying the application:

  1. Create a new sample database and configure its credentials in `app/config/parameters.ini` file
  2. Create the schema: `php app/console doctrine:schema:create`
  3. Initialize the ACL tables: `php app/console init:acl`
  4. Load data fixtures: `php app/console doctrine:fixtures:load`
  5. Dump web assets with Assetic: `php app/console assetic:dump --env=prod --no-debug`

In case of error, don't forget to clear de cache:

  * Development environment: `php app/console cache:clear`
  * Production environment: `php app/console cache:clear --env=prod`

### Frontend ###

  * URL:
    * Development environment: `http://cupon/app_dev.php`
    * Production environment: `http://cupon/app.php`
  * User credentials:
    * Login: `usuarioN@localhost` being `N` an integer ranging from `1` to `500`
    * Password: `usuarioN` being `N` the number used in login

### Extranet ###

  * URL:
    * Development environment: `http://cupon/app_dev.php/extranet`
    * Production environment: `http://cupon/app.php/extranet`
  * User credentials:
    * Login: `tiendaN` being `N` an integer ranging from `1` to `80` approximately (the upper bound is randomly generated)
    * Password: same as login

### Backend ###

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
  5. `chmod -R 777 app/cache app/logs`
  5. Configura bien tu servidor web

## Uso ##

Para poder probar bien la aplicación:

  1. Crea una nueva base de datos de prueba y configura sus datos de acceso en el archivo de configuración `app/config/parameters.ini`
  2. Crea el esquema de la base de datos con el comando: `php app/console doctrine:schema:create`
  3. Crea las tablas de la ACL: `php app/console init:acl`
  4. Carga los datos de pruebas con el siguiente comando: `php app/console doctrine:fixtures:load`
  5. Genera los web assets con Assetic: `php app/console assetic:dump --env=prod --no-debug`

Si tienes algún problema, limpia la cache:

  * Entorno de desarrollo: `php app/console cache:clear`
  * Entorno de producción: `php app/console cache:clear --env=prod`

### Frontend ###

  * URL:
    * Entorno de desarrollo: `http://cupon/app_dev.php`
    * Entorno de producción: `http://cupon/app.php`
  * Credenciales de usuarios:
    * Nombre de usuario: `usuarioN@localhost` siendo `N` un número entre `1` y `500`
    * Contraseña: `usuarioN` siendo `N` el mismo valor que el del nombre de usuario

### Extranet ###

  * URL:
    * Entorno de desarrollo: `http://cupon/app_dev.php/extranet`
    * Entorno de producción: `http://cupon/app.php/extranet`
  * Credenciales de usuarios:
    * Nombre de usuario: `tiendaN` siendo `N` un número entre `1` y `80` aproximadamente (el límite superior es aleatorio)
    * Contraseña: la misma que el nombre de usuario

### Backend ###

  * URL:
    * Entorno de desarrollo: `http://cupon/app_dev.php/backend`
    * Entorno de producción: `http://cupon/app.php/backend`
  * Credenciales de usuarios:
    * Nombre de usuario: `admin`
    * Contraseña: `1234`

