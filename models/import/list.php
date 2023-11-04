<?php

class MBAI_Import_List extends MBAI_Model_List {
	public function __construct() {
		parent::__construct();
		$this->setTable(MBAI_Plugin::getInstance()->getTablePrefix() . 'imports');
	}
}