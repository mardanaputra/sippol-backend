# Laravel Backend Guidelines (sippol-backend)

Refer to root `/home/dhanu/code/sippol/CLAUDE.md` for overarching architecture and role definitions.

## Backend Commands
- Dev Server: `php artisan serve`
- Run Migrations: `php artisan migrate`
- Run Seeders: `php artisan db:seed`
- Route List: `php artisan route:list`
- Code Formatting: `./vendor/bin/pint`
- Tests: `php artisan test`

## Key Patterns
- API Controllers reside in `app/Http/Controllers/Api/`
- All authenticated routes use `auth:sanctum`
- Role authorization is enforced using `middleware('role:bidang_trantib')` or similar
- Controllers log sensitive mutations to `App\Models\ActivityLog`
