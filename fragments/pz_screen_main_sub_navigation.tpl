
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
     
    if(isset($this->flyout)) echo $this->flyout;
    
    ?>
    
    
    
    <?php

    /*
      <ul class="sl1 w3">
        <li class="selected"><span class="selected">Bitte wählen Sie ein Projekt...</span>
          <div class="flyout">
            <div class="content">
              <ul class="entries">
                <li class="entry first"><a class="email" href=""><span class="name">Christian Sittler</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
                <li class="entry"><a class="email" href=""><span class="name">Christian Sittler</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
                <li class="entry"><a class="email" href=""><span class="name">Christian Sittler</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
                <li class="entry last"><a class="email" href=""><span class="name">Christian Sittler</span><span class="title">Lorem ipsum dolor sit amet</span></a></li>
              </ul>
            </div>
          </div>
        </li>
      </ul>
	*/

      
      
        // Test fuer Fragment
/*
		if(isset($this->entries) && is_array($this->entries) && count($this->entries) > 0 )
		{
*/	        



/*
	        for ($i = 1; $i <= 10; $i++)
	        {
	          $entries[$i]['url'] = '#';
	          $entries[$i]['class'] = 'email';
	          
	          if ($i == 2)
	           $entries[$i]['class'] = 'email active';
	          
	          $entries[$i]['name'] = 'Jan Mikael Kristinus';
	          $entries[$i]['info'] = '15.12.2011';
	          $entries[$i]['title'] = 'Prozer the next generation';
	          $entries[$i]['checkbox'] = '<input type="checkbox" name="" value="" />'; // braucht keine Id, da implizierte Variante
	        }
	        
	        $f = new rex_fragment();
	        $f->setVar('class_ul', 'w3', false);
	        $f->setVar('class_selected', '', false);
	        $f->setVar('selected', $title, false);
	        $f->setVar('entries', $entries, false);
	        $f->setVar('extra', '', false);
	        echo $f->parse('pz_select_dropdown');
	        */
//		}

      ?>
    </section>