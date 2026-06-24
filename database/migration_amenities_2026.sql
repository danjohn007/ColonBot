-- ============================================================
-- Migración: Agregar nuevas amenidades al catálogo
-- ============================================================
INSERT IGNORE INTO `amenities` (`name`, `icon`, `active`) VALUES
('Área de Pesca', 'fishing', 1),
('Cabañas', 'cabin', 1),
('Paseos en lancha', 'boat', 1),
('Mirador', 'binoculars', 1),
('Paseo a Caballo', 'horse', 1),
('Zona infantil', 'child', 1),
('Área de Hamacas', 'hammock', 1),
('Palapas', 'umbrella-beach', 1),
('Airbnb', 'home', 1),
('Tiendita', 'store', 1),
('Recorridos de Viñedos/Cosechas', 'wine-bottle', 1),
('Temazcal', 'spa', 1),
('Alberca', 'swimmer', 1),
('Eventos', 'calendar-alt', 1),
('Senderismo', 'hiking', 1),
('Acceso gratuito', 'money-bill', 1),
('Reserva de Bisonte Americano', 'buffalo', 1),
('Hotel en sitio', 'hotel', 1),
('SPA', 'spa', 1);