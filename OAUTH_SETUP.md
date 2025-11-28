# Configuración de Google OAuth

Este documento contiene las instrucciones para configurar la autenticación con Google OAuth en la aplicación.

## Pasos para obtener las credenciales de Google

### 1. Crear un proyecto en Google Cloud Console

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Habilita la **Google+ API** o **Google Identity API**:
    - Ve a "APIs y servicios" > "Biblioteca"
    - Busca "Google+ API" o "Google Identity Services"
    - Haz clic en "Habilitar"

### 2. Configurar la pantalla de consentimiento OAuth

1. Ve a "APIs y servicios" > "Pantalla de consentimiento de OAuth"
2. Selecciona el tipo de usuario (Externo o Interno)
3. Completa la información requerida:
    - Nombre de la aplicación
    - Correo de soporte
    - Dominios autorizados
    - Información de contacto del desarrollador

### 3. Crear credenciales OAuth 2.0

1. Ve a "APIs y servicios" > "Credenciales"
2. Haz clic en "Crear credenciales" > "ID de cliente de OAuth"
3. Selecciona "Aplicación web" como tipo de aplicación
4. Configura los siguientes campos:

    - **Nombre**: Findi API
    - **URIs de redireccionamiento autorizados**:
        - Para desarrollo: `http://localhost:8000/api/auth/google/callback`
        - Para producción: `https://tu-dominio.com/api/auth/google/callback`
    - **Orígenes de JavaScript autorizados**:
        - Para desarrollo: `http://localhost:8000`
        - Para producción: `https://tu-dominio.com`

5. Haz clic en "Crear"
6. Guarda el **ID de cliente** y el **Secreto de cliente**

### 4. Configurar las variables de entorno

Copia el archivo `.env.example` a `.env` si aún no lo has hecho:

```bash
cp .env.example .env
```

Actualiza las siguientes variables en tu archivo `.env`:

```env
GOOGLE_CLIENT_ID=tu-client-id-aqui.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=tu-client-secret-aqui
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback
```

> **Nota**: Para producción, asegúrate de actualizar `GOOGLE_REDIRECT_URI` con tu dominio real.

### 5. Ejecutar las migraciones

Ejecuta las migraciones para agregar las columnas necesarias a la tabla `users`:

```bash
php artisan migrate
```

## Flujo de autenticación

### 1. Iniciar sesión con Google

**Endpoint**: `GET /api/auth/google`

Respuesta:

```json
{
    "url": "https://accounts.google.com/o/oauth2/auth?..."
}
```

El frontend debe redirigir al usuario a esta URL.

### 2. Callback de Google

**Endpoint**: `GET /api/auth/google/callback`

Este endpoint es llamado automáticamente por Google después de que el usuario autoriza la aplicación.

Respuesta exitosa:

```json
{
    "access_token": "1|xxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "https://lh3.googleusercontent.com/..."
    }
}
```

### 3. Obtener información del usuario autenticado

**Endpoint**: `GET /api/auth/me`

**Headers**:

```
Authorization: Bearer {access_token}
```

Respuesta:

```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "https://lh3.googleusercontent.com/..."
    }
}
```

### 4. Cerrar sesión

**Endpoint**: `POST /api/auth/logout`

**Headers**:

```
Authorization: Bearer {access_token}
```

Respuesta:

```json
{
    "message": "Successfully logged out"
}
```

## Proteger rutas con autenticación

Para proteger rutas que requieran autenticación, usa el middleware `auth:sanctum`:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('proveedores', [ProveedorController::class, 'obtenerColeccion']);
    // ... más rutas protegidas
});
```

## Notas importantes

1. **Tokens de acceso**: Los tokens de Sanctum se generan automáticamente después de la autenticación exitosa con Google.

2. **CORS**: Asegúrate de configurar correctamente CORS en `config/cors.php` para permitir peticiones desde tu frontend.

3. **HTTPS en producción**: En producción, asegúrate de usar HTTPS para todas las comunicaciones.

4. **Seguridad**: Nunca expongas tu `GOOGLE_CLIENT_SECRET` en el frontend o en repositorios públicos.

5. **Dominios verificados**: En Google Cloud Console, agrega todos los dominios desde donde se realizarán peticiones a tu API.

## Troubleshooting

### Error: "redirect_uri_mismatch"

-   Verifica que el `GOOGLE_REDIRECT_URI` en tu `.env` coincida exactamente con la URI configurada en Google Cloud Console.
-   Asegúrate de incluir el protocolo (`http://` o `https://`) y el path completo.

### Error: "invalid_client"

-   Verifica que `GOOGLE_CLIENT_ID` y `GOOGLE_CLIENT_SECRET` sean correctos.
-   Asegúrate de que las credenciales estén habilitadas en Google Cloud Console.

### Usuario no puede autenticarse

-   Verifica que la Google+ API esté habilitada.
-   Revisa los logs en `storage/logs/laravel.log` para más detalles.
