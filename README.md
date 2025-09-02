# Cart Backend (Symfony 7.3)

Backend modular con **Symfony 7.3** que aplica **Arquitectura Hexagonal**, **DDD** y **CQRS**. El proyecto se compone de tres bundles:

- **ProductBundle**: gestión de productos (crear/actualizar/eliminar), lectura optimizada desde Redis.
- **CartBundle**: carrito de compras (agregar/actualizar/eliminar ítems, ver carrito y checkout → pedido).
- **UserBundle**: registro, login y actualización de usuarios, y refresh de token de acceso (Se implementa seguridad a los endpoints correspondientes de ProductBundle y CartBundle).

Esta guía explica cómo levantar el proyecto y correr tests.  

- **Update de 2025-08-13**: Se que no cuenta para la prueba, sin embargo me pareció adecuado hacerlo. Agregué eventos en el carrito para que se sincronice con Elastic Search, y posteriormente pueda ser consumido por el área de marketing a través de Kibana (por ejemplo).
- **Update de 2025-08-23**: Se implementó seguridad a los endpoints correspondientes de ProductBundle y CartBundle.
---

## Tabla de contenidos

- [Stack](#stack)
- [Arquitectura](#arquitectura)
- [Contenedores Docker](#contenedores-docker)
- [Documentación de API](#documentación-de-api)
- [Puesta en marcha](#puesta-en-marcha)
- [Tests](#tests)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Generar carrito de compras desde endpoints](#generar-carrito-de-compras-desde-endpoints)

---

## Stack

- **PHP 8.2**, **Symfony 7.3**
- **MySQL** (persistencia principal)
- **Redis** (lectura de catálogo)
- **Swagger/OpenAPI** (documentación de endpoints con `zircote/swagger-php`)
- Tests: **PHPUnit**, **SQLite** para `APP_ENV=test`

> Nota: No se usa API Platform (se descartó por temas de versión).  
> Para las coberturas de tests, debes tener configurardo el Xdebug.

---

## Arquitectura

- **Hexagonal y DDD**
    - Los bundles se encuentran en `symfony/siroko-bundles/`.
    - Los dominios se encuentran en `BundleName/Domain/`.
    - Los repositorios se encuentran en `BundleName/Infrastructure/`.
    - Los servicios se encuentran en `BundleName/Application/`.
    - Los eventos se encuentran en `BundleName/Domain/Event/`.

- **CQRS**
    - *Write model*: casos de uso que escriben en MySQL y disparan **eventos de dominio**.
    - *Read model (productos)*: sincronizado en Redis para responder catálogos/consultas rápidas.
- **Eventos entre bundles**
    - Cuando **ProductBundle** actualiza un producto, **CartBundle** escucha el evento y **actualiza el precio** en carritos `open` donde esté ese producto.
- **Checkout**
    - Convierte un carrito en **pedido**, marca el carrito como `checked_out`  y **ajusta stock**.

---

## Contenedores Docker

- **PHP**
- **Nginx**
- **MySQL**
- **Redis**
- **Elasticsearch**

---

## Documentación de API

- **Swagger UI**: `http://host:port/swagger.html` (una vez levantado el proyecto).
- Se incluye una **colección de Postman** en el repositorio para probar los endpoints.
- Una vez levantado el proyecto, se puede acceder al enlace `http://host:port/coverage/index.html` para ver la cobertura de tests.

---

## Puesta en marcha

### 1) Requisitos

- PHP 8.2, Composer
- Docker
- Xdebug 

### 2) Clonar e instalar

```bash
git clone git@github.com:arneon/symfony-cart.git
cd symfony-cart
./deploy-dev.sh
```
---

## Tests

Los tests se ejecutan al momento de hacer el despliegue, se puede verificar la cobertura de tests en `coverage/index.html`.

También puede ejecutarlos desde la consola, en la raiz del proyecto (directorio symfony):
```bash
php bin/phpunit --testdox --colors
```
---

## Estructura del proyecto

```
symfony/
  ├─ config/
  ├─ public/
  ├─ src/
  ├─ siroko-bundles/
  │   ├─ ProductBundle/
  │   └─ CartBundle/
  │   └─ UserBundle/
  ├─ var/
  └─ vendor/
```

---

## Generar carrito de compras desde endpoints
- Crear productos -> /api/products/
- Agregar productos al carrito -> /api/carts/
- Ver carrito -> /api/carts/{cartCode}
- Checkout -> /api/carts/checkout/ 
