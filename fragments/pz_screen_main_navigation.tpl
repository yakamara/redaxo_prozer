<ul id="navi-main">
  
  <li class="lev1 logo"><a href="/"><?php echo pz_i18n::msg('startpage'); ?></a></li>
	<?php foreach($this->items as $key => $item):
		$active = '';
		if($this->item_active == $key) {
			$active = " active";
		}
		$class = '';
		if (isset($item["classes"]) && $item["classes"] != '') $class = ' '.$item["classes"];
		echo '<li class="lev1'.$active.$class.'">';
		
		$a = '<a class="lev1'.$active.$class.'" href="'.$item["url"].'" title="'.pz_i18n::msg($item["name"]).'">'.pz_i18n::msg($item["name"]);
		if (isset($item["span"]) && $item["span"] != "")
			$a .= '<span class="info info1"><span class="inner">'.$item["span"].'</span></span>';
		$a .= '</a>';

		echo $a;
		
		if(isset($item["flyout"]))
		{
			echo $item["flyout"];
		}
		
		echo '</li>';
	endforeach; ?>

</ul>