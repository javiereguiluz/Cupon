# Aplicación de prueba Cupon para Symfony 2.0.x #

Cupon es una aplicación de prueba desarrollada para aprender a programar con 
Symfony2. Se trata de un clon simplificado de Groupon, de ahí el nombre. Esta 
aplicación es la base del libro **[Desarrollo web ágil con Symfony2](http://www.symfony.es/libro-symfony2/)** publicado por Javier Eguiluz.

Si descubres algún error, por favor utiliza [la página de issues de Github](https://github.com/javiereguiluz/Cupon/issues) para avisarnos.

## Pantallazos (pincha cada imagen para ampliarla) ##

### Frontend ###

[![Portada](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-frontend-portada.png)](http://javiereguiluz.com/cupon/screenshots/cupon-frontend-portada.png)
[![Página de detalle de la oferta](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-frontend-oferta.png)](http://javiereguiluz.com/cupon/screenshots/cupon-frontend-oferta.png)
[![Página de ofertas recientes en una ciudad](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-frontend-recientes.png)](http://javiereguiluz.com/cupon/screenshots/cupon-frontend-recientes.png)
[![Formulario de login](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-frontend-login.png)](http://javiereguiluz.com/cupon/screenshots/cupon-frontend-login.png)

### Extranet ###

[![Listado de ofertas](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-extranet-listado.png)](http://javiereguiluz.com/cupon/screenshots/cupon-extranet-listado.png)
[![Formulario para modificar oferta](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-extranet-modificar-oferta.png)](http://javiereguiluz.com/cupon/screenshots/cupon-extranet-modificar-oferta.png)

### Backend ###

[![Listing](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-backend-listado.png)](http://javiereguiluz.com/cupon/screenshots/cupon-backend-listado.png)
[![Página de detalle de la oferta](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-backend-ver-oferta.png)](http://javiereguiluz.com/cupon/screenshots/cupon-backend-ver-oferta.png)

## Instalación ##

  1. `mkdir cupon`
  2. `git clone git://github.com/javiereguiluz/Cupon.git cupon`
  3. `cd cupon`
  4. `php bin/vendors install`
  5. `chmod -R 777 app/cache app/logs` (lee la sección [Setting up Permissions](http://symfony.com/doc/2.0/book/installation.html#configuration-and-setup) para hacer esto de forma más elegante)
  6. Configura bien tu servidor web
  7. Asegúrate de que tienes APC instalado y configurado (se usa en el entorno de producción)

## Uso ##

Para poder probar bien la aplicación:

  1. Crea una nueva base de datos de prueba y configura sus datos de acceso en el
     archivo de configuración `app/config/parameters.ini`
  2. Crea el esquema de la base de datos con el comando: `php app/console doctrine:schema:create`
  3. Crea las tablas de la ACL: `php app/console init:acl`
  4. Carga los datos de pruebas con los siguientes comandos:
      * `php app/console doctrine:fixtures:load` para cargar todos los datos de
      prueba de la aplicación terminada (incluye todas las propiedades relacionadas
      con la ACL y la seguridad). Si se muestra una excepción de tipo *Truncating table with foreign keys fails* , ejecuta el siguiente comando: `php app/console doctrine:fixtures:load --append`
      * `php app/console doctrine:fixtures:load --fixtures=app/Resources` para
      cargar una versión simplificada de los datos de prueba. Utiliza estos datos
      si estás creando la aplicación a mano y todavía no has llegado al capítulo
      relacionado con la seguridad y la ACL.
  5. Genera los web assets con Assetic: `php app/console assetic:dump --env=prod --no-debug`
  6. Asegúrate de que el directorio `web/uploads/images/` tiene permisos de escritura.

Si tienes algún problema, limpia la cache:

  * Entorno de desarrollo: `php app/console cache:clear`
  * Entorno de producción: `php app/console cache:clear --env=prod`

## Test unitarios y funcionales ##

La aplicación incluye varios test unitarios y funcionales de ejemplo. Para
ejecutarlos debes tener la herramienta [PHPUnit](https://github.com/sebastianbergmann/phpunit/) instalada. Después, ejecuta el siguiente comando en el directorio raíz del proyecto:

~~~
$ phpunit -c app
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
    * Nombre de usuario: `tiendaN` siendo `N` un número entre `1` y `80` aproximadamente
    (el límite superior es aleatorio)
    * Contraseña: la misma que el nombre de usuario

## Backend ##

  * URL:
    * Entorno de desarrollo: `http://cupon/app_dev.php/backend`
    * Entorno de producción: `http://cupon/app.php/backend`
  * Credenciales de usuarios:
    * Nombre de usuario: `admin`
    * Contraseña: `1234`

