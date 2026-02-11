# Quick Fix for Duplicate Entry Error

## Option 1: Direct SQL (Fastest)
Run this SQL in your MySQL database:

```sql
TRUNCATE TABLE migrations;
ALTER TABLE migrations AUTO_INCREMENT = 1;
```

Then run:
```bash
php artisan migrate
```

## Option 2: Using Laravel Tinker
```bash
php artisan tinker
```

Then in tinker:
```php
DB::statement('TRUNCATE TABLE migrations');
DB::statement('ALTER TABLE migrations AUTO_INCREMENT = 1');
exit
```

Then run:
```bash
php artisan migrate
```

## Option 3: Using MySQL Command Line
```bash
mysql -u your_username -p your_database_name -e "TRUNCATE TABLE migrations; ALTER TABLE migrations AUTO_INCREMENT = 1;"
```

Then run:
```bash
php artisan migrate
```

