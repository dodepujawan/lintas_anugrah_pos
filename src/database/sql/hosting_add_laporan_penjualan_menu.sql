-- ============================================================================
-- Hosting: menu Laporan Penjualan (tanpa php artisan migrate)
-- Jalankan sekali di phpMyAdmin / SQL console DB hosting.
-- Aman diulang (NOT EXISTS).
-- ============================================================================

START TRANSACTION;

-- 1) Insert menu jika belum ada
INSERT INTO menus (
    name, route_name, section, icon, sort_order, is_sidebar, description, created_at, updated_at
)
SELECT
    'Laporan Penjualan',
    'laporan.penjualan',
    'Laporan',
    'bi bi-cart-check',
    10,
    1,
    'Laporan penjualan dari penambahan data piutang dengan filter tanggal, customer, dan jenis EKS/REN/CO.',
    NOW(),
    NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM menus WHERE route_name = 'laporan.penjualan'
);

-- Samakan data jika sudah ada
UPDATE menus
SET
    name = 'Laporan Penjualan',
    section = 'Laporan',
    icon = 'bi bi-cart-check',
    sort_order = 10,
    is_sidebar = 1,
    description = 'Laporan penjualan dari penambahan data piutang dengan filter tanggal, customer, dan jenis EKS/REN/CO.',
    updated_at = NOW()
WHERE route_name = 'laporan.penjualan';

-- 2) Copy permission dari Laporan Piutang (role yang belum punya)
INSERT INTO role_menu (
    role_id, menu_id,
    can_view, can_create, can_edit, can_delete, can_post, can_void, can_export,
    created_at, updated_at
)
SELECT
    rm.role_id,
    m_new.id,
    rm.can_view,
    0, 0, 0, 0, 0,
    rm.can_export,
    NOW(),
    NOW()
FROM role_menu rm
INNER JOIN menus m_src
    ON m_src.id = rm.menu_id
   AND m_src.route_name = 'laporan.piutang'
INNER JOIN menus m_new
    ON m_new.route_name = 'laporan.penjualan'
WHERE NOT EXISTS (
    SELECT 1
    FROM role_menu x
    WHERE x.role_id = rm.role_id
      AND x.menu_id = m_new.id
);

-- 3) Pastikan Administrator punya view + export
INSERT INTO role_menu (
    role_id, menu_id,
    can_view, can_create, can_edit, can_delete, can_post, can_void, can_export,
    created_at, updated_at
)
SELECT
    r.id,
    m.id,
    1, 0, 0, 0, 0, 0, 1,
    NOW(),
    NOW()
FROM roles r
CROSS JOIN menus m
WHERE r.name = 'Administrator'
  AND m.route_name = 'laporan.penjualan'
  AND NOT EXISTS (
      SELECT 1 FROM role_menu x
      WHERE x.role_id = r.id AND x.menu_id = m.id
  );

UPDATE role_menu rm
INNER JOIN roles r ON r.id = rm.role_id AND r.name = 'Administrator'
INNER JOIN menus m ON m.id = rm.menu_id AND m.route_name = 'laporan.penjualan'
SET
    rm.can_view = 1,
    rm.can_export = 1,
    rm.updated_at = NOW();

-- 4) Catat migration sudah jalan (supaya artisan migrate nanti skip / tidak bentrok)
SET @next_batch := (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations);

INSERT INTO migrations (migration, batch)
SELECT '2026_08_13_000001_add_laporan_penjualan_menu', @next_batch
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM migrations
    WHERE migration = '2026_08_13_000001_add_laporan_penjualan_menu'
);

COMMIT;

-- Cek:
-- SELECT id, name, route_name FROM menus WHERE route_name = 'laporan.penjualan';
-- SELECT * FROM migrations WHERE migration LIKE '%laporan_penjualan%';
