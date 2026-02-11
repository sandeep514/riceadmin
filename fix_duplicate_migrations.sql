-- Fix duplicate entry error in migrations table
-- Run this SQL directly in your MySQL database

-- Method 1: If you want to start completely fresh (RECOMMENDED for new setup)
TRUNCATE TABLE migrations;
ALTER TABLE migrations AUTO_INCREMENT = 1;

-- Method 2: If you want to keep existing records and just fix duplicates
-- First, find duplicates:
-- SELECT migration, COUNT(*) as count FROM migrations GROUP BY migration HAVING count > 1;

-- Then remove duplicates (keeps the entry with the lowest id):
-- DELETE m1 FROM migrations m1
-- INNER JOIN migrations m2 
-- WHERE m1.id > m2.id AND m1.migration = m2.migration;

-- Reset auto-increment after removing duplicates:
-- SET @max_id = (SELECT MAX(id) FROM migrations);
-- SET @sql = CONCAT('ALTER TABLE migrations AUTO_INCREMENT = ', @max_id + 1);
-- PREPARE stmt FROM @sql;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

