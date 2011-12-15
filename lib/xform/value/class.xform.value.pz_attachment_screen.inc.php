<?php

/*
	// TODO
		- wenn Bild nicht übernommen wird, dann Clip löschen ?


*/

class rex_xform_value_pz_attachment_screen extends rex_xform_value_abstract
{

	function enterObject()
	{
		$value_ids = explode(",",$this->getValue());
		
		$clip_ids = "";
		$clips = array();
		foreach($value_ids as $value_id) {
			$value_id = (int) $value_id;
			if($clip = pz_clipboard::getClipById($value_id)) {
				$clip_ids .= $clip["id"].',';
				$clips[] = $clip;
			}
		}
		
		$this->setValue($clip_ids);
		
		$output = '	<div class="rex-form-row">
						<label></label>
						<div id="pz_multiupload_'.$this->getId().'"></div>
					</div>
					<script>
					function pz_createUploader_'.$this->getId().'(){            
						var uploader = new qq.FileUploader({
							element: document.getElementById(\'pz_multiupload_'.$this->getId().'\'),
							action: \''.pz::url("screen", "clipboard", "upload", array( "mode"=>"file" ) ).'\',
							
							template: \'<div class="qq-uploader"><div class="qq-upload-drop-area"><span>'.rex_i18n::msg("files_for_upload").'</span></div><div class="qq-upload-button">'.rex_i18n::msg("dragdrop_files_for_upload").'</div><ul class="qq-uploaded-list"></ul><ul class="qq-upload-list"></ul></div>\',
							
							fileTemplate: \'<li><span class="qq-upload-file"></span><span class="qq-upload-spinner"></span><span class="qq-upload-size"></span><a class="qq-upload-cancel" href="javascript:void(0);">'.rex_i18n::msg("dragdrop_files_exit").'</a><span class="qq-upload-failed-text">'.rex_i18n::msg("dragdrop_files_upload_failed").'</span></li>\',
							
							removeTemplate: \'<span class="clear_link"><a href="javascript:void(0);" onclick="\'+
						\'	li_field = $(this).parents(\\\'li\\\'); \'+
						\'	clip_id = li_field.attr(\\\'data-clip_id\\\'); \'+
						\'	hidden_field = $(\\\'#'.$this->getFieldId().'\\\'); \'+
						\'	hidden_field.val( hidden_field.val().replace(clip_id+\\\',\\\',\\\'\\\') ); \'+
						\'	li_field.remove(); \'+
						\'	 \'+
						\'">'.rex_i18n::msg("dragdrop_files_remove_from_list").'</a></span>\',
							
							// remove();
							
							classes: {
					            button: "qq-upload-button",
					            drop: "qq-upload-drop-area",
					            dropActive: "qq-upload-drop-area-active",
					            list: "qq-upload-list",
					            file: "qq-upload-file",
					            spinner: "qq-upload-spinner",
					            size: "qq-upload-size",
					            cancel: "qq-upload-cancel",
					            success: "qq-upload-success",
					            fail: "qq-upload-fail"
					        },
							
							sizeLimit: 0, // max size   
							minSizeLimit: 0, // min size
							onSubmit: function() {
							},
							onComplete: function(id, fileName, result) {
								pz_hidden = $("#'.$this->getFieldId().'");
								if(result.clipdata.id) {
					    			pz_hidden.val(pz_hidden.val()+result.clipdata.id+",");
					    			l = $("#pz_multiupload_'.$this->getId().' .qq-upload-list").children().length - 1;
					    			m = $("#pz_multiupload_'.$this->getId().' .qq-upload-list li:eq("+l+")");
					    			l +" ##  "+m.attr("data-clip_id",result.clipdata.id);
						    	}
							},
							maxConnections: 4,
							debug: true
							
						});
						
						';
						$c = 0;
						
						$output .= '
							uploaded_list = $("#pz_multiupload_'.$this->getId().' .qq-uploaded-list");
								';
						
						foreach($clips as $clip)
						{
							// size noch hinterlegen
							$file_name = htmlspecialchars($clip["filename"]);
							$file_size = (int) $clip["content_length"];
							$file_type = htmlspecialchars($clip["content_type"]);
							$clip_id = $clip["id"];
							
							$output .= '
							fileName = uploader._formatFileName("'.$file_name.'");
							fileSize = uploader._formatSize('.$file_size.');
								
							li = (\'<li class="qq-upload-success" data-clip_id="'.$clip["id"].'">\'+
								\'<span class="qq-upload-file">\'+fileName+\'</span>\'+
								\'<span class="qq-upload-size">\'+fileSize+\'\'+
								\'<span class="clear_link"><a href="javascript:void(0);" onclick="\'+
								\'	li_field = $(this).parents(\\\'li\\\'); \'+
								\'	clip_id = li_field.attr(\\\'data-clip_id\\\'); \'+
								\'	hidden_field = $(\\\'#'.$this->getFieldId().'\\\'); \'+
								\'	hidden_field.val( hidden_field.val().replace(clip_id+\\\',\\\',\\\'\\\') ); \'+
								\'	li_field.remove(); \'+
								\'">'.rex_i18n::msg("dragdrop_files_remove_from_list").'</a></span></span></li>\');
								
							uploaded_list.append(li);								
							';
						}
						
		$output .= '
						uploader._filesInProgress = 0;           
					}
					jQuery(document).ready(function(){
						pz_createUploader_'.$this->getId().'();
					});
					</script>';

		$class = $this->getHTMLClass();
		$classes = $class;
		if ($this->getElement(5) != '') 
	  		$classes .= ' '.$this->getElement(5);
		if (isset($this->params["warning"][$this->getId()]))
			$classes .= ' '.$this->params["warning"][$this->getId()];
		
		$classes = (trim($classes) != '') ? ' class="'.trim($classes).'"' : '';
		$before = '';
		$after = '';    
		$label = ($this->getElement(2) != '') ? '<label'.$classes.' for="' . $this->getFieldId() . '">' . rex_i18n::translate($this->getElement(2)) . '</label>' : '';	
		$field = '<input'.$classes.' id="'.$this->getFieldId().'" type="hidden" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($this->getValue())).'" />'.$output;
		$extra = '';
		$html_id = $this->getHTMLId();
		$name = $this->getName();
		
		
		$f = new rex_fragment();
		$f->setVar('before', $before, false);
		$f->setVar('after', $after, false);
		$f->setVar('label', $label, false);
		$f->setVar('field', $field, false);
		$f->setVar('extra', $extra, false);
		$f->setVar('html_id', $html_id, false);
		$f->setVar('name', $name, false);
		$f->setVar('class', $class, false);
		
		$fragment = $this->params['fragment'];
		$this->params["form_output"][$this->getId()] = $f->parse($fragment).$this->getValue();

		$this->params["value_pool"]["email"][$this->getElement(1)] = stripslashes($this->getValue());
		$this->params["value_pool"]["sql"][$this->getElement(1)] = $this->getValue();

		return;

	}

	function getDescription()
	{
		return "pz_attachment_screen -> Beispiel: pz_attachment_screen|label|Bezeichnung|";
	}

}

?>