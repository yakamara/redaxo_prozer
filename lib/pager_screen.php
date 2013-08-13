<?php

class pz_pager_screen{

	public 
		$pager = "",
		$rows,
		$link_vars = array();

	public function __construct($pager, $layer) 
	{
	  $this->pager = $pager;
	  $this->layer = $layer;
	
	}

	private function getUrl($p, $skip)
	{
		$p["linkvars"]["skip"] = $skip;
		return "javascript:pz_loadPage('".$this->layer."','".
			pz::url($p["mediaview"],$p["controll"],$p["function"],$p["linkvars"])."')";
		
	}


	public function getPlainView($p = array(), $content = "")
	{
	
		if($this->pager->getRowCount() <= $this->pager->getRowsPerPage()) return $content;

		// if($this->pager->getCursor() > $this->pager->getRowCount() || $this->pager->getCursor() < 0) $this->pager->getCursor() = 0;
		
		$last = (intval($this->pager->getRowCount()/$this->pager->getRowsPerPage())*$this->pager->getRowsPerPage())-$this->pager->getRowsPerPage();
		$next = $this->pager->getCursor()+$this->pager->getRowsPerPage();
		if($next >= $this->pager->getRowCount()) $next = "";
		$prev = $this->pager->getCursor()-$this->pager->getRowsPerPage();
		if($prev < 0) $prev = "";
		
		$page_current = intval($this->pager->getCursor()/$this->pager->getRowsPerPage());
		$page_all = intval(($this->pager->getRowCount()-1)/$this->pager->getRowsPerPage());
		
		/*
		$echo .=  'Ergebnissliste'.@$p["first_c"].' bis '.@$p["last_c"].' von '.$this->pager->getRowCount().' Treffer';
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
		  		$echo .=  '<li><a class="page bt7 active" href="'.$this->getUrl($p,($i*$this->pager->getRowsPerPage())).'">'.($i+1).'</a></li>'; // ($i*$this->pager->getRowsPerPage())
				$dot = TRUE;
			}elseif(in_array($i,$show_pages)) {
		  		$echo .=  '<li><a class="page bt7" href="'.$this->getUrl($p,($i*$this->pager->getRowsPerPage())).'">'.($i+1).'</a></li>'; // ($i*$this->pager->getRowsPerPage())
				$dot = TRUE;
			}elseif($dot) {
				$echo .=  '<li><a class="page bt7" href="'.pz::url().'">...</a></li>';
				$dot = FALSE;
			}
		}
		
		$echo .=  '</ul>';
		
		$count_from = ($page_current*$this->pager->getRowsPerPage())+1;
		$count_to = (($page_current+1)*$this->pager->getRowsPerPage());
		if($count_to > $this->pager->getRowCount()) 
			$count_to = $this->pager->getRowCount();

    $greater = "";
    if($this->pager->getRowCount() == $this->list_max)
      $greater = "&gt;";

		$links = array();
		$links[] = '<li>'.$count_from.' - '.$count_to.' von '.$greater.$this->pager->getRowCount().' Treffern</li>';
		
		$echo = '<div class="grid2col setting"><div class="column first">'.$echo.'</div><div class="column last"><ul>'.implode("",$links).'</ul></div></div>';
		$echo .= $content.$this->setPaginateLoader($p);
		
		return $echo;
	}

  public function getScrollView($p = array(), $content = "") {
    return $content.$this->setPaginateLoader($p);
  }

  public function setPaginateLoader($p)
  {
    $return = ""; // .$this->pager->getCurrentPage()." < ".$this->pager->getLastPage();
    if($this->pager->getCurrentPage() < $this->pager->getLastPage())
    {
      $load_id = $this->layer.'-'.$this->pager->getCurrentPage().'-paginate';
      
      $p["linkvars"]["scroll"] = 1;
      $p["linkvars"]["skip"] = $this->pager->getCursor() + $this->pager->getRowsPerPage();
      
      $link = "pz_paginatePage('#".$this->layer."','".pz::url($p["mediaview"],$p["controll"],$p["function"],$p["linkvars"])."','#".$load_id."','#".$load_id."');";

      $return .= '<div id="'.$load_id.'" class="page-load not-visible">'.rex_i18n::msg("paginate_page",($this->pager->getCurrentPage()+2)).'</div>';

      $return .= '<script>
      $(document).ready(function() {
        $("#'.$load_id.'").bind("enterviewport",function(){
          '.$link.'
        }).bullseye().bind("click",function(){
          $(this).trigger("enterviewport");
        });
      })
      </script>';
      
      
    }
    return $return;
  }

  public function isScrollPage()
  {
    if(rex_request("scroll","int",0) == 1) {
      return true;
    }
    return false;
  }



}