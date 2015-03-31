<?php

class pz_clipboard_screen
{
    public function __construct($clipboard)
    {
        $this->clipboard = $clipboard;
    }

    // ---------------------------------------------------------------- VIEWS

    public function getSearchForm($p)
    {
        $xform = new rex_xform();
        $xform->setDebug(true);
        $xform->setValueField('objparams', ['form_wrap', '<div class="xform xform-search-small">#</div>']);
        $xform->setObjectparams('form_action', "javascript:pz_loadFormPage('clipboard_list','clipboard_search_form','".pz::url('screen', 'clipboard', 'my', ['mode' => 'list'])."')");
        $xform->setObjectparams('form_id', 'clipboard_search_form');
        $xform->setObjectparams('form_showformafterupdate', 1);
        $xform->setObjectparams('real_field_names', true);
        $xform->setValueField('objparams', ['fragment', 'pz_screen_xform.tpl']);
        $xform->setValueField('text', ['search_name', pz_i18n::msg('label_title')]);
        $xform->setValueField('submit', ['submit', pz_i18n::msg('ok')]);
        $xform_search = $xform->getForm();

        return $xform_search;
    }

    public function getListView($clips, $p)
    {
        $p['layer'] = 'clipboard_list';
        $paginate_screen = new pz_paginate_screen($clips);
        $paginate = $paginate_screen->getPlainView($p);

        $list = '';

        foreach ($paginate_screen->getCurrentElements() as $clip) {
            $clip_screen = new pz_clip_screen($clip);
            $list .= $clip_screen->getListView($p);
        }

        $content = $paginate;
        $content .= '<table class="clips tbl1">
	                 <colgroup>
	                   <col width="*" />
	                   <col width="100" />
	                   <col width="90" />
	                   <col width="90" />
	                 </colgroup>
	                 '.$list.'
	               </table>';
        $content .= $paginate_screen->setPaginateLoader($p, '#clipboard_list');

        $content .= '<script>pz_clipboard_init();</script>';

        if ($paginate_screen->isScrollPage()) {
            return $content;
        }

        $return = '<div id="clipboard_list">';
        if (!isset($p['hide_counter']) || !$p['hide_counter']) {
            $return .= '<h3 class="hl3">'.pz_i18n::msg('clips_exist', count($clips)).'</h2>';
        }
        $return .= $content;
        $return .= '</div>';

        return $return;
    }

    public function getMultiuploadView($p = [])
    {
        $return = ''; // '<h2 class="hl2">'.pz_i18n::msg("clipboard_upload").'</h2>';

        $user_id = $this->clipboard->user->getId();
        $field_id = '123'.$this->clipboard->user->getId();
        $layer = 'pz_multiupload_user_'.$user_id;

        $return .= '<div id="'.$layer.'"></div>';
        $return .= '
	    <script>
					function pz_clipboard_upload_user_'.$user_id.'(){
						var uploader = new qq.FileUploader({

							element: document.getElementById(\''.$layer.'\'),
							action: \''.pz::url('screen', 'clipboard', 'upload', ['mode' => 'file']).'\',

							template: \'<div class="qq-uploader"><div class="qq-upload-drop-area"><span>'.pz_i18n::msg('files_for_upload').'</span></div><div class="qq-upload-button">'.pz_i18n::msg('dragdrop_files_for_upload').'</div><ul class="qq-uploaded-list"></ul><ul class="qq-upload-list"></ul></div>\',

							fileTemplate: \'<li><span class="qq-upload-file"></span><span class="qq-upload-spinner"></span><span class="qq-upload-size"></span><a class="qq-upload-cancel" href="javascript:void(0);">'.pz_i18n::msg('dragdrop_files_exit').'</a><span class="qq-upload-failed-text">'.pz_i18n::msg('dragdrop_files_upload_failed').'</span></li>\',

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
								pz_hidden = $("#'.$field_id.'");
								if(result.clipdata.id) {

					    			l = $("#'.$layer.' .qq-upload-list").children().length - 1;
					    			m = $("#'.$layer.' .qq-upload-list li:eq("+l+")");
					    			l +" ##  "+m.attr("data-clip_id",result.clipdata.id);

								    url = "/screen/clipboard/get/?mode=refreshview&clip_id="+result.clipdata.id;
					          $.ajax({
                      url: url,
                      clip_id: result.clipdata.id,
                      success: function(html)
                      {
                        $("#clipboard table.clips").append(html);
                        $(\'[data-clip_id="\'+this.clip_id+\'"]\').remove();
						          }
						        });

						    	}
							},
							maxConnections: 4,
							debug: true

						});
						uploader._filesInProgress = 0;
					}
					jQuery(document).ready(function(){
					  pz_clipboard_upload_user_'.$user_id.'();
					});
					</script>';

        $p['hide_counter'] = true;
        $return .= $this->getListView([], $p);

        return $return;
    }
}
