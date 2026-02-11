-- Reset migrations table to fix duplicate entry error
-- Run this SQL directly in your MySQL database

-- Step 1: Clear all existing migration records
TRUNCATE TABLE migrations;

-- Step 2: Reset the auto-increment counter
ALTER TABLE migrations AUTO_INCREMENT = 1;

-- After running this, you can run: php artisan migrate

