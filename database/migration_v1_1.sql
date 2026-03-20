-- ============================================================
-- Migración v1.1 – Plataforma Turística Colón
-- Aplica sobre la base de datos: idactivo_colonbot
-- ============================================================
-- Este script corrige:
--   1. Negocios publicados sin coordenadas (les asigna las del
--      centro de Colón para que aparezcan en el mapa).
--   2. Negocios sin slug (les genera uno a partir del nombre).
--   3. Garantiza que el slug sea único añadiendo el ID como
--      sufijo cuando hay colisión.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '-06:00';

USE `idactivo_colonbot`;

-- ─── 1. Asignar coordenadas por defecto a negocios publicados sin ubicación ─
--        (Centro de Colón, Querétaro: 20.7272, -100.0403)
UPDATE `businesses`
SET
    `lat` = 20.7272000,
    `lng` = -100.0403000
WHERE
    `status` = 'published'
    AND (`lat` IS NULL OR `lat` = 0)
    AND (`lng` IS NULL OR `lng` = 0);

-- ─── 2. Generar slug para negocios que no lo tengan ──────────────────────────
--   Crea un slug básico (lowercase, sin acentos comunes) y añade el ID para
--   garantizar unicidad. Ajusta la función si necesitas mayor sofisticación.
UPDATE `businesses`
SET `slug` = CONCAT(
    LOWER(
        REPLACE(
        REPLACE(
        REPLACE(
        REPLACE(
        REPLACE(
        REPLACE(
        REPLACE(
        REPLACE(
        REPLACE(
        REPLACE(
        REPLACE(
        REPLACE(
            REGEXP_REPLACE(
                REGEXP_REPLACE(
                    LOWER(`name`),
                    '[^a-z0-9\\s-]', ''
                ),
                '[\\s-]+', '-'
            ),
        'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),
        'ä','a'),'ë','e'),'ï','i'),'ö','o'),'ü','u'),
        'ñ','n'),'ç','c')
    ),
    '-',
    `id`
)
WHERE `slug` IS NULL OR `slug` = '';

-- ─── 3. Confirmar operación ──────────────────────────────────────────────────
SELECT
    COUNT(*) AS negocios_sin_coordenadas
FROM `businesses`
WHERE `lat` IS NULL OR `lat` = 0 OR `lng` IS NULL OR `lng` = 0;

SELECT
    COUNT(*) AS negocios_sin_slug
FROM `businesses`
WHERE `slug` IS NULL OR `slug` = '';
