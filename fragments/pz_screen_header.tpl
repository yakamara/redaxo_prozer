        
    <nav>
      <?php echo $this->navigation; ?>
    </nav>
    
    <ul id="navi-meta" class="sl1">
      <li class="lev1 first date"><span class="date"><?php echo pz::strftime(rex_i18n::msg("show_date_long"),pz::getDatetime()->format("U")); ?></span></li>
      <?php if(pz::getUser()) { ?>
      
      <!--

      <li class="lev1 search">
        <div class="search">
        <form action="index.php" method="post" enctype="multipart/form-data">
        <fieldset>
          <p class="formtext">
            <input type="text" class="text" name="q" value="<?php echo htmlspecialchars(rex_i18n::msg("enter_keyword")); ?>" onblur="if(this.value == '') this.value='<?php echo htmlspecialchars(rex_i18n::msg("enter_keyword")); ?>'" onfocus="if(this.value == '<?php echo htmlspecialchars(rex_i18n::msg("enter_keyword")); ?>') this.value=''"  />
            <a class="bt1 search" href=""><span><?php echo htmlspecialchars(rex_i18n::msg("search")); ?></span></a>
          </p>
        </fieldset>
        </form>
        </div>
      </li>
      
      -->

      <li class="lev1 clipboard"><?php echo pz_screen::getTooltipView('<a class="bt1 clipboard" href="javascript:void(0)" onclick="pz_loadClipboard();"><span>'.htmlspecialchars(rex_i18n::msg("clipboard")).'</span></a>',rex_i18n::msg("clipboard")); ?>
      </li>
      
      <?php } ?>

      <?php if(@$this->user != "") { ?>

	      <li class="lev1 user selected"><span class="selected"><?php echo htmlspecialchars(pz::cutText($this->user,24)); ?></span>
		      <?php
		      
		      if(isset($this->user_navigation) && is_array($this->user_navigation))
		      {
		      	echo '<div class="flyout">
		          <div class="content">
		            <ul class="entries">';	
		      	foreach($this->user_navigation as $user) 
		      	{
		      		echo '<li class="entry "><a href="'.$user["link"].'"><span class="title">'.htmlspecialchars(pz::cutText($user["name"],24)).'</span></a></li>';
		      	}
		      	echo '</ul>
		          </div>
		        </div>';
		      }
		      
		      ?></li>
      
      <?php } ?>

	  <?php 
	  if(pz::getUser()) {
	  	echo '<li class="lev1 last logout"><a class="bt1 logout" href="/?logout=1">'.rex_i18n::msg("logout").'</a></li>';
	  }else { 
	  	echo '<li class="lev1 last logout"><a class="bt1 login" href="javascript:pz_getLogin()">'.rex_i18n::msg("login").'</a></li>';
	  }
	  ?>
    </ul>
