<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// User Management Routes - Only accessible by admin role
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('usuarios', UserController::class);
});

// Clientes CRUD - Only accessible by admin role
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('clientes', App\Http\Controllers\AdminClienteController::class);
});

// Planes CRUD - Only accessible by admin role
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('planes', App\Http\Controllers\PlanController::class);
});

// Integración Shopify-Lioren Routes - SOLO ADMIN
Route::middleware(['auth', 'role:admin'])->prefix('integracion')->name('integracion.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\IntegracionController::class, 'dashboard'])->name('dashboard');
    Route::get('/', [App\Http\Controllers\IntegracionController::class, 'index'])->name('index');
    Route::post('/procesar', [App\Http\Controllers\IntegracionController::class, 'procesar'])->name('procesar');
    Route::get('/productos', [App\Http\Controllers\IntegracionController::class, 'productos'])->name('productos');
    Route::get('/productos-lioren', [App\Http\Controllers\IntegracionController::class, 'productosLioren'])->name('productos-lioren');
    Route::get('/estado', [App\Http\Controllers\IntegracionController::class, 'estado'])->name('estado');
    Route::get('/resetear', [App\Http\Controllers\IntegracionController::class, 'confirmarReset'])->name('resetear');
    Route::delete('/resetear', [App\Http\Controllers\IntegracionController::class, 'resetearIntegracion'])->name('resetear.ejecutar');
});

// Boletas Routes - SOLO ADMIN
Route::middleware(['auth', 'role:admin'])->prefix('boletas')->name('boletas.')->group(function () {
    Route::get('/', [App\Http\Controllers\IntegracionController::class, 'boletas'])->name('index');
    Route::get('/emitir', [App\Http\Controllers\IntegracionController::class, 'boletasForm'])->name('form');
    Route::post('/emitir', [App\Http\Controllers\IntegracionController::class, 'emitirBoleta'])->name('emitir');
});

// Configuración de Bodegas/Locations - SOLO ADMIN
Route::middleware(['auth', 'role:admin'])->prefix('warehouse')->name('warehouse.')->group(function () {
    Route::get('/config', [App\Http\Controllers\WarehouseConfigController::class, 'index'])->name('config');
    Route::post('/config/simple', [App\Http\Controllers\WarehouseConfigController::class, 'configureSimple'])->name('config.simple');
    Route::post('/config/advanced', [App\Http\Controllers\WarehouseConfigController::class, 'configureAdvanced'])->name('config.advanced');
    Route::post('/config/mapping', [App\Http\Controllers\WarehouseConfigController::class, 'createMapping'])->name('config.mapping');
    Route::delete('/config/mapping/{locationId}', [App\Http\Controllers\WarehouseConfigController::class, 'deleteMapping'])->name('config.mapping.delete');
    Route::get('/config/refresh-bodegas', [App\Http\Controllers\WarehouseConfigController::class, 'refreshBodegas'])->name('config.refresh');
});

// Boletas PDF/XML - SOLO ADMIN
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/boletas/{id}/pdf', [App\Http\Controllers\IntegracionController::class, 'boletaPdf'])->name('boletas.pdf');
    Route::get('/boletas/{id}/xml', [App\Http\Controllers\IntegracionController::class, 'boletaXml'])->name('boletas.xml');
});

// Rutas para CLIENTES
Route::middleware(['auth', 'role:cliente'])->prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\ClienteController::class, 'dashboard'])->name('dashboard');
    Route::get('/planes', [App\Http\Controllers\ClienteController::class, 'planes'])->name('planes');
    Route::get('/estados-solicitud', [App\Http\Controllers\ClienteController::class, 'estadosSolicitud'])->name('estados-solicitud');
    Route::get('/planes-activos', [App\Http\Controllers\ClienteController::class, 'planesActivos'])->name('planes-activos');
    Route::get('/facturas', [App\Http\Controllers\ClienteController::class, 'facturas'])->name('facturas');
    
    // Chats
    Route::get('/chats', [App\Http\Controllers\ChatController::class, 'index'])->name('chats');
    Route::post('/chats', [App\Http\Controllers\ChatController::class, 'store'])->name('chats.store');
    
    // Solicitudes
    Route::post('/solicitudes', [App\Http\Controllers\SolicitudController::class, 'store'])->name('solicitudes.store');
});

// Rutas de Chat (compartidas entre admin y cliente)
Route::middleware(['auth'])->group(function () {
    Route::get('/chats/{chat}', [App\Http\Controllers\ChatController::class, 'show'])->name('chats.show');
    Route::post('/chats/{chat}/messages', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chats.sendMessage');
    Route::get('/chats/{chat}/new-messages', [App\Http\Controllers\ChatController::class, 'getNewMessages'])->name('chats.getNewMessages');
    Route::post('/chats/{chat}/close', [App\Http\Controllers\ChatController::class, 'close'])->name('chats.close');
});

// Rutas de Chat para ADMIN
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/chats', [App\Http\Controllers\ChatController::class, 'adminIndex'])->name('chats');
    Route::get('/chats/unread-count', [App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chats.unreadCount');
    Route::get('/solicitudes', [App\Http\Controllers\SolicitudController::class, 'index'])->name('solicitudes');
    Route::get('/solicitudes/{solicitud}', [App\Http\Controllers\SolicitudController::class, 'show'])->name('solicitudes.show');
    Route::post('/solicitudes/{solicitud}/estado', [App\Http\Controllers\SolicitudController::class, 'updateEstado'])->name('solicitudes.updateEstado');
});

// Webhook receiver - Public route (no auth required)
Route::post('/integracion/webhook-receiver', [App\Http\Controllers\IntegracionController::class, 'webhookReceiver'])->name('integracion.webhook');

require __DIR__.'/auth.php';
