# Laravel Custom Commands

## App Clean Command

A comprehensive command to clean all Laravel caches, routes, configs, views, and queues.

### Usage

#### Basic Cleanup
```bash
php artisan app:clean
```

This will:
- ✅ Clear application cache
- ✅ Clear config cache
- ✅ Clear route cache
- ✅ Clear view cache
- ✅ Clear event cache
- ✅ Clear failed queue jobs
- ✅ Restart queue workers
- ✅ Clear all optimization caches

#### Cleanup + Optimization
```bash
php artisan app:clean --optimize
```

This will perform all cleanup tasks above, plus:
- ⚡ Cache config
- ⚡ Cache routes
- ⚡ Cache views
- ⚡ Cache events
- ⚡ Optimize application

### When to Use

**Use `app:clean` when:**
- You've made changes to config files
- You've updated routes
- You've modified views
- Queue workers aren't picking up new code
- You're experiencing caching issues
- After pulling new code from Git
- Before deploying to production

**Use `app:clean --optimize` when:**
- Deploying to production
- After running `composer update`
- When you want maximum performance after cleanup

### Development vs Production

**Development:**
```bash
php artisan app:clean
```
Clears everything without caching, so changes are picked up immediately.

**Production:**
```bash
php artisan app:clean --optimize
```
Clears everything and rebuilds caches for optimal performance.

### Command Location

`app/Console/Commands/AppCleanCommand.php`

