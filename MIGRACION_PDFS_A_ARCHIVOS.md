# 📁 Migración de PDFs de Base de Datos a Archivos

## 🎯 Objetivo
Cambiar el almacenamiento de PDFs y XMLs desde campos `TEXT/LONGTEXT` en la base de datos a archivos en el sistema de archivos del servidor.

## ✅ Ventajas
- Base de datos más liviana y rápida
- Backups más eficientes
- Mejor rendimiento en consultas
- Más fácil servir archivos estáticos
- Sin límites de tamaño en campos TEXT

## 📋 Cambios Realizados

### 1. Migración de Base de Datos
Se agregaron nuevos campos a las tablas:
- `pdf_path` - Ruta del archivo PDF
- `xml_path` - Ruta del archivo XML

**Tablas afectadas:**
- `boletas`
- `notas_credito`
- `facturas_emitidas`

### 2. Modelos Actualizados
Se agregaron métodos helper en los modelos:
- `Boleta::savePdfFromBase64()`
- `NotaCredito::savePdfFromBase64()`
- `FacturaEmitida::savePdfFromBase64()`

### 3. Estructura de Carpetas
Los archivos se guardan en:
```
storage/app/
├── boletas/
│   ├── 2026/
│   │   ├── 01/
│   │   │   ├── boleta_12345_1.pdf
│   │   │   ├── boleta_12345_1.xml
├── notas_credito/
│   ├── 2026/
│   │   ├── 01/
│   │   │   ├── nc_67890_1.pdf
├── facturas/
    ├── 2026/
        ├── 01/
            ├── factura_11111_1.pdf
```

## 🚀 Pasos para Aplicar la Migración

### Paso 1: Ejecutar la migración de base de datos
```bash
php artisan migrate
```

### Paso 2: Migrar PDFs existentes de BD a archivos
```bash
php artisan pdfs:migrate-to-files
```

Este comando:
- Lee todos los registros con `pdf_base64` pero sin `pdf_path`
- Decodifica el base64 y guarda el archivo
- Actualiza el campo `pdf_path` con la ruta del archivo
- **NO elimina** los campos `pdf_base64` (por seguridad)

### Paso 3: Verificar que todo funciona
1. Accede a la vista de boletas/notas de crédito
2. Descarga algunos PDFs para verificar que funcionan
3. Revisa los logs en `storage/logs/laravel.log`

### Paso 4 (Opcional): Limpiar campos base64
Una vez verificado que todo funciona correctamente, puedes eliminar los datos base64 para liberar espacio:

```sql
-- ⚠️ SOLO DESPUÉS DE VERIFICAR QUE TODO FUNCIONA
UPDATE boletas SET pdf_base64 = NULL, xml_base64 = NULL WHERE pdf_path IS NOT NULL;
UPDATE notas_credito SET pdf_base64 = NULL, xml_base64 = NULL WHERE pdf_path IS NOT NULL;
UPDATE facturas_emitidas SET pdf_base64 = NULL, xml_base64 = NULL WHERE pdf_path IS NOT NULL;
```

### Paso 5 (Opcional): Eliminar columnas base64
Si quieres eliminar completamente las columnas (después de varios días de pruebas):

```sql
ALTER TABLE boletas DROP COLUMN pdf_base64, DROP COLUMN xml_base64;
ALTER TABLE notas_credito DROP COLUMN pdf_base64, DROP COLUMN xml_base64;
ALTER TABLE facturas_emitidas DROP COLUMN pdf_base64, DROP COLUMN xml_base64;
```

## 🔄 Compatibilidad con Datos Antiguos

El código mantiene **compatibilidad hacia atrás**:
- Si existe `pdf_path`, se usa el archivo
- Si NO existe `pdf_path` pero SÍ existe `pdf_base64`, se usa el base64
- Esto permite una migración gradual sin romper nada

## 📊 Estimación de Espacio Liberado

Ejemplo con 1000 boletas:
- PDF promedio: 200 KB en base64 = 200 MB en BD
- Después de migrar: 150 KB por archivo = 150 MB en disco
- **Ahorro en BD: ~200 MB**
- Consultas más rápidas: ~30-50% mejora

## ⚠️ Consideraciones

1. **Backups**: Asegúrate de incluir `storage/app/` en tus backups
2. **Permisos**: Verifica que Laravel tenga permisos de escritura en `storage/app/`
3. **Espacio en disco**: Asegúrate de tener suficiente espacio
4. **Rollback**: Los campos `pdf_base64` se mantienen por si necesitas revertir

## 🧪 Testing

Prueba estos escenarios:
1. ✅ Crear nueva boleta → debe guardar en archivo
2. ✅ Ver boleta antigua (con base64) → debe funcionar
3. ✅ Ver boleta nueva (con archivo) → debe funcionar
4. ✅ Descargar PDF → debe funcionar en ambos casos

## 📝 Notas Técnicas

- Los archivos se organizan por año/mes para mejor organización
- Los nombres incluyen folio e ID para evitar colisiones
- Se usa `Storage::put()` de Laravel para compatibilidad con diferentes drivers (local, S3, etc.)
- El código es compatible con almacenamiento en la nube (S3, DigitalOcean Spaces, etc.)
