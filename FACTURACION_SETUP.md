# Sistema de Facturación y Control de Acceso

## Descripción General

Este sistema implementa la lógica de facturación mensual y control de acceso para organizaciones. Las organizaciones deben tener un pago activo para poder acceder a la aplicación.

## Estructura de Base de Datos

### Tabla: `facturas`

| Campo                | Tipo          | Descripción                                                     |
| -------------------- | ------------- | --------------------------------------------------------------- |
| `id`                 | bigint        | ID único de la factura                                          |
| `organizacion_id`    | bigint        | ID de la organización (FK)                                      |
| `periodo`            | string(7)     | Periodo de facturación (formato: YYYY-MM)                       |
| `fecha_corte`        | date          | Fecha en la que se congeló el conteo de asociados               |
| `cantidad_asociados` | integer       | Cantidad de asociados activos al momento del corte              |
| `precio_unitario`    | decimal(10,2) | Precio por asociado (actualmente $2.00 USD)                     |
| `monto_total`        | decimal(10,2) | Monto total de la factura                                       |
| `fecha_vencimiento`  | date          | Fecha límite de pago                                            |
| `estado`             | enum          | Estado de la factura: `pending`, `paid`, `expired`, `cancelled` |
| `fecha_pago`         | timestamp     | Fecha en que se realizó el pago (nullable)                      |

**Índices:** `organizacion_id`, `periodo`, `estado`, `fecha_vencimiento`  
**Unique constraint:** `organizacion_id` + `periodo` (evita facturas duplicadas)

### Tabla: `organizaciones` (campos agregados)

| Campo               | Tipo | Descripción                                      |
| ------------------- | ---- | ------------------------------------------------ |
| `fecha_vencimiento` | date | Fecha hasta la cual la organización tiene acceso |
| `estado`            | enum | Estado de la organización: `active`, `blocked`   |

**Nota:** El campo `habilitada` se mantiene por compatibilidad pero será reemplazado por `estado`.

## Modelos

### Factura

**Ubicación:** `app/Models/Factura.php`

**Constantes de Estado:**

-   `ESTADO_PENDING`: Factura pendiente de pago
-   `ESTADO_PAID`: Factura pagada
-   `ESTADO_EXPIRED`: Factura vencida
-   `ESTADO_CANCELLED`: Factura cancelada

**Métodos principales:**

-   `estaPagada()`: Verifica si la factura está pagada
-   `estaVencida()`: Verifica si la factura está vencida
-   `estaPendiente()`: Verifica si la factura está pendiente
-   `marcarComoPagada()`: Marca la factura como pagada
-   `marcarComoVencida()`: Marca la factura como vencida
-   `cancelar()`: Cancela la factura

**Scopes:**

-   `pendientes()`: Filtra facturas pendientes
-   `pagadas()`: Filtra facturas pagadas
-   `vencidas()`: Filtra facturas vencidas
-   `porPeriodo($periodo)`: Filtra por periodo específico

### Organizacion (actualizado)

**Ubicación:** `app/Models/Organizacion.php`

**Constantes de Estado:**

-   `ESTADO_ACTIVE`: Organización activa
-   `ESTADO_BLOCKED`: Organización bloqueada

**Métodos principales:**

-   `estaActiva()`: Verifica si la organización está activa
-   `estaBloqueada()`: Verifica si la organización está bloqueada
-   `tieneAcceso()`: Verifica si la organización tiene acceso a la app
-   `estaVencida()`: Verifica si el periodo de acceso ha vencido
-   `cantidadAsociadosActivos()`: Obtiene la cantidad de asociados activos
-   `bloquear()`: Bloquea la organización
-   `activar()`: Activa la organización

**Relaciones:**

-   `facturas()`: Relación hasMany con Factura

## Servicios

### FacturaService

**Ubicación:** `app/Services/FacturaService.php`

**Constantes:**

-   `PRECIO_UNITARIO_DEFAULT`: 2.00 USD
-   `DIAS_VENCIMIENTO`: 5 días después del inicio del mes

**Métodos principales:**

#### `generarFactura(Organizacion $organizacion, ?string $periodo = null, ?float $precioUnitario = null): Factura`

Genera una nueva factura para una organización.

-   Valida que no exista factura duplicada para el periodo
-   Congela la cantidad de asociados activos
-   Calcula el monto total
-   Define la fecha de vencimiento

#### `generarFacturasParaTodasLasOrganizaciones(?string $periodo = null): array`

Genera facturas para todas las organizaciones activas (no en prueba).
Retorna un array con:

-   `generadas`: cantidad de facturas creadas exitosamente
-   `errores`: cantidad de errores
-   `detalles`: array con detalles de cada operación

#### `procesarPago(Factura $factura): void`

Procesa el pago de una factura:

-   Marca la factura como pagada
-   Actualiza la fecha de vencimiento de la organización
-   Activa la organización si estaba bloqueada

#### `marcarFacturasVencidas(): int`

Marca todas las facturas pendientes como vencidas y bloquea las organizaciones correspondientes.

#### `bloquearOrganizacionesVencidas(): array`

Bloquea todas las organizaciones con fecha de vencimiento superada.

#### `obtenerFacturasPendientes(Organizacion $organizacion)`

Obtiene todas las facturas pendientes de una organización.

#### `obtenerHistorialFacturas(Organizacion $organizacion)`

Obtiene el historial completo de facturas de una organización.

#### `cancelarFactura(Factura $factura, ?string $motivo = null): void`

Cancela una factura (no se puede cancelar si ya fue pagada).

## Middleware

### CheckOrganizacionAcceso

**Ubicación:** `app/Http/Middleware/CheckOrganizacionAcceso.php`

**Función:** Verifica que la organización del usuario tenga acceso a la aplicación antes de permitir el acceso a las rutas protegidas.

**Configuración requerida:**
Debes ajustar el método `obtenerOrganizacionDelUsuario()` según tu estructura de datos:

-   Si User tiene un campo `organizacion_id`
-   Si la organización viene en el request
-   Si User tiene una relación con Organizacion

**Respuestas de error:**

-   403: Organización sin acceso (incluye detalles de la organización)

**Para usar el middleware:**

1. Registrarlo en `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ... otros middlewares
    'organizacion.acceso' => \App\Http\Middleware\CheckOrganizacionAcceso::class,
];
```

2. Aplicarlo a rutas:

```php
Route::middleware(['auth:sanctum', 'organizacion.acceso'])->group(function () {
    // Rutas protegidas
});
```

## Comandos Artisan

### Generar Facturas Mensuales

**Comando:** `php artisan facturas:generar-mensuales`

**Opciones:**

-   `--periodo=YYYY-MM`: Periodo específico (por defecto: mes actual)

**Ejemplo:**

```bash
php artisan facturas:generar-mensuales
php artisan facturas:generar-mensuales --periodo=2026-01
```

**Uso recomendado:** Programar en cron para ejecutarse el primer día de cada mes.

**Crontab:**

```bash
0 0 1 * * cd /path/to/app && php artisan facturas:generar-mensuales
```

### Marcar Facturas Vencidas

**Comando:** `php artisan facturas:marcar-vencidas`

**Función:**

-   Marca las facturas pendientes como vencidas
-   Bloquea las organizaciones con facturas vencidas
-   Bloquea organizaciones con fecha_vencimiento superada

**Ejemplo:**

```bash
php artisan facturas:marcar-vencidas
```

**Uso recomendado:** Programar en cron para ejecutarse diariamente.

**Crontab:**

```bash
0 1 * * * cd /path/to/app && php artisan facturas:marcar-vencidas
```

## Controlador API

### FacturaController

**Ubicación:** `app/Http/Controllers/FacturaController.php`

**Endpoints sugeridos:**

#### `GET /api/organizaciones/{organizacionId}/facturas`

Lista todas las facturas de una organización.

#### `GET /api/organizaciones/{organizacionId}/facturas/pendientes`

Lista las facturas pendientes de una organización.

#### `GET /api/facturas/{id}`

Obtiene una factura específica.

#### `POST /api/facturas`

Genera una nueva factura manualmente.

**Request body:**

```json
{
    "organizacion_id": 1,
    "periodo": "2026-01",
    "precio_unitario": 2.0
}
```

#### `POST /api/facturas/{id}/procesar-pago`

Procesa el pago de una factura.

#### `POST /api/facturas/{id}/cancelar`

Cancela una factura.

**Request body:**

```json
{
    "motivo": "Organización canceló su suscripción"
}
```

#### `GET /api/organizaciones/{organizacionId}/facturas/estadisticas`

Obtiene estadísticas de facturación de una organización.

**Respuesta:**

```json
{
    "data": {
        "total_facturas": 12,
        "facturas_pendientes": 1,
        "facturas_pagadas": 10,
        "facturas_vencidas": 0,
        "facturas_canceladas": 1,
        "monto_total_pendiente": 50.0,
        "monto_total_pagado": 500.0
    }
}
```

## DTO

### FacturaDTO

**Ubicación:** `app/DTOs/Factura/FacturaDTO.php`

**Métodos:**

-   `desdeArray(array $datos)`: Crear desde array
-   `aArray()`: Convertir a array
-   `desdeModelo(Factura $factura)`: Crear desde modelo

## Migración e Instalación

### Paso 1: Ejecutar migraciones

```bash
php artisan migrate
```

Esto creará:

-   Tabla `facturas`
-   Agregará campos `fecha_vencimiento` y `estado` a `organizaciones`

### Paso 2: Actualizar datos existentes (opcional)

Si tienes organizaciones existentes con el campo `habilitada`, puedes migrar los datos:

```php
use App\Models\Organizacion;

// Migrar habilitada a estado
Organizacion::where('habilitada', true)->update(['estado' => 'active']);
Organizacion::where('habilitada', false)->update(['estado' => 'blocked']);
```

### Paso 3: Configurar middleware

Registrar el middleware en `app/Http/Kernel.php` y aplicarlo a las rutas necesarias.

### Paso 4: Configurar rutas API

Agregar las rutas al archivo `routes/api.php`:

```php
use App\Http\Controllers\FacturaController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Rutas de facturas
    Route::get('organizaciones/{organizacionId}/facturas', [FacturaController::class, 'index']);
    Route::get('organizaciones/{organizacionId}/facturas/pendientes', [FacturaController::class, 'pendientes']);
    Route::get('organizaciones/{organizacionId}/facturas/estadisticas', [FacturaController::class, 'estadisticas']);
    Route::get('facturas/{id}', [FacturaController::class, 'show']);
    Route::post('facturas', [FacturaController::class, 'store']);
    Route::post('facturas/{id}/procesar-pago', [FacturaController::class, 'procesarPago']);
    Route::post('facturas/{id}/cancelar', [FacturaController::class, 'cancelar']);
});
```

### Paso 5: Configurar tareas programadas

Agregar en `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Generar facturas el primer día de cada mes a las 00:00
    $schedule->command('facturas:generar-mensuales')
             ->monthlyOn(1, '00:00');

    // Marcar facturas vencidas diariamente a las 01:00
    $schedule->command('facturas:marcar-vencidas')
             ->dailyAt('01:00');
}
```

## Flujo de Trabajo

### 1. Generación de Facturas (Mensual)

-   El comando `facturas:generar-mensuales` se ejecuta automáticamente
-   Se generan facturas para todas las organizaciones activas
-   Cada factura congela la cantidad de asociados del momento
-   Se calcula el monto total (cantidad_asociados × precio_unitario)

### 2. Notificación de Facturas

-   **Pendiente:** Enviar emails a las organizaciones con facturas generadas
-   Incluir enlace para realizar el pago
-   Recordatorio antes del vencimiento

### 3. Procesamiento de Pago

-   Al recibir confirmación de pago, llamar a `procesarPago()`
-   La factura se marca como pagada
-   La organización obtiene acceso hasta el fin del periodo facturado

### 4. Control de Vencimientos (Diario)

-   El comando `facturas:marcar-vencidas` se ejecuta automáticamente
-   Facturas pendientes que superan fecha_vencimiento → estado `expired`
-   Organizaciones con facturas vencidas → estado `blocked`
-   Organizaciones con fecha_vencimiento superada → estado `blocked`

### 5. Control de Acceso

-   El middleware `CheckOrganizacionAcceso` verifica en cada request
-   Bloquea el acceso si:
    -   La organización está bloqueada
    -   El periodo de prueba ha vencido
    -   La fecha_vencimiento ha sido superada

## Próximos Pasos Recomendados

1. **Integración con pasarela de pagos**

    - Stripe, PayPal, MercadoPago, etc.
    - Webhook para recibir confirmaciones de pago

2. **Sistema de notificaciones**

    - Emails de facturas generadas
    - Recordatorios de pago
    - Notificaciones de vencimiento

3. **Panel administrativo**

    - Ver todas las facturas
    - Procesar pagos manualmente
    - Generar reportes

4. **Reportes y analytics**

    - Ingresos mensuales
    - Tasa de pago
    - Organizaciones en riesgo de vencimiento

5. **Funcionalidades adicionales**
    - Descuentos por volumen
    - Planes anuales
    - Pruebas gratuitas automáticas
    - Facturación en diferentes monedas

## Consideraciones

### Precios Variables

El precio unitario está configurado como constante pero puede variar:

-   Al generar facturas manualmente puedes especificar un precio diferente
-   Considera agregar un campo `precio_unitario` a la tabla `organizaciones` si necesitas precios personalizados

### Organizaciones de Prueba

-   Las organizaciones con `es_prueba = true` no se incluyen en la facturación automática
-   Una vez que termine la prueba, debes convertirla a organización regular

### Transiciones de Estado

-   `pending` → `paid`: Al procesar pago
-   `pending` → `expired`: Al vencer sin pago
-   `pending` → `cancelled`: Al cancelar manualmente
-   `expired` no puede cambiar a `paid` directamente (generar nueva factura si es necesario)

### Seguridad

-   Validar que el usuario tiene permisos para ver/modificar facturas de la organización
-   Implementar roles y permisos según sea necesario
-   Logs de todas las acciones importantes (pago, cancelación, etc.)
