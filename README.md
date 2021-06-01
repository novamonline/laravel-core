# Laravel Custom Core Logic

This is meant to extend laravel methods and functions without being too specific to an application.
This code can be used by any application that wishes to take advantage of these customizations.

## Installation
* First, specify where you wish this package to be installed, e.g. root of the application, if you don't want it in the root folder
* Then, add the Core namespace to your composer.json file. NOTE: If you let it install in the vendor folder you don't need to do this:
```
...
"autoload": {
    ...
    "psr-4": {
        ...
        "Core\\": "core/src",
        ...
    }
},
...
```

* Finally, just require it using composer
```shell script
> composer requre popcx/core
```

That's it!

## Core Structure
### Boot
Contains logic for pipelines, service providers, observers, etc.

### Data
Contains logic for controllers, databases, migrations, models, etc.

### Http
Contains logic for controllers, requests, resources, middlewares, etc.
