# SmartCampus Spaces

Sistema de Gestión y Reserva de Espacios Académicos y Tecnológicos.

**Actividad Formativa 2** — Configuración Inicial e Implementación del Esquema de BD con Migraciones en Laravel
**Actividad Complementaria 3** — Enrutamiento, Controladores y Vistas Estáticas/Dinámicas del Dominio del Problema ([ver sección 8](#8-actividad-3--enrutamiento-controladores-y-vistas))
Corporación Universitaria Antonio José de Sucre (UAJS) · Facultad de Ciencias de Ingeniería
Materia: Electiva Profesional II · Profesor: Guillermo Antonio González Márquez

**Integrantes:** Arnovis David Osorio Castillo · Diego Alfredo Pérez Corpas · Camilo Andrés Ricardo Hoyos · Simón Antonio Santana Santana

---

## 1. Stack y versiones

| Componente | Versión |
|---|---|
| Laravel | 12.x (starter kit Livewire + Volt + Flux) |
| PHP | 8.2+ |
| Motor de BD | MySQL 8 / MariaDB (XAMPP) |
| ORM | Eloquent + Schema Builder (migraciones) |

## 2. Instalación y puesta en marcha

```bash
git clone https://github.com/Carh-rgb/smartcampus-spaces.git
cd smartcampus-spaces
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run build
```

Crear la base de datos vacía y configurar la conexión en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartcampus_spaces
DB_USERNAME=root
DB_PASSWORD=
```

```sql
CREATE DATABASE smartcampus_spaces CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

> **Si el puerto 3306 está ocupado** (por ejemplo por una instalación aparte de MySQL Server), se puede levantar el MariaDB de XAMPP en otro puerto: cambiar `port=3306` por `port=3307` en `C:\xampp\mysql\bin\my.ini`, usar `DB_CONNECTION=mariadb` y `DB_PORT=3307` en el `.env`, y agregar `$cfg['Servers'][$i]['port'] = '3307';` en `C:\xampp\phpMyAdmin\config.inc.php` para que phpMyAdmin apunte al mismo motor.

Ejecutar el esquema completo con datos de prueba:

```bash
php artisan migrate:fresh --seed
```

Levantar el proyecto:

```bash
php artisan serve
```

### Usuarios creados por el seeder

| Rol | Correo | Contraseña |
|---|---|---|
| admin | admin@example.com | password |
| teacher | docente@example.com | password |
| student | estudiante@example.com | password |

## 3. Esquema implementado

```
users (1) ──< reservations >── (1) spaces
users (1) ──< incidents
reservations (1) ──< incidents
spaces (N) ──< resource_space >── (M) resources
```

### Tablas

| Tabla | Descripción | Migración |
|---|---|---|
| `users` | Estudiantes, docentes y administradores. Se le agregan `role` e `is_active`. | `2026_09_17_000100_add_role_to_users_table.php` |
| `spaces` | Aulas, laboratorios, auditorios y salas de reunión. | `2026_09_17_000200_create_spaces_table.php` |
| `resources` | Catálogo de equipamiento tecnológico del campus. | `2026_09_17_000300_create_resources_table.php` |
| `resource_space` | Pivote N:M espacio–recurso, con la columna `quantity`. | `2026_09_17_000400_create_resource_space_table.php` |
| `reservations` | Solicitudes de uso de un espacio con su estado de aprobación. | `2026_09_17_000500_create_reservations_table.php` |
| `incidents` | Fallas o novedades reportadas sobre una reserva. | `2026_09_17_000600_create_incidents_table.php` |

### Restricciones de integridad

| Restricción | Dónde | Comportamiento |
|---|---|---|
| `resource_id`, `space_id` | `resource_space` | `constrained()` + `onDelete('cascade')`: al eliminar un espacio o un recurso desaparece su asignación. |
| `unique(resource_id, space_id)` | `resource_space` | Un recurso no se puede repetir dentro del mismo espacio. |
| `user_id`, `space_id` | `reservations` | `onDelete('cascade')`: las reservas dependen del solicitante y del espacio. |
| `approved_by` | `reservations` | `nullOnDelete()`: si se elimina el administrador, la reserva conserva su historial. |
| `reservation_id`, `user_id` | `incidents` | `onDelete('cascade')`: el incidente no existe sin su reserva. |
| `assigned_to` | `incidents` | `nullOnDelete()`: el incidente sobrevive al técnico asignado. |
| Índices | varias | `spaces(status, building)`, `reservations(space_id, date, start_time)`, `reservations(status, date)`, `incidents(status, priority)`, `users(role)`, `resources(status)`. |
| Valores por defecto | varias | `status` de espacios/recursos/reservas/incidentes, `quantity = 1`, `role = student`, `is_active = true`, `reported_at = CURRENT_TIMESTAMP`. |
| Campos nulos | varias | `description`, `review_notes`, `solution`, `resolved_at`, `approved_by`, `assigned_to`. |
| Borrado lógico | `spaces`, `resources`, `reservations`, `incidents` | `softDeletes()` para no perder el histórico de uso del campus. |

> **Nota sobre solapamiento de reservas:** no se define un `unique` sobre `(space_id, date, start_time)` porque una reserva *cancelada* o *rechazada* bloquearía la franja para siempre. La validación de cruce de horarios se resuelve en la capa de aplicación, apoyada en el índice compuesto creado para esa consulta.

## 4. Mapeo con el MER de la Actividad 1

Las convenciones exigidas por la *Guía Rápida de Convenciones de Nombres en Laravel* (tablas en **inglés, plural, snake_case**; PK `id`; FK `modelo_id`; pivote con los dos modelos en singular y **orden alfabético**) obligan a traducir los nombres del MER original:

| Actividad 1 (MER) | Implementación Laravel | Modelo Eloquent |
|---|---|---|
| `usuarios` (`id_usuario`, `nombre`, `correo_inst`, `password`, `rol`) | `users` (`id`, `name`, `email`, `password`, `role`) | `User` |
| `espacios` (`id_espacio`, `nombre_espacio`, `capacidad`, `ubicacion_bloque`, `estado`) | `spaces` (`id`, `name`, `capacity`, `building`, `status`) | `Space` |
| `recursos` (`id_recurso`, `nombre_recurso`, `estado_recurso`) | `resources` (`id`, `name`, `status`) | `Resource` |
| `espacio_recursos` (`id_espacio`, `id_recurso`, `cantidad`) | `resource_space` (`resource_id`, `space_id`, `quantity`) | pivote (`belongsToMany`) |
| `reservas` (`id_reserva`, `id_usuario`, `id_espacio`, `fecha`, `hora_inicio`, `hora_fin`, `estado_reserva`) | `reservations` (`id`, `user_id`, `space_id`, `date`, `start_time`, `end_time`, `status`) | `Reservation` |
| `incidentes` (`id_incidente`, `id_reserva`, `id_usuario`, `descripcion`, `fecha_reporte`) | `incidents` (`id`, `reservation_id`, `user_id`, `description`, `reported_at`) | `Incident` |

Campos añadidos frente al MER original (justificados por el alcance funcional de la Actividad 1): `spaces.type`, `reservations.purpose` ("razón de uso"), `reservations.approved_by` / `reviewed_at` / `review_notes` (el administrador aprueba o rechaza), `incidents.priority` / `assigned_to` / `solution` / `resolved_at` (el administrador "gestiona y asigna la solución").

## 5. Relaciones Eloquent

| Modelo | Relación | Método |
|---|---|---|
| `User` | 1:N reservas | `reservations()` |
| `User` | 1:N incidentes reportados | `incidents()` |
| `Space` | 1:N reservas | `reservations()` |
| `Space` | N:M recursos (con `quantity`) | `resources()` |
| `Resource` | N:M espacios | `spaces()` |
| `Reservation` | N:1 usuario / espacio / administrador | `user()`, `space()`, `approver()` |
| `Reservation` | 1:N incidentes | `incidents()` |
| `Incident` | N:1 reserva / reportante / asignado | `reservation()`, `user()`, `assignee()` |

## 6. Seeders y factories

`DatabaseSeeder` orquesta los seeders en orden de dependencia:

1. `UserSeeder` — administrador por defecto, docente y estudiante de prueba + 11 usuarios con factory.
2. `SpaceSeeder` — 6 espacios reales del campus.
3. `ResourceSeeder` — catálogo de 6 recursos y su asignación a cada espacio (llena el pivote con `quantity`).
4. `ReservationSeeder` — reservas aprobadas, pendientes y rechazadas.
5. `IncidentSeeder` — incidentes abiertos, en proceso y resueltos.

Factories disponibles: `UserFactory` (estados `admin()`, `teacher()`, `student()`), `SpaceFactory` (`maintenance()`), `ResourceFactory` (`outOfService()`), `ReservationFactory` (`approved()`, `rejected()`), `IncidentFactory` (`resolved()`).

## 7. Verificación

```bash
php artisan migrate:fresh --seed
```

Resultado esperado (filas creadas): `users` 14 · `spaces` 6 · `resources` 6 · `resource_space` 17 · `reservations` 13 · `incidents` 7.

Comprobación rápida de las relaciones:

```bash
php artisan tinker --execute="\App\Models\Space::with('resources')->first()->resources->pluck('pivot.quantity','name')"
```

---

## 8. Actividad 3 — Enrutamiento, controladores y vistas

Se eligió la **opción A (monolito con Blade)**: vistas Blade sobre un layout maestro y componentes reutilizables, estilos con **Tailwind CSS 4** compilado por Vite y los componentes de interfaz de **Flux**. Además se expone una API REST de solo lectura para un posible frontend desacoplado.

### 8.1 Rutas (`routes/web.php` y `routes/api.php`)

Todas las rutas tienen nombre y las vistas enlazan con `route('nombre')`, nunca con URL literales. Listado completo: `php artisan route:list --except-vendor`.

| Bloque | Declaración | Rutas generadas |
|---|---|---|
| Vistas estáticas | `Route::view('/', 'welcome')`, `Route::view('acerca', 'about')` | `home`, `about` (públicas, sin controlador porque no consultan datos) |
| Panel | `Route::get('dashboard', DashboardController::class)` | `dashboard` (controlador invocable) |
| Espacios | `Route::resource('spaces', …)` | `spaces.index/create/store/show/edit/update/destroy` |
| Recursos de un espacio | `Route::controller(…)->prefix('spaces/{space}/resources')->name('spaces.resources.')` | `POST` `store`, `DELETE` `destroy` (tabla pivote) |
| Recursos | `Route::resource('resources', …)` | las 7 del CRUD |
| Reservas | `Route::resource('reservations', …)` + grupo `prefix('reservations/{reservation}')` | CRUD + `PATCH` `approve`, `reject`, `cancel` |
| Incidentes | `Route::resource('incidents', …)` + grupo `prefix('incidents/{incident}')` | CRUD + `PATCH` `assign`, `resolve` |
| Cuenta | grupo `prefix('settings')->name('settings.')` | `settings.profile`, `settings.password`, `settings.appearance` (Livewire Volt) |
| API | grupo `prefix('v1')->name('api.v1.')` en `routes/api.php` | `GET /api/v1/spaces`, `/api/v1/spaces/{space}`, `/api/v1/spaces/{space}/availability?date=` |

Verbos HTTP: `GET` para consultar, `POST` para crear, `PUT/PATCH` para actualizar, `DELETE` para borrar (borrado lógico) y `PATCH` para las acciones que solo cambian el estado de un registro. Las rutas de la aplicación están dentro del grupo `middleware(['auth', 'verified'])`.

### 8.2 Controladores delgados

Cada controlador solo recibe la petición, delega y devuelve la vista (`view('nombre', $datos)`) o la respuesta JSON. Las demás responsabilidades están separadas:

| Responsabilidad | Dónde vive |
|---|---|
| Permisos por rol y por estado | `app/Policies` — `SpacePolicy`, `ResourcePolicy`, `ReservationPolicy`, `IncidentPolicy` (se invocan con `$this->authorize()` y en Blade con `@can`) |
| Validación | `app/Http/Requests` — `SpaceRequest`, `ResourceRequest`, `AssignResourceRequest`, `ReservationRequest` (incluye el control de cruce de horarios), `ReviewReservationRequest`, `IncidentRequest`, `AssignIncidentRequest`, `ResolveIncidentRequest` |
| Consultas reutilizables | Scopes de Eloquent: `Space::filter()`, `Space::bookable()`, `Reservation::visibleTo()`, `active()`, `overlapping()`, `reportableBy()`, `Incident::visibleTo()`, `unresolved()`, `byPriority()` |
| Cambios de estado | Métodos del modelo: `Reservation::approve()`, `reject()`, `cancel()`; `Incident::assignTo()`, `resolve()` |
| Forma del JSON | API Resource `app/Http/Resources/SpaceResource.php` |

| Controlador | Tipo |
|---|---|
| `SpaceController`, `ResourceController` | Resource (7 métodos) |
| `ReservationController`, `IncidentController` | Resource + acciones de estado |
| `SpaceResourceController` | Rutas anidadas (pivote `resource_space`) |
| `DashboardController` | Invocable (`__invoke`) |
| `Api\SpaceController` | API JSON |

### 8.3 Vistas y componentes

```
resources/views/
├── components/
│   ├── layouts/app.blade.php        # layout maestro de la aplicación (barra lateral)
│   ├── layouts/public.blade.php     # layout de las páginas públicas
│   ├── page.blade.php               # encabezado, acciones y avisos de cada pantalla
│   ├── btn, badge, flash, input, select, textarea, feature-card, public-link
├── welcome.blade.php, about.blade.php   # vistas estáticas
├── dashboard.blade.php
└── spaces/ resources/ reservations/ incidents/
    ├── index, show, create, edit
    └── form.blade.php               # formulario compartido por create y edit
```

- Los datos llegan desde el controlador (`view('spaces.index', compact('spaces', 'buildings'))`); las vistas no consultan la base de datos.
- Los botones de administración se muestran según las Policies (`@can('update', $space)`), las mismas que protegen las rutas.
- Listados paginados (10 por página) que conservan los filtros en la URL.
- Interfaz completa en español, incluidos los mensajes de validación, de permisos (página 403) y la paginación.

### 8.4 Diseño adaptativo

Tailwind CSS con enfoque *mobile first*: barra lateral que se oculta en pantallas pequeñas, tarjetas y formularios que pasan de una a dos o cuatro columnas (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`), tablas con desplazamiento horizontal propio y tema claro/oscuro. Verificado sin desbordes desde 320 px de ancho.

### 8.5 Pruebas

```bash
php artisan test
```

41 pruebas. `tests/Feature/NavigationTest.php` recorre la navegación de la actividad: listados, formularios, vistas estáticas, permisos por rol, cruce de horarios, estados que bloquean la edición (reserva aprobada, incidente resuelto) y respuestas de la API.

