
    <section class="column first">
      <ul id="navi-lev2">
        <?php
        foreach($this->items as $key => $item):
		      $active = "";
      		if($this->item_active == $key) $active = " active";
      		
		      $class = "";
      		if(isset($item["classes"]) && $item["classes"] != '') $class = ' '.$item["classes"];
      		
        	echo '<li class="lev2'.$active.$class.'"><a class="lev2 bt3'.$active.$class.'" href="'.$item["url"].'">'.$item["name"].'</a></li>';
      	endforeach;
      	?>
      </ul>  
    </section>
    
    <section class="column last">
    <?php
    if(isset($this->flyout)) 
      echo $this->flyout;
    ?>
    </section>