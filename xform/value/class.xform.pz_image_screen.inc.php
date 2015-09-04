<?php

/*
    // TODO
        - Clip löschen ?

*/

class rex_xform_pz_image_screen extends rex_xform_abstract
{
    public function enterObject()
    {
        $default_image_path = $this->getElement(3);

        $output = '	<div class="rex-form-row">
						<label></label>
						<div id="pz_multiupload_'.$this->getId().'"></div>
					</div>
					<script>
					function pz_createUploader_'.$this->getId().'(){
						var uploader = new qq.FileUploader({
							element: document.getElementById(\'pz_multiupload_'.$this->getId().'\'),
							action: \''.pz::url('screen', 'clipboard', 'upload', ['mode' => 'file']).'\',

							template: \'<div class="qq-uploader"><div class="qq-upload-drop-area"><span>'.pz_i18n::msg('file_for_upload').'</span></div><div class="qq-upload-button">'.pz_i18n::msg('dragdrop_file_for_upload').'</div><ul class="qq-uploaded-list"></ul><ul class="qq-upload-list"></ul></div>\',

							fileTemplate: \'<li><span class="qq-upload-file"></span><span class="qq-upload-spinner"></span><span class="qq-upload-size"></span><a class="qq-upload-cancel" href="javascript:void(0);">'.pz_i18n::msg('dragdrop_file_exit').'</a><span class="qq-upload-failed-text">'.pz_i18n::msg('dragdrop_files_upload_failed').'</span></li>\',

							removeTemplate: \'\',

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
								replaceImage(result.clipdata.id);

							},
							maxConnections: 1

						});

						uploaded_list = $("#pz_multiupload_'.$this->getId().' .qq-uploaded-list");
						uploader._filesInProgress = 0;
					}
					function replaceImage(clip_id){
						pz_hidden = $("#'.$this->getHTMLId().' #'.$this->getFieldId().'");
						pz_image = $("#'.$this->getHTMLId().' label img");
						if(clip_id) {
					    	link = "'.pz::url('screen', 'clipboard', 'get', ['mode' => 'image_src']).'"+"&clip_id="+clip_id;
					    	$.post(link, "", function(data) {
					    		if(data == "") {

								}else {
					    			pz_hidden.val(data);
					    			pz_image.attr("src",data);
					    		}
							});

						}
						window.setTimeout(function(){
							clearUploadListSuccess();
						}, 3000);
					}
					jQuery(document).ready(function(){
						pz_createUploader_'.$this->getId().'();
					});
					</script>';

        $class = $this->getHTMLClass();
        $classes = $class;
        if ($this->getElement(5) != '') {
            $classes .= ' '.$this->getElement(5);
        }
        if (isset($this->params['warning'][$this->getId()])) {
            $classes .= ' '.$this->params['warning'][$this->getId()];
        }

        $img = $this->getValue();
        if ($img == '') {
            $img = $default_image_path;
        }

		$after = '<a class="bt-upload" id="'.$this->getFieldId('clipboard_button').'" href="javascript:pz_clipboard_select(\'#'.$this->getFieldId('clipboard_button').'\',\'#pz_multiupload_'.$this->getId().' .qq-uploaded-list\',\'#'.$this->getFieldId().'\', \'#pz_multiupload_'.$this->getId().' .qq-upload-list\', true )">'.pz_i18n::msg('get_from_clipboard').'</a>';
        $classes = (trim($classes) != '') ? ' class="'.trim($classes).'"' : '';
        $label = ($this->getElement(2) != '') ? '<label'.$classes.' for="' . $this->getFieldId() . '">'.
            '<img src="'.$img.'" title="' . pz_i18n::translate($this->getElement(2)) . '" width=40 height=40 />'.
            '</label>' : '';
        $field = '<input id="'.$this->getFieldId().'" type="hidden" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($this->getValue())).'" />'.$output;
        $html_id = $this->getHTMLId();
        $name = $this->getName();

        $f = new pz_fragment();
        $f->setVar('after', $after, false);
		$f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('html_id', $html_id, false);
        $f->setVar('name', $name, false);
        $f->setVar('class', $class, false);

        $fragment = $this->params['fragment'];
        $this->params['form_output'][$this->getId()] = $f->parse($fragment);

        $this->params['value_pool']['email'][$this->getElement(1)] = stripslashes($this->getValue());
        $this->params['value_pool']['sql'][$this->getElement(1)] = $this->getValue();

        return;
    }

    public function getDescription()
    {
        return 'pz_image_screen -> Beispiel: pz_image_screen|label|Bezeichnung|';
    }
}
