# Star Wars Explorer

Aplicación full-stack desarrollada como prueba técnica para consultar películas y naves de Star Wars, revisar sus datos principales y administrar una colección local mediante una API REST.

El frontend consume exclusivamente la API de Laravel. Laravel actúa como intermediario ante SWAPI, normaliza sus respuestas y mantiene separado el proveedor externo de la interfaz. Las naves guardadas se almacenan en una base de datos propia.

## Funcionalidades

- Consulta de películas disponibles en SWAPI.
- Búsqueda de películas por título o director.
- Listado de las naves asociadas con una película.
- Consulta de datos principales de una nave.
- Formulario editable con validaciones en frontend y backend.
- Creación, consulta, actualización y eliminación de naves guardadas.
- Detección de registros existentes mediante el identificador de SWAPI.
- Prevención de registros duplicados.
- Caché temporal de las respuestas externas.
- Estados de carga, error y contenido vacío.
- Diálogo propio para confirmar eliminaciones.
- Interfaz responsive basada en el diseño de la prueba.

## Tecnologías

### Backend

- PHP 8.3
- Laravel 13
- Eloquent ORM
- Laravel HTTP Client
- PHPUnit
- Laravel Pint
- MySQL 8
- Amazon RDS para la base de datos de producción

### Frontend

- Vue 3
- Vue Router
- Axios
- Tailwind CSS 4
- Vite
- Prettier

### Servicios externos

- [SWAPI.info](https://swapi.info/) como proveedor de información de Star Wars.

### Documentación

- OpenAPI 3.0
- L5-Swagger
- Swagger UI

## Arquitectura

```text
Vue
├── Views
├── Components
└── API services (Axios)
        │
        ▼
Laravel API
├── SWAPI Controller → SWAPI Service → Cache → SWAPI
└── Starship Controller → Form Requests → Eloquent → Database
```

Vue no consulta SWAPI directamente. El backend ofrece un contrato estable, selecciona los campos utilizados por la aplicación y centraliza los tiempos de espera, reintentos, manejo de errores y caché.

## Requisitos

- PHP 8.3 o superior.
- Composer 2.
- Node.js 22.18 o superior recomendado.
- npm 10 o superior.
- MySQL 8.

También deben estar habilitadas las extensiones de PHP requeridas por Laravel y el controlador `pdo_mysql`.

## Instalación

Clona el repositorio:

```bash
git clone https://github.com/alfonsonadamas/star-wars-fullstack-challenge.git
cd star-wars-fullstack-challenge
```

Instala las dependencias:

```bash
composer install
npm install
```

Crea el archivo de entorno y genera la clave:

### Windows PowerShell

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

### macOS o Linux

```bash
cp .env.example .env
php artisan key:generate
```

## Base de datos

Configura `.env` sin compartir las credenciales:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=star_wars
DB_USERNAME=usuario
DB_PASSWORD=contraseña
```

La aplicación utiliza una instancia MySQL alojada en Amazon RDS para producción. Las credenciales no se incluyen en el repositorio ni se comparten en archivos públicos. Si se requiere acceso a la base de datos de producción, deben solicitarse directamente al responsable del proyecto.

Aplica las migraciones:

```bash
php artisan migrate
```

Las migraciones crean las tablas utilizadas por sesiones, caché, colas y naves guardadas.

## Configuración de SWAPI

La integración funciona con los valores incluidos en `.env.example`:

```dotenv
SWAPI_BASE_URL=https://swapi.info/api
SWAPI_CACHE_TTL=3600
```

`SWAPI_CACHE_TTL` representa la duración de la caché en segundos. Si una entrada existe y sigue vigente, Laravel responde desde la caché. Cuando no existe o expiró, consulta SWAPI y guarda el nuevo resultado.

Para limpiar manualmente la caché:

```bash
php artisan cache:clear
```

## Ejecución en desarrollo

La opción sencilla utiliza dos terminales.

Terminal del backend:

```bash
php artisan serve
```

Terminal del frontend:

```bash
npm run dev
```

Después abre:

```text
http://127.0.0.1:8000/movies
```

Laravel también incluye un comando que levanta servidor, Vite, cola y logs:

```bash
composer run dev
```

## Rutas de la interfaz

| Ruta                       | Descripción                       |
| -------------------------- | --------------------------------- |
| `/movies`                  | Catálogo de películas             |
| `/movies/{film}/starships` | Naves asociadas con una película  |
| `/starships`               | Orientación para elegir película  |
| `/starships/{starship}`    | Detalle y formulario de una nave  |
| `/saved-starships`         | Administración de naves guardadas |

## API

Todas las respuestas se entregan en JSON.

### Swagger

La documentación interactiva de los endpoints está disponible en:

```text
http://127.0.0.1:8000/api/documentation
```

El documento OpenAPI en formato JSON está disponible en:

```text
http://127.0.0.1:8000/docs
```

Para regenerar la especificación después de modificar rutas, esquemas o contratos:

```bash
php artisan l5-swagger:generate
```

En producción se recomienda generar el documento durante el despliegue y mantener:

```dotenv
L5_SWAGGER_GENERATE_ALWAYS=false
```

De esta manera, Swagger UI sirve un documento previamente generado y no escanea el código en cada petición.

### Consulta de SWAPI

| Método | Endpoint                            | Descripción                     |
| ------ | ----------------------------------- | ------------------------------- |
| `GET`  | `/api/swapi/films`                  | Lista de películas normalizadas |
| `GET`  | `/api/swapi/films/{film}/starships` | Película y sus naves            |
| `GET`  | `/api/swapi/starships/{starship}`   | Datos principales de una nave   |

Si SWAPI no responde, Laravel devuelve `502 Bad Gateway` con un mensaje controlado.

### Naves guardadas

| Método   | Endpoint              | Descripción             |
| -------- | --------------------- | ----------------------- |
| `GET`    | `/api/starships`      | Listado paginado        |
| `POST`   | `/api/starships`      | Crear un registro       |
| `GET`    | `/api/starships/{id}` | Consultar por ID local  |
| `PATCH`  | `/api/starships/{id}` | Actualizar parcialmente |
| `PUT`    | `/api/starships/{id}` | Actualizar un registro  |
| `DELETE` | `/api/starships/{id}` | Eliminar un registro    |

Es posible buscar si una nave externa ya fue guardada:

```http
GET /api/starships?swapi_id=10
```

Ejemplo de creación:

```json
{
    "swapi_id": 10,
    "name": "Millennium Falcon",
    "max_atmosphering_speed": 1050,
    "cargo_capacity": 100000
}
```

Los límites aplicados son:

- Nombre: máximo 80 caracteres.
- Velocidad: entero entre 0 y 999999.
- Capacidad de carga: entero entre 0 y 999999999999999.
- `swapi_id`: único cuando está presente.

## Pruebas

Ejecuta todas las pruebas:

```bash
php artisan test
```

Los tests utilizan SQLite en memoria. La integración externa se simula con `Http::fake()`, por lo que no consume SWAPI ni modifica la base de datos configurada en `.env`.

Las pruebas cubren:

- Normalización de películas y naves.
- Creación, listado y consulta por ID.
- Filtrado por identificador de SWAPI.
- Actualización y eliminación.
- Campos obligatorios y límites.
- Prevención de duplicados.
- Disponibilidad de Swagger UI y presencia de todas las operaciones OpenAPI.

## Calidad y compilación

Formato del backend:

```bash
php vendor/bin/pint
```

Formato del frontend:

```bash
npx prettier --write resources/js
```

Compilación para producción:

```bash
npm run build
php artisan l5-swagger:generate
```

Antes de crear un commit se recomienda ejecutar:

```bash
php artisan test
npm run build
```

## Decisiones técnicas

- **Backend intermediario:** desacopla Vue de SWAPI y centraliza errores y caché.
- **Controladores pequeños:** la integración externa vive en `SwapiService`.
- **Form Requests:** la validación no depende únicamente del navegador.
- **API Resources:** mantienen estable la estructura JSON del CRUD.
- **Identificador externo separado:** `swapi_id` relaciona el recurso externo sin reemplazar el ID local.
- **Índice único:** evita guardar varias veces la misma nave de SWAPI.
- **Sin autenticación:** la prueba administra una colección global y no define usuarios propietarios.
- **Estado local sencillo:** no se añadió Pinia porque el alcance no requiere estado global complejo.

## Seguridad

- `.env` está excluido de Git.
- No deben publicarse credenciales de base de datos.
- Las credenciales de Amazon RDS deben solicitarse directamente al responsable del proyecto mediante un canal privado.
- En producción, `APP_DEBUG` debe configurarse como `false`.
- Una base de datos remota debe aceptar conexiones únicamente desde orígenes autorizados.
- No se recomienda exponer MySQL públicamente a `0.0.0.0/0`.

## Estado del despliegue

La infraestructura de producción se despliega en Amazon Web Services:

```text
Internet
   │
   ▼
Amazon EC2
Laravel + Vue + servidor web
   │
   ▼
Amazon RDS
MySQL
```

- **Amazon EC2:** ejecuta la aplicación Laravel, sirve el frontend compilado de Vue y expone la API.
- **Amazon RDS:** aloja la base de datos MySQL utilizada por el CRUD de naves guardadas.
- **Security Groups:** restringen la conexión de RDS para que sólo los orígenes autorizados puedan utilizar el puerto de MySQL.
- **Credenciales:** se mantienen fuera del repositorio y deben solicitarse directamente al responsable del proyecto cuando sea necesario trabajar con producción.

La URL pública se añadirá después de completar la configuración final de EC2:

```text
Producción: pendiente
```

## Repositorio

[github.com/alfonsonadamas/star-wars-fullstack-challenge](https://github.com/alfonsonadamas/star-wars-fullstack-challenge)
