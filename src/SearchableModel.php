<?php namespace ArchyBold\EloquentSearchable;

interface SearchableModel {

	public function search($query);
	public function getSearchBody();
}
