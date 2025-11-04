# 🚀 pack:generate Quick Reference

## Basic Syntax
```bash
php artisan pack:generate {ResourceName} [options]
```

## Common Commands

### 🆕 Generate New Resource
```bash
php artisan pack:generate BlogPost
```

### 👁️ Preview Without Creating
```bash
php artisan pack:generate BlogPost --dry-run
```

### 🔄 Regenerate (Overwrite)
```bash
php artisan pack:generate BlogPost --force
```

### ⏭️ Skip Migration (Already Created)
```bash
php artisan pack:generate BlogPost --skip-migration
```

### 🎯 Service Layer Only
```bash
php artisan pack:generate BlogPost --skip-migration --skip-controller --skip-resource
```

### 🤖 Non-Interactive
```bash
php artisan pack:generate BlogPost --no-interaction
```

## All Options

| Option | Description | Example |
|--------|-------------|---------|
| `--force` | Overwrite existing files | `--force` |
| `--dry-run` | Preview only | `--dry-run` |
| `--skip-migration` | Skip migration | `--skip-migration` |
| `--skip-controller` | Skip controller | `--skip-controller` |
| `--skip-resource` | Skip API resource | `--skip-resource` |
| `--no-backup` | No backup on overwrite | `--force --no-backup` |
| `--no-interaction` | No prompts | `--no-interaction` |
| `-v, --verbose` | Detailed errors | `-v` |

## Generated Files

✅ Model → `app/Models/{Name}.php`  
✅ Migration → `database/migrations/{timestamp}_create_{names}_table.php`  
✅ Repository → `app/Repositories/{Name}Repository.php`  
✅ Service → `app/Services/{Name}Service.php`  
✅ Provider → `app/Providers/{Name}ServiceProvider.php`  
✅ Controller → `app/Http/Controllers/{Name}Controller.php`  
✅ Resource → `app/Http/Resources/{Name}Resource.php`  
✅ Provider Registration → `bootstrap/providers.php`

## Examples for Project Models

```bash
# Generate all missing files for project models
php artisan pack:generate ProjectLifecycleStage --skip-migration
php artisan pack:generate ProjectFeasibilityStudy --skip-migration
php artisan pack:generate ProjectStakeholder --skip-migration
php artisan pack:generate ProjectRisk --skip-migration
php artisan pack:generate ProjectIssue --skip-migration
php artisan pack:generate ProjectChangeRequest --skip-migration
php artisan pack:generate ProjectPerformanceMetric --skip-migration
php artisan pack:generate ProjectInspection --skip-migration
```

## Troubleshooting

### "File already exists"
```bash
# Preview first
php artisan pack:generate MyResource --dry-run

# Then force if needed
php artisan pack:generate MyResource --force
```

### "Invalid resource name"
❌ `user-profile` (kebab-case)  
❌ `user_profile` (snake_case)  
✅ `UserProfile` (PascalCase)

### Failed Generation
Command will automatically offer rollback.  
Choose 'yes' to clean up partial changes.

## Tips

💡 Use `--dry-run` first to preview  
💡 Use `--force` to regenerate safely (creates backups)  
💡 Use `--skip-migration` if migration already exists  
💡 Use `-v` for detailed error messages  
💡 Backups are saved as `filename.php.backup-TIMESTAMP`

## Next Steps After Generation

1. ✅ Run migrations: `php artisan migrate`
2. ✅ Add routes in `routes/api.php` or `routes/web.php`
3. ✅ Customize validation in `{Name}Service::rules()`
4. ✅ Add relationships in `{Name}` model
5. ✅ Implement custom logic in repository's `parse()` method

