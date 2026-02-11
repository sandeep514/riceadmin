# Web Access Module - Implementation Documentation

## Overview
This document details all the work done to implement the "Web Access" module that allows admins to control which role, category, and plan combinations can access which web side menu items with specific CRUD permissions.

## Module Purpose
Admin can decide which role, category, and plan person can access which menu items (from web_side_menu table) with Create, Read, Update, Delete permissions.

## Database Changes

### 1. Migration: `2024_01_01_000116_create_web_access_table.php`
**Location:** `database/migrations/2024_01_01_000116_create_web_access_table.php`

**Table Structure:**
- `id` - Primary key
- `role_id` - Foreign key to `roles` table
- `category_id` - Foreign key to `category` table (nullable)
- `plan_id` - Foreign key to `plan` table (nullable)
- `web_side_menu_id` - Foreign key to `web_side_menu` table
- `can_create` - Boolean (default 0)
- `can_read` - Boolean (default 0)
- `can_update` - Boolean (default 0)
- `can_delete` - Boolean (default 0)
- `status` - Integer (default 1)
- `created_at` - Timestamp
- `updated_at` - Timestamp

**Indexes:**
- Foreign key on `role_id`
- Foreign key on `web_side_menu_id`
- Composite index on `['role_id', 'category_id', 'plan_id', 'web_side_menu_id']`

### 2. Migration: `2024_01_01_000117_update_web_access_table_for_menu.php`
**Location:** `database/migrations/2024_01_01_000117_update_web_access_table_for_menu.php`

**Changes:**
- Removed `route_name` column (if exists)
- Added `web_side_menu_id` column
- Updated indexes
- Added foreign key to `web_side_menu` table

## Models Created/Modified

### 1. WebAccess Model
**Location:** `app/WebAccess.php`

**Key Features:**
- Relationships: `role()`, `category()`, `plan()`, `webSideMenu()`
- Fillable fields: role_id, category_id, plan_id, web_side_menu_id, can_create, can_read, can_update, can_delete, status
- Casts: Boolean casts for CRUD permissions

### 2. Category Model
**Location:** `app/Category.php`

**Purpose:** Model for category table (if didn't exist)

### 3. CategoryRoleMap Model
**Location:** `app/CategoryRoleMap.php`

**Purpose:** Model for category_role_map table to handle role-category relationships

## Controllers

### WebAccessController
**Location:** `app/Http/Controllers/WebAccessController.php`

**Methods:**
1. `index()` - Display list view
2. `getData()` - AJAX endpoint for DataTable
3. `create()` - Show create form
4. `save()` - Save new access permissions
5. `edit($id)` - Show edit form
6. `update($id)` - Update access permissions
7. `delete($id)` - Delete access record
8. `getCategoriesByRole(Request $request)` - AJAX endpoint to get categories by role

**Key Features:**
- Fetches all web_side_menu items for the form
- Handles multiple menu items with CRUD permissions
- Updates all records for a role/category/plan combination

## Requests (Validation)

### WebAccessRequest
**Location:** `app/Http/Requests/WebAccessRequest.php`

**Validation Rules:**
- `role_id` - required, exists in roles table
- `category_id` - nullable, exists in category table
- `plan_id` - nullable, exists in plan table
- `menu_permissions` - required array
- `menu_permissions.*.can_create` - nullable boolean
- `menu_permissions.*.can_read` - nullable boolean
- `menu_permissions.*.can_update` - nullable boolean
- `menu_permissions.*.can_delete` - nullable boolean
- `status` - nullable integer (0 or 1)

## Services & Repositories

### WebAccessService
**Location:** `app/Services/WebAccessService.php`

**Methods:**
- `saveWebAccess($request)` - Save new permissions
- `updateWebAccess($request, $roleId, $categoryId, $planId)` - Update permissions
- `deleteWebAccess($id)` - Delete access record

### WebAccessRepository
**Location:** `app/Repositories/WebAccessRepository.php`

**Methods:**
- `saveWebAccess($request)` - Creates multiple records (one per menu item with permissions)
- `updateWebAccess($request, $roleId, $categoryId, $planId)` - Deletes existing and recreates
- `deleteWebAccess($id)` - Deletes single record

**Key Logic:**
- Only creates records for menu items that have at least one permission checked
- On update, deletes all existing records for the role/category/plan combination and recreates them

## Views

### 1. Index View
**Location:** `resources/views/web-access/index.blade.php`

**Features:**
- DataTable with server-side processing
- Shows: ID, Role, Category, Plan, Menu Item, Create, Read, Update, Delete, Status, Actions
- AJAX data loading from `getData()` method

### 2. Create View
**Location:** `resources/views/web-access/create.blade.php`

**Features:**
- Form with role, category, plan dropdowns
- Table showing all menu items with CRUD checkboxes
- AJAX category loading based on role selection

### 3. Edit View
**Location:** `resources/views/web-access/edit.blade.php`

**Features:**
- Same as create view but with existing data pre-filled
- Role, Category, Plan are read-only (disabled with hidden fields)
- Menu permissions pre-checked based on existing records

### 4. Form Partial
**Location:** `resources/views/web-access/_form.blade.php`

**Key Features:**
- Role dropdown (disabled in edit mode)
- Category dropdown (filtered by role via AJAX, disabled in edit mode)
- Plan dropdown (disabled in edit mode)
- Table of all menu items with checkboxes for:
  - Create Access
  - Read Access
  - Update Access
  - Delete Access
- Status dropdown

**Form Structure:**
```php
menu_permissions[menu_id][can_create] = 1
menu_permissions[menu_id][can_read] = 1
menu_permissions[menu_id][can_update] = 1
menu_permissions[menu_id][can_delete] = 1
```

### 5. Actions Partial
**Location:** `resources/views/web-access/_actions.blade.php`

**Features:**
- Edit button
- Delete button with confirmation

## Routes

**Location:** `routes/web.php`

**Route Group:**
```php
Route::group(['module'=>'web_access','icon'=>'fa-lock'], function() {
    Route::get('web-access', ['as' => 'web-access', 'uses' => 'WebAccessController@index','action'=>'view']);
    Route::get('web-access/data', ['as' => 'web-access.data', 'uses' => 'WebAccessController@getData']);
    Route::get('web-access/create', ['as' => 'create.web-access', 'uses' => 'WebAccessController@create','action'=>'create']);
    Route::post('web-access/save', ['as' => 'save.web-access', 'uses' => 'WebAccessController@save','action'=>'create']);
    Route::get('web-access/edit/{id}', ['as' => 'edit.web-access', 'uses' => 'WebAccessController@edit','action'=>'edit']);
    Route::put('web-access/update/{id}', ['as' => 'update.web-access', 'uses' => 'WebAccessController@update','action'=>'edit']);
    Route::delete('web-access/delete/{id}', ['as' => 'delete.web-access', 'uses' => 'WebAccessController@delete','action'=>'delete']);
    Route::get('web-access/get-categories', ['as' => 'web-access.get-categories', 'uses' => 'WebAccessController@getCategoriesByRole']);
});
```

## Sidebar Integration

**Location:** `resources/views/components/sidebar.blade.php`

**Added:**
```php
<li class="{{ (in_array($currentRoute,['web-access','create.web-access','edit.web-access']))?'active':'' }}">
    <a href="{{ route('web-access') }}">
        <i class="fa fa-lock"></i> <span>Web Access</span>
    </a>
</li>
```

## Key Functionality

### 1. Multiple Menu Items Support
- Form displays all active menu items from `web_side_menu` table
- Each menu item has individual CRUD checkboxes
- System creates one record per menu item that has at least one permission

### 2. Role-Based Category Filtering
- When role is selected, categories are filtered via AJAX
- Uses `category_role_map` table to get categories for selected role

### 3. Data Management
- Create: Creates multiple records (one per menu item with permissions)
- Edit: Shows all existing permissions for role/category/plan combination
- Update: Deletes all existing records and recreates them
- Delete: Deletes single access record

### 4. DataTable Display
- Shows all access records with relationships loaded
- Displays role name, category name, plan name, menu item title
- Shows Yes/No labels for CRUD permissions
- Shows Active/Inactive status

## Files Created

1. `database/migrations/2024_01_01_000116_create_web_access_table.php`
2. `database/migrations/2024_01_01_000117_update_web_access_table_for_menu.php`
3. `app/WebAccess.php`
4. `app/Category.php`
5. `app/CategoryRoleMap.php`
6. `app/Http/Controllers/WebAccessController.php`
7. `app/Http/Requests/WebAccessRequest.php`
8. `app/Services/WebAccessService.php`
9. `app/Repositories/WebAccessRepository.php`
10. `resources/views/web-access/index.blade.php`
11. `resources/views/web-access/create.blade.php`
12. `resources/views/web-access/edit.blade.php`
13. `resources/views/web-access/_form.blade.php`
14. `resources/views/web-access/_actions.blade.php`

## Files Modified

1. `routes/web.php` - Added web access routes
2. `resources/views/components/sidebar.blade.php` - Added Web Access menu link

## Dependencies

- Requires `web_side_menu` table to exist
- Requires `roles` table to exist
- Requires `category` table to exist
- Requires `category_role_map` table to exist
- Requires `plan` table to exist
- Uses Yajra DataTables for listing
- Uses Laravel Collective Forms

## Migration Order

1. First run: `2024_01_01_000116_create_web_access_table.php` (creates table with route_name)
2. Second run: `2024_01_01_000117_update_web_access_table_for_menu.php` (replaces route_name with web_side_menu_id)

## Important Notes

1. **Multiple Records**: The system creates multiple records - one for each menu item that has at least one permission checked
2. **Update Behavior**: When updating, all existing records for the role/category/plan combination are deleted and recreated
3. **Form Structure**: Menu permissions are submitted as `menu_permissions[menu_id][permission_type]`
4. **Read-Only Fields**: In edit mode, role, category, and plan are disabled (read-only) with hidden fields to maintain values
5. **AJAX Category Loading**: Categories are loaded dynamically based on selected role

## Testing Checklist

- [ ] Create new access with multiple menu items
- [ ] Edit existing access permissions
- [ ] Delete access record
- [ ] Category filtering based on role selection
- [ ] DataTable displays correctly
- [ ] CRUD permissions save correctly
- [ ] Multiple menu items with different permissions
- [ ] Validation works correctly

## Next Steps (If Needed)

1. Add bulk operations (select multiple and delete)
2. Add duplicate functionality
3. Add export functionality
4. Add search/filter in DataTable
5. Add permission checking middleware to use these permissions

---

**Created:** [Date when implemented]
**Last Updated:** [Date of last modification]

