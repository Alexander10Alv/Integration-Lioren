# 🔐 Spatie Permission - Guía de Uso

Este proyecto utiliza **Spatie Laravel Permission** para gestionar roles y permisos de forma profesional y escalable.

## 📋 Tabla de Contenidos

- [Instalación](#instalación)
- [Roles Configurados](#roles-configurados)
- [Permisos Disponibles](#permisos-disponibles)
- [Uso en Código](#uso-en-código)
- [Uso en Vistas](#uso-en-vistas)
- [Uso en Rutas](#uso-en-rutas)
- [Comandos Útiles](#comandos-útiles)
- [Agregar Nuevos Roles/Permisos](#agregar-nuevos-rolespermisos)

---

## 📦 Instalación

El paquete ya está instalado y configurado. Si necesitas reinstalarlo:

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

---

## 👥 Roles Configurados

### Admin
- **Email:** `admin@admin.com`
- **Password:** `12345678`
- **Permisos:** Todos los permisos del sistema
- **Acceso:** Dashboard completo, Integración, Bodegas, Boletas

### Cliente
- **Email:** `cliente@demo.com`
- **Password:** `12345678`
- **Permisos:** Solo ver sus propios pedidos y facturas
- **Acceso:** Dashboard cliente, Pedidos, Facturas

---

## 🔑 Permisos Disponibles

### Integración
- `view-integracion` - Ver configuración de integración
- `manage-integracion` - Gestionar integración
- `configure-webhooks` - Configurar webhooks

### Bodegas
- `view-bodegas` - Ver bodegas
- `manage-bodegas` - Gestionar bodegas

### Boletas/Facturas (Admin)
- `view-boletas` - Ver todas las boletas
- `manage-boletas` - Gestionar boletas
- `emit-documentos` - Emitir documentos tributarios

### Productos
- `view-productos` - Ver productos
- `sync-productos` - Sincronizar productos

### Pedidos
- `view-pedidos` - Ver todos los pedidos (Admin)
- `view-own-pedidos` - Ver solo propios pedidos (Cliente)

### Facturas
- `view-facturas` - Ver todas las facturas (Admin)
- `view-own-facturas` - Ver solo propias facturas (Cliente)

---

## 💻 Uso en Código

### Verificar Roles

```php
// Verificar si tiene un rol
if ($user->hasRole('admin')) {
    // Es admin
}

// Verificar múltiples roles
if ($user->hasAnyRole(['admin', 'cliente'])) {
    // Tiene alguno de estos roles
}

// Verificar todos los roles
if ($user->hasAllRoles(['admin', 'superadmin'])) {
    // Tiene todos estos roles
}
```

### Verificar Permisos

```php
// Verificar un permiso
if ($user->can('manage-integracion')) {
    // Tiene permiso
}

// Verificar múltiples permisos
if ($user->hasAnyPermission(['view-boletas', 'manage-boletas'])) {
    // Tiene alguno de estos permisos
}

// Verificar todos los permisos
if ($user->hasAllPermissions(['view-boletas', 'manage-boletas'])) {
    // Tiene todos estos permisos
}
```

### Asignar Roles

```php
// Asignar un rol
$user->assignRole('admin');

// Asignar múltiples roles
$user->assignRole(['admin', 'editor']);

// Remover rol
$user->removeRole('admin');

// Sincronizar roles (reemplaza todos)
$user->syncRoles(['admin']);
```

### Asignar Permisos

```php
// Asignar permiso directo
$user->givePermissionTo('manage-integracion');

// Remover permiso
$user->revokePermissionTo('manage-integracion');

// Sincronizar permisos
$user->syncPermissions(['view-boletas', 'manage-boletas']);
```

---

## 🎨 Uso en Vistas (Blade)

### Directivas de Roles

```blade
@role('admin')
    <!-- Solo visible para admin -->
    <a href="{{ route('integracion.index') }}">Integración</a>
@endrole

@role('cliente')
    <!-- Solo visible para cliente -->
    <a href="{{ route('cliente.dashboard') }}">Mi Dashboard</a>
@endrole

@hasrole('admin')
    <!-- Alternativa -->
@endhasrole

@hasanyrole('admin|cliente')
    <!-- Visible para admin O cliente -->
@endhasanyrole

@hasallroles('admin|superadmin')
    <!-- Visible solo si tiene AMBOS roles -->
@endhasallroles

@unlessrole('admin')
    <!-- Visible para todos EXCEPTO admin -->
@endunlessrole
```

### Directivas de Permisos

```blade
@can('manage-integracion')
    <!-- Solo si tiene el permiso -->
    <button>Configurar Integración</button>
@endcan

@cannot('manage-integracion')
    <!-- Solo si NO tiene el permiso -->
    <p>No tienes permiso</p>
@endcannot

@canany(['view-boletas', 'manage-boletas'])
    <!-- Si tiene ALGUNO de estos permisos -->
@endcanany
```

### Verificación en Línea

```blade
{{ auth()->user()->hasRole('admin') ? 'Admin' : 'Cliente' }}

@if(auth()->user()->can('manage-integracion'))
    <a href="{{ route('integracion.index') }}">Configurar</a>
@endif
```

---

## 🛣️ Uso en Rutas

### Middleware de Roles

```php
// Proteger ruta con rol específico
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/integracion', [IntegracionController::class, 'index']);
});

// Múltiples roles (OR)
Route::middleware(['auth', 'role:admin|cliente'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### Middleware de Permisos

```php
// Proteger con permiso
Route::middleware(['auth', 'permission:manage-integracion'])->group(function () {
    Route::post('/integracion/procesar', [IntegracionController::class, 'procesar']);
});

// Múltiples permisos (OR)
Route::middleware(['auth', 'permission:view-boletas|manage-boletas'])->group(function () {
    Route::get('/boletas', [BoletaController::class, 'index']);
});
```

### Middleware Personalizado (CheckRole)

```php
// Nuestro middleware personalizado
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
});
```

---

## 🛠️ Comandos Útiles

### Limpiar Caché de Permisos

```bash
# Después de cambiar roles/permisos
php artisan permission:cache-reset
```

### Crear Roles y Permisos

```bash
# Ejecutar seeder
php artisan db:seed --class=RolePermissionSeeder
```

### Ver Roles de un Usuario

```bash
php artisan tinker

# Ver roles
>>> $user = User::find(1);
>>> $user->getRoleNames();

# Ver permisos
>>> $user->getAllPermissions();

# Ver permisos de un rol
>>> $role = Role::findByName('admin');
>>> $role->permissions;
```

---

## ➕ Agregar Nuevos Roles/Permisos

### 1. Crear Nuevo Rol

```php
use Spatie\Permission\Models\Role;

$role = Role::create(['name' => 'editor']);
```

### 2. Crear Nuevo Permiso

```php
use Spatie\Permission\Models\Permission;

$permission = Permission::create(['name' => 'edit-articles']);
```

### 3. Asignar Permiso a Rol

```php
$role = Role::findByName('editor');
$role->givePermissionTo('edit-articles');

// O múltiples
$role->givePermissionTo(['edit-articles', 'delete-articles']);
```

### 4. Actualizar Seeder

Edita `database/seeders/RolePermissionSeeder.php`:

```php
$permissions = [
    // ... permisos existentes
    'edit-articles',
    'delete-articles',
];

$editorRole = Role::firstOrCreate(['name' => 'editor']);
$editorRole->givePermissionTo(['edit-articles', 'delete-articles']);
```

### 5. Ejecutar Seeder

```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan permission:cache-reset
```

---

## 🔄 Migrar Usuarios Existentes

Si tienes usuarios con la columna `role` antigua:

```php
use App\Models\User;
use Spatie\Permission\Models\Role;

// Migrar todos los usuarios
User::all()->each(function ($user) {
    if ($user->role === 'admin') {
        $user->assignRole('admin');
    } elseif ($user->role === 'cliente') {
        $user->assignRole('cliente');
    }
});
```

O ejecutar en tinker:

```bash
php artisan tinker

>>> User::where('role', 'admin')->get()->each(fn($u) => $u->assignRole('admin'));
>>> User::where('role', 'cliente')->get()->each(fn($u) => $u->assignRole('cliente'));
```

---

## 📊 Estructura de Base de Datos

Spatie crea estas tablas:

- `roles` - Roles del sistema
- `permissions` - Permisos disponibles
- `model_has_roles` - Relación usuarios ↔ roles
- `model_has_permissions` - Permisos directos a usuarios
- `role_has_permissions` - Permisos asignados a roles

---

## 🎯 Ejemplos Prácticos

### Ejemplo 1: Controlador con Permisos

```php
class IntegracionController extends Controller
{
    public function index()
    {
        // Verificar permiso en el método
        $this->authorize('view-integracion');
        
        return view('integracion.index');
    }
    
    public function procesar(Request $request)
    {
        // Verificar permiso
        if (!auth()->user()->can('manage-integracion')) {
            abort(403, 'No tienes permiso');
        }
        
        // Procesar...
    }
}
```

### Ejemplo 2: Vista con Roles

```blade
<nav>
    @role('admin')
        <a href="{{ route('integracion.index') }}">Integración</a>
        <a href="{{ route('warehouse.config') }}">Bodegas</a>
        <a href="{{ route('boletas.index') }}">Boletas</a>
    @endrole
    
    @role('cliente')
        <a href="{{ route('cliente.pedidos') }}">Mis Pedidos</a>
        <a href="{{ route('cliente.facturas') }}">Mis Facturas</a>
    @endrole
</nav>
```

### Ejemplo 3: Rutas Protegidas

```php
// routes/web.php

// Rutas de Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::resource('integracion', IntegracionController::class);
    Route::resource('bodegas', WarehouseController::class);
});

// Rutas de Cliente
Route::middleware(['auth', 'role:cliente'])->prefix('cliente')->group(function () {
    Route::get('/dashboard', [ClienteController::class, 'dashboard']);
    Route::get('/pedidos', [ClienteController::class, 'pedidos']);
    Route::get('/facturas', [ClienteController::class, 'facturas']);
});
```

---

## 📚 Documentación Oficial

- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
- [GitHub Repository](https://github.com/spatie/laravel-permission)

---

## ✅ Ventajas de Usar Spatie

1. **Escalable** - Fácil agregar nuevos roles y permisos
2. **Flexible** - Permisos directos a usuarios o vía roles
3. **Caché** - Sistema de caché para mejor performance
4. **Blade Directives** - Directivas limpias en vistas
5. **Middleware** - Protección de rutas integrada
6. **Estándar** - Usado por miles de proyectos Laravel
7. **Mantenido** - Actualizaciones constantes

---

**Última actualización:** 12 de Diciembre, 2024
