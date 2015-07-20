<?php

class pz_clipboard_controller_screen extends pz_clipboard_controller
{
    public $name = 'clipboard';
    public $function = '';
    public $functions = ['my', 'upload','get'];
    public $function_default = 'my';
    public $visible = false;

    public function controller($function)
    {

        // ---- direct download
        if (isset($_REQUEST['clip_key'])) {
            // /clip/clipkyy/optional-filename

            $clip_key = explode('/', rex_request('clip_key', 'string'));
            if (isset($clip_key[1]) && ($clip = pz_clip::getByUri($clip_key[1])) && $clip->isReleased()) {
                $clip->saveToHistory('download');
                return $clip->download();
            }
            header('HTTP/1.0 404 Not Found');
            return '';
        }

        if (!pz::getUser()) {
            return '';
        }

        if (!in_array($function, $this->functions)) {
            $function = $this->function_default;
        }
        $this->function = $function;

        $p = [];
        $p['linkvars'] = [];
        $p['mediaview'] = 'screen';
        $p['controll'] = 'clipboard';
        $p['function'] = $this->function;

        switch ($this->function) {
            case('get'): return $this->getClip($p);
            case('upload'): return $this->setUpload($p);
            case('my'):    return $this->getClipboard($p);
            default: break;
        }

        return '';
    }

    // -----------------

    public function getClip($p)
    {
        $clip_id = rex_request('clip_id', 'int');
        if (($clip = pz_clip::get($clip_id))) {
            $mode = rex_request('mode', 'string');
            switch ($mode) {
                case('download_clip'):
                    if ($clip->checkUserperm()) {
                        $clip->saveToHistory('download');
                        return $clip->download();
                    }
                    return '';
            }

            if ($clip->getUser()->getId() != pz::getUser()->getId()) {
                return '';
            }

            switch ($mode) {

                case('refreshview'):
                    $clip_screen = new pz_clip_screen($clip);
                    return $clip_screen->getListView($p, true).'<script>pz_clipboard_init();</script>';

                case('release_clip'):
                    $return = '';
                    $duration = rex_request('clip_release_duration', 'string');
                    $offline_date = new DateTime();
                    switch ($duration) {
                        case('today'):
                            $offline_date->setTime(23, 59);
                            break;
                        case('week'):
                            $offline_date->modify('+7 days');
                            $offline_date->setTime(23, 59);
                            break;
                        case('month'):
                            $offline_date->modify('+1 month');
                            $offline_date->setTime(23, 59);
                            break;
                        default:
                            $offline_date->modify('+3 months');
                    }

                    $clip = $clip->release(null, $offline_date);
                    $link = pz::url('screen', 'clipboard', 'get', array_merge($p['linkvars'], ['mode' => 'refreshview', 'clip_id' => $clip->getId()]));
                    $return = '<script>pz_loadPage("#clipboard .clip-'.$clip->getId().'", "'.$link.'");</script>';
                    return $return;

                case('unrelease_clip'):
                    $return = '';
                    $clip = $clip->unrelease();
                    $link = pz::url('screen', 'clipboard', 'get', array_merge($p['linkvars'], ['mode' => 'refreshview', 'clip_id' => $clip->getId()]));
                    $return = '<script>pz_loadPage("#clipboard .clip-'.$clip->getId().'", "'.$link.'");</script>';
                    return $return;

                case('delete_clip'):
                    $return = '';
                    if (pz::getLoginUser()->getId() == $clip->getUserId()) {
                        $return .= '<script>
						$(".clip-'.$clip->getId().'").remove();
						// $(".clip-ids").val($(".clip-ids").val().replace("'.$clip->getId().',",""));
						</script>';
                        $clip->delete();
                    }
                    return $return;

                case('image_src_raw'):
                    $image_size = rex_request('image_size', 'string', 'm');
                    $image_type = rex_request('image_type', 'string', 'image/jpg');
                    return $clip->getInlineImage(false, $image_size, $image_type);

                case('image_inline'):
                case('image_src'):
                    $image_size = rex_request('image_size', 'string', 'm');
                    $image_type = rex_request('image_type', 'string', 'image/jpg');
                    return $clip->getInlineImage(true, $image_size, $image_type);

            }
        }
    }

    // -----------------

    public function setUpload($p)
    {
        $clipboard = pz_clipboard::get(pz::getLoginUser()->getId());

        $return = [];
        $return['clipdata'] = [];

        $filename = rex_request('qqfile', 'string');
        if ($filename != '') {
            $input = fopen('php://input', 'r');
            $temp = tmpfile();
            $real_size = stream_copy_to_stream($input, $temp);
            fclose($input);

            if (isset($_SERVER['CONTENT_LENGTH']) && isset($_SERVER['CONTENT_TYPE'])) {
                $content_length = (int) $_SERVER['CONTENT_LENGTH'];
                $content_type = $_SERVER['CONTENT_TYPE'];
                if ($real_size == $content_length) {
                    $clip = pz_clip::createAsStream($temp, $filename, $content_length, $content_type);
                    $return['clipdata'] = ['id' => $clip->getId(), 'filename' => $clip->getFilename()];
                    $return['success'] = true;
                }
            }
        }

        return htmlspecialchars(json_encode($return), ENT_NOQUOTES);
    }

    // -----------------

    public function getClipboard($p)
    {
        $navigation = [
            'my' => pz_i18n::msg('clipboard_my'),
            'released' => pz_i18n::msg('clipboard_releasedlist'),
            'upload' => pz_i18n::msg('clipboard_upload'),
            'upload_project' => pz_i18n::msg('clipboard_project_files'),
        ];

        // ----- navi

        $n = rex_request('n', 'string');
        if (!array_key_exists($n, $navigation)) {
            $n = 'my';
        }

        $return_navi = '<nav><ul>';
        foreach ($navigation as $k => $v) {
            $link = pz::url($p['mediaview'], $p['controll'], $p['function'], array_merge($p['linkvars'], ['n' => $k]));
            if ($k == $n) {
                $return_navi .= '<li><a class="active" href="javascript:void(0);" onclick="pz_loadPage(\'#clipboard\', \''.$link.'\')">'.$v.'</a></li>';
            } else {
                $return_navi .= '<li><a href="javascript:void(0);" onclick="pz_loadPage(\'#clipboard\', \''.$link.'\')">'.$v.'</a></li>';
            }
        }
        $return_navi .= '</ul></nav>';

        $p['linkvars']['n'] = $n;

        // ----- output

        $cb = pz_clipboard::get(pz::getLoginUser()->getId());
        $cb_screen = new pz_clipboard_screen($cb);

        $filter = [];
        $search_name = rex_request('search_name', 'string');
        if ($search_name != '') {
            $filter[] = ['field' => 'filename', 'type' => 'like', 'value' => $search_name];
        }

        $p['linkvars']['search_name'] = $search_name;

        $mode = rex_request('mode', 'string');
        $p['linkvars']['mode'] = 'list';

        $return_content = '';
        switch ($n) {
            case('upload_project'):

                unset($p['linkvars']['search_name']);
                $project_id = rex_request('project_id', 'int');

                if (!($project = pz::getLoginUser()->getProjectById($project_id))) {
                    $p['layer_list'] = 'clipboard_project_list';
                    $return_content = pz_project_screen::getProjectsClipboardListView($p);
                } else {
                    $p['layer_list'] = 'clipboard_project_files';
                    $project_screen = new pz_project_screen($project);

                    $p['linkvars']['project_id'] = $project->getId();

                    $file_id = rex_request('file_id', 'string', '');
                    if ($file_id == '') {
                        $return_content .= $project_screen->getClipboardLabelView($p);
                    }

                    if (($category = pz_project_node::get($file_id)) && ($category->isDirectory())) {
                        $category = $category;
                    } else {
                        $category = $project->getDirectory();
                    }

                    $return_content .= pz_project_file_screen::getClipboardFilesListView(
                        $category, $category->getChildren([]), $p, []
                    );
                }

                if ($mode == 'list') {
                    return $return_content;
                }

                break;

            case('upload'):
                // $return_content .= '<h3 class="hl3">'.pz_i18n::msg('clip_upload').'</h2>';
                $return_content .= $cb_screen->getMultiuploadView($p);
                if ($mode == 'list') {
                    return $return_content;
                }
                break;

            case('released'):
                $return_content = $cb_screen->getListView($cb->getReleasedClips($filter), $p);
                if ($mode == 'list') {
                    return $return_content;
                }
                $return_content = $cb_screen->getSearchForm($p).$return_content;
                break;

            case('my'):
                $return_content .= $cb_screen->getListView($cb->getMyClips($filter), $p);
                if ($mode == 'list') {
                    return $return_content;
                }
                $return_content = $cb_screen->getSearchForm($p).$return_content;
                break;

        }

        $return = '<div id="clipboard" class="popbox">
		            <h1 class="hl1">'.pz_i18n::msg('clipboard').': '.$navigation[$n].'</h1>
		            <div class="popbox-frame">
		              '.$return_navi.'
		              <div class="popbox-content">
		                '.$return_content.'
		                <!-- <ul class="actions">
                      <li><a href="#" class="bt2">Auswahl übernehmen</a></li>
                      <li><a href="#" class="bt2">Abbrechen</a></li>
                    </ul>
                    -->
		              </div>
		            </div>
		          </div>';

        $return .= '<script>
    pz_centerPopbox();
	  pz_setZIndex("#clipboard");
    </script>';

        return $return;
    }
}
