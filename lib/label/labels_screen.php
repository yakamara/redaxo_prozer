<?php

class pz_labels_screen 
{

	public $labels;

	function __construct($labels)
	{
		$this->labels = $labels;
	}

	// --------------------------------------------------------------- Listviews

	function getListView($p = array()) 
	{
		$content = "";
		$design = "";
		
		foreach($this->labels as $label) 
		{
			$ls = new pz_label_screen($label);
			$content .= '<li class="lev1 entry">'.$ls->getListView($p).'</li>';
			$first = "";
		}
		
		$content = '<ul class="entries view-list">'.$content.'</ul>';

		// $content = $this->getSearchPaginatePlainView().$content;
		// $content = '<a href="'.pz::url('screen','tools','labels',array("mode"=>"add")).'">'.pz_i18n::msg("label_add").'</a>'.$content;

		$f = new pz_fragment();
		$f->setVar('design', $design, false);
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		$f->setVar('paginate', '', false);
	
		return '<div id="labels_list" class="design2col">'.$f->parse('pz_screen_list.tpl').'</div>';	
	}

}