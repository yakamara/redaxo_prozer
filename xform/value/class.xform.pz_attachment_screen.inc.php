<?php

class rex_xform_pz_attachment_screen extends rex_xform_abstract
{
    public function enterObject()
    {
        $value_ids = explode(',', $this->getValue());

        $clip_ids = '';
        $clips = [];
        foreach ($value_ids as $value_id) {
            $value_id = (int) $value_id;
            if (($clip = pz_clip::get($value_id)) ) {
                if ($clip->checkUserperm()) {
                  $clip_ids .= $clip->getId().',';
                  $clips[] = $clip;
                }
            }
        }

        $this->setValue($clip_ids);

        $output = '	<div class="rex-form-row">
						<label></label>
						<div id="pz_multiupload_'.$this->getId().'"></div>
					</div>
					<script>
					$("#'.$this->getFieldId().'").val("");
					function pz_createUploader_'.$this->getId().'(){
						var uploader = new qq.FileUploader({
							element: document.getElementById(\'pz_multiupload_'.$this->getId().'\'),
							action: \''.pz::url('screen', 'clipboard', 'upload', ['mode' => 'file']).'\',
							template: \'<div class="qq-uploader"><div class="qq-upload-drop-area"><span>'.pz_i18n::msg('files_for_upload').'</span></div><div class="qq-upload-button">'.pz_i18n::msg('dragdrop_files_for_upload').'</div><ul class="qq-uploaded-list"></ul><ul class="qq-upload-list"></ul></div>\',
							fileTemplate: \'<li><span class="qq-upload-file"></span><span class="qq-upload-spinner"></span><span class="qq-upload-size"></span><a class="qq-upload-cancel" href="javascript:void(0);">'.pz_i18n::msg('dragdrop_files_exit').'</a><span class="qq-upload-failed-text">'.pz_i18n::msg('dragdrop_files_upload_failed').'</span></li>\',
							removeTemplate: \'<span class="clear_link"><a href="javascript:void(0);" onclick="\'+
							\' pz_clip_deselect($(this).parents(\\\'li\\\').attr(\\\'data-clip_id\\\'),\\\'#'.$this->getFieldId().'\\\'); return; \'+
						  \'">'.pz_i18n::msg('dragdrop_files_remove_from_list').'</a></span>\',

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
								var pz_hidden = $("#'.$this->getFieldId().'");
								if(result.clipdata.id) {
					    			pz_hidden.val(pz_hidden.val()+result.clipdata.id+",");
					    			m = $("#pz_multiupload_'.$this->getId().' .qq-upload-list").find("li:eq("+ id +")");
                                    m.attr("data-clip_id", result.clipdata.id).addClass("clip-" + result.clipdata.id);
					    			n = m.find(".qq-upload-file");
					    			n.html(\'<a href="/screen/clipboard/get/?mode=download_clip&clip_id=\'+result.clipdata.id+\'">\'+n.html()+\'</a>\');

					    			pz_clipboard_init();
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

        foreach ($clips as $clip) {
            // size noch hinterlegen
            $file_name = htmlspecialchars($clip->getFilename());
            $file_size = (int) $clip->getContentLength();
            $file_type = htmlspecialchars($clip->getContentType());
            $clip_id = $clip->getid();

            $output .= '
							fileName = uploader._formatFileName("'.$file_name.'");
							fileSize = uploader._formatSize('.$file_size.');
							var pz_hidden = $("#'.$this->getFieldId().'");
						    pz_hidden.val(pz_hidden.val()+'.$clip->getId().'+",");
							li = (\'<li class="qq-upload-success clip-'.$clip->getId().'" data-clip_id="'.$clip->getId().'">\'+
								\'<span class="qq-upload-file"><a href="/screen/clipboard/get/?mode=download_clip&clip_id='.$clip->getId().'" target="_blank">\'+fileName+\'</a></span>\'+
								\'<span class="qq-upload-size">\'+fileSize+\'\'+
								\'<span class="clear_link"><a href="javascript:void(0);" onclick="\'+
							  \' pz_clip_deselect($(this).parents(\\\'li\\\').attr(\\\'data-clip_id\\\'),\\\'#'.$this->getFieldId().'\\\'); return; \'+
								\'">'.pz_i18n::msg('dragdrop_files_remove_from_list').'</a></span></span></li>\');

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
        if ($this->getElement(5) != '') {
            $classes .= ' '.$this->getElement(5);
        }
        if (isset($this->params['warning'][$this->getId()])) {
            $classes .= ' '.$this->params['warning'][$this->getId()];
        }

        $after = '<a class="bt-upload" id="'.$this->getFieldId('clipboard_button').'" href="javascript:pz_clipboard_select(\'#'.$this->getFieldId('clipboard_button').'\',\'#pz_multiupload_'.$this->getId().' .qq-uploaded-list\',\'#'.$this->getFieldId().'\', \'#pz_multiupload_'.$this->getId().' .qq-upload-list\' )">'.pz_i18n::msg('get_from_clipboard').'</a>';

        $label = ($this->getElement(2) != '') ? '<label class="'.$classes.'" for="' . $this->getFieldId() . '">' . pz_i18n::translate($this->getElement(2)) . '</label>' : '';
        $field = '<input class="'.$classes.' clip-ids" id="'.$this->getFieldId().'" type="hidden" name="'.$this->getFieldName().'" value="'.htmlspecialchars(stripslashes($this->getValue())).'" />'.$output;
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
        return 'pz_attachment_screen -> Beispiel: pz_attachment_screen|label|Bezeichnung|';
    }
}
