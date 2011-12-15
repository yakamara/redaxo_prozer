<ul id="navi-main">
  
  <li class="lev1 logo"><a href="/">Startseite</a></li>
	<?php foreach($this->items as $key => $item):
		$active = '';
		if($this->item_active == $key) {
			$active = " active";
		}
		$class = '';
		if (isset($item["classes"]) && $item["classes"] != '') $class = ' '.$item["classes"];
		echo '<li class="lev1'.$active.$class.'">';
		echo '<a class="lev1'.$active.$class.'" href="'.$item["url"].'">'.$item["name"].'<span class="info1"><span class="inner">'.rand(0,200).'</span></span></a>';
		
		if(isset($item["flyout"]))
		{
			echo $item["flyout"];
		}
		
		echo '</li>';
	endforeach; ?>

</ul>