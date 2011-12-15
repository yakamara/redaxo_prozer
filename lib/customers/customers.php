<?php

class pz_customers{

	public $search_table = "pz_customers";
	static $customers = array();

	static function get() {
		
		if(count(self::$customers)>0) {
			return self::$customers;
		}
		
		$sql = rex_sql::factory();
		$sql->setQuery('select * from pz_customer order by id');
		
		foreach($sql->getArray() as $l)
		{
			$customer = new pz_customer($l);
			self::$customers[$customer->getId()] = $customer;
		}

		return self::$customers;
		
	}

	static function getAsString() {
	
		$return = array();
		foreach(self::get() as $customer) {
			$v = $customer->getName();
			$v = str_replace('=','',$v);
			$v = str_replace(',','',$v);
			$return[] = $v.'='.$customer->getId();
		}
		return implode(",",$return);
		
	}

}