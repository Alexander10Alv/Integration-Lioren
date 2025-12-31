# Design Document

## Overview

El sistema de gestión de usuarios con roles se implementará sobre una aplicación Laravel 9 existente, integrando Laravel Breeze para autenticación y extendiendo el modelo User con funcionalidad de roles. La arquitectura seguirá el patrón MVC de Laravel con middleware personalizado para control de acceso basado en roles.

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      Browser (Cliente)                       │
└───────────────────────────┬─────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Routes (web.php)                          │
│  - Auth Routes (Breeze)                                      │
│  - User Management Routes (Protected)                        │
└───────────────────────────┬─────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Middleware Layer                          │
│  - auth (Laravel)                                            │
│  - CheckRole (Custom)                                        │
└───────────────────────────┬─────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Controllers                               │
│  - AuthenticatedSessionController (Breeze)                   │
│  - UserController (Custom CRUD)                              │
└───────────────────────────┬─────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Models                                    │
│  - User (Extended with role methods)                         │
└───────────────────────────┬─────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│              Database (Al_shopify_integrator)                │
│  - users table (with role column)                            │
└─────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Authentication System (Laravel Breeze)

**Purpose:** Proporcionar funcionalidad de login/logout sin registro público

**Installation:**
- Composer package: `laravel/breeze`
- Stack: Blade with Alpine
- Customization: Remove registration routes and views

**Modified Files:**
- `routes/auth.php` - Remove registration routes
- `resources/views/auth/login.blade.php` - Remove registration links

### 2. Database Schema Extension

**Migration:** `add_role_to_users_table`

```php
Schema::table('users', function (Blueprint $table) {
    $table->enum('role', ['admin', 'cliente'])
          ->default('cliente')
          ->after('email');
});
```

**Rollback Strategy:**
```php
Schema::table('users', function (Blueprint $table) {
    $table->dropColumn('role');
});
```

### 3. User Model Extension

**File:** `app/Models/User.php`

**Additions:**
- Add 'role' to `$fillable` array
- Method `isAdmin(): bool` - Returns true if role is 'admin'
- Method `isCliente(): bool` - Returns true if role is 'cliente'

**Implementation:**
```php
protected $fillable = [
    'name',
    'email',
    'password',
    'role',
];

public function isAdmin(): bool
{
    return $this->role === 'admin';
}

public function isCliente(): bool
{
    return $this->role === 'cliente';
}
```

### 4. Admin Seeder

**File:** `database/seeders/AdminSeeder.php`

**Purpose:** Create initial admin user

**Data:**
- Name: 'Administrador'
- Email: 'admin@admin.com'
- Password: '12345678' (hashed with bcrypt)
- Role: 'admin'

**Execution:** Can be run via `php artisan db:seed --class=AdminSeeder`

### 5. CheckRole Middleware

**File:** `app/Http/Middleware/CheckRole.php`

**Purpose:** Verify user has required role(s) before accessing routes

**Interface:**
```php
public function handle(Request $request, Closure $next, ...$roles)
{
    // Check if user is authenticated
    // Check if user has one of the required roles
    // Return 403 if not authorized
    // Continue to next middleware if authorized
}
```

**Registration:** 
- File: `app/Http/Kernel.php`
- Alias: 'role'
- Location: `$routeMiddleware` array

### 6. UserController (Resource Controller)

**File:** `app/Http/Controllers/UserController.php`

**Methods:**

| Method | Route | Purpose | Validation |
|--------|-------|---------|------------|
| index() | GET /usuarios | List all users | None |
| create() | GET /usuarios/create | Show creation form | None |
| store() | POST /usuarios | Create new user | name, email (unique), password (min:8), role |
| edit() | GET /usuarios/{id}/edit | Show edit form | None |
| update() | PUT /usuarios/{id} | Update user | name, email (unique), role, password (optional, min:8) |
| destroy() | DELETE /usuarios/{id} | Delete user | None |

**Validation Rules:**
```php
// For store
'name' => 'required|string|max:255',
'email' => 'required|string|email|max:255|unique:users',
'password' => 'required|string|min:8',
'role' => 'required|in:admin,cliente'

// For update
'name' => 'required|string|max:255',
'email' => 'required|string|email|max:255|unique:users,email,'.$id,
'password' => 'nullable|string|min:8',
'role' => 'required|in:admin,cliente'
```

**Authorization:** All methods protected by 'auth' and 'role:admin' middleware

### 7. Views Structure

**Directory:** `resources/views/usuarios/`

**Files:**

1. **index.blade.php**
   - Table with columns: Name, Email, Role, Actions
   - Actions: Edit button, Delete button
   - "Create New User" button at top
   - Success/error message display

2. **create.blade.php**
   - Form with fields: Name, Email, Password, Role (select)
   - Submit button
   - Cancel/Back button
   - Validation error display

3. **edit.blade.php**
   - Form with fields: Name, Email, Password (optional), Role (select)
   - Submit button
   - Cancel/Back button
   - Validation error display

**Styling:** All views use Tailwind CSS classes for responsive design

**Layout:** Extend from `layouts.app` (provided by Breeze)

## Data Models

### User Model

```php
class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Custom methods
    public function isAdmin(): bool;
    public function isCliente(): bool;
}
```

**Database Table:** users

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | Primary Key, Auto Increment |
| name | varchar(255) | Not Null |
| email | varchar(255) | Not Null, Unique |
| role | enum('admin','cliente') | Not Null, Default: 'cliente' |
| email_verified_at | timestamp | Nullable |
| password | varchar(255) | Not Null |
| remember_token | varchar(100) | Nullable |
| created_at | timestamp | Nullable |
| updated_at | timestamp | Nullable |

## Error Handling

### Authentication Errors

- **Unauthenticated Access:** Redirect to `/login` (handled by Laravel auth middleware)
- **Invalid Credentials:** Display error message on login form

### Authorization Errors

- **Insufficient Role:** Return HTTP 403 with error view
- **Message:** "No tienes permisos para acceder a esta sección"

### Validation Errors

- **Form Validation:** Display errors above form fields
- **Unique Email Violation:** "El email ya está registrado"
- **Password Length:** "La contraseña debe tener al menos 8 caracteres"
- **Invalid Role:** "El rol seleccionado no es válido"

### CRUD Errors

- **User Not Found:** Return HTTP 404
- **Database Errors:** Catch exceptions and display generic error message
- **Delete Constraint:** Prevent deletion of last admin user (optional enhancement)

## Testing Strategy

### Manual Testing Checklist

1. **Authentication:**
   - Login with admin credentials
   - Logout functionality
   - Verify registration routes are disabled (404)

2. **User Management (as admin):**
   - Create new user with admin role
   - Create new user with cliente role
   - Edit existing user
   - Delete user
   - View user list

3. **Authorization:**
   - Attempt to access /usuarios as unauthenticated user (should redirect to login)
   - Login as cliente and attempt to access /usuarios (should get 403)
   - Login as admin and access /usuarios (should work)

4. **Validation:**
   - Submit form with empty fields
   - Submit form with invalid email
   - Submit form with short password
   - Submit form with duplicate email
   - Submit form with invalid role

5. **Responsive Design:**
   - Test views on desktop
   - Test views on tablet
   - Test views on mobile

### Database Testing

1. Run migrations: `php artisan migrate`
2. Run seeder: `php artisan db:seed --class=AdminSeeder`
3. Verify admin user exists in database
4. Verify role column exists with correct enum values

## Security Considerations

1. **Password Hashing:** All passwords stored using bcrypt
2. **CSRF Protection:** All forms include @csrf token
3. **SQL Injection:** Use Eloquent ORM and parameter binding
4. **XSS Protection:** Blade templates auto-escape output
5. **Role Verification:** Middleware checks on every request
6. **Session Security:** Laravel's built-in session management

## Deployment Notes

### Installation Steps

1. Install Laravel Breeze: `composer require laravel/breeze --dev`
2. Install Breeze scaffolding: `php artisan breeze:install blade`
3. Install NPM dependencies: `npm install`
4. Build assets: `npm run build`
5. Run migrations: `php artisan migrate`
6. Run admin seeder: `php artisan db:seed --class=AdminSeeder`

### Configuration Requirements

- Database connection configured in `.env`
- APP_KEY generated
- Node.js and NPM installed for asset compilation

### Post-Deployment Verification

1. Access `/login` - should display login form
2. Login with admin@admin.com / 12345678
3. Access `/usuarios` - should display user management interface
4. Verify registration routes return 404
