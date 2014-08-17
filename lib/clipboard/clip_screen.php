<?php

class pz_clip_screen
{
	
  public $clip = NULL;
	
	function __construct($clip) 
	{
		$this->clip = $clip;
	}


	// ---------------------------------------------------------------- VIEWS

	public function getListView($p = array(), $showInfo = false) 
	{
	
		$p["linkvars"]["clip_id"] = $this->clip->getId();
	
		if(!($ext = pz::getExtensionByMimetype($this->clip->getContentType())))
			$ext = 'file';
  		
		$select_link = 'pz_clip_select('.$this->clip->getId().',\''.$this->clip->getFilename().'\',\''.pz::readableFilesize($this->clip->getContentLength()).'\');';

		$download_link = pz::url("screen","clipboard","get",array_merge($p["linkvars"],array("mode"=>"download_clip")));

		$delete_link = "pz_exec_javascript('".pz::url("screen","clipboard","get",array_merge($p["linkvars"],array("mode"=>"delete_clip")))."')";

    $classes = array('item','clip','clip-'.$this->clip->getId());
    if($this->clip->isReleased()) $classes[] = 'released';
    else $classes[] = 'notreleased';

		$return = '';
		
		$return .= '<tbody class="clipboard-clip '.implode(" ",$classes).'">';
		$return .= '<tr class="clip">';
		// $return .= '<td>checkbox</td>';
		
		$return .= '<td class="clipname">';
		$return .= '<a class="file25i '.$ext.'" href="'.$download_link.'" title="'.htmlspecialchars($this->clip->getFilename()).'"><span>'.htmlspecialchars(pz::cutText($this->clip->getFilename(),30, '…', 'center')).'</span></a>';
		$return .= '<span class="filesize">'.pz::readableFilesize($this->clip->getContentLength()).'</span>';
		$return .= '<span class="datetime">'.$this->clip->getCreateDate()->format(pz_i18n::msg("format_datetime")).'</span>';
		$return .= '</td>';
    
    $td_release = '';
    $tr_release = '';
		
		if($this->clip->isReleased())
		{
		
		  $unrelease_link = "pz_exec_javascript('".pz::url("screen","clipboard","get",array_merge($p["linkvars"],array("mode"=>"unrelease_clip")))."')";
   		$td_release .= '<td class="function-unrelease"><a class="bt5" href="javascript:void(0);" onclick="'.$unrelease_link.'">'.pz_i18n::msg('clip_unrelease').'</a></td>';

      $tr_release .= '</tr>';
      $tr_release .= '<tr class="clipinfo">';
      
      $tr_release .= '<td>';
      $tr_release .= '<b class="url">'.pz_i18n::msg('clip_url').':</b> '.$this->clip->getUri().'';
		  $tr_release .= '</td>';
		  
      
      $tr_release .= '<td colspan="3">';

      $release_link_today    = "pz_exec_javascript('".pz::url("screen","clipboard","get",array_merge($p["linkvars"],array("mode"=>"release_clip", "clip_release_duration" => "today")))."')";
   		$release_link_week     = "pz_exec_javascript('".pz::url("screen","clipboard","get",array_merge($p["linkvars"],array("mode"=>"release_clip", "clip_release_duration" => "week")))."')";
   		$release_link_month    = "pz_exec_javascript('".pz::url("screen","clipboard","get",array_merge($p["linkvars"],array("mode"=>"release_clip", "clip_release_duration" => "month")))."')";
   		$release_link_month_3  = "pz_exec_javascript('".pz::url("screen","clipboard","get",array_merge($p["linkvars"],array("mode"=>"release_clip", "clip_release_duration" => "3month")))."')";

      $tr_release .= '
                  <ul class="sl1 light">
                    <li class="selected flyouthover">
                      <span class="selected"><span class="datetime"><b>'.pz_i18n::msg('clip_released_until').':</b> '.$this->clip->getOfflineDate()->format(pz_i18n::msg("format_datetime")).'</span></span>
                      <div class="flyout">
                        <div class="content">
                          <ul class="entries">
                            <li class="entry"><a href="javascript:void(0);" onclick="'.$release_link_today.'"><span class="title">'.pz_i18n::msg("clip_release_duration_today").'</span></a></li>
                            <li class="entry"><a href="javascript:void(0);" onclick="'.$release_link_week.'"><span class="title">'.pz_i18n::msg("clip_release_duration_week").'</span></a></li>
                            <li class="entry"><a href="javascript:void(0);" onclick="'.$release_link_month.'"><span class="title">'.pz_i18n::msg("clip_release_duration_month").'</span></a></li>
                            <li class="entry"><a href="javascript:void(0);" onclick="'.$release_link_month_3.'"><span class="title">'.pz_i18n::msg("clip_release_duration_month_3").'</span></a></li>
                          </ul>			
                        </div>
                      </div>
                    </li>
                  </ul>';


      $tr_release .= '</td>';
		  
    }else 
    {
  		$release_link = "pz_exec_javascript('".pz::url("screen","clipboard","get",array_merge($p["linkvars"],array("mode"=>"release_clip")))."')";
   		$td_release .= '<td class="function-clip-release"><a class="bt5" href="javascript:void(0);" onclick="'.$release_link.'">'.pz_i18n::msg('clip_release').'</a></td>';
      
		}
		
		$return .= '<td class="function-clip-select"><a class="bt5" href="javascript:void(0);" onclick="'.$select_link.'">'.pz_i18n::msg('clip_select').'</a></td>';
  	$return .= $td_release;
		$return .= '<td class="function-clip-delete"><a class="bt17 flright" href="javascript:void(0);" onclick="'.$delete_link.'">'.pz_i18n::msg('clip_delete').'</a></td>';

		$return .= '</tr>';
  	$return .= $tr_release;
		$return .= '</tbody>';

    // TODO:
    // - selected noch auswählen
    // - Info wenn bestimmte Dinge freigegeben oder geändert wurden.
    // - bis Freigabezeit anzeigen

		return $return;
	
	}
	
	
}

