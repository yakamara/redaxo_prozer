<?php

class pz_paginate_screen{

	private 
		$list_amount = 10,
		$list_max = 5000,
		$counter_all,
		$link_vars = array(),
		$elements = array(),
		$current_elements = array(),
		$current = 0,
		$page_current = 0,
		$page_all = 0;
		

	public function __construct($elements) 
	{
		$this->elements = array_values($elements);
		$this->current_elements = array_values($elements);
		$this->counter_all = count($elements);
	}

	public function setListAmount($l)
	{
		$this->list_amount = (int) $l;	
	}

	private function getUrl($p, $skip)
	{
		$p["linkvars"]["skip"] = $skip;
		return "javascript:pz_loadPage('".$p["layer"]."','".
			pz::url($p["mediaview"],$p["controll"],$p["function"],$p["linkvars"])."')";
		
	}


	public function getPlainView($p = array())
	{
	
		if($this->counter_all <= $this->list_amount) return '';
	
		// TODO - Linkmanagement..
		// order
		// skip
		// layer
		// linkvars


    $scroll = rex_request("scroll","int",0);
	
		$current = rex_request("skip","int",0);
		if($current > $this->counter_all || $current < 0) $current = 0;
		
		$last = (intval($this->counter_all/$this->list_amount)*$this->list_amount)-$this->list_amount;
		$next = $current+$this->list_amount;
		if($next >= $this->counter_all) $next = "";
		$prev = $current-$this->list_amount;
		if($prev < 0) $prev = "";
		
		$page_current = intval($current/$this->list_amount);
		$page_all = intval(($this->counter_all-1)/$this->list_amount);
		
		/*
		$echo .=  'Ergebnissliste'.@$p["first_c"].' bis '.@$p["last_c"].' von '.$this->counter_all.' Treffer';
		 */
		$echo =  '<ul class="pagination">';
		// $echo .=  '<li><a class="page bt7" href="'.pz::url("api","oo",$p).'">erste Seite</a></li>'; // ,"0"
		if($prev !== "") {
		  
		  $echo .=  '<li class="first prev active"><a class="page prev bt5" href="'.$this->getUrl($p,$prev).'"><span class="inner">zurück</span></a></li>'; // $prev
		}else {
		  $echo .=  '<li class="first prev"><a class="page prev bt5 inactive" href="'.pz::url().'"><span class="inner">zurück</span></a></li>';
		}
		
		$show_pages = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9);
		if($page_all > count($show_pages)) {
			$show_pages = array();
			$show_pages[0] = 0;
			$show_pages[1] = 1;
			$show_pages[2] = 2;
			if($page_current<($page_all/3) || $page_current>($page_all/3*2)) {
				$m = (int) ($page_all / 2);
				$show_pages[$m-1] = $m-1;
				$show_pages[$m] = $m;
				$show_pages[$m+1] = $m+1;
			}
			$show_pages[$page_current-1] = $page_current-1;
			$show_pages[$page_current] = $page_current;
			$show_pages[$page_current+1] = $page_current+1;
			$show_pages[$page_all-2] = $page_all-2;
			$show_pages[$page_all-1] = $page_all-1;
			$show_pages[$page_all] = $page_all;
		}

		if($next !== "") {
		  $echo .=  '<li class="next"><a class="page next bt5" href="'.$this->getUrl($p,$next).'"><span class="inner">vorwärts</span></a></li>'; // $next
		}else {
		  $echo .=  '<li class="next"><a class="page next bt5 inactive" href="'.pz::url().'"><span class="inner">vorwärts</span></a></li>';
		}

		$dot = TRUE;
		for($i=0;$i<=$page_all;$i++) {
			if($page_current == $i) {
		  		$echo .=  '<li><a class="page bt7 active" href="'.$this->getUrl($p,($i*$this->list_amount)).'">'.($i+1).'</a></li>'; // ($i*$this->list_amount)
				$dot = TRUE;
			}elseif(in_array($i,$show_pages)) {
		  		$echo .=  '<li><a class="page bt7" href="'.$this->getUrl($p,($i*$this->list_amount)).'">'.($i+1).'</a></li>'; // ($i*$this->list_amount)
				$dot = TRUE;
			}elseif($dot) {
				$echo .=  '<li><a class="page bt7" href="'.pz::url().'">...</a></li>';
				$dot = FALSE;
			}
		}
		// $echo .=  '<li><a class="page bt7" href="'.pz::url("api","oo",$p).'">letzte Seite</a></li>'; // $last
		
		$echo .=  '</ul>';
		
		$count_from = ($page_current*$this->list_amount)+1;
		$count_to = (($page_current+1)*$this->list_amount);
		if($count_to > $this->counter_all) 
			$count_to = $this->counter_all;

    $greater = "";
    if($this->counter_all == $this->list_max)
      $greater = "&gt;";

		$links = array();
		$links[] = '<li>'.$count_from.' - '.$count_to.' von '.$greater.$this->counter_all.' Treffern</li>';
		
		$echo = '<div class="grid2col setting"><div class="column first">'.$echo.'</div><div class="column last"><ul>'.implode("",$links).'</ul></div></div>';
	
	  $this->current = $current;
	  $this->page_current = $page_current;
	  $this->page_all = $page_all;
	  
		$this->current_elements = array();
		for($i=$current;$i<($current+$this->list_amount);$i++)
		{
			if(isset($this->elements[$i]))
				$this->current_elements[] = $this->elements[$i];
		}
		
		if($scroll != 1) return $echo;
		else return "";
	}

	public function getCurrentElements()
	{
		return $this->current_elements;
	
	}

  public function setPaginateLoader($p, $append_layer)
  {
    $return = "";
    if($this->page_current < $this->page_all)
    {
      $load_id = $p["layer"].'-'.$this->page_current.'-paginate';
      
      $p["linkvars"]["scroll"] = 1;
      $p["linkvars"]["skip"] = ($this->current+$this->list_amount);
      
      $link = "pz_paginatePage('".$append_layer."','".pz::url($p["mediaview"],$p["controll"],$p["function"],$p["linkvars"])."','#".$load_id."','#".$load_id."');";

      $return .= '<div id="'.$load_id.'" class="page-load not-visible">'.pz_i18n::msg("paginate_page",($this->page_current+2)).'</div>';

      $return .= '<script>
      $(document).ready(function() {
        $("#'.$load_id.'").on("enterviewport",function(){
          '.$link.'
        }).bullseye().on("click",function(){
          $(this).trigger("enterviewport");
        });
      })
      </script>';
      
    }
    return $return;
  }

  public function isScrollPage()
  {
    if(rex_request("scroll","int",0) == 1)
    {
      return true;
    }
    return false;
  }



}