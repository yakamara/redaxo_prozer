<?php

class pz_customers
{

	public $search_table = "pz_customers";
	static $customers = NULL;
	static $activeCustomers = NULL;


	static function get() 
	{

		if(is_array(self::$customers)) 
		{
			return self::$customers;
		}
		
		$sql = rex_sql::factory();
		// $sql->debugsql = 1;
		$sql->setQuery('select * from pz_customer where archived=0 and archived IS NOT NULL order by name');
		
		foreach($sql->getArray() as $l)
		{
			$customer = new pz_customer($l);
			self::$customers[$customer->getId()] = $customer;
		}

		return self::$customers;
		
	}


	static function getActive() 
	{

		if(is_array(self::$activeCustomers)) 
		{
			return self::$activeCustomers;
		}
		
		$sql = rex_sql::factory();
		$sql->setQuery('select * from pz_customer where id in (select customer_id from pz_project where archived=0 and archived IS NOT NULL) and archived=0 and archived IS NOT NULL order by name');
		
		foreach($sql->getArray() as $l)
		{
			$customer = new pz_customer($l);
		  self::$activeCustomers[$customer->getId()] = $customer;
		}

		return self::$activeCustomers;
		
	}


	static function getAsString() 
	{
	
		$return = array();
		foreach(self::get() as $customer) 
		{
			$v = $customer->getName();
			$v = str_replace('=','',$v);
			$v = str_replace(',','',$v);
			$return[] = $v.'='.$customer->getId();
		}
		return implode(",",$return);
		
	}

}