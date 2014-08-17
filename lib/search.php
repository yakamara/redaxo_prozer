<?php

class pz_search extends pz_controller{

	public $search_items = array(); // Treffer
	public $search_order = '';
	public $search_link_search_vars = array();
	public $search_vars = array();
	public $search_url = "";
	public $search_count = 0;
	public $search_list_amount = 10;
	public $search_counter_all = 0; // Gesamtanzahl der Ergebnisse
	public $search_counter_pos = 0; // Position der aktuellen Abfrage
	public $search_counter_query = 0; // Anzahl der Ergebnisse des direkten Aufrufes - muss kleiner/gleich search_list_amount sein
	public $search_search_pages = 0; // Anzahl Seiten
	public $search_page_current = 0; // Aktuelle Seite
	public $search_table = "pz";
	public $search_fulltext_fields = array("fulltext" => 1,"name" => 5); // fieldname => relevance
	public $search_order_fields = array('id_new' => 'id desc', 'id_old' => 'id asc');

	function pz_search() {
		$this->search_elements = array();
		$this->setSearchOrderKey();
		$this->setSearchPaginateField('search[pos]');
	}

	function setSearchTable($search_table) {
		$this->search_table = $search_table;
	}

	function getSearchTable() {
		return $this->search_table;
	}

	function setSearchLinkvars($k,$v)	{
		$this->search_link_search_vars[$k] = $v;
	}

	function getSearchVars() {
		return $this->search_link_search_vars;
	}

	function setSearchUrl($search_url) {
		$this->search_url = $search_url;
	}

	function setSearchVars($label, $k, $v, $how = "=") {
		$this->search_vars[$label] = array('key'=>$k,'value'=>$v,'how'=>$how);
	}
	
	function setSearchListAmount($la = 5) {
		$this->search_list_amount = $la;
	}

	function getSearchListAmount() {
		return $this->search_list_amount;
	}
	
	function setSearchPaginateField($pf) {
		$this->paginate_field = $pf;
	}
	
	function getSearchRows() {
		return $this->search_counter_all;
	}
	
	function setSearchOrderKey($sort = '')
	{
		if(!array_key_exists($sort,$this->search_order_fields)) {
			$this->search_order_key = key($this->search_order_fields);
		}else {
			$this->search_order_key = $sort; // $sort;
		}
		$this->search_order_value = $this->search_order_fields[$this->search_order_key];
		return $this->search_order_key;	
	}
	
	function getSearchOrderKey() {
		return $this->search_order_key;
	}

	function executeSearch($pos = 0) {

		$this->search_pos = (int) $pos;
		$relevances = array();
		
		$relevances[] = "1";

		$ps_i = 0;
		$ps_replaces = array();


		$query = array();
		foreach($this->search_vars as $label => $v)
		{
			$singlequery = "";
			$ps_i_key = ":v".$ps_i;
			$ps_replaces[$ps_i_key] = $v["value"];
			
			$ps_i++;
			switch($v["how"])
			{
				case("<="):
					$singlequery = '(`'.$v['key'].'` <= \''.mysql_real_escape_string($v["value"]).'\')';
					break;
				case(">="):
					$singlequery = '(`'.$v['key'].'` >= \''.mysql_real_escape_string($v["value"]).'\')';
					break;
				case("<"):
					$singlequery = '(`'.$v['key'].'` < \''.mysql_real_escape_string($v["value"]).'\')';
					break;
				case(">"):
					$singlequery = '(`'.$v['key'].'` > \''.mysql_real_escape_string($v["value"]).'\')';
					break;
				case("<>"):
					$singlequery = '(`'.$v['key'].'` <> \''.mysql_real_escape_string($v["value"]).'\')';
					break;
				case("STARTLIKE"):
					$singlequery = '(`'.$v['key'].'`  LIKE \''.mysql_real_escape_string($v["value"].'%').'\')';
					break;
				case("LIKE"):
					if($v['key'] == "fulltext") {
						foreach($this->search_fulltext_fields as $i_field => $i_relevance) {
							if($singlequery == "") { 
								$singlequery = 'MATCH (`'.mysql_real_escape_string($field).'`) AGAINST (\''.mysql_real_escape_string($v["value"]).'\' IN BOOLEAN MODE)';
							}
							$relevances[] = '('.$i_field.' * '.$i_relevance.')';
						}
					}else {
						$singlequery = '(`'.$v['key'].'` LIKE \''.mysql_real_escape_string('%'.$v["value"].'%').'\')';
					}
					break;
				case("OR_LIKE"):		
					$q = array();
					foreach(explode(",",$v["value"]) as $sv) { 
						$kkk = 'MATCH (`'.$v['key'].'`) AGAINST (\''.mysql_real_escape_string($sv).'\' IN BOOLEAN MODE)';
						$singlequery = '('.$kkk.')';
						$q[] = $singlequery;
					}
					$singlequery = '('.implode(" OR ",$q).')';
					break;
				case("OR_EXPLODE"):
					$q = array();
					foreach(explode(",",$v["value"]) as $sv){ $q[] = '`'.$v['key'].'` LIKE \''.mysql_real_escape_string($sv).'%\''; }
					$singlequery = '('.implode(" OR ",$q).')';
					break;
				case("OR_EXPLODE="):
					$q = array();
					foreach(explode(",",$v["value"]) as $sv){ $q[] = '`'.$v['key'].'` = \''.mysql_real_escape_string($sv).'\''; }
					$singlequery = '('.implode(" OR ",$q).')';
					break;
				default:
					$singlequery = '(`'.$v['key'].'`';
					$singlequery .= '=';
					$singlequery .= '\''.$ps_i_key.'\')';
			}
			if($singlequery != "")
				$query[] = $singlequery;
		}
		
		$where = implode(" AND ",$query);
		if($where != "") $where = " where $where";
		$query_counter_sql = 'select count(id) as c from '.$this->getSearchTable().$where.' ';

		pz::debug($query_counter_sql);

		$q = pz_sql::factory();
		// $q->setQuery($query_counter_sql);
		$q->setQuery($query_counter_sql, $ps_replaces);
		
		$aq = $q->getArray();
		$this->search_counter_all = $aq[0]['c'];
		if($this->search_pos >= $this->search_counter_all) { 
			$this->search_pos = 0;
		}

		$relevance = '';
		if(count($relevances)>0) {
			$relevance = ',('.implode(' + ', $relevances).') as relevance';
		}		
		
		$query_sql = 'select * '.$relevance.' from '.$this->getSearchTable().$where.' order by '.$this->search_order_value.' LIMIT '.$pos.','.$this->search_list_amount;
		$q = pz_sql::factory();
		// $q->setQuery($query_sql);
		$q->setQuery($query_sql, $ps_replaces);
		
		
		$this->search_items = $q->getArray();
		$this->search_counter_query = count($this->search_items);

	}

	function getSearchItems()
	{
		return $this->search_items;
	}

	function setSearchItems($search_items)
	{
		$this->search_items = $search_items;
	}




	// ------------------------------------------------------------------- Views
	
	/*
	<div class="grid1col setting">
	    <div class="column first last">
	      <ul class="pagination">
	        <li class="first prev"><a class="page prev bt5" href="#"><span class="inner">zurück</span></a></li>
	        <li class="next"><a class="page next bt5" href="#"><span class="inner">vorwärts</span></a></li>
	        <li><a class="page bt7" href="#">1</a></li>
	        <li><a class="page bt7" href="#">2</a></li>
	        <li class="last"><a class="page bt7" href="#">3</a></li>
	        <li class="last"><a class="page bt7" href="#">...</a></li>
	      </ul>
	    </div>
	  </div>
	*/
	
	
	function getSearchPaginatePlainView($p = array())
	{
	
		if(!isset($p["skip_key"])) $p["skip_key"] = "skip";
		$current = (int) @$_REQUEST[$p["skip_key"]];
		
		$p["mediaview"] = "screen";
		if( !isset($p["linkvars"]) ) {
			$p["linkvars"] = array();
		}

		if( !isset($p["controll"]) ) {
			$p["controll"] = $this->getName();
		}

		if( !isset($p["function"]) ) {
			$p["function"] = $this->function;
		}


		if($current > $this->search_counter_all || $current < 0) $current = 0;
		
		$last = (intval($this->search_counter_all/$this->search_list_amount)*$this->search_list_amount)-$this->search_list_amount;
		$next = $current+$this->search_list_amount;
		if($next >= $this->search_counter_all) $next = "";
		$prev = $current-$this->search_list_amount;
		if($prev < 0) $prev = "";
		
		$page_current = intval($current/$this->search_list_amount);
		$page_all = intval(($this->search_counter_all-1)/$this->search_list_amount);
		
		/* $echo .=  'Ergebnissliste'.@$p["first_c"].' bis '.@$p["last_c"].' von '.$this->search_counter_all.' Treffer'; */
		$echo =  '<ul class="pagination">';
		// $echo .=  '<li><a class="page bt7" href="'.pz::url("api","oo",$p).'">erste Seite</a></li>'; // ,"0"
		if($prev !== "") {
		  $p["linkvars"][$p["skip_key"]] = $prev;
		  $echo .=  '<li class="first prev active"><a class="page prev bt5" href="'.pz::url($p["mediaview"],$p["controll"],$p["function"],$p["linkvars"]).'"><span class="inner">zurück</span></a></li>'; // $prev
		}else {
		  $echo .=  '<li class="first prev"><a class="page prev bt5 inactive" href="'.pz::url().'"><span class="inner">zurück</span></a></li>';
		}
		
		$show_pages = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6);
		if($page_all > 6) {
			$show_pages = array();
			$show_pages[0] = 0;
			$show_pages[1] = 1;
			if($page_current<($page_all/3) || $page_current>($page_all/3*2)) {
				$m = (int) ($page_all / 2);
				$show_pages[$m-1] = $m-1;
				$show_pages[$m] = $m;
				$show_pages[$m+1] = $m+1;
			}
			$show_pages[$page_current-1] = $page_current-1;
			$show_pages[$page_current] = $page_current;
			$show_pages[$page_current+1] = $page_current+1;
			$show_pages[$page_all-1] = $page_all-1;
			$show_pages[$page_all] = $page_all;
		}

		if($next !== "") {
		  $p["linkvars"][$p["skip_key"]] = $next;
		  $echo .=  '<li class="next"><a class="page next bt5" href="'.pz::url($p["mediaview"],$p["controll"],$p["function"],$p["linkvars"]).'"><span class="inner">vorwärts</span></a></li>'; // $next
		}else {
		  $echo .=  '<li class="next"><a class="page next bt5 inactive" href="'.pz::url().'"><span class="inner">vorwärts</span></a></li>';
		}

		$dot = TRUE;
		for($i=0;$i<=$page_all;$i++) {
			if($page_current == $i) {
		  		$p["linkvars"][$p["skip_key"]] = ($i*$this->search_list_amount);
				$echo .=  '<li><a class="page bt7 active" href="'.pz::url($p["mediaview"],$p["controll"],$p["function"],$p["linkvars"]).'">'.($i+1).'</a></li>'; // ($i*$this->search_list_amount)
				$dot = TRUE;
			}elseif(in_array($i,$show_pages)) {
		  		$p["linkvars"][$p["skip_key"]] = ($i*$this->search_list_amount);
				$echo .=  '<li><a class="page bt7" href="'.pz::url($p["mediaview"],$p["controll"],$p["function"],$p["linkvars"]).'">'.($i+1).'</a></li>'; // ($i*$this->search_list_amount)
				$dot = TRUE;
			}elseif($dot) {
				$echo .=  '<li><a class="page bt7" href="'.pz::url().'">...</a></li>';
				$dot = FALSE;
			}
		}
		// $echo .=  '<li><a class="page bt7" href="'.pz::url("api","oo",$p).'">letzte Seite</a></li>'; // $last
		
		$echo .=  '</ul>';
		
		$echo = '<div class="grid1col setting"><div class="column first last">'.$echo.'</div></div>';
		
		return $echo;
	}







}