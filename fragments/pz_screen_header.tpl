        
    <nav>
      <?php echo $this->navigation; ?>
    </nav>
    
    <ul id="navi-meta" class="sl1">
      <li class="lev1 first date"><span class="date"><?php echo date(rex_i18n::msg("format_DdFY"))?></span></li>
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
      
      <li class="lev1 clipboard"><a class="bt1 clipboard" href="javascript:void(0)" onclick="pz_loadPage('sidebar','<?php echo pz::url('screen','clipboard','my',array('mode'=>'list')); ?>');"><span><?php echo htmlspecialchars(rex_i18n::msg("clipboard")); ?></span></a></li>
      -->
      
      <?php } ?>

      <?php if(@$this->user != "") { ?>

      <li class="lev1 user selected"><span class="selected"><?php echo pz::cutText($this->user,20); ?></span>
      <!--
        <div class="flyout">
          <div class="content">
            <ul class="entries">
              <li class="entry first"><a href=""><span class="title">Kai Kristinus</span></a></li>
              <li class="entry"><a href=""><span class="title">Ralph Zumkeller</span></a></li>
            </ul>
          </div>
        </div>
      -->
      </li>
      
      <?php } ?>

	  <?php 
	  if(pz::getUser()) {
	  	echo '<li class="lev1 last logout"><a class="bt1 logout" href="/?logout=1">'.rex_i18n::msg("logout").'</a></li>';
	  }else { 
	  	echo '<li class="lev1 last logout"><a class="bt1 login" href="javascript:pz_getLoginForm()">'.rex_i18n::msg("login").'</a></li>';
	  }
	  ?>
    </ul>
