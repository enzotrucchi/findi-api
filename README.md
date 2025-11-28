# Findi API - Laravel

API REST para el sistema Findi, desarrollada con Laravel siguiendo las mejores prácticas de diseño.

## Características

- ✅ Arquitectura limpia con separación de responsabilidades
- ✅ DTOs (Data Transfer Objects) para todas las transferencias de datos
- ✅ Repositorios para abstracción de datos
- ✅ Services para lógica de negocio
- ✅ Controllers ligeros (solo orquestación)
- ✅ Validación de datos con Form Requests
- ✅ Respuestas JSON estandarizadas
- ✅ Manejo de excepciones centralizado
- ✅ CORS configurado
- ✅ Todo en español (código, comentarios, mensajes)

## Instalación

### 1. Configurar base de datos

Editar el archivo `.env` con las credenciales de tu base de datos remota:

```env
DB_CONNECTION=mysql
DB_HOST=tu-host-remoto
DB_PORT=3306
DB_DATABASE=findi
DB_USERNAME=tu-usuario
DB_PASSWORD=tu-contraseña
```

### 2. Ejecutar migraciones

```bash
php artisan migrate
```

### 3. Iniciar servidor

```bash
php artisan serve
```

La API estará en: `http://localhost:8000`

## Arquitectura

```
app/
├── DTOs/                          # Data Transfer Objects
├── Http/Controllers/Api/          # Controllers (solo orquestación)
├── Http/Requests/                 # Form Requests (validación)
├── Http/Responses/                # Helper de respuestas
├── Models/                        # Modelos Eloquent
├── Repositories/                  # Repositorios (acceso a datos)
│   └── Contracts/                 # Interfaces
├── Services/                      # Lógica de negocio
└── Providers/                     # Service Providers
```

## Endpoints - Proveedores

**Base URL:** `http://localhost:8000/api`

### GET /proveedores
Método: `obtenerColeccion`
Lista todos los proveedores
- Query: `?activos=true` - Filtrar activos
- Query: `?busqueda=texto` - Buscar

### GET /proveedores/{id}
Método: `obtener`
Obtener un proveedor

### POST /proveedores
Método: `crear`
Crear proveedor
```json
{
  "nombre": "Juan Pérez",
  "email": "juan@example.com",
  "telefono": "1234567890",
  "activo": true
}
```

### PUT/PATCH /proveedores/{id}
Método: `actualizar`
Actualizar proveedor (campos opcionales)

### DELETE /proveedores/{id}
Método: `eliminar`
Eliminar proveedor

## Formato de Respuesta

**Éxito:**
```json
{
  "exito": true,
  "mensaje": "...",
  "datos": {}
}
```

**Error:**
```json
{
  "exito": false,
  "mensaje": "...",
  "errores": {}
}
```

## Próximos Pasos

- Agregar autenticación
- Implementar: Asociados, Proyectos, Movimientos, Organizaciones
- Paginación y filtros avanzados
