# 📋 Instrucciones para Implementar Módulo de Suscripciones

## 1️⃣ Ejecutar Consultas SQL

Abre tu cliente MySQL (phpMyAdmin, MySQL Workbench, etc.) y ejecuta el archivo:
```
database_suscripciones.sql
```

Esto creará:
- ✅ Tabla `suscripciones`
- ✅ Campos nuevos en `payments`: `suscripcion_id`, `periodo_inicio`, `periodo_fin`

---

## 2️⃣ Agregar Rutas al Sistema

Agrega estas rutas en `routes/web.php`:

```php
// Rutas de Suscripciones para CLIENTES
Route::middleware(['auth', 'role:cliente'])->prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/suscripciones', [App\Http\Controllers\SuscripcionController::class, 'index'])->name('suscripciones');
    Route::get('/suscripciones/{suscripcion}/renovar', [App\Http\Controllers\SuscripcionController::class, 'renovar'])->name('suscripciones.renovar');
    Route::delete('/suscripciones/{suscripcion}/cancelar', [App\Http\Controllers\SuscripcionController::class, 'cancelar'])->name('suscripciones.cancelar');
});

// Rutas de Suscripciones para ADMIN
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/suscripciones', [App\Http\Controllers\SuscripcionController::class, 'admin'])->name('suscripciones');
});
```

---

## 3️⃣ Configurar el Cron Job en el Servidor

### En Desarrollo (tu PC):
Ejecuta manualmente cuando quieras probar:
```bash
php artisan suscripciones:verificar-vencimientos
```

O simula el cron con:
```bash
php artisan schedule:work
```

### En Producción (servidor):
Agrega esta línea al crontab del servidor (solo UNA vez):
```bash
* * * * * cd /ruta/a/tu/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Para editar el crontab:
```bash
crontab -e
```

---

## 4️⃣ Agregar Links en el Menú de Navegación

Edita `resources/views/layouts/navigation.blade.php`:

### Para Clientes:
```blade
@role('cliente')
    <x-nav-link :href="route('cliente.suscripciones')" :active="request()->routeIs('cliente.suscripciones')">
        Mi Suscripción
    </x-nav-link>
@endrole
```

### Para Admin:
```blade
@role('admin')
    <x-nav-link :href="route('admin.suscripciones')" :active="request()->routeIs('admin.suscripciones')">
        Suscripciones
    </x-nav-link>
@endrole
```

---

## 5️⃣ Probar el Sistema

### Flujo Completo:

1. **Cliente paga un plan:**
   - Va a "Planes" → Selecciona un plan → Paga con Flow
   - Flow confirma el pago → Se crea automáticamente la suscripción

2. **Ver suscripción activa:**
   - Cliente: `/cliente/suscripciones`
   - Admin: `/admin/suscripciones`

3. **Renovar suscripción:**
   - Cliente hace clic en "Renovar Ahora"
   - Paga nuevamente → La suscripción se extiende 30 días más

4. **Verificar vencimientos (automático):**
   - El comando corre diario a las 00:00
   - Marca como "vencida" las suscripciones no renovadas

---

## 6️⃣ Verificar que Todo Funciona

### Consultas SQL de Verificación:

```sql
-- Ver todas las suscripciones
SELECT * FROM suscripciones;

-- Ver pagos con suscripción
SELECT 
    p.id,
    p.order_id,
    p.amount,
    p.status,
    s.estado as suscripcion_estado,
    pl.nombre as plan_nombre
FROM payments p
LEFT JOIN suscripciones s ON p.suscripcion_id = s.id
LEFT JOIN planes pl ON s.plan_id = pl.id
ORDER BY p.created_at DESC;

-- Ver suscripciones próximas a vencer (7 días)
SELECT 
    s.id,
    u.name as cliente,
    pl.nombre as plan,
    s.proximo_pago,
    DATEDIFF(s.proximo_pago, CURDATE()) as dias_restantes
FROM suscripciones s
JOIN users u ON s.user_id = u.id
JOIN planes pl ON s.plan_id = pl.id
WHERE s.estado = 'activa'
AND s.proximo_pago BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
ORDER BY s.proximo_pago ASC;
```

---

## 📊 Estructura de Datos

### Tabla `suscripciones`:
- `id`: ID único
- `user_id`: Cliente dueño de la suscripción
- `plan_id`: Plan contratado
- `estado`: activa | vencida | cancelada
- `fecha_inicio`: Cuándo empezó
- `fecha_fin`: Cuándo termina el período actual
- `proximo_pago`: Fecha límite para renovar

### Tabla `payments` (campos nuevos):
- `suscripcion_id`: A qué suscripción pertenece este pago
- `periodo_inicio`: Inicio del período pagado
- `periodo_fin`: Fin del período pagado

---

## 🔄 Flujo de Renovación

```
Día 1: Cliente paga → Suscripción creada
  - fecha_inicio: 2026-01-17
  - fecha_fin: 2026-02-16
  - proximo_pago: 2026-02-16
  - estado: activa

Día 30 (2026-02-16): 
  - Si NO paga → Command marca como "vencida"
  - Si SÍ paga → Suscripción se extiende:
    - fecha_fin: 2026-03-18
    - proximo_pago: 2026-03-18
    - estado: activa
```

---

## ✅ Checklist Final

- [ ] Ejecutar `database_suscripciones.sql`
- [ ] Agregar rutas en `routes/web.php`
- [ ] Configurar cron job en servidor (producción)
- [ ] Agregar links en navegación
- [ ] Probar pago de plan
- [ ] Verificar que se crea la suscripción
- [ ] Probar renovación
- [ ] Ejecutar comando manualmente: `php artisan suscripciones:verificar-vencimientos`

---

¡Listo! Tu sistema de suscripciones está completo 🎉
