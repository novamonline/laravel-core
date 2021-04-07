# Laravel Custom Core Logic

This is meant to extend laravel methods and functions without being too specific to an application.
This code can be used by any application that wishes to take advantage of these customizations.

## Installation
* First, specify where you wish this package to be installed, e.g. root of the application, if you don't want it in the vendor folder
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
> composer requre novam/laravel-core
```

That's it!

## Core Structure
Logic is organized into the following folders:
### Boot
Contains logic for pipelines, service providers, observers, etc.

### Conf
Contains configurations

### Data
Contains logic for controllers, databases, migrations, models, etc.

### Dev
Contains all dev commands and scripts needed in your dev environment

### Http
Contains logic for controllers, requests, resources, middlewares, etc.

### Mock
Entry point for facades and fixtures 
