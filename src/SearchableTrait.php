<?php namespace ArchyBold\EloquentSearchable;

trait SearchableTrait {

	static $_searchProvider = null;

	public function search($query){
		$search = self::getSearchProvider();
		return $search->
		search(get_class($this), $query);
	}

	public function getSearchBody(){
		$searchableCols = array();
		foreach ($this->searchable['columns'] as $column) {
			if (strpos($column, '.') !== false){
				// We're dealing with a column found through a relationship.
				$rels = explode('.', $column);
				$numRels = count($rels) - 1;
				$obj = $this;
				$indexArray = &$searchableCols;
				// Loop through each relationship until we get to the last one.
				for ($i = 0; $i < $numRels; $i++) {
					// Add the index to the array if it's not there.
					$rel = $rels[$i];
					if (!array_key_exists($rel, $indexArray)){
						$indexArray[$rel] = array();
					}
					// One step lower.
					$indexArray = &$indexArray[$rel];
					$obj = $obj->$rel;
				}
				// Assign the data.
				$column = $rels[$numRels];
				$indexArray[$column] = utf8_encode($obj->$column);
			}
			else{
				$searchableCols[$column] = utf8_encode($this->$column);
			}
		}
		return $searchableCols;
	}

	protected static function getSearchProvider(){
		if (is_null(self::$_searchProvider)){
			self::$_searchProvider = app('ArchyBold\EloquentSearchable\SearchProvider');
		}
		return self::$_searchProvider;
	}

	public static function bootSearchableTrait(){
		static::created(function($item){
			$search = self::getSearchProvider();
			$search->indexItem($item);
		});

		static::updated(function($item){
			$search = self::getSearchProvider();
			$search->updateItemIndex($item);
		});
		
		static::deleted(function($item){
			$search = self::getSearchProvider();
			$search->removeIndex($item);
		});
	}
	
}
