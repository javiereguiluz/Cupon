Aplicación de prueba Cupon para Symfony 2.5.x
=============================================

**Cupon** es una aplicación de prueba desarrollada para aprender a programar con Symfony 2.5. Se trata de un clon simplificado de Groupon, de ahí el nombre. Esta aplicación es la base del libro **[Desarrollo web ágil con Symfony2](http://www.symfony.es/libro/)** publicado por Javier Eguiluz.

Si descubres algún error, por favor utiliza [la página de issues de Github](https://github.com/javiereguiluz/Cupon/issues) para avisarnos.

Pantallazos (pincha cada imagen para ampliarla)
-----------------------------------------------

### Frontend

[![Portada](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-frontend-portada.png)](http://javiereguiluz.com/cupon/screenshots/cupon-frontend-portada.png)
[![Página de detalle de la oferta](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-frontend-oferta.png)](http://javiereguiluz.com/cupon/screenshots/cupon-frontend-oferta.png)
[![Página de ofertas recientes en una ciudad](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-frontend-recientes.png)](http://javiereguiluz.com/cupon/screenshots/cupon-frontend-recientes.png)
[![Formulario de login](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-frontend-login.png)](http://javiereguiluz.com/cupon/screenshots/cupon-frontend-login.png)

### Extranet

[![Listado de ofertas](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-extranet-listado.png)](http://javiereguiluz.com/cupon/screenshots/cupon-extranet-listado.png)
[![Formulario para modificar oferta](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-extranet-modificar-oferta.png)](http://javiereguiluz.com/cupon/screenshots/cupon-extranet-modificar-oferta.png)

### Backend

[![Listing](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-backend-listado.png)](http://javiereguiluz.com/cupon/screenshots/cupon-backend-listado.png)
[![Página de detalle de la oferta](http://javiereguiluz.com/cupon/screenshots/thumb-cupon-backend-ver-oferta.png)](http://javiereguiluz.com/cupon/screenshots/cupon-backend-ver-oferta.png)

Instalando la aplicación
------------------------

En el libro [Desarrollo web ágil con Symfony2](http://www.symfony.es/libro/) se expica detalladamente cómo instalar bien Symfony2 y la aplicación Cupon. A continuación sólo se indican los principales pasos necesarios.

En **primer lugar** debes tener Composer instalador globalmente. Si utilizas Linux o Mac OS X, ejecuta los siguientes comandos:

```bash
$ curl -sS https://getcomposer.org/installer | php 
$ sudo mv composer.phar /usr/local/bin/composer
```

Si utilizas Windows, descárgate el [instalador ejecutable de Composer](https://getcomposer.org/download) y sigue los pasos indicados por el instalador.

Una vez instalado **Composer**, ejecuta los siguientes comandos para descargar e instalar la aplicación **Cupon**:

```bash
# clona el código de la aplicación
$ cd proyectos/
$ git clone git://github.com/javiereguiluz/Cupon.git

# instala las dependencias del proyecot (incluyendo Symfony)
$ cd Cupon/
$ composer install

# prepara los archivos CSS y JS de la aplicación
$ php app/console assetic:dump --env=prod
```

Probando la aplicación
----------------------

La forma más sencilla de probar la aplicación, ejecuta el siguiente comando, que arranca el servidor web interno de PHP y hace que tu aplicación se pueda ejecutar sin necesidad de usar Apache o Nginx:

```bash
$ php app/console server:run
Server running on http://localhost:8000
```

Ahora ya puedes abrir tu navegador y acceder a `http://localhost:8000` para probar la aplicación.

El comando anterior requiere PHP 5.4. Si utilizas una versión anterior de PHP, tendrás que configurar un *virtual host* en tu servidor web, tal y como se explica con detalle en el libro.

### Solución a los problemas comunes

Al empezar a programar con Symfony, es común no saber la causa exacta de algunos de los errores que se producen. En estos casos es útil borrar la caché de la aplicación ejecutando los siguientes comandos:

  * Entorno de desarrollo: `php app/console cache:clear`
  * Entorno de producción: `php app/console cache:clear --env=prod`

Si aún así siguen persistiendo los errores, al principio también suele ser útil borrar completamente los directorios dentro de `app/cache/` (por ejemplo con el comando `rm -fr app/cache/*`).

**1. Si solamente ves una página en blanco**, es posible que se trate de un problema de permisos. En el libro se explica detalladamente cómo solucionarlo, pero una solución rápida puede ser ejecutar el siguiente comando:

```bash
$ cd proyectos/Cupon/
$ chmod -R 777 app/cache app/logs
```

Si no te funciona esta solución, también puedes consultar el artículo [Cómo solucionar el problema de los permisos de Symfony2](http://symfony.es/documentacion/como-solucionar-el-problema-de-los-permisos-de-symfony2/).

**2. Si ves un error relacionado con la base de datos**, es posible que tu instalación de PHP no tenga instalada o activada la extensión para SQLite.

Para facilitar la instalación de la aplicación, SQLite se usa por defecto. Si prefieres usar una base de datos como MySQL, sigue estos pasos:

  1. Edita el archivo `app/config/parameters.yml` comentando todo lo relacionado con SQLite y descomentando todo lo relacionado con MySQL.
  2. Edita el archivo `app/config/config.yml` y en la sección `dbal`, comenta todo lo relacionado con SQLite y descomenta todo lo relacionado con MySQL.
  3. Ejecuta los siguientes comandos para crear la base de datos y rellenarla con datos de prueba:

```bash
$ php app/console doctrine:database:create
$ php app/console doctrine:schema:create
$ php app/console doctrine:fixtures:load

# si este último comando da error, ejecuta en su lugar:
$ php app/console doctrine:fixtures:load --append

# si estás desarrollando la aplicación desde cero, ejecuta lo siguiente
# para cargar los datos de prueba simplificados que no utilizan la seguridad
$ php app/console doctrine:fixtures:load --fixtures=app/Resources
```

**3. Si ves la aplicación sin estilos CSS**

Asegúrate de ejecutar el siguiente comando para que Symfony genere los archivos CSS y JS de la aplicación:

```bash
$ php app/console assetic:dump --env=prod
```

**4. Si no puedes subir imágenes al crear una oferta**

Asegúrate de que el directorio `web/uploads/images/` tiene permisos de escritura.

Test unitarios y funcionales
----------------------------

La aplicación incluye varios test unitarios y funcionales de ejemplo. Para
ejecutarlos debes tener la herramienta [PHPUnit](https://github.com/sebastianbergmann/phpunit/) instalada. Después, ejecuta el siguiente comando en el directorio raíz del proyecto:

```bash
$ phpunit -c app
```

Frontend
--------

  * URL:
    * Entorno de desarrollo: `http://cupon/app_dev.php`
    * Entorno de producción: `http://cupon/app.php`
  * Credenciales de usuarios:
    * Nombre de usuario: `usuarioN@localhost` siendo `N` un número entre `1` y `200`
    * Contraseña: `usuarioN` siendo `N` el mismo valor que el del nombre de usuario

Extranet
--------

  * URL:
    * Entorno de desarrollo: `http://cupon/app_dev.php/extranet`
    * Entorno de producción: `http://cupon/app.php/extranet`
  * Credenciales de usuarios:
    * Nombre de usuario: `tiendaN` siendo `N` un número entre `1` y `80` aproximadamente
    (el límite superior es aleatorio)
    * Contraseña: la misma que el nombre de usuario

Backend
-------

  * URL:
    * Entorno de desarrollo: `http://cupon/app_dev.php/backend`
    * Entorno de producción: `http://cupon/app.php/backend`
  * Credenciales de usuarios:
    * Nombre de usuario: `admin`
    * Contraseña: `1234`
