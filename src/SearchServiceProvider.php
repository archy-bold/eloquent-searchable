<?php namespace ArchyBold\EloquentSearchable;

use ArchyBold\EloquentSearchable\SearchProvider;
use ArchyBold\EloquentSearchable\ElasticSearch\ElasticSearchProvider;

use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('ArchyBold\EloquentSearchable\SearchProvider', function()
        {
        	$search = new ElasticSearchProvider();
        	$search->boot();
            return $search;
        });
	}

}
