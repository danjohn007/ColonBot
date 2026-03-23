-- ============================================================
-- Datos de ejemplo – Estado de Querétaro
-- Plataforma Turística Interactiva – Municipio de Colón
-- ============================================================

USE `colonbot`;

-- ─── Usuarios ─────────────────────────────────────────────────────────────
-- Contraseña por defecto: Admin@2024
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `active`) VALUES
('Super Administrador', 'superadmin@colonbot.mx', '$2y$12$YHa5lNW4e4DkbbAE5OHQ7.V2eN1k9vWfBgmcBb9DYy3cT4F8LRDM6', 'superadmin', '4421000001', 1),
('Admin Restaurante El Mirador', 'admin.mirador@colonbot.mx', '$2y$12$YHa5lNW4e4DkbbAE5OHQ7.V2eN1k9vWfBgmcBb9DYy3cT4F8LRDM6', 'admin', '4421000002', 1),
('Admin Viñedo La Redonda', 'admin.redonda@colonbot.mx', '$2y$12$YHa5lNW4e4DkbbAE5OHQ7.V2eN1k9vWfBgmcBb9DYy3cT4F8LRDM6', 'admin', '4421000003', 1),
('Admin Hotel Plaza Colón', 'admin.plaza@colonbot.mx', '$2y$12$YHa5lNW4e4DkbbAE5OHQ7.V2eN1k9vWfBgmcBb9DYy3cT4F8LRDM6', 'admin', '4421000004', 1),
('Admin Hacienda Los Fresnos', 'admin.fresnos@colonbot.mx', '$2y$12$YHa5lNW4e4DkbbAE5OHQ7.V2eN1k9vWfBgmcBb9DYy3cT4F8LRDM6', 'admin', '4421000005', 1);

-- Nota: contraseña = Admin@2024 (hash bcrypt cost 12)

-- ─── Categorías ───────────────────────────────────────────────────────────
INSERT INTO `categories` (`name`, `slug`, `icon`, `color`, `sort_order`) VALUES
('Restaurantes',           'restaurantes',   'utensils',       '#EF4444', 1),
('Hoteles',                'hoteles',        'hotel',          '#3B82F6', 2),
('Viñedos',                'vinedos',        'wine',           '#7C3AED', 3),
('Sitios Históricos',      'historicos',     'landmark',       '#D97706', 4),
('Experiencias Turísticas','experiencias',   'star',           '#10B981', 5),
('Balnearios',             'balnearios',     'waves',          '#0EA5E9', 6),
('Artesanías',             'artesanias',     'shopping-bag',   '#EC4899', 7);

-- ─── Amenidades globales ──────────────────────────────────────────────────
INSERT INTO `amenities` (`name`, `icon`) VALUES
('WiFi Gratis',              'wifi'),
('Estacionamiento',          'parking'),
('Pet Friendly',             'paw'),
('Acceso a discapacitados',  'wheelchair'),
('Aire Acondicionado',       'wind'),
('Piscina',                  'waves'),
('Bar',                      'glass-martini'),
('Restaurante en sitio',     'utensils'),
('Admite tarjetas',          'credit-card'),
('Reservaciones',            'calendar'),
('Guía de turismo',          'user-tie'),
('Vista panorámica',         'mountain');

-- ─── Negocios (lugares turísticos del municipio de Colón, Querétaro) ─────
-- Coordenadas dentro del rango del municipio: lat 20.45–20.85, lng -100.20–-99.75
INSERT INTO `businesses`
  (`user_id`,`category_id`,`name`,`slug`,`description`,`address`,`lat`,`lng`,`phone`,`whatsapp`,`schedule`,`status`,`featured`,`rating`) VALUES

-- Restaurantes
(2, 1, 'Restaurante El Mirador',
 'restaurante-el-mirador',
 'Auténtica cocina queretana con vista panorámica al centro histórico de Colón. Especialidad en carnitas y barbacoa de olla estilo Querétaro.',
 'Av. Hidalgo 45, Centro, Colón, Qro.', 20.7155, -99.9178,
 '4421100001','5214421100001',
 '{"lun-vie":"09:00-21:00","sab":"09:00-22:00","dom":"10:00-20:00"}',
 'published', 1, 4.70),

(2, 1, 'Fonda Doña Lupita',
 'fonda-dona-lupita',
 'Comida casera y antojitos mexicanos preparados con recetas tradicionales de la región queretana. Ideal para desayunos y comidas.',
 'Calle Morelos 12, Col. Centro, Colón, Qro.', 20.7141, -99.9183,
 '4421100002','5214421100002',
 '{"lun-dom":"08:00-18:00"}',
 'published', 0, 4.40),

-- Hoteles
(4, 2, 'Hotel Plaza Colón',
 'hotel-plaza-colon',
 'Hotel boutique en el corazón de Colón con 24 habitaciones equipadas, desayuno incluido y sala de eventos. La mejor opción para descanso en Querétaro.',
 'Juárez 8, Centro, Colón, Qro.', 20.7162, -99.9170,
 '4421200001','5214421200001',
 '{"recepcion":"24/7"}',
 'published', 1, 4.60),

(4, 2, 'Posada Real del Centro',
 'posada-real-del-centro',
 'Acogedora posada colonial con jardín interior y alberca climatizada. Habitaciones con decoración artesanal queretana.',
 'Independencia 23, Colón, Qro.', 20.7170, -99.9165,
 '4421200002','5214421200002',
 '{"recepcion":"24/7"}',
 'published', 0, 4.20),

-- Viñedos
(3, 3, 'Viñedo La Redonda',
 'vinedo-la-redonda',
 'Viñedo familiar con más de 40 años de tradición. Ofrece catas de vino artesanal, recorridos por los viñedos y venta de productos locales.',
 'Carr. Colón-Jalpan km 5, Qro.', 20.6920, -99.9650,
 '4421300001','5214421300001',
 '{"sab-dom":"10:00-18:00"}',
 'published', 1, 4.80),

(3, 3, 'Cava San Miguelito',
 'cava-san-miguelito',
 'Pequeña producción artesanal de vinos tintos y rosados. Tour guiado con degustación y maridaje con quesos de la región.',
 'Rancho San Miguelito, Colón, Qro.', 20.6850, -99.9580,
 '4421300002','5214421300002',
 '{"vie-dom":"11:00-17:00"}',
 'published', 0, 4.50),

-- Sitios Históricos
(1, 4, 'Ex Convento de Colón',
 'ex-convento-de-colon',
 'Monumento histórico del siglo XVII. Uno de los íconos arquitectónicos más importantes del municipio de Colón, Querétaro.',
 'Plaza Principal s/n, Colón, Qro.', 20.7165, -99.9172,
 '4421400001', NULL,
 '{"lun-dom":"09:00-18:00"}',
 'published', 1, 4.90),

(1, 4, 'Parroquia de San Juan Bautista',
 'parroquia-san-juan-bautista',
 'Hermosa parroquia colonial que data del siglo XVIII. Declarada patrimonio histórico del estado de Querétaro.',
 'Jardín Principal, Colón, Qro.', 20.7164, -99.9170,
 NULL, NULL,
 '{"lun-dom":"07:00-20:00"}',
 'published', 0, 4.70),

-- Experiencias
(5, 5, 'Hacienda Los Fresnos – Turismo Rural',
 'hacienda-los-fresnos',
 'Experiencia de turismo rural en hacienda restaurada: paseos a caballo, tirolesa, senderismo y gastronomía local. El plan perfecto para toda la familia.',
 'Carr. Colón-Huimilpan km 8, Qro.', 20.6500, -99.9900,
 '4421500001','5214421500001',
 '{"sab-dom":"09:00-18:00"}',
 'published', 1, 4.75),

-- Balnearios
(1, 6, 'Balneario Las Gardenias',
 'balneario-las-gardenias',
 'Balneario familiar con albercas termales de agua natural, toboganes, zona de juegos infantiles y área de camping.',
 'Carr. Federal 57, km 12, Colón, Qro.', 20.6200, -100.0400,
 '4421600001','5214421600001',
 '{"sab-dom":"09:00-18:00","feriados":"09:00-18:00"}',
 'published', 0, 4.30),

-- Restaurantes (adicionales)
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

-- Hoteles (adicionales)
(1, 2, 'Hotel Casa del Marqués',
 'hotel-casa-del-marques',
 'Hotel con arquitectura colonial restaurada, jardines floridos y alberca al aire libre. Perfecto para escapadas románticas y viajes de negocios.',
 'Calle 5 de Mayo 18, Colón, Qro.', 20.7175, -99.9155,
 '4421200003','5214421200003',
 '{"recepcion":"24/7"}',
 'published', 1, 4.65),

-- Viñedos (adicionales)
(1, 3, 'Viñedo El Campanario',
 'vinedo-el-campanario',
 'Viñedo boutique rodeado de cactáceas y mezquites. Producción de vinos blancos y tintos de altura con tours privados y degustaciones maridadas.',
 'Camino a San Pedro km 3, Colón, Qro.', 20.6780, -99.9720,
 '4421300003','5214421300003',
 '{"jue-dom":"10:00-17:00"}',
 'published', 0, 4.60),

-- Sitios Históricos (adicionales)
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

-- Experiencias (adicionales)
(1, 5, 'Zipline y Escalada Colón',
 'zipline-escalada-colon',
 'Centro de aventura extrema con tirolesas de hasta 500 metros, escalada en roca natural, rappel y senderismo de montaña. Equipos certificados y guías profesionales.',
 'Sierra de Colón km 12, Colón, Qro.', 20.7800, -100.1200,
 '4421500002','5214421500002',
 '{"sab-dom":"08:00-17:00"}',
 'published', 1, 4.80),

-- Balnearios (adicionales)
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

-- ─── Imágenes de ejemplo (rutas ficticias, se reemplazarán con uploads) ──
INSERT INTO `business_images` (`business_id`, `path`, `caption`, `sort_order`) VALUES
(1, 'default/restaurante1.jpg', 'Vista exterior', 0),
(1, 'default/restaurante2.jpg', 'Platillos típicos', 1),
(3, 'default/hotel1.jpg', 'Fachada', 0),
(5, 'default/vinedo1.jpg', 'Viñedos en temporada', 0),
(5, 'default/vinedo2.jpg', 'Cata de vinos', 1),
(7, 'default/convento1.jpg', 'Fachada principal', 0),
(9, 'default/hacienda1.jpg', 'Área de caballos', 0),
(9, 'default/hacienda2.jpg', 'Tirolesa', 1);

-- ─── Amenidades por negocio ───────────────────────────────────────────────
INSERT INTO `business_amenities` (`business_id`, `amenity_id`) VALUES
(1, 1),(1, 2),(1, 8),(1, 9),
(3, 1),(3, 2),(3, 5),(3, 8),(3, 9),(3, 10),
(4, 1),(4, 2),(4, 6),(4, 8),
(5, 2),(5, 7),(5, 11),
(9, 2),(9, 3),(9, 11),(9, 12);

-- ─── Servicios ───────────────────────────────────────────────────────────
INSERT INTO `services` (`business_id`, `name`, `description`, `price`) VALUES
(5, 'Tour por el viñedo', 'Recorrido guiado de 60 min por los viñedos', 150.00),
(5, 'Cata de vinos', 'Degustación de 4 vinos con maridaje', 280.00),
(5, 'Evento privado', 'Renta del espacio para grupos hasta 50 personas', 5000.00),
(9, 'Paseo a caballo', 'Paseo de 30 min por los senderos de la hacienda', 200.00),
(9, 'Tirolesa', 'Tirolesa de 300 metros con equipo incluido', 180.00),
(9, 'Paquete familiar', 'Acceso completo para 4 personas: caballo + tirolesa + almuerzo', 950.00),
(3, 'Habitación estándar', 'Habitación doble con desayuno', 850.00),
(3, 'Habitación suite', 'Suite master con jacuzzi y vista panorámica', 1800.00);

-- ─── Productos ───────────────────────────────────────────────────────────
INSERT INTO `products` (`business_id`, `name`, `description`, `price`, `available`) VALUES
(5, 'Vino tinto Cabernet', 'Botella 750ml cosecha 2022', 320.00, 1),
(5, 'Vino rosado Cava', 'Botella 750ml edición limitada', 280.00, 1),
(5, 'Set degustación x3', 'Tres botellas de la colección premium', 850.00, 1),
(1, 'Carnitas 1 kg', 'Carnitas estilo Querétaro para llevar', 220.00, 1),
(1, 'Chiles en nogada', 'Platillo de temporada por encargo', 185.00, 0);

-- ─── Calificaciones ──────────────────────────────────────────────────────
INSERT INTO `reviews` (`business_id`, `user_name`, `rating`, `comment`) VALUES
(1, 'María G.', 5, 'Excelente comida, servicio impecable y vista increíble.'),
(1, 'Carlos R.', 4, 'Muy buena cocina queretana, precios accesibles.'),
(3, 'Ana P.', 5, 'Hotel limpio, cómodo y bien ubicado en el centro.'),
(5, 'Luis M.', 5, 'El viñedo más bonito de Querétaro, la cata fue maravillosa.'),
(7, 'Sandra T.', 5, 'Lugar hermoso y lleno de historia. Imperdible.'),
(9, 'Roberto F.', 5, 'La hacienda es un paraíso. Los niños lo pasaron increíble.');

-- ─── Configuraciones globales ─────────────────────────────────────────────
INSERT INTO `settings` (`key`, `value`, `group`) VALUES
('site_name',         'Plataforma Turística – Colón', 'general'),
('site_logo',         '', 'general'),
('site_tagline',      'Descubre el Municipio de Colón, Querétaro', 'general'),
('contact_email',     'contacto@colonbot.mx', 'general'),
('contact_phone',     '+52 442 100 0000', 'general'),
('contact_phone2',    '', 'general'),
('schedule',          'Lun–Vie 09:00–18:00', 'general'),
('color_primary',     '#3B82F6', 'theme'),
('color_secondary',   '#10B981', 'theme'),
('color_accent',      '#F59E0B', 'theme'),
('map_lat',           '20.2862', 'map'),
('map_lng',           '-99.7242', 'map'),
('map_zoom',          '13', 'map'),
('wa_token',          '', 'chatbot'),
('wa_phone_id',       '', 'chatbot'),
('wa_verify_token',   'colonbot_verify_2024', 'chatbot'),
('wa_api_version',    'v19.0', 'chatbot'),
('paypal_client_id',  '', 'payments'),
('paypal_mode',       'sandbox', 'payments'),
('qr_api_key',        '', 'qr'),
('gps_api_url',       '', 'gps'),
('gps_api_key',       '', 'gps');

-- ─── Datos analítica de ejemplo ─────────────────────────────────────────
INSERT INTO `analytics` (`business_id`, `event`) VALUES
(1,'map_view'),(1,'map_view'),(1,'whatsapp_click'),
(3,'map_view'),(3,'whatsapp_click'),(3,'directions_click'),
(5,'map_view'),(5,'map_view'),(5,'map_view'),(5,'whatsapp_click'),
(7,'map_view'),(9,'map_view'),(9,'whatsapp_click'),
(NULL,'chatbot_session'),(NULL,'chatbot_session'),(NULL,'chatbot_session');
