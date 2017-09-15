# TESTING Bootstrap 3 & 4 LogViewer

#### To be deleted!

I'm new to package development.  

I did not know how to test the LogViewer package other than creating a local filesystem repository.

If there is a better way please let me know!

Here is how I developed:

1.  Download or clone the https://github.com/derekphilipau/LogViewer

2.  Create a new laravel installation.  Generate application key.

3.  In composer.json, add a filesystem repository:

```
    "repositories": [
        {
            "type": "path",
            "url": "/Path/to/project/on/your/filesystem/LogViewer"
        }
    ],
```

4.  Add in the filesystem LogViewer:

```
    composer require "arcanedev/log-viewer @dev"
```

5.  Publish the LogViewer files:

```
     artisan log-viewer:publish
```

6.  In config/app.php, set loggin to "daily":
    
```
    'log' => env('APP_LOG', 'daily'),
```

7.  In config/log-viewer.php, set theme to either bootstrap-3 or bootstrap-4:

```
    'theme'         => 'bootstrap-3',
```

8.  View the website

```
    php artisan serve
```

9.  Open in browser:

```
    http://127.0.0.1:8000/log-viewer
```
