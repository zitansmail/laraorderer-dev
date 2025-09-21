# 📦 MigrationOrderer — Dependency-Aware Laravel Migration Ordering

[![Laravel Package](https://img.shields.io/badge/Laravel-Package-FF2D20?logo=laravel)](https://laravel.com)


Automatically computes a dependency-safe order for your migrations and (optionally) renames files to match it — no more mysterious foreign key errors.

```bash
composer require zitansmail/migration-orderer --dev
```

---

## 🚀 Features

- 🔎 **Dependency scan**: detects relations like `foreignId()->constrained()`
- 🧩 **Topological sort**: builds a dependency graph and computes a safe order
- 👁️ **Preview table**: shows **# (Computed)** vs **Current Pos**, **Status** (`OK` / `UNORDERED`), and **Issue** (first offending dependency)
- 🔄 **Reorder**: rewrite migration filenames to match the safe order
- 💾 **DB manifest**: persists every rename into the **`migration_orderer`** table
- ↩️ **Undo last**: restore filenames using the saved DB manifest
- 📂 **Custom paths**: analyze any directory with `--path=`
- 🛡️ **Safe by default**: no file changes unless you ask

---

## ✅ Requirements

- PHP 8.1+
- Laravel 10.x / 11.x

---

## 📦 Installation

1) Install the package:

```bash
composer require zitansmail/migration-orderer --dev
```

2) Run migrations to create the package table:

```bash
php artisan migrate
```

> This creates **`migration_orderer`**, where reorder manifests are stored for undo operations.

---

## 🛠 Usage

### Preview (safe, no changes)

```bash
php artisan migrate:ordered --preview
```

**Columns**
- **# (Computed)** — position in dependency-safe order  
- **File** — filename without path  
- **Current Pos** — natural timestamp order position  
- **Status** — `OK` or **`UNORDERED`**  
- **Issue** — first dependency that appears *after* the file (red flag)

---

### Reorder (rename files to match the safe order)

Writes a manifest to `migration_orderer` so you can undo later.

```bash
php artisan migrate:ordered --reorder
# non-interactive (CI):
php artisan migrate:ordered --reorder --force
```

---

### Undo last reorder (restore filenames)

```bash
php artisan migrate:ordered --undo-last
```

---

### Custom migrations path

```bash
php artisan migrate:ordered --preview --path=modules/Blog/database/migrations
```

---

## 🔒 Safety & Workflow Tips

- **Preview first:** `--preview` highlights problems before any changes.  
- **Commit before renaming:** easy rollback, clear diffs.  
- **Use a feature branch:** PRs make review simple.  
- **Undo available:** `--undo-last` restores the previous rename exactly.  
- **Break cycles:** if your schema is cyclic, split tables or defer constraints.


## ❓ FAQ

**Does this run migrations?**  
No. The command is **non-destructive by default** (preview only). Use `--reorder` to normalize filenames, then run Laravel’s native `php artisan migrate`.

**Where is the “state” stored?**  
In the **`migration_orderer`** table. Each reorder writes rename pairs there so you can undo later.

**Does it update the `migrations` table?**  
Yes — when files are renamed, corresponding rows in the `migrations` table are updated to stay in sync.

---

## 🧭 Command Reference

```
php artisan migrate:ordered
  --preview          Show computed vs natural order in a diagnostics table
  --reorder          Rename files to match the dependency-safe order (writes DB manifest)
  --undo-last        Restore files from the last DB manifest
  --path=...         Use a custom migrations directory (default: database/migrations)
  --force            Bypass confirmation (useful in CI)
```

---

## ⚠️ Best Practices

- Always `--preview` first  
- Keep migration logic simple; avoid circular dependencies  
- Use `--path=` for modular/monorepo setups  
- Prefer running `--reorder` in dev/staging, then deploy
- Use `--undo-last` to reset you migration files before reordering
