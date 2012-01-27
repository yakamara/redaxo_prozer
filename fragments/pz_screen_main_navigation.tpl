<ul id="navi-main">
  
  <li class="lev1 logo"><a href="/"><?php echo rex_i18n::msg('startpage'); ?></a></li>
	<?php foreach($this->items as $key => $item):
		$active = '';
		if($this->item_active == $key) {
			$active = " active";
		}
		$class = '';
		if (isset($item["classes"]) && $item["classes"] != '') $class = ' '.$item["classes"];
		echo '<li class="lev1'.$active.$class.'">';
		echo '<a class="lev1'.$active.$class.'" href="'.$item["url"].'">'.$item["name"];
		if (isset($item["span"]) && $item["span"] != "")
			echo '<span class="info info1"><span class="inner">'.$item["span"].'</span></span>';
		echo '</a>';
		
		if(isset($item["flyout"]))
		{
			echo $item["flyout"];
		}
		
		echo '</li>';
	endforeach; ?>

</ul>