# Upgrade guide

## Upgrading from 1.2.x to 2.0

Version 2 adds a large-file streaming engine and two opt-in themes. Review the following items before deploying.

### 1. Update and publish configuration

Back up local configuration changes, update the package, and merge the new `streaming` section from `config/log-viewer.php`.

The default configuration enables streaming for files larger than 2 MB. To retain the classic whole-file behavior initially:

```php
'streaming' => [
    'enabled' => false,
],
```

### 2. Update custom contract implementations

If the application provides a custom implementation of `Arcanedev\LogViewer\Contracts\LogViewer`, add:

```php
public function logForViewer($date);
```

It must return an `Arcanedev\LogViewer\Entities\Log`. Implementations that do not support streaming may delegate to the existing `get($date)` method.

### 3. Prepare index storage

When `index-path` is `null`, index sidecars are stored beside their log files. The PHP process must be able to create and update those files.

For read-only or shared log mounts, configure a dedicated writable directory:

```php
'streaming' => [
    'index-path' => storage_path('framework/log-viewer-indexes'),
],
```

Create the directory during deployment and grant it the same ownership and permissions used by the PHP process.

### 4. Warm large-file indexes

Optionally build indexes during deployment:

```bash
php artisan log-viewer:index
```

Cold large files remain available through the bounded tail fallback while their index is being prepared.

### 5. Select a theme

The default remains `bootstrap-5`. To opt in:

```php
'theme' => 'observatory', // analytics and support workflow
// or
'theme' => 'console',     // dense message-first workflow
```

No npm installation or asset build is required.

### 6. Validate log formats

Version 2 supports the classic line formatter and JSON formatter through format drivers. Test representative production samples before enabling streaming globally, especially when using custom Monolog formatting.

### Rollback

Set `streaming.enabled` to `false` to return viewer requests to the classic path. Index sidecars are caches and may be removed after rollback when no process is using them.
