# 📦 MigrationOrderer — Dependency-Aware Laravel Migration Ordering

[![Laravel Package](https://img.shields.io/badge/Laravel-Package-FF2D20?logo=laravel)](https://laravel.com)
[![Tests](https://img.shields.io/badge/Tests-Passing-brightgreen)](https://github.com/zitansmail/migration-orderer)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://php.net)

Automatically analyze and reorder Laravel migrations based on foreign key dependencies. Prevent foreign key constraint errors by ensuring tables are created in the correct order.

```bash
composer require zitansmail/migration-orderer --dev
```

---

## 🎯 The Problem

Ever encountered this error when running migrations?

```bash
SQLSTATE[HY000]: General error: 1005 Can't create table `posts`
(errno: 150 "Foreign key constraint is incorrectly formed")
```

This happens when migrations create foreign keys to tables that don't exist yet. Traditional solutions require manually renaming migration files or creating migrations in perfect chronological order.

## 💡 The Solution

MigrationOrderer automatically:
- **Scans** your migrations for foreign key dependencies
- **Builds** a dependency graph using topological sorting
- **Reorders** files to ensure dependencies come first
- **Shows** you exactly what needs to be fixed

---

## 🚀 Features

- 🔍 **Smart Detection**: Finds `foreignId()`, `constrained()`, `foreignIdFor()`, and legacy foreign keys
- 🧩 **Topological Sort**: Uses graph algorithms to compute safe execution order
- 👁️ **Rich Preview**: Shows current vs computed positions with dependency details
- 🛡️ **Circular Detection**: Identifies and reports circular dependencies with clear error messages
- 🔄 **Safe Reordering**: Renames files while maintaining full undo capability
- 💾 **State Management**: Tracks changes in database for reliable undo operations
- 📂 **Flexible Paths**: Works with custom migration directories and modular setups
- ✅ **Production Ready**: Comprehensive test coverage and error handling

---

## ✅ Requirements

- PHP 8.1+
- Laravel 10.x / 11.x / 12.x

---

## 📦 Installation

```bash
composer require zitansmail/migration-orderer --dev
```

That's it! The package auto-registers and creates its tracking table automatically when needed. No manual migrations required.

---

## 🛠 Usage

### 1. Preview Dependencies (Safe, No Changes)

```bash
php artisan migrate:ordered --preview
```

**Example Output:**
```
+-------------+----------------------+-------------+---------------+------------------+------------------------+
| # (Computed)| Migration            | Current Pos | Status        | Dependencies     | Issue                  |
+-------------+----------------------+-------------+---------------+------------------+------------------------+
| 1           | create_users_table   | 2           | NEEDS REORDER | -                | -                      |
| 2           | create_posts_table   | 1           | NEEDS REORDER | create_users_... | Depends on: create_... |
+-------------+----------------------+-------------+---------------+------------------+------------------------+

⚠️  1 migration(s) need reordering for dependency safety.
```

### 2. Reorder Files

```bash
php artisan migrate:ordered --reorder
```

**With confirmation bypass (CI/automation):**
```bash
php artisan migrate:ordered --reorder --force
```

### 3. Undo Last Reorder

```bash
php artisan migrate:ordered --undo-last
```

### 4. Custom Migration Paths

```bash
php artisan migrate:ordered --preview --path=modules/Blog/database/migrations
```

---

## 🔍 Supported Foreign Key Patterns

The scanner detects all modern Laravel foreign key patterns:

```php
// ✅ Modern foreignId with implicit constraint
$table->foreignId('user_id')->constrained();

// ✅ Modern foreignId with explicit table
$table->foreignId('author_id')->constrained('users');

// ✅ ForeignIdFor helper
$table->foreignIdFor(User::class);
$table->foreignIdFor(User::class, 'author_id');

// ✅ Legacy foreign key syntax
$table->foreign('user_id')->references('id')->on('users');

// ✅ Legacy unsigned big integer (partial detection)
$table->unsignedBigInteger('user_id');

// ⚠️ Polymorphic relationships (detected but no dependency)
$table->morphs('commentable');
$table->uuidMorphs('taggable');
```

---

## 🔄 Workflow Examples

### Basic Workflow
```bash
# 1. Check current state
php artisan migrate:ordered --preview

# 2. Fix ordering if needed
php artisan migrate:ordered --reorder

# 3. Run migrations normally
php artisan migrate

# 4. Undo if something goes wrong
php artisan migrate:ordered --undo-last
```

### Team Development
```bash
# Before merging a feature branch
git checkout feature/user-system
php artisan migrate:ordered --preview
php artisan migrate:ordered --reorder --force
git add database/migrations/
git commit -m "Fix migration dependency order"
```

---

## 🚨 Error Handling

### Circular Dependencies
```bash
Migration Orderer Error: Circular dependency detected:
create_users_table.php -> create_roles_table.php -> create_users_table.php
```

**Solution:** Break the cycle by:
- Moving foreign keys to separate migrations
- Using nullable foreign keys initially
- Deferring constraints with `Schema::enableForeignKeyConstraints()`

### Missing Dependencies
The preview shows missing tables that your migrations reference but don't create:
```
Missing: ["external_api_users", "legacy_data"]
```

---

## 🧭 Command Reference

```bash
php artisan migrate:ordered [options]

Options:
  --preview         Show dependency analysis without making changes
  --reorder         Rename files to match computed order
  --undo-last       Restore files from last reorder operation
  --path=PATH       Custom migrations directory
  --force           Skip confirmation prompts
```

---

## 🔒 Safety Features

- **Preview First**: Always shows what will change before making modifications
- **Atomic Operations**: File renames are tracked; failures can be undone
- **State Persistence**: Every reorder is logged in the database
- **Backup Strategy**: Undo capability restores exact previous state
- **Non-Destructive**: Default mode makes no changes to your files
- **Validation**: Detects and prevents circular dependencies

---

## 🛡️ Best Practices

### Development Workflow
1. **Always preview first** to understand dependencies
2. **Commit before reordering** for easy rollback
3. **Use feature branches** for complex schema changes
4. **Test migrations** in staging before production

### Schema Design
- Avoid circular foreign key dependencies
- Consider nullable foreign keys for complex relationships
- Use pivot tables for many-to-many relationships
- Plan table creation order during initial design

### Team Collaboration
- Run `--preview` before pushing migration changes
- Include reordering in your CI/CD pipeline
- Document complex dependency relationships
- Use consistent naming conventions

---

## 🧪 Testing

The package includes focused tests covering core functionality:

```bash
# Run the test suite
composer test

# With coverage
composer test-coverage
```

Essential test coverage:
- ✅ **Command Interface** - Preview, reorder, undo operations
- ✅ **Dependency Detection** - All foreign key patterns and missing dependencies
- ✅ **Circular Dependencies** - Detection and error handling
- ✅ **File Operations** - Safe reordering with automatic table creation

---

## 🤝 Contributing

We welcome contributions! Here's how you can help improve MigrationOrderer:

### Quick Start
```bash
git clone https://github.com/zitansmail/migration-orderer
cd migration-orderer
composer install
composer test
```

### Contributing Guidelines

**🐛 Reporting Bugs**
- Check existing issues before creating new ones
- Include Laravel version, PHP version, and error details
- Provide minimal reproduction steps

**✨ Suggesting Features**
- Open an issue with a clear description
- Explain the use case and expected behavior
- Include code examples if applicable

**🔧 Code Contributions**
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Write tests for new functionality
4. Ensure all tests pass: `composer test`
5. Follow PSR-12 coding standards
6. Submit a pull request with clear description

**📝 Documentation**
- Fix typos and improve clarity
- Add examples for complex features
- Update README when adding new functionality

### Development Guidelines
- Write tests for all new features
- Keep backwards compatibility
- Follow existing code patterns
- Add meaningful commit messages

---

## 📝 License

MIT License. See [LICENSE](https://github.com/ludoguenet/zap-for-laravel/blob/main/LICENSE) for details.

---

## 📚 Learn More

**📖 Technical Deep Dive**
Read the complete story behind MigrationOrderer's development:
[Solving Laravel Migration Dependency Hell: Building MigrationOrderer Package](https://www.blog.zitansmail.com/blogs/solving-laravel-migration-dependency-hell-building-migrationorderer-package)

The blog post covers:
- The problem and motivation behind the package
- Technical implementation details and algorithms
- Real-world usage patterns and team workflows
- Development challenges and lessons learned
- Future enhancements and roadmap

---

## 🙏 Credits

Created by [Zitane Smail](https://github.com/zitansmail)

Built with ❤️ for the Laravel community.
