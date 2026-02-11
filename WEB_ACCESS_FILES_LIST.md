# Web Access Module - Files List

## Quick Reference: All Files Created/Modified

### Database Migrations
1. `database/migrations/2024_01_01_000116_create_web_access_table.php` - **CREATED**
2. `database/migrations/2024_01_01_000117_update_web_access_table_for_menu.php` - **CREATED**

### Models
3. `app/WebAccess.php` - **CREATED**
4. `app/Category.php` - **CREATED**
5. `app/CategoryRoleMap.php` - **CREATED**

### Controllers
6. `app/Http/Controllers/WebAccessController.php` - **CREATED**

### Requests (Validation)
7. `app/Http/Requests/WebAccessRequest.php` - **CREATED**

### Services
8. `app/Services/WebAccessService.php` - **CREATED**

### Repositories
9. `app/Repositories/WebAccessRepository.php` - **CREATED**

### Views
10. `resources/views/web-access/index.blade.php` - **CREATED**
11. `resources/views/web-access/create.blade.php` - **CREATED**
12. `resources/views/web-access/edit.blade.php` - **CREATED**
13. `resources/views/web-access/_form.blade.php` - **CREATED**
14. `resources/views/web-access/_actions.blade.php` - **CREATED**

### Routes
15. `routes/web.php` - **MODIFIED** (Added web access routes)

### Sidebar
16. `resources/views/components/sidebar.blade.php` - **MODIFIED** (Added Web Access menu link)

---

## Commands to Run

```bash
# Run migrations
php artisan migrate

# Clear cache (if needed)
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Database Table Structure

**Table:** `web_access`

```sql
CREATE TABLE `web_access` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `plan_id` bigint unsigned DEFAULT NULL,
  `web_side_menu_id` bigint unsigned DEFAULT NULL,
  `can_create` tinyint(1) NOT NULL DEFAULT '0',
  `can_read` tinyint(1) NOT NULL DEFAULT '0',
  `can_update` tinyint(1) NOT NULL DEFAULT '0',
  `can_delete` tinyint(1) NOT NULL DEFAULT '0',
  `status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `web_access_role_id_foreign` (`role_id`),
  KEY `web_access_web_side_menu_id_foreign` (`web_side_menu_id`),
  KEY `web_access_role_category_plan_menu_index` (`role_id`,`category_id`,`plan_id`,`web_side_menu_id`),
  CONSTRAINT `web_access_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `web_access_web_side_menu_id_foreign` FOREIGN KEY (`web_side_menu_id`) REFERENCES `web_side_menu` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Key Routes

- `GET /administrator/web-access` - List all access records
- `GET /administrator/web-access/data` - AJAX data for DataTable
- `GET /administrator/web-access/create` - Create form
- `POST /administrator/web-access/save` - Save new access
- `GET /administrator/web-access/edit/{id}` - Edit form
- `PUT /administrator/web-access/update/{id}` - Update access
- `DELETE /administrator/web-access/delete/{id}` - Delete access
- `GET /administrator/web-access/get-categories` - Get categories by role (AJAX)

---

## Form Data Structure

When submitting the form, data is sent as:

```php
[
    'role_id' => 1,
    'category_id' => 2, // or null
    'plan_id' => 3, // or null
    'menu_permissions' => [
        '1' => [ // menu item ID
            'can_create' => '1',
            'can_read' => '1',
            'can_update' => '0',
            'can_delete' => '0',
        ],
        '2' => [
            'can_create' => '0',
            'can_read' => '1',
            'can_update' => '1',
            'can_delete' => '1',
        ],
        // ... more menu items
    ],
    'status' => 1
]
```

---

## Important Implementation Details

1. **Multiple Records**: One record per menu item that has at least one permission checked
2. **Update Strategy**: Delete all existing records for role/category/plan combo, then recreate
3. **Edit Mode**: Role, Category, Plan are read-only (disabled with hidden fields)
4. **Category Filtering**: AJAX-based, filtered by selected role using `category_role_map` table
5. **Menu Items**: Fetched from `web_side_menu` table, ordered by `sort_order`

---

**Note:** This is a quick reference. See `WEB_ACCESS_MODULE_DOCUMENTATION.md` for detailed documentation.

