<?php
/**
 * Archivo de Configuración de Ejemplo
 * 
 * INSTRUCCIONES:
 * 1. Renombra este archivo a "config.php"
 * 2. Completa tus credenciales reales
 * 3. Asegúrate de que config.php esté en .gitignore para no exponer credenciales
 */

// ============================================
// CONFIGURACIÓN DE SHOPIFY
// ============================================

// Nombre de tu tienda Shopify (ejemplo: mi-tienda.myshopify.com)
define('SHOPIFY_TIENDA', 'tu-tienda.myshopify.com');

// Access Token de tu app personalizada de Shopify
// Lo obtienes desde: Admin > Apps > Develop apps > [Tu App] > API credentials
define('SHOPIFY_ACCESS_TOKEN', 'shpat_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

// API Secret Key de tu app de Shopify
// Se usa para validar la firma HMAC de los webhooks
// Lo encuentras en el mismo lugar que el Access Token
define('SHOPIFY_API_SECRET', 'shpss_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

// Versión de la API de Shopify a utilizar
define('SHOPIFY_API_VERSION', '2024-01');


// ============================================
// CONFIGURACIÓN DE LIOREN
// ============================================

// API Key (Bearer Token) de Lioren
// Lo obtienes desde tu panel de Lioren en la sección de API
define('LIOREN_API_KEY', 'tu_api_key_de_lioren_aqui');

// URL base de la API de Lioren
define('LIOREN_API_URL', 'https://www.lioren.cl/api/v1/');


// ============================================
// CONFIGURACIÓN DE WEBHOOKS
// ============================================

// URL pública donde Shopify enviará los webhooks
// Debe ser una URL accesible desde internet (no localhost)
// Ejemplo: https://tudominio.com/prueba_integracion/webhook_receiver.php
define('WEBHOOK_URL', 'https://tudominio.com/prueba_integracion/webhook_receiver.php');


// ============================================
// CONFIGURACIÓN DE LOGS
// ============================================

// Directorio donde se guardarán los logs
define('LOG_DIR', __DIR__ . '/logs/');

// Nivel de detalle de los logs
// Opciones: 'debug', 'info', 'warning', 'error'
define('LOG_LEVEL', 'info');


// ============================================
// CONFIGURACIÓN DE SINCRONIZACIÓN
// ============================================

// Número máximo de productos a sincronizar en la configuración inicial
define('MAX_PRODUCTOS_SYNC', 10);

// Tiempo de espera para peticiones HTTP (en segundos)
define('HTTP_TIMEOUT', 30);


// ============================================
// NOTAS IMPORTANTES
// ============================================

/*
 * CÓMO OBTENER TUS CREDENCIALES DE SHOPIFY:
 * 
 * 1. Inicia sesión en tu tienda de Shopify
 * 2. Ve a: Settings > Apps and sales channels > Develop apps
 * 3. Crea una nueva app o selecciona una existente
 * 4. En "Configuration", configura los permisos necesarios:
 *    - read_products, write_products
 *    - read_orders, write_orders
 *    - read_inventory, write_inventory
 * 5. En "API credentials", encontrarás:
 *    - Admin API access token (SHOPIFY_ACCESS_TOKEN)
 *    - API secret key (SHOPIFY_API_SECRET)
 * 
 * CÓMO OBTENER TU API KEY DE LIOREN:
 * 
 * 1. Inicia sesión en tu cuenta de Lioren
 * 2. Ve a la sección de configuración o API
 * 3. Genera o copia tu API Key
 * 4. Guárdala de forma segura
 * 
 * CONFIGURAR WEBHOOK_URL:
 * 
 * - Si estás en desarrollo local, necesitas exponer tu servidor local
 *   usando herramientas como ngrok, localtunnel, o similar
 * - Ejemplo con ngrok: ngrok http 80
 * - Usa la URL pública que te proporcione + /prueba_integracion/webhook_receiver.php
 * 
 * SEGURIDAD:
 * 
 * - NUNCA subas este archivo con credenciales reales a un repositorio público
 * - Agrega config.php a tu .gitignore
 * - Mantén tus credenciales seguras y no las compartas
 * - Rota tus tokens periódicamente
 */

?>
