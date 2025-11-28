# Estructura y Convenciones del Proyecto

## Nombres de Métodos en Controllers

Todos los controllers usan nombres de métodos en español:

- `obtenerColeccion()` - Lista recursos (antes: `index`)
- `obtener($id)` - Obtiene un recurso (antes: `show`)
- `crear()` - Crea un recurso (antes: `store`)
- `actualizar($id)` - Actualiza un recurso (antes: `update`)
- `eliminar($id)` - Elimina un recurso (antes: `destroy`)

## Form Requests Unificados

Un solo Form Request por entidad que maneja tanto creación como actualización:

```php
public function rules(): array
{
    $esCreacion = $this->isMethod('POST');
    $requerido = $esCreacion ? 'required' : 'sometimes';
    
    return [
        'campo' => [$requerido, 'string', 'max:255'],
    ];
}
```

## Patrón de Arquitectura

### 1. Controller (Solo orquestación)
```php
public function crear(ProveedorRequest $request): JsonResponse
{
    $dto = CrearProveedorDTO::desdeArray($request->validated());
    $resultado = $this->service->crear($dto);
    return ApiResponse::creado($resultado->aArray());
}
```

### 2. Service (Lógica de negocio)
```php
public function crear(CrearProveedorDTO $dto): ProveedorDTO
{
    // Validaciones de negocio
    // Normalización de datos
    // Uso del repository
    // Retorno de DTO
}
```

### 3. Repository (Acceso a datos)
```php
public function crear(array $datos): Proveedor
{
    return Proveedor::create($datos);
}
```

### 4. DTOs (Transferencia de datos)
```php
// Para respuestas
class ProveedorDTO {
    public readonly int $id;
    public readonly string $nombre;
    // ...
}

// Para entrada
class CrearProveedorDTO {
    public readonly string $nombre;
    // ...
}
```

## Estructura de Respuestas

**Éxito:**
```json
{
  "exito": true,
  "mensaje": "Operación exitosa",
  "datos": {}
}
```

**Error:**
```json
{
  "exito": false,
  "mensaje": "Mensaje de error",
  "errores": {}
}
```

## Nomenclatura

- **Variables y métodos**: camelCase español (`obtenerTodos`, `nombreCompleto`)
- **Clases**: PascalCase español (`ProveedorService`, `CrearProveedorDTO`)
- **Tablas DB**: snake_case plural (`proveedores`, `tipos_documento`)
- **Columnas DB**: snake_case (`fecha_creacion`, `nombre_completo`)

## Convenciones de Nombres

### DTOs
- `[Entidad]DTO` - Representación completa
- `Crear[Entidad]DTO` - Para creación
- `Actualizar[Entidad]DTO` - Para actualización

### Repositories
- `[Entidad]RepositoryInterface` - Contrato
- `[Entidad]Repository` - Implementación

### Services
- `[Entidad]Service` - Lógica de negocio

### Controllers
- `[Entidad]Controller` - Orquestación HTTP

### Form Requests
- `[Entidad]Request` - Validaciones (unificado para crear/actualizar)

## Flujo de Datos

```
Request → Controller → Service → Repository → Model
                ↓          ↓          ↓
            FormReq    DTO (in)   Array
                                   ↓
                              Model (out)
                                   ↓
                              DTO (out)
                                   ↓
                              Response
```

## Próximas Entidades

Aplicar el mismo patrón para:
- Asociados
- Proyectos
- Movimientos
- Organizaciones
- ModosPago
