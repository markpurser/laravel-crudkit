# laravel-crudkit
[![license](https://img.shields.io/github/license/mashape/apistatus.svg)](https://github.com/markpurser/laravel-crudkit/blob/master/LICENSE)

Open source CRUD framework for Laravel 5.3+.

## Should use this if...

* You need a front-end to your existing MySQL or Sqlite relational database
* You don't require a graphical builder or generator

## Getting started

Install Laravel 5.3 or above.

Require this package with composer using the following command:

```bash
composer require markpurser/laravel-crudkit
```

After updating composer, add the service providers to the `providers` array in `config/app.php`

```php
Markpurser\LaravelCrudKit\CrudKitServiceProvider::class,
Markpurser\LaravelCrudKit\Demo\BooksServiceProvider::class,
```

Publish the configuration file and public assets.

```bash
php artisan vendor:publish --provider="Markpurser\LaravelCrudKit\CrudKitServiceProvider"
```

In your .env file, set up your database connection to point to the example sqlite3 file.

```php
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/vendor/markpurser/laravel-crudkit/src/Demo/books.sqlite3
```

In your browser, go to `http://localhost:8000/admin-panel`

## Features

Many of the features that were experimental or incomplete in the original http://crudkit.com are now working, including:

* Many-to-one and many-to-many relationships
* Datetime data types
* Enum and boolean data types
* Correct pagination of entries in the summary view
* Simple search
* Support for soft delete and Laravel timestamps

Inspired by http://crudkit.com, this project is a complete rewrite with a simplified codebase.

## Documentation

Coming soon

## Future Roadmap

* Integration with Laravel Eloquent ORM
* Postgres, Sql Server

## Contributing

If you'd like to contribute, please fork the repository and use a feature branch. Pull requests are warmly welcome.

## Licensing

The code in this project is licensed under MIT license.
