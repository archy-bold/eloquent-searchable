# Eloquent Searchable

Add search functionality to Eloquent models in Laravel 5.

This package is a WIP and currently only supports very basic text search in the Java-based [elasticsearch](https://www.elastic.co/products/elasticsearch) currently. I will probably add fallback search functionality in the future, but feel free to fork and add additional third-party search engines.

> **NOTE**: This package is missing many features that means it's only useful for the most basic of searches. See the 'Issues' section for a list of known issues and functionality gaps. Improvements to come!

* [Requirements](#requirements)
* [Installation](#installation)
* [Setup Models](#setup)
* [Usage](#usage)
* [Dealing With Errors](#errors)
* [Issues](#issues)

<a name="requirements"></a>
## Requirements

### PHP >= 5.4.0

This pakages uses traits which require PHP 5.4. Laravel 5 also requires PHP 5.4.

### elasticsearch

The package currently only supports search through elasticsearch, so you'll need server access and to get that running before you can use this package. Download the elasticsearch server [here](https://www.elastic.co/downloads/elasticsearch).

Java is also required to use elasticsearch.

<a name="installation"></a>
## Installation

You can install the package through composer with the following command:

```bash
$ composer require 'archy-bold/eloquent-sluggable:0.*'
```

Next, run `composer update` from your command line to get the dependencies.

Then, update `config/app.php` and add an entry for the service provider.

```php
	'providers' => [

		// ...

		'ArchyBold\EloquentSearchable\SearchServiceProvider',

	];
```

To get the default config.php file you must run the following command:

```bash
$ php artisan vendor:publish
```

<a name="setup"></a>
## Setup

To make one of your models searchable, you should set it up as follows:

```php
use ArchyBold\EloquentSearchable\SearchableModel;
use ArchyBold\EloquentSearchable\SearchableTrait;

class User extends Model implements SearchableModel {

	use SearchableTrait;

	protected $searchable = [
		'columns' => [
			'name', // Reference columns that should be searchable here
			'country.name', // You can reference columns through relationships too.
		],
	];
```

Your model should implement `SearchableModel` and use `SearchableTrait`. You should also include a `$searchable` variable for configuration. Currently this only takes a `columns` argument. This should be an array of columns that should be indexed. You can also reference columns through relationships eg `country.name`.

> **NOTE**: This only currently supports 1:1 or m:1 relationships. 1:m or m:m relationships are not currently supported.

Next add your model to the `search.php` config file.

```php
	'models' => [
		'users' => 'App\User',
	],
```

Where the key is a unique name to identify the models in results.

This will allow you to search on models as follows:

```php
$user = new User;
$results = $user->search("query string");
```

Where the returned object is an `EloquentCollection` of the results.

> **NOTE**: By default, none of your models will be indexed. They will be automatically indexed when created or updated. If you delete a model, it will be removed from the index too.

<a name="usage"></a>
## Usage

As well as searching on the model itself (See above), you can search on all searchable models too.

First get an instance of the search provider by getting it from the app layer:

```php
$search = app('ArchyBold\EloquentSearchable\SearchProvider');
```

Or use [dependency injection](http://laravel.com/docs/5.0/controllers#dependency-injection-and-controllers) on your controllers.

```php
use ArchyBold\EloquentSearchable\SearchProvider;

class UserController extends Controller {

    /**
     * The search provider instance.
     */
    protected $search;

    /**
     * Create a new controller instance.
     *
     * @param  SearchProvider  $search
     * @return void
     */
    public function __construct(SearchProvider $search)
    {
        $this->search = $search;
    }

    // Or with methods injection ...

    public function search(SearchProvider $search)
    {
    	// Use search instance
    }

}
```

You then have access to an instance of a `SearchProvider` (in this case, always an `ElasticSearchProvider`). You can then call use the `searchAll()` function.

```php
	$results = $search->searchAll("query string");
	dd($results['users']);
```

This returns an array of `EloquentCollection`s, with each collection containing the results for the search on a different model. The array is indexed by the key you chose for the model in the search config.

<a name="errors"></a>
## Dealing With Errors

If you get the following exception:

```
CouldNotConnectToHost: couldn't connect to host
```

Elasticsearch isn't running. You'll need to start up the server with `bin/elasticsearch` where the `bin/` directory is wherever you installed your elasticsearch instance.

<a name="issues"></a>
## Issues

* Initial indexing - the only models to be indexed are those added after the package has been installed.
* Basic text search only - the package currently only supports very basic searching on text.
* Limited results - only a limited number of results (10) are currently returned and pagination isn't supported.
* Limited search engines - only elasticsearch is currently supported. You need server access and Java installed to use elasticsearch.
