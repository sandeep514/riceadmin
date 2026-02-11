-- Fix duplicate entries in migrations table
-- This will reset the migrations table and allow you to re-run migrations

-- Option 1: Clear all migration entries (if you want to start fresh)
TRUNCATE TABLE migrations;

-- Option 2: Remove duplicate entries (if you want to keep some)
-- First, let's see what duplicates exist:
-- SELECT migration, COUNT(*) as count FROM migrations GROUP BY migration HAVING count > 1;

-- Then remove duplicates, keeping only the one with the highest batch number:
-- DELETE m1 FROM migrations m1
-- INNER JOIN migrations m2 
-- WHERE m1.id > m2.id AND m1.migration = m2.migration;

-- Option 3: Reset auto-increment and clear table
-- ALTER TABLE migrations AUTO_INCREMENT = 1;
-- TRUNCATE TABLE migrations;

