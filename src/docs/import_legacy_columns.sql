-- =====================================================
-- Data Migration: Add legacy FoxPro columns to price tables
-- Run this AFTER `php artisan migrate` if "Nothing to migrate"
-- or if Docker volume doesn't sync new migration files
-- =====================================================

-- === KENDARAANS ===
SET @sql = NULL;
SELECT GROUP_CONCAT(
  CONCAT('ALTER TABLE kendaraans ADD COLUMN `', COLUMN_NAME, '` ', COLUMN_TYPE, ' NULL')
) INTO @sql
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'kendaraans'
  AND COLUMN_NAME IN ('FNO_PRK_S', 'FNO_PRK_O', 'FNO_PRK_M')
  AND TABLE_SCHEMA = DATABASE()
HAVING COUNT(*) = 0;  -- only if column doesn't exist

SET @sql = IFNULL(@sql, 'SELECT "all columns exist"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
