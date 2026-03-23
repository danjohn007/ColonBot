-- ============================================================
-- Migración: Nuevos negocios – Municipio de Colón, Querétaro
-- Aplicar sobre base de datos existente (MySQL 5.7)
-- Coordenadas dentro del rango del municipio:
--   Latitud:  20.45 → 20.85
--   Longitud: -100.20 → -99.75
-- ============================================================

USE `colonbot`;

-- ─── Corrección de coordenadas de negocios existentes ────────────────────────
-- Los negocios insertados originalmente tenían coordenadas fuera del rango
-- del municipio de Colón. Se actualizan a coordenadas correctas.

UPDATE `businesses` SET `lat` = 20.7155, `lng` = -99.9178 WHERE `slug` = 'restaurante-el-mirador';
UPDATE `businesses` SET `lat` = 20.7141, `lng` = -99.9183 WHERE `slug` = 'fonda-dona-lupita';
UPDATE `businesses` SET `lat` = 20.7162, `lng` = -99.9170 WHERE `slug` = 'hotel-plaza-colon';
UPDATE `businesses` SET `lat` = 20.7170, `lng` = -99.9165 WHERE `slug` = 'posada-real-del-centro';
UPDATE `businesses` SET `lat` = 20.6920, `lng` = -99.9650 WHERE `slug` = 'vinedo-la-redonda';
UPDATE `businesses` SET `lat` = 20.6850, `lng` = -99.9580 WHERE `slug` = 'cava-san-miguelito';
UPDATE `businesses` SET `lat` = 20.7165, `lng` = -99.9172 WHERE `slug` = 'ex-convento-de-colon';
UPDATE `businesses` SET `lat` = 20.7164, `lng` = -99.9170 WHERE `slug` = 'parroquia-san-juan-bautista';
UPDATE `businesses` SET `lat` = 20.6500, `lng` = -99.9900 WHERE `slug` = 'hacienda-los-fresnos';
UPDATE `businesses` SET `lat` = 20.6200, `lng` = -100.0400 WHERE `slug` = 'balneario-las-gardenias';

-- ─── Nuevos negocios en el municipio de Colón, Querétaro ─────────────────────
-- Se usa INSERT IGNORE para evitar duplicados en caso de re-ejecución.
-- user_id = 1 (superadmin) para negocios sin administrador propio.

INSERT IGNORE INTO `businesses`
  (`user_id`,`category_id`,`name`,`slug`,`description`,`address`,`lat`,`lng`,`phone`,`whatsapp`,`schedule`,`status`,`featured`,`rating`)
VALUES

-- Restaurantes
(1, 1, 'Restaurante La Terraza Queretana',
 'restaurante-la-terraza-queretana',
 'Cocina tradicional queretana con terraza al aire libre y vista al campo. Especializados en cabrito al pastor y enchiladas mineras.',
 'Blvd. Colón 102, Colón, Qro.', 20.7148, -99.9185,
 '4421100003','5214421100003',
 '{"lun-dom":"08:00-22:00"}',
 'published', 0, 4.55),

(1, 1, 'Tacos El Mezquital',
 'tacos-el-mezquital',
 'Taquería tradicional con los mejores tacos de canasta y guisados de la región. Punto de reunión para lugareños y turistas.',
 'Calle Guerrero 7, Colón, Qro.', 20.7150, -99.9165,
 '4421100004','5214421100004',
 '{"lun-dom":"07:00-15:00"}',
 'published', 0, 4.35),

-- Hoteles
(1, 2, 'Hotel Casa del Marqués',
 'hotel-casa-del-marques',
 'Hotel con arquitectura colonial restaurada, jardines floridos y alberca al aire libre. Perfecto para escapadas románticas y viajes de negocios.',
 'Calle 5 de Mayo 18, Colón, Qro.', 20.7175, -99.9155,
 '4421200003','5214421200003',
 '{"recepcion":"24/7"}',
 'published', 1, 4.65),

-- Viñedos
(1, 3, 'Viñedo El Campanario',
 'vinedo-el-campanario',
 'Viñedo boutique rodeado de cactáceas y mezquites. Producción de vinos blancos y tintos de altura con tours privados y degustaciones maridadas.',
 'Camino a San Pedro km 3, Colón, Qro.', 20.6780, -99.9720,
 '4421300003','5214421300003',
 '{"jue-dom":"10:00-17:00"}',
 'published', 0, 4.60),

-- Sitios Históricos
(1, 4, 'Capilla de La Cañada',
 'capilla-de-la-canada',
 'Pequeña capilla del siglo XIX ubicada en una cañada rodeada de vegetación semiárida. Lugar de peregrinación local con gran valor histórico y espiritual.',
 'Comunidad La Cañada, Colón, Qro.', 20.5400, -100.0950,
 NULL, NULL,
 '{"lun-dom":"08:00-18:00"}',
 'published', 0, 4.45),

(1, 4, 'Museo Regional de Colón',
 'museo-regional-de-colon',
 'Museo que exhibe la historia del municipio desde la época prehispánica hasta el siglo XX. Cuenta con piezas arqueológicas, fotografías históricas y documentos originales.',
 'Calle Allende 5, Centro, Colón, Qro.', 20.7163, -99.9173,
 '4421400002', NULL,
 '{"mar-dom":"09:00-17:00"}',
 'published', 0, 4.50),

-- Experiencias
(1, 5, 'Zipline y Escalada Colón',
 'zipline-escalada-colon',
 'Centro de aventura extrema con tirolesas de hasta 500 metros, escalada en roca natural, rappel y senderismo de montaña. Equipos certificados y guías profesionales.',
 'Sierra de Colón km 12, Colón, Qro.', 20.7800, -100.1200,
 '4421500002','5214421500002',
 '{"sab-dom":"08:00-17:00"}',
 'published', 1, 4.80),

-- Balnearios
(1, 6, 'Balneario El Ojito',
 'balneario-el-ojito',
 'Balneario de aguas naturales con albercas rústicas, área de camping, fogatas y zona para día de campo. Ambiente familiar en entorno natural.',
 'Rancho El Ojito, Mun. Colón, Qro.', 20.5800, -100.0600,
 '4421600002','5214421600002',
 '{"sab-dom":"08:00-18:00","feriados":"08:00-18:00"}',
 'published', 0, 4.20),

-- Artesanías
(1, 7, 'Artesanías El Ópalo',
 'artesanias-el-opalo',
 'Tienda especializada en artesanías locales: tallado de ópalo, joyería artesanal, tapetes de lana y cerámica pintada a mano. Directamente de artesanos del municipio.',
 'Mercado Municipal, Colón, Qro.', 20.7157, -99.9168,
 '4421700001','5214421700001',
 '{"lun-sab":"09:00-19:00","dom":"10:00-15:00"}',
 'published', 1, 4.70),

(1, 7, 'Casa de Artesanías Colón',
 'casa-de-artesanias-colon',
 'Centro de difusión y venta de artesanías elaboradas por productores del municipio. Talleres en vivo de alfarería, tejido y pintura en tela.',
 'Av. Constitución 30, Colón, Qro.', 20.7160, -99.9162,
 '4421700002','5214421700002',
 '{"lun-vie":"09:00-18:00","sab":"09:00-14:00"}',
 'published', 0, 4.40);
