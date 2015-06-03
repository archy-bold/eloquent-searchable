<?php namespace ArchyBold\EloquentSearchable\ElasticSearch;

use \Config;
use \Exception;
use \Elasticsearch\Client;

use ArchyBold\EloquentSearchable\SearchableModel;
use ArchyBold\EloquentSearchable\SearchException;
use ArchyBold\EloquentSearchable\SearchProvider;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ElasticSearchProvider implements SearchProvider {

	private $client = null;
	private $indexName = null;
	private $registeredModels = array();

	public function boot(){
		//  Create a new client.
		$this->client = new Client();

		// Get the index name.
		if (Config::has('search.app_name')){
			$this->indexName = Config::get('search.app_name');
		}
		else{
			throw new SearchException("You must have a valid search.php config file with the app_name extry set to use ElasticSearch.");
		}

		// Get the registered models from the configuration file.
		if (Config::has('search.models')){
			$registeredModels = Config::get('search.models');

			if (is_array($registeredModels)){
				$this->registeredModels = $registeredModels;
			}
		}
	}

	public function indexItem(SearchableModel $item){
		$type = $this->getTypeForModel($item);

		try{
			$this->client->index(array(
				'index' => $this->indexName,
				'type'  => $type,
				'id'    => $item->id,
				'body'  => json_encode($item->getSearchBody()),
			));
		}
		catch (Exception $e){
			throw new SearchException("Error inserting index for $type with ID $item->id. Message: ".$e->getMessage());
		}
	}

	public function updateItemIndex(SearchableModel $item){
		// Can just call the standard index method in the event of updating with Elastic.
		$this->indexItem($item);
	}

	public function removeIndex(SearchableModel $item){
		$type = $this->getTypeForModel($item);

		$this->client->delete(array(
			'index' => $this->indexName,
			'type'  => $type,
			'id'    => $item->id,
		));
	}

	public function reindexAll(){
		// Does nothing since we index as we go along with this.
	}

	public function search($type, $query){
		// Get the type first
		if (in_array($type, $this->registeredModels)){
			$type = array_search($type, $this->registeredModels);
		}

		$rawResults = $this->client->search(array(
			'index' => $this->indexName,
			'type'  => $type,
			'body'  => array(
				'query' => array(
					'query_string' => array('query' =>$query),
				),
			),
		));

		$results = new EloquentCollection;

		// Loop through all the results and get the IDs.
		if ($rawResults['hits']['total'] > 0){
			$resultIds = array();
			
			foreach ($rawResults['hits']['hits'] as $result) {
				$resultIds[] = $result['_id'];
			}

			// Now convert to Eloquent models.
			$class = $this->registeredModels[$type];
			$results = $class::find($resultIds);
		}

		return $results;
	}

	public function searchAll($query){
		$results = array();
		// Loop through each model and return the results as an array of EloquentCollections.
		// The key being the type.
		foreach ($this->registeredModels as $type => $class) {
			$results[$type] = $this->search($type, $query);
		}

		return $results;
	}

	public function clearIndexes(){
		$this->client->indices()->delete(array(
			'index' => $this->indexName,
		));
	}

	/**
	 * Function to get the type name given a model instance.
	 *
	 * @return string The type name 
	 */
	protected function getTypeForModel(SearchableModel $item){
		return array_search(get_class($item), $this->registeredModels);
	}
	
}
