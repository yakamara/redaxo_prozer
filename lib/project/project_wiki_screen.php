<?php

class pz_project_wiki_screen
{

	public
		$article = NULL;

	function __construct($article)
	{
		$this->article = $article;
	}


	// ------------------------------------------------------------------ Navigation

	public function getArticlelist($p = array(), $project)
	{
		$return = '
		  <div class="design1col">
		    <header>
          <div class="header">
            <h1 class="hl1">Wiki <span class="info">(420 Ergebnisse)</span></h1>
          </div>
        </header>
	      
		    <dl class="navi-box1">
  		    <dt>Navigation</dt>
  		    <dd>
  		      <ul class="navi-list1">
              <li class="lev1"><a class="lev1" href="#"><span>Hauptseite</span></a></li>
              <li class="lev1"><a class="lev1" href="#"><span>von A bis Z</span></a></li>
              <li class="lev1"><a class="lev1 active" href="#"><span>Hilfe</span></a></li>
            </ul>
  		    </dd>
  		  </dl>
  		</div>
		
		';
		return $return;
	}


	// ------------------------------------------------------------------ Navigation

	public function getArticle($p = array(), $project)
	{
		$return = '
		  <div class="design2col wiki article">
		    <header>
          <div class="header">
            <h1 class="hl1">Hilfe</h1>
          </div>
          <ul class="navi-list2 clearfix">
            <li class="lev1 active"><a class="lev1 active" href="#"><span>Artikel</span></a></li>
            <li class="lev1"><a class="lev1" href="#"><span>Bearbeiten</span></a></li>
            <li class="lev1"><a class="lev1" href="#"><span>Versionen</span></a></li>
            <li class="lev1"><a class="lev1" href="#"><span>Druckansicht</span></a></li>
          </ul>
        </header>
        <article class="formatted">
          <h1>Artikel</h1>
          
          <p><strong>Sehr geehrte Damen und Herren,</strong><br />
              Eorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren
          </p>
          
          <ul>
            <li>Eorem ipsum dolor sit amet</li>
            <li>Eorem ipsum dolor sit amet</li>
            <li>Eorem ipsum dolor sit amet</li>
            <li>Eorem ipsum dolor sit amet</li>
            <li>Eorem ipsum dolor sit amet</li>
            <li>Eorem ipsum dolor sit amet</li>
          </ul>
          
          <ol>
            <li>Eorem ipsum dolor sit amet</li>
            <li>Eorem ipsum dolor sit amet</li>
            <li>Eorem ipsum dolor sit amet</li>
            <li>Eorem ipsum dolor sit amet</li>
            <li>Eorem ipsum dolor sit amet</li>
            <li>Eorem ipsum dolor sit amet</li>
          </ol>
          
          <ul>
            <li><a href="#">Eorem ipsum dolor sit amet</a></li>
            <li><a href="#">Eorem ipsum dolor sit amet</a></li>
            <li><a href="#">Eorem ipsum dolor sit amet</a></li>
            <li><a href="#">Eorem ipsum dolor sit amet</a></li>
          </ul>
          
          <h2>Lorem ipsum dolor sit amet, consetetur sadipscing elitr nonumy eirmod tempor invidunt ut labore et dolore</h2>
				  
				  <p>erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur 
						sadipscing elitr, sed diam nonumy eirmod <a href="#">Eorem ipsum dolor sit amet</a>
				  </p>
        </article>
  		</div>';
		return $return;
	}


	// ------------------------------------------------------------------- Forms

	static function getAddForm($p = array(), $project)
	{
	
		$return = '
	        <header>
	          <div class="header">
	            <h1 class="hl1">'.rex_i18n::msg("add_project_wiki").'</h1>
	          </div>
	        </header>';
	
		$xform = new rex_xform;
		// $xform->setDebug(TRUE);
		
		$xform->setObjectparams("real_field_names",TRUE);

		$xform->setValueField('objparams', array('fragment', 'pz_screen_xform'));
		$xform->setValueField('text', array('name', rex_i18n::msg('article_name')));
		$return .= $xform->getForm();

		// $xform->setObjectparams("form_showformafterupdate", TRUE);
/*
		$xform->setObjectparams("main_table",'pz_project_user');
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('projectuser_add','projectuser_add_form','".pz::url('screen','project','user',array("mode"=>'add_form'))."')");
		
		$xform->setObjectparams("form_id", "projectuser_add_form");
		
		$xform->setHiddenField("project_id",$project->getId());

		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		
		
		$xform->setValidateField("pz_projectuser",array("pu",$project));
		
		$xform->setValueField("hidden",array("project_id",$project->getId()));
		
		
		
		$xform->setValueField('pz_select_screen',array('user_id', rex_i18n::msg('user'), pz_users::getAsString(),"","",1,rex_i18n::msg("please_choose")));
		$xform->setValueField("stamp",array("created","created","mysql_datetime","0","1","","","",""));
		$xform->setValueField("stamp",array("updated","updated","mysql_datetime","0","0","","","",""));
		
		if($project->hasEmails() == 1) {
			$xform->setValueField("checkbox",array("emails",rex_i18n::msg("emails_access"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("emails","0"));
		}
		
		if($project->hasCalendar() == 1) {
			$xform->setValueField("checkbox",array("calendar",rex_i18n::msg("calendar_access"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("calendar","0"));
		}

		if($project->hasFiles() == 1) {
			$xform->setValueField("checkbox",array("files",rex_i18n::msg("files_access"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("files","0"));
		}

		if($project->hasWiki() == 1) {
			$xform->setValueField("checkbox",array("wiki",rex_i18n::msg("wiki_access"),"1","1","0","","","",""));
		}else {
			$xform->setValueField("hidden",array("wiki","0"));
		}

		$xform->setValueField("checkbox",array("admin",rex_i18n::msg("admin_access"),"1","1","0","","","",""));

		$xform->setActionField("db",array());
		$return .= $xform->getForm();
		
		if($xform->getObjectparams("actions_executed")) {
			
			$return .= '<p class="xform-info">'.rex_i18n::msg("projectuser_added").'</p>';
		}
*/
		$return = '<div id="project_wiki_add" class="design1col xform-add">'.$return.'</div>';

		return $return;	
	
	}


}