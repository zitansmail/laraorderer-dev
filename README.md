# 📦 MigrationOrderer: Automated Dependency-Aware Migration Sequencing
[![Laravel Package](https://img.shields.io/badge/Laravel-Package-FF2D20?logo=laravel)](https://laravel.com)
[![Topological Sorting](https://img.shields.io/badge/Algorithm-Topological_Sorting-blue)](https://en.wikipedia.org/wiki/Topological_sorting)
> Automatically orders Laravel migration files based on database dependency relationships to eliminate foreign key constraint errors
```bash
composer require zitansmail/migration-orderer
```
## 🚀 Key Features
- 🔍 **Dependency Scanning** - Detects foreign keys across migration files
- 🧠 **Graph Processing** - Builds dependency graph with topological sorting
- 🔄 **File Renaming** - Rewrites timestamps to enforce execution order (`--reorder`)
- 📋 **Database Sync** - Updates `migrations` table when files change
- ⚙️ **Migration Execution** - Runs migrations in resolved order (`--run`)
- 👁️ **Dry Run Previews** - Simulate changes without modifications (`--dry-run`)
- 📊 **JSON Output** - Machine-readable dependency graph (`--json`)
- 📂 **Custom Paths** - Processes non-standard directories (`--path=`)
## 📦 Installation
1. Install via Composer:
```bash
composer require zitansmail/migration-orderer
```
2. If Laravel doesn't auto-discover the package, register the service provider in `config/app.php`:
```php
'providers' => [
    // Other service providers...
    MigrationOrderer\MigrationOrdererServiceProvider::class,
],
```
## 🛠 Usage Examples
### Preview migration order (no changes)
```bash
php artisan migrate:ordered --dry-run
```
### Generate dependency graph (JSON format)
```bash
php artisan migrate:ordered --dry-run --json
```
### Process migrations in custom directory
```bash
php artisan migrate:ordered --path=database/migrations/v2 --dry-run
```
### Reorder & rename migrations
```bash
php artisan migrate:ordered --reorder
```
### Execute migrations in resolved order
```bash
php artisan migrate:ordered --run
```
## ⚙️ How It Works
### Dependency Detection
Scans migration files for:
- `$table->foreignId()`
- `$table->foreign()`
- `->references()`
- `->on()`
- `@depends` docblock tags
### Processing Workflow
```mermaid
graph LR
    A[Scan migrations] --> B[Build dependency graph]
    B --> C{Detect circular deps?}
    C -- Yes --> D[Throw exception]
    C -- No --> E[Topological sort]
    E --> F{User options}
    F -- --dry-run --> G[Show execution plan]
    F -- --reorder --> H[Rename files + Update DB]
    F -- --run --> I[Execute migrations]
```
### File Renaming Example
```
Original:
2023_01_15_000000_create_posts_table.php
2023_01_10_000000_create_users_table.php
After --reorder:
2023_01_09_000000_create_users_table.php
2023_01_10_000000_create_posts_table.php
```
## ⚠️ Best Practices
1. **Always preview** with `--dry-run` before executing changes
2. **Commit your work** before using `--reorder` or `--run`
3. **Test thoroughly** after reordering migrations
4. **Document custom paths** for team members
5. **Resolve circular dependencies** before processing
6. **Avoid production use** - Recommended for development only
## 🚨 Troubleshooting
**Problem:** "Class not found" after renaming  
**Solution:** Run `composer dump-autoload`
**Problem:** Circular dependency detected  
**Solution:** 
```php
/**
 * @depends App\Database\Migrations\CreateUsersTable
 */
class CreateProfilesTable extends Migration
{
    // Manual dependency declaration
}
```
**Problem:** Migrations not found  
**Solution:** Verify `--path` points to correct directory:
```bash
php artisan migrate:ordered --path=database/migrations/v2 --dry-run
```
**Problem:** Foreign key errors persist  
**Solution:** Check for typos in table names and column references
## ❓ FAQ
**Q: Will my migration code be modified?**  
A: No - only filenames are changed. Your schema code remains untouched.
**Q: How are timestamp collisions handled?**  
A: The package uses microsecond precision to ensure unique timestamps.
**Q: Can I use manual dependencies?**  
A: Yes - add docblock comments:
```php
/**
 * @depends App\Database\Migrations\CreateCountriesTable
 */
```
**Q: Is this compatible with Laravel 10?**  
A: Yes - supports Laravel 8.x through 10.x
**Q: What about pivot tables?**  
A: Automatically detected through `->on()` references
## 📜 Sample Workflow
```mermaid
sequenceDiagram
    participant User
    participant Artisan
    participant Package
    participant DB
    
    User->>Artisan: php artisan migrate:ordered --reorder
    Artisan->>Package: Scan migrations
    Package->>Package: Build dependency graph
    Package->>Package: Topological sort
    Package->>Package: Rename files
    Package->>DB: Update migrations table
    DB-->>Package: Update confirmation
    Package-->>Artisan: Success message
    Artisan-->>User: Migrations reordered!
```
## 📝 License
MIT License - [https://opensource.org/licenses/MIT](https://opensource.org/licenses/MIT)
## ⚙️ Contributing
PRs welcome! See [Contribution Guide](https://github.com/your-vendor/migration-orderer/CONTRIBUTING.md) for:
- Bug reports
- Feature requests
- Code style requirements
- Testing guidelines
---
> **Pro Tip**: Combine with Laravel's migration commands:
> ```bash
> php artisan migrate:ordered --reorder
> php artisan migrate:fresh --seed
> php artisan migrate:refresh --seed
> ```
**MigrationOrderer** is not affiliated with The Laravel Framework. Laravel is a trademark of Taylor Otwell.
```
