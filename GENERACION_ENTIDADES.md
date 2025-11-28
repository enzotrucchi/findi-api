# Guía de Generación de Entidades Restantes

Este documento contiene las especificaciones para completar todas las entidades.

## Entidades a Crear

### 1. Asociado (SIMILAR A PROVEEDOR)
- Campos: nombre, email, telefono, domicilio, es_admin, activo
- Ya tiene migración creada y configurada

### 2. Proyecto  
- Campos: descripcion, monto_actual, monto_objetivo, fecha_alta, fecha_realizacion (nullable)

### 3. ModoPago (CATÁLOGO SIMPLE)
- Campos: nombre
- Ejemplos: Efectivo, Transferencia, Cheque, etc.

### 4. Organizacion
- Campos: nombre, admin_email, admin_nombre, fecha_alta, es_prueba, fecha_fin_prueba

### 5. Movimiento (COMPLEJO - CON RELACIONES)
- Campos propios: fecha, hora, detalle, monto, tipo (ingreso/egreso), status, adjunto
- Relaciones: proyecto_id, asociado_id, proveedor_id, modo_pago_id, organizacion_id

## Patrón a Seguir (Igual que Proveedor)

Para cada entidad crear:
1. Migración con campos
2. Modelo Eloquent
3. 3 DTOs (DTO, CrearDTO, ActualizarDTO)
4. Repository Interface
5. Repository Implementation
6. Service
7. Form Request (unificado)
8. Controller con métodos: obtenerColeccion, obtener, crear, actualizar, eliminar
9. Rutas en api.php
10. Binding en RepositoryServiceProvider

## Órden de Creación Recomendado

1. ModoPago (más simple, catálogo)
2. Organizacion
3. Proyecto
4. Asociado (ya iniciado)
5. Movimiento (último porque tiene relaciones con todos)

## Notas Importantes

- Movimiento debe migrar DESPUÉS de todas las demás tablas
- Usar foreignId()->constrained() para relaciones
- Todos los métodos en español
- Un solo Request por entidad
- Validación automática POST vs PUT/PATCH

