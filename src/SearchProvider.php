<?php namespace ArchyBold\EloquentSearchable;

interface SearchProvider {

	/**
	 * This function handles the registering of the event listeners and any other initialisation.
	 */
	public function boot();

	public function indexItem(SearchableModel $item);
	public function updateItemIndex(SearchableModel $item);
	public function removeIndex(SearchableModel $item);
	public function clearIndexes();
	
	public function reindexAll();
	public function search($type, $query);
	public function searchAll($query);
	
}
