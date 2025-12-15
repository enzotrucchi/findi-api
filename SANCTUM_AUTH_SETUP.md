# Configuración de Autenticación Sanctum + SPA Angular

## 📋 Resumen

Este documento describe cómo está configurada la autenticación entre el backend Laravel (Findi API) y el frontend Angular usando Laravel Sanctum en modo **stateful** (con cookies).

---

## 🔐 ¿Cómo funciona Sanctum en modo Stateful?

### Modo Stateful (Lo que usamos)

-   **Autenticación basada en cookies HTTP-only**
-   **Sesión server-side** que persiste entre recargas
-   **No requiere refresh token manual**
-   **Ideal para SPAs (Angular, Vue, React)**

### Modo Stateless (No lo usamos)

-   Autenticación basada en Bearer tokens API
-   Requiere refresh token flow
-   Más complejo de implementar

---

## ⚙️ Configuración Backend (Laravel)

### 1. Middleware Sanctum en `app/Http/Kernel.php`

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

**¿Qué hace?**

-   Detecta peticiones desde el frontend (Angular) como "stateful"
-   Permite que Sanctum use cookies en lugar de tokens Bearer
-   Sin esto, Sanctum trataría las peticiones como API tokens (incorrecto para SPA)

### 2. Variables de entorno en `.env`

```env
SESSION_LIFETIME=2880          # 48 horas de inactividad
SESSION_DRIVER=file            # Guardar sesión en archivos
SANCTUM_STATEFUL_DOMAINS=localhost:4200
SESSION_DOMAIN=localhost
FRONTEND_URL=http://localhost:4200
```

**Explicación:**

-   `SESSION_LIFETIME=2880`: Sesión expira tras 2880 minutos (48 horas) **sin actividad**
-   `SANCTUM_STATEFUL_DOMAINS`: Define qué dominios se consideran "frontend" (tu Angular)
-   `SESSION_DOMAIN`: Dominio donde se guardan las cookies

### 3. Configuración `config/sanctum.php`

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort()
))),

'expiration' => null,  // Las sesiones no expiran por token (usan SESSION_LIFETIME)
```

### 4. CORS en `config/cors.php`

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'supports_credentials' => true,  // Permite enviar cookies en requests CORS
```

---

## 🎯 Configuración Frontend (Angular)

### 1. Obtener CSRF Token al cargar la app

En tu `app.component.ts` o en un servicio de inicialización:

```typescript
import { HttpClient } from '@angular/common/http';

constructor(private http: HttpClient) {
  this.initializeApp();
}

private initializeApp() {
  // Obtener el token CSRF desde Sanctum
  this.http.get('http://localhost:8000/sanctum/csrf-cookie', {
    withCredentials: true
  }).subscribe(
    () => console.log('CSRF token obtained'),
    (error) => console.error('Failed to get CSRF token', error)
  );
}
```

### 2. HttpClient Interceptor

Crear `src/app/interceptors/auth.interceptor.ts`:

```typescript
import { Injectable } from "@angular/core";
import {
    HttpRequest,
    HttpHandler,
    HttpEvent,
    HttpInterceptor,
} from "@angular/common/http";
import { Observable } from "rxjs";

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
    intercept(
        request: HttpRequest<unknown>,
        next: HttpHandler
    ): Observable<HttpEvent<unknown>> {
        // Enviar TODAS las peticiones con credenciales (cookies)
        const cloned = request.clone({
            withCredentials: true,
        });

        return next.handle(cloned);
    }
}
```

Registrar en `app.config.ts` o `app.module.ts`:

```typescript
// app.config.ts (Standalone API)
import { HTTP_INTERCEPTORS } from "@angular/common/http";
import { AuthInterceptor } from "./interceptors/auth.interceptor";

export const appConfig: ApplicationConfig = {
    providers: [
        {
            provide: HTTP_INTERCEPTORS,
            useClass: AuthInterceptor,
            multi: true,
        },
    ],
};

// O en app.module.ts (NgModule)
@NgModule({
    providers: [
        {
            provide: HTTP_INTERCEPTORS,
            useClass: AuthInterceptor,
            multi: true,
        },
    ],
})
export class AppModule {}
```

### 3. Manejar errores 401 (Sesión expirada)

```typescript
import { Injectable } from "@angular/core";
import {
    HttpRequest,
    HttpHandler,
    HttpEvent,
    HttpInterceptor,
    HttpErrorResponse,
} from "@angular/common/http";
import { Observable, throwError } from "rxjs";
import { catchError } from "rxjs/operators";
import { Router } from "@angular/router";

@Injectable()
export class ErrorInterceptor implements HttpInterceptor {
    constructor(private router: Router) {}

    intercept(
        request: HttpRequest<unknown>,
        next: HttpHandler
    ): Observable<HttpEvent<unknown>> {
        return next.handle(request).pipe(
            catchError((error: HttpErrorResponse) => {
                if (error.status === 401) {
                    // Sesión expirada
                    console.log("Sesión expirada. Redirigiendo al login...");
                    this.router.navigate(["/login"]);
                }
                return throwError(() => error);
            })
        );
    }
}
```

---

## 🔄 Flujo de autenticación completo

### 1️⃣ Usuario abre Angular

```
Angular carga → Interceptor obtiene CSRF token (/sanctum/csrf-cookie)
              → Laravel crea sesión en cookies
              → Angular guarda XSRF-TOKEN en memoria
```

### 2️⃣ Usuario hace login

```
POST /api/login → Laravel valida credenciales
               → Crea sesión en servidor
               → Devuelve usuario + datos
Angular recibe respuesta → Guarda en localStorage/SessionStorage
                        → Sesión activa (cookies automáticas)
```

### 3️⃣ Usuario hace peticiones a API

```
GET /api/proyectos → HttpInterceptor agrega withCredentials: true
                   → Cookies se envían automáticamente
                   → Laravel valida sesión
                   → Responde datos
```

### 4️⃣ Usuario se va 48 horas sin actividad

```
Sesión en servidor expira (SESSION_LIFETIME=2880)
Cookies se eliminan
```

### 5️⃣ Usuario intenta hacer algo después de expiración

```
GET /api/proyectos → Sanctum no encuentra sesión válida
                   → Responde 401 Unauthorized
Angular recibe 401 → ErrorInterceptor redirige a /login
                   → Usuario debe hacer login nuevamente
```

---

## 📊 Comparativa: Antes vs Después

### ❌ Antes (incorrecto)

| Aspecto                | Valor                                                 |
| ---------------------- | ----------------------------------------------------- |
| Middleware             | Comentado (deshabilitado)                             |
| Comportamiento         | Sanctum trata Angular como API externa (token Bearer) |
| Requiere refresh?      | SÍ (manualmente)                                      |
| Sesión entre recargas? | NO (token expira al recargar)                         |
| Seguridad              | Media (tokens en memoria)                             |

### ✅ Después (correcto)

| Aspecto                | Valor                                                  |
| ---------------------- | ------------------------------------------------------ |
| Middleware             | `EnsureFrontendRequestsAreStateful::class` activado    |
| Comportamiento         | Sanctum reconoce Angular como SPA (sesión con cookies) |
| Requiere refresh?      | NO (automático con cookies)                            |
| Sesión entre recargas? | SÍ (48 horas de inactividad)                           |
| Seguridad              | Alta (cookies HTTP-only, CSRF protection)              |

---

## 🚀 Casos de uso y comportamiento

### Caso 1: Usuario activo en la app

```
Minuto 0:   Usuario abre Angular → Sesión comienza
Minuto 5:   GET /api/proyectos → Sesión se recarga
Minuto 15:  POST /api/movimiento → Sesión se recarga
Minuto 48h: Usuario sigue usando → Sesión se recarga constantemente
            ✅ Permanece logueado indefinidamente mientras esté activo
```

### Caso 2: Usuario se va sin cerrar sesión

```
Minuto 0:   Usuario abre Angular → Sesión comienza
Minuto 30:  Usuario se va de la app
Minuto 48h: Sesión expira automáticamente (inactividad)
Minuto 48h + 1min: Usuario vuelve y trata de hacer algo
                   → 401 Unauthorized
                   → Redirige a login ❌
```

### Caso 3: Usuario cierra sesión manualmente

```
GET /api/logout → Laravel destruye sesión
                → Cookies se limpian
                → Angular redirige a /login ✅
```

---

## ⚠️ Consideraciones de seguridad

### ✅ Implementado correctamente

1. **HTTP-only Cookies**: Las cookies XSRF no son accesibles desde JavaScript
2. **CSRF Protection**: Token CSRF validado en POST/PUT/DELETE
3. **Same-origin**: Solo Angular en `localhost:4200` puede usar la sesión
4. **Credentials**: `withCredentials: true` necesario para enviar cookies

### 🔒 Puntos clave

-   **NO guardar tokens en localStorage** (vulnerable a XSS)
-   **Las cookies son automáticas** (manejadas por el navegador)
-   **XSRF-TOKEN** se envía en header `X-XSRF-TOKEN` (Angular lo hace automáticamente)

---

## 🛠️ Troubleshooting

### ❌ Problema: "401 Unauthorized" después de recargar Angular

**Causa**: El middleware Sanctum no está activado

**Solución**:

```php
// app/Http/Kernel.php - 'api' group
\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
```

### ❌ Problema: Cookies no se envían desde Angular

**Causa**: Falta `withCredentials: true`

**Solución**: Usar el HttpInterceptor que agrega esto automáticamente

### ❌ Problema: CORS error al obtener CSRF token

**Causa**: `sanctum/csrf-cookie` no está en rutas CORS

**Solución**:

```php
// config/cors.php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'supports_credentials' => true,
```

### ❌ Problema: Sesión expira muy rápido

**Causa**: `SESSION_LIFETIME` muy bajo

**Solución**:

```env
SESSION_LIFETIME=2880  # 48 horas
```

---

## 📝 Checklist de implementación

### Backend (Laravel)

-   ✅ Middleware `EnsureFrontendRequestsAreStateful` activado
-   ✅ `SANCTUM_STATEFUL_DOMAINS` configurado con dominio Angular
-   ✅ `SESSION_LIFETIME=2880` (48 horas)
-   ✅ CORS habilitado con `supports_credentials: true`
-   ✅ Rutas API protegidas con `auth:sanctum`

### Frontend (Angular)

-   ⏳ HttpInterceptor obtiene CSRF token al iniciar
-   ⏳ HttpInterceptor agrega `withCredentials: true` a todas las peticiones
-   ⏳ ErrorInterceptor maneja 401 y redirige a login
-   ⏳ Servicio de autenticación implementado
-   ⏳ Guard de rutas para proteger páginas

---

## 🔗 Referencias

-   [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
-   [Laravel Session Configuration](https://laravel.com/docs/session)
-   [Angular HTTP Client with Credentials](https://angular.io/guide/http#requesting-an-interceptor-service)
-   [CORS and Credentials](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS#credentialed_requests)

---

**Última actualización**: 15 de diciembre de 2025
