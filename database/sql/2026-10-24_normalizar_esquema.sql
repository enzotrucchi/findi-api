-- =============================================================================
-- Findi · Normalizar esquema (equivalente MySQL de las migraciones)
--   2026_10_24_000001_normalizar_esquema
--   2026_10_24_000002_normalizar_membresias
--
-- Producción no corre `php artisan migrate`: ejecutar este script a mano.
-- Probado sobre stage el 2026-10-24 (MySQL, mismo esquema de partida).
--
-- IMPORTANTE
--   * Hacer backup antes (mysqldump o exportación de phpMyAdmin).
--   * En MySQL los ALTER TABLE hacen commit implícito: no hay rollback.
--     Correr bloque por bloque y revisar el resultado de cada uno.
--   * Desplegar el código nuevo de la API DESPUÉS de correr este script
--     (el código nuevo asume habilitada/es_admin/activo no nulos).
-- =============================================================================


-- -----------------------------------------------------------------------------
-- 0. Verificación previa: confirmar que el esquema de partida es el esperado.
--    Debe devolver exactamente estas columnas/tipos (igual que stage antes):
--      asociados.organizacion_seleccionada_id        int, NULL
--      organizaciones.habilitada                      tinyint, NULL
--      organizaciones.es_prueba                       tinyint, NULL
--      proyectos.organizacion_id                      bigint, NULL
--      proyectos.monto_actual / monto_objetivo        float
--      asociado_organizacion.activo / es_admin        tinyint, NULL
--    Si algo difiere (falta una columna, ya es NOT NULL, etc.), PARAR y revisar.
-- -----------------------------------------------------------------------------
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (TABLE_NAME, COLUMN_NAME) IN (
    ('asociados', 'organizacion_seleccionada_id'),
    ('organizaciones', 'habilitada'),
    ('organizaciones', 'es_prueba'),
    ('proyectos', 'organizacion_id'),
    ('proyectos', 'monto_actual'),
    ('proyectos', 'monto_objetivo'),
    ('asociado_organizacion', 'activo'),
    ('asociado_organizacion', 'es_admin')
  )
ORDER BY TABLE_NAME, COLUMN_NAME;

-- Índices únicos de asociados.email (se espera asociados_pk y asociados_email_unique):
SHOW INDEX FROM asociados WHERE Column_name = 'email';

-- Columnas que en stage NO existían. Si alguna aparece, avisar antes de seguir:
SELECT TABLE_NAME, COLUMN_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (TABLE_NAME, COLUMN_NAME) IN (
    ('asociados', 'es_admin'), ('asociados', 'password'),
    ('modos_pago', 'activo'), ('modos_pago', 'descripcion')
  );
SHOW TABLES LIKE 'users';
SHOW TABLES LIKE 'proyecto_historial';


-- -----------------------------------------------------------------------------
-- 1. organizaciones: habilitada y es_prueba como booleanos no nulos
-- -----------------------------------------------------------------------------
UPDATE organizaciones SET habilitada = 1 WHERE habilitada IS NULL;
UPDATE organizaciones SET es_prueba = 0 WHERE es_prueba IS NULL;

ALTER TABLE organizaciones
  MODIFY es_prueba  TINYINT(1) NOT NULL DEFAULT 0,
  MODIFY habilitada TINYINT(1) NOT NULL DEFAULT 1;


-- -----------------------------------------------------------------------------
-- 2. asociados.organizacion_seleccionada_id: BIGINT UNSIGNED + FK
-- -----------------------------------------------------------------------------
-- Limpia referencias a organizaciones inexistentes (en stage: 0 filas).
UPDATE asociados a
LEFT JOIN organizaciones o ON o.id = a.organizacion_seleccionada_id
SET a.organizacion_seleccionada_id = NULL
WHERE a.organizacion_seleccionada_id IS NOT NULL
  AND o.id IS NULL;

ALTER TABLE asociados
  MODIFY organizacion_seleccionada_id BIGINT UNSIGNED NULL;

ALTER TABLE asociados
  ADD CONSTRAINT asociados_organizacion_seleccionada_id_foreign
    FOREIGN KEY (organizacion_seleccionada_id) REFERENCES organizaciones (id)
    ON DELETE SET NULL;


-- -----------------------------------------------------------------------------
-- 3. proyectos: organizacion_id BIGINT UNSIGNED + índice + FK; montos a DECIMAL
-- -----------------------------------------------------------------------------
-- Proyectos con organización inexistente se desvinculan (en stage: 0 filas).
UPDATE proyectos p
LEFT JOIN organizaciones o ON o.id = p.organizacion_id
SET p.organizacion_id = NULL
WHERE p.organizacion_id IS NOT NULL
  AND o.id IS NULL;

ALTER TABLE proyectos
  MODIFY organizacion_id BIGINT UNSIGNED NULL,
  ADD INDEX proyectos_organizacion_id_index (organizacion_id);

ALTER TABLE proyectos
  ADD CONSTRAINT proyectos_organizacion_id_foreign
    FOREIGN KEY (organizacion_id) REFERENCES organizaciones (id)
    ON DELETE CASCADE;

UPDATE proyectos SET monto_actual = 0 WHERE monto_actual IS NULL;
UPDATE proyectos SET monto_objetivo = 0 WHERE monto_objetivo IS NULL;

ALTER TABLE proyectos
  MODIFY monto_actual   DECIMAL(12,2) NOT NULL DEFAULT 0,
  MODIFY monto_objetivo DECIMAL(12,2) NOT NULL DEFAULT 0;


-- -----------------------------------------------------------------------------
-- 4. asociados.email: eliminar el índice único duplicado
--    (solo si el paso 0 mostró asociados_pk Y asociados_email_unique)
-- -----------------------------------------------------------------------------
ALTER TABLE asociados DROP INDEX asociados_pk;


-- -----------------------------------------------------------------------------
-- 5. Tabla users sin uso (la autenticación usa asociados)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS users;


-- -----------------------------------------------------------------------------
-- 6. asociado_organizacion: activo / es_admin como booleanos no nulos
--    (la baja de asociados guardaba activo = NULL: equivale a inactivo)
-- -----------------------------------------------------------------------------
UPDATE asociado_organizacion SET activo = 0 WHERE activo IS NULL;
UPDATE asociado_organizacion SET es_admin = 0 WHERE es_admin IS NULL;

ALTER TABLE asociado_organizacion
  MODIFY activo   TINYINT(1) NOT NULL DEFAULT 1,
  MODIFY es_admin TINYINT(1) NOT NULL DEFAULT 0;


-- -----------------------------------------------------------------------------
-- 7. Registrar las migraciones como corridas (para que Laravel no las repita)
-- -----------------------------------------------------------------------------
INSERT INTO migrations (migration, batch)
SELECT '2026_10_24_000001_normalizar_esquema', COALESCE(MAX(batch), 0) + 1 FROM migrations;

INSERT INTO migrations (migration, batch)
SELECT '2026_10_24_000002_normalizar_membresias', MAX(batch) + 1 FROM migrations;


-- -----------------------------------------------------------------------------
-- 8. Verificación posterior
-- -----------------------------------------------------------------------------
SHOW CREATE TABLE organizaciones;
SHOW CREATE TABLE asociados;
SHOW CREATE TABLE proyectos;
SHOW CREATE TABLE asociado_organizacion;
SELECT migration, batch FROM migrations ORDER BY id DESC LIMIT 2;
