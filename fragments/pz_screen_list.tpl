	
  <div<?php echo (@$this->design != '') ? ' class="'.trim(@$this->design).'"' : ''; ?>>
	
	<header>
	
    <div class="grid2col header setting-layout">
      <div class="column first">
        <h1 class="hl1"><?php echo $this->title; ?></h1>
      </div>
    
      <div class="column last">
        <ul class="sl1 view-layout">

		  <?php if(is_array(@$this->orders) && count($this->orders) > 0){ ?>
		  
          <li class="first selected"><span class="selected">Sortierung: <?php echo $this->order; ?></span>
            <div class="flyout">
              <div class="content">
                <ul class="entries sort">
				  <?php
				  $first = " first";
				  foreach($this->orders as $sk => $sv) {
				    $active = "";
				    if($this->order == $sk) $active = "active";
				  	echo '<li class="entry'.$first.'"><a class="'.$active.'" href="'.$sv["link"].'">'.$sv["name"].'</a></li>';
				  	$first = "";
				 	// last fehlt
				  }
				  ?>
                </ul>
              </div>
            </div>
          </li>
          <?php } ?>

		  <?php if(is_array(@$this->listviews) && count($this->listviews) > 0){ 
			  $first = " split-v";
			  foreach($this->listviews as $lk => $lv) {
			    $active = "";
				if($this->listview == $lk)  $active = " active";
			  	echo '<li class="view'.$first.'"><a class="last '.$lk.''.$active.'" href="'.$lv["link"].'">'.$lv["name"].'</a></li>';
			  	$first = "last ";
			 	// last fehlt
			 }
			 /*
          <li class="split-v view"><a class="block active" href="#">als Block</a></li>
          <li class="last view"><a class="list" href="#">als Liste</a></li>
          <li class="last view"><a class="table" href="#">als Tabelle</a></li>
          <li class="last view"><a class="matrix" href="#">als Matrix</a></li>
            */
		  } ?>

        </ul>
      </div>
    </div>
	    
	</header>
  
	<?php echo $this->content; ?>
  
	<footer>
	
	  <?php echo @$this->paginate; ?>
	  <?php 
	  /*
	  <div class="grid2col setting">
	    <div class="column first">
	      <ul class="pagination">
	        <li class="first prev"><a class="page prev bt5" href="#"><span class="inner">zurück</span></a></li>
	        <li class="next"><a class="page next bt5" href="#"><span class="inner">vorwärts</span></a></li>
	        <li><a class="page bt7" href="#">1</a></li>
	        <li><a class="page bt7" href="#">2</a></li>
	        <li class="last"><a class="page bt7" href="#">3</a></li>
	        <li class="last"><a class="page bt7" href="#">...</a></li>
	      </ul>
	    </div>
	  
	    <div class="column last">
	      <ul class="sl1">
	        <li class="selected"><span class="selected">25</span>
	          <div class="flyout">
	            <div class="content">
	              <ul class="entries perpage">
	                <li class="entry first"><a href="">10</a></li>
	                <li class="entry"><a class="active" href="">25</a></li>
	                <li class="entry"><a href="">50</a></li>
	                <li class="entry last"><a href="">100</a></li>
	              </ul>
	            </div>
	          </div>
	        </li>
	      </ul>
	    </div>
	  </div>
	  <?php 
	  */ 
	  ?>
	</footer>
	
	</div>
