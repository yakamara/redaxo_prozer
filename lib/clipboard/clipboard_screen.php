<?php

class pz_clipboard_screen{
	

	// ---------------------------------------------------------------- VIEWS

	static function getClipSideView($clip,$p) {
	
		$p["linkvars"]["clip_id"] = $clip["id"];
	
		if(!($ext = pz::getExtensionByMimetype($clip["content_type"])))
			$ext = 'file';
	
	
		$select_link = 'pz_clip_select('.$clip["id"].',\''.$clip["filename"].'\',\''.pz::readableFilesize($clip["content_length"]).'\');';
		$download_link = pz::url("screen","clipboard","get",array_merge($p["linkvars"],array("mode"=>"download_clip")));
		$delete_link = "pz_exec_javascript('".pz::url("screen","clipboard","get",array_merge($p["linkvars"],array("mode"=>"delete_clip")))."')";

		$return = '<li class="item clip-'.$clip["id"].'"><a class="file25i '.$ext.'" href="javascript:void(0);" onclick="$(this).parent().toggleClass(\'active\').find(\'ul\').toggle();" title="'.htmlspecialchars($clip["filename"]).'">'.htmlspecialchars(pz::cutText($clip["filename"],30)).' ['.pz::readableFilesize($clip["content_length"]).']</a>';
			$return .= '<ul style="display: none;">';
			$return .= '<li><span class="datetime">'.$clip["created"].'</span></li>';
			$return .= '<li><a href="'.$download_link.'" target="_blank">'.rex_i18n::msg('download').'</a></li>';
			$return .= '<li><a href="javascript:void(0);" onclick="'.$delete_link.'">'.rex_i18n::msg('delete').'</a></li>';
			$return .= '<li class="clip-select"><a href="javascript:void(0);" onclick="'.$select_link.'">'.rex_i18n::msg('select').'</a></li>';
			$return .= '</ul>';
		
		$return .= '</li>';

		// - link generieren können und rauskopieren können
		// - laufzeit anzeigen

		return $return;
	
	}

	
	static function getSearchForm($p) {
	
		$xform = new rex_xform;
		$xform->setDebug(true);

		$xform->setValueField('objparams',array('form_wrap', '<div class="xform xform-search-small">#</div>'));
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('clipboard_list','clipboard_search_form','".pz::url('screen','clipboard','my',array("mode"=>'list'))."')");
		$xform->setObjectparams("form_id", "clipboard_search_form");
		$xform->setObjectparams('form_showformafterupdate',1);
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform'));
		
		$xform->setValueField('text',array('search_name',rex_i18n::msg('label_title')));
		$xform->setValueField('submit',array('submit',rex_i18n::msg('ok')));
		$xform_search = $xform->getForm();
		
		return $xform_search;
	
	}
	
	static function getClipboardSideView($clips, $p) {
    $return = '<div id="clipboard_list">';
		$return .= '<h2 class="hl3">'.rex_i18n::msg('clips_exist',count($clips)).'</h2>';
		$return .= '<ul class="list">';
		
		foreach($clips as $clip) {
			$return .= pz_clipboard_screen::getClipSideView($clip,$p);
		}
		$return .= '</ul>';
		$return .= '</div>';
		return $return;
	}
	
	
	
	
	
	
}

