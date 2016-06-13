<?php

class pz
{
    protected static $login_user = null;
    protected static $user       = null;
    protected static $users      = null;
    public static $mediaviews = ['screen', 'calcarddav', 'caldav', 'webdav', 'carddav', 'api']; // 'mobile'
    public static $mediaview  = 'screen';
    protected static $header     = [];
    protected static $properties = [];

    const CONFIG_NAMESPACE = 'prozer';

    public static function controller()
    {
        $timer = new rex_timer();

        // TODO UTF8 einstellen
        // ini_set("mbstring.func_overload",7);
        // echo ini_get("mbstring.func_overload");

        mb_internal_encoding('UTF-8');

        // error_reporting(E_STRICT);
        // ini_set("display_errors",1);
        // ob_start();

        setlocale(LC_ALL, [pz_i18n::msg('locale_utf8'), pz_i18n::msg('locale')]);
        date_default_timezone_set(self::getDateTimeZone()->getName());

        $func = rex_request('func');

        self::$mediaview = rex_request('mediaview');
        if (!in_array(self::$mediaview, self::$mediaviews)) {
            self::$mediaview = 'screen';
        }

        $class = 'pz_' . self::$mediaview . '_controller';

        if (!class_exists($class)) {
            return 'ERROR PCNE ' . $class;
        }
        $ctr = new $class();
        $return = $ctr->controller($func);

        // debug - if page needs more than xxx seconds
        if (is_object(self::getUser()) && self::getUser()->getId() == 1 && $timer->getDelta() > 700) {
        }

        return $return;
    }

    // -------------------------------------------------------------------------

    public static function getFilter($filter = [], $where = [], $params = [], $how = 'AND')
    {
        foreach ($filter as $f) {
            if (!isset($f['type'])) {
                $f['type'] = '';
            }

            switch ($f['type']) {

                case('plain'):
                    $where[] = $f['value'];
                    break;

                case('findinset'):
                    $where[] = ' FIND_IN_SET(?, `' . $f['field'] . '`) ';
                    $params[] = $f['value'];
                    break;

                case('findinmyset'):
                    $f['value'] = implode(',', $f['value']);
                    $w = $f['field'];
                    $where[] = ' FIND_IN_SET(`' . $w . '`, ? ) ';
                    $params[] = $f['value'];
                    break;

                case('like'):
                    $w = $f['field'];
                    $w .= ' LIKE ? ';
                    $f['value'] = '%' . $f['value'] . '%';
                    $where[] = $w;
                    $params[] = $f['value'];
                    break;

                case('orlike'):
                    $fields = explode(',', $f['field']);
                    $w = [];
                    foreach ($fields as $field) {
                        $w[] = ' ( `' . $field . '` LIKE ? )';
                        $params[] = '%' . $f['value'] . '%';
                    }
                    $where[] = '(' . implode(' OR ', $w) . ')';
                    break;

                case('query'):
                    $where[] = $f['query'];
                    $params = array_merge($params, $f['params']);
                    break;

                case('>'):
                    $w = $f['field'];
                    $w .= ' > ? ';
                    $where[] = $w;
                    $params[] = $f['value'];
                    break;

                case('>='):
                case('=>'):
                    $w = $f['field'];
                    $w .= ' >= ? ';
                    $where[] = $w;
                    $params[] = $f['value'];
                    break;

                case('<'):
                    $w = $f['field'];
                    $w .= ' < ? ';
                    $where[] = $w;
                    $params[] = $f['value'];
                    break;

                case('<='):
                case('=<'):
                    $w = $f['field'];
                    $w .= ' <= ? ';
                    $where[] = $w;
                    $params[] = $f['value'];
                    break;

                case('<>'):
                case('><'):
                    $w = $f['field'];
                    $w .= ' <> ? ';
                    $where[] = $w;
                    $params[] = $f['value'];
                    break;

                case('='):
                default:
                    $w = $f['field'];
                    $w .= ' = ? ';
                    $where[] = $w;
                    $params[] = $f['value'];
            }
        }

        $return = [];
        $return['where'] = $where;
        $return['params'] = $params;
        $return['where_sql'] = '';

        if ($how == 'OR') {
            $return['query'] = '(' . implode(' OR ', $return['where']) . ')';
        } else {
            $return['query'] = '(' . implode(' AND ', $return['where']) . ')';
        }

        if (count($return['where']) > 0) {
            $return['where_sql'] = ' where ' . $return['query'] . '';
        }

        return $return;
    }

    // ----------- user/s

    public static function setUser(pz_user $user, pz_login $login = null)
    {
        self::$login_user = $user;
        self::$user = $user;

        if ($login) {
            $new_user_id = rex_request('pz_set_user', 'int', 0);
            if ($new_user_id < 1 && $login->getSessionVar('pz_active_user') != '') {
                $new_user_id = $login->getSessionVar('pz_active_user');
            }

            if ($new_user_id > 0) {
                if ($new_user_id == self::$login_user->getId()) {
                    $login->setSessionVar('pz_active_user', '');

                    return;
                }

                foreach ($user->getGivenUserPerms() as $user_perm) {
                    if ($user_perm->getFromUser()->getId() == $new_user_id) {
                        self::$user = $user_perm->getFromUser();
                        $login->setSessionVar('pz_active_user', self::$user->getId());
                        self::$user->setUserPerm($user_perm);
                    }
                }
            }
        }
    }

    /**
     * @return pz_user
     */
    public static function getUser()
    {
        return self::$user;
    }

    public static function getLoginUser()
    {
        return self::$login_user;
    }

    public static function getUsers($filter = [])
    {
        if (count($filter) == 0 && count(self::$users) > 0) {
            return self::$users;
        }

        $params = [];
        $where = [];

        $f = self::getFilter($filter, $where, $params);
        $where = $f['where'];
        $params = $f['params'];
        $where_sql = $f['where_sql'];

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT u.* FROM pz_user u ' . $where_sql . ' ORDER BY u.name', $params);
        $users = [];
        foreach ($sql->getArray() as $row) {
            $users[$row['id']] = pz_user::get($row['id']);
        }

        self::$users = $users;

        return $users;
    }

    public static function getUsersAsString($users = null)
    {
        $return = [];

        if (!$users) {
            $users = self::getUsers();
        }

        foreach ($users as $user) {
            $v = $user->getName();
            $v = str_replace('=', '', $v);
            $v = str_replace(',', '', $v);
            $return[] = $v . '=' . $user->getId();
        }

        return implode(',', $return);
    }

    public static function getActiveAdminUsersAsString()
    {

        $sql = rex_sql::factory();
        $query = 'SELECT * FROM pz_user WHERE id IN '
               . '(SELECT pu.user_id FROM pz_project AS p INNER JOIN pz_project_user AS pu ON p.id = pu.project_id '
               . 'WHERE p.archived=0 AND p.archived IS NOT NULL AND pu.`admin` GROUP BY pu.user_id) ORDER BY name;';
        $sql->setQuery($query);

        $users = [];
        foreach ($sql->getArray() as $row) {
            $users[$row['id']] = pz_user::get($row['id']);
        }

        self::$users = $users;

        return self::getUsersAsString($users);
    }

    public static function getUsersAsArray($users = null)
    {
        if (!$users) {
            $users = self::getUsers();
        }
        $return = [];
        foreach ($users as $user) {
            $return[] = ['id' => $user->getId(), 'label' => $user->getName()];
        }

        return $return;
    }

    public static function getProjectsAsArray($projects = null)
    {
        if (!$projects) {
            return [];
        }
        $return = [];
        foreach ($projects as $project) {
            $return[] = ['id' => $project->getId(), 'label' => $project->getName()];
        }

        return $return;
    }

    public static function getServer()
    {
        return $_SERVER['HTTP_HOST'];
        // return self::getProperty('server');
    }

    public static function getServerUrl()
    {
        $protocolSecure = '';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $protocolSecure = 's';
        }

        return 'http' . $protocolSecure . '://' . self::getServer();
    }

    public static function setConfig($key, $value = null)
    {
        return pz_config::set(self::CONFIG_NAMESPACE, $key, $value);
    }

    /**
     * @see rex_config::get()
     */
    public static function getConfig($key = null, $default = null)
    {
        return pz_config::get(self::CONFIG_NAMESPACE, $key, $default);
    }

    /**
     * @see rex_config::has()
     */
    public static function hasConfig($key)
    {
        return pz_config::has(self::CONFIG_NAMESPACE, $key);
    }

    /**
     * @see rex_config::remove()
     */
    public static function removeConfig($key)
    {
        return pz_config::remove(self::CONFIG_NAMESPACE, $key);
    }

    public static function setProperty($key, $value)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }
        $exists = isset(self::$properties[$key]);
        self::$properties[$key] = $value;

        return $exists;
    }

    public static function getProperty($key, $default = null)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }
        if (isset(self::$properties[$key])) {
            return self::$properties[$key];
        }

        return $default;
    }

    public static function hasProperty($key)
    {
        return is_string($key) && isset(self::$properties[$key]);
    }

    public static function removeProperty($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }
        $exists = isset(self::$properties[$key]);
        unset(self::$properties[$key]);

        return $exists;
    }

    // ----------- date

    public static function getDatetime($datetime = null)
    {
        // TODO: settimezone - greenwhich time +0000

        if (!$datetime) {
            $datetime = new DateTime();
        }

        $datetime->setTimezone(self::getDateTimeZone());

        return $datetime;
    }

    public static function getDateTimeZone()
    {
        return new DateTimeZone('Europe/Berlin');
    }

    // ----------- tools

    public static function stripSlashes($a)
    {

        $process = array(&$a);
        while (list($key, $val) = each($process)) {
            foreach ($val as $k => $v) {
                unset($process[$key][$k]);
                if (is_array($v)) {
                    $process[$key][stripslashes($k)] = $v;
                    $process[] = &$process[$key][stripslashes($k)];
                } else {
                    $process[$key][stripslashes($k)] = stripslashes($v);
                }
            }
        }
        unset($process);
        return $a;

    }

    public static function cutText($text = '', $size = 30, $ext = ' ...', $style = 'left')
    {
        if ($style == 'left') {
            if (mb_strlen($text . $ext) > $size) {
                $text = mb_substr($text, 0, $size - mb_strlen($ext)) . $ext;
            }
        } else {
            if (mb_strlen($text . $ext) > $size) {
                $size = intval($size / 2);
                $text = mb_substr($text, 0, $size) . $ext . mb_substr($text, (-1 * $size));
            }
        }

        return $text;
    }

    public static function url($mediaview = '', $controll = '', $func = '', $params = [], $split = '&')
    {
        if ($mediaview == '' or $controll == '') {
            return 'javascript:void(0);';
        }

        $return = '/' . $mediaview . '/' . urlencode($controll) . '/';

        if (is_array($func)) {
            return 'XXXXXXXX';
        } elseif ($func != '') {
            $return .= urlencode($func) . '/';
        }

        $p = '';
        if (count($params) > 0) {
            foreach ($params as $k => $v) {
                if ($p != '') {
                    $p .= $split;
                }
                $p .= urlencode($k) . '=' . urlencode($v);
            }
            $return .= '?' . $p;
        }

        return $return;
    }

    public static function error($message)
    {
        echo '<pre>' . $message;
        var_dump(debug_backtrace());
        echo '</pre>';
    }

    public static function debug($message, $p = '', $type = 'log')
    {
        // return;

        switch ($type) {
            case('error'):
            case('err'):
                $type = 'error';
                break;
            case('warning'):
            case('warn'):
                $type = 'warn';
                break;
            case('info'):
                $type = 'info';
                break;
            default:
                $type = 'log';
                break;
        }

        FB::$type('pz: ' . $message);
        if (is_array($p)) {
            self::debugArray($p, $type, 0);
        } elseif ($p != '') {
            FB::$type($p);
        }
    }

    public static function debugArray($p, $type = 'info', $level = 1)
    {
        return;

        FB::$type('array (');
        foreach ($p as $k => $m) {
            if (is_array($m)) {
                FB::$type('[' . $k . '] = ');
                $level++;
                self::debugArray($m, $type, $level);
                $level--;
            } else {
                FB::$type('[' . $k . '] = ' . $m);
            }
        }
        FB::$type(')');
    }

    public static function makeInlineImage($image_path, $size = 'm', $mimetype = 'image/png')
    {
        // TODO
        // anhand vom mimetype erkennen welches Bildrenderer genommen werdenkann oder muss oder auch nicht
        // if(isset(self::$mimetypes[$mimetype]))
        // 	return self::$mimetypes[$mimetype]["extension"];

        $src = @imagecreatefrompng($image_path);
        if ($src) {
            return self::makeInlineImageFromSource($src, $size, $mimetype, true);
        }

        return '';
    }

    public static function makeInlineImageFromSource($data, $size = 'm', $mimetype = 'image/png', $inline = true)
    {
        $data = $data;
        $src = imagecreatefromstring($data);
        if ($src) {
            imagealphablending($src, true);
            imagesavealpha($src, true);

            $image_width = imagesx($src);
            $image_height = imagesy($src);

            $new_width = 25;
            $new_height = 25;

            if ($size == 'xxl') {
                $new_width = 400;
                $new_height = 400;
            } elseif ($size == 'xl') {
                $new_width = 200;
                $new_height = 200;
            } elseif ($size == 'm') {
                $new_width = 40;
                $new_height = 40;
            } elseif ($size == 's') {
                $new_width = 20;
                $new_height = 20;
            }

            $dest_width = $new_width;
            $dest_height = $new_width;

            $image_ratio = $image_width / $image_height;
            $resize_ratio = $new_width / $new_height;

            if ($image_ratio < $resize_ratio) {
                $new_height = ceil($new_width / $image_width * $image_height);
            } else {
                $new_width = ceil($new_height / $image_height * $image_width);
            }

            $tmp = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($tmp, $src, 0, 0, 0, 0, $new_width, $new_height, $image_width, $image_height);
            $src = $tmp;

            $image_width = imagesx($src);
            $image_height = imagesy($src);

            $offset_height = (int) (($image_height - $dest_height) / 2);
            $offset_width = (int) (($image_width - $dest_width) / 2);

            $tmp = imagecreatetruecolor($dest_width, $dest_height);
            imagecopyresampled($tmp, $src, 0, 0, $offset_width, $offset_height, $dest_width, $dest_height, $dest_width, $dest_height);

            /*
            $grey   = ImageColorAllocate ($tmp, 200, 200, 200);
            imageline ( $tmp , 0 , 0 , ($dest_width-2) , 0 ,  $grey );
            imageline ( $tmp , ($dest_width-1) , 0 , ($dest_width-1) , ($dest_height-1) , $grey );
            imageline ( $tmp , ($dest_width-1) , ($dest_height-1) , 0 , ($dest_height-1) , $grey );
            imageline ( $tmp , 0 , ($dest_height-1) , 0 , 0 , $grey );
            */

            $src = $tmp;

            ob_start();
            switch ($mimetype) {
                case('image/jpeg'):
                case('image/jpg'):
                    imagejpeg($src);
                    $mimetype = 'image/jpg';
                    break;
                default:
                    imagepng($src);
                    $mimetype = 'image/png';
            }

            $image = ob_get_contents();
            ob_end_clean();

            if ($inline) {
                $base64_img = 'data:' . $mimetype . ';base64,' . base64_encode($image);
            } else {
                $base64_img = base64_encode($image);
            }

            return $base64_img;
        }

        return '';
    }

    public static function readableFilesize($size)
    {
        $size = $size + 0;
        if ($size == 0) {
            return '0 Bytes';
        }
        $filesizename = [' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB'];

        return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i];
    }

    public static function array2csv($as = [])
    {
        $search = [',', '"', "\n", "\r", ' '];
        $replace = [';', '""', '', '', '_'];

        $h = [];
        foreach ($as as $a) {
            foreach ($a as $k => $v) {
                $k = str_replace($search, $replace, $k);
                $h[$k] = $k;
            }
        }

        $search = [',', '"', "\n", "\r"];
        $replace = [';', '""', '', ''];

        $return = [];
        $return[] = implode(',', $h);

        foreach ($as as $a) {
            $data = [];
            foreach ($h as $t => $tt) {
                $t = str_replace($search, $replace, $t);
                $v = @$a[$t];
                if (!is_int($v)) {
                    $v = '"' . str_replace($search, $replace, $v) . '"';
                }
                $data[] = $v;
            }
            $return[] = implode(',', $data);
        }

        return implode("\n", $return);
    }

    public static function array2excel($as = [])
    {
        $return = '';
        $return .= '<table>';

        $th = [];
        foreach ($as as $a) {
            foreach ($a as $k => $v) {
                $th[$k] = '<th>' . $k . '</th>';
            }
        }

        $return .= '<tr>' . implode('', $th) . '</tr>';

        foreach ($as as $a) {
            $return .= '<tr>';
            foreach ($th as $t => $tt) {
                $return .= '<td>' . @$a[$t] . '</td>';
            }
            $return .= '</tr>';
        }

        $return .= '</table>';

        $file_name = 'excel_export' . date('Ymd') . '.xls';

        self::setHeader('pragma', 'public');
        self::setHeader('expires', '0');
        self::setHeader('cache-control', 'private');
        self::setHeader('content-type', 'application/vnd.ms-excel');
        self::setHeader('content-disposition', 'attachment');
        self::setHeader('filename', basename($file_name));
        self::setHeader('content-length', strlen($return));
        self::setHeader('content-transfer-encoding', 'binary');

        return $return;
    }

    public static function dateTime2dateFormat($datetime, $dateFormat)
    {
        $caracs = [
            // Day - no strf eq : S
            'd' => '%d', 'D' => '%a', 'j' => '%e', 'l' => '%A', 'N' => '%u', 'w' => '%w', 'z' => '%j',
            // Week - no date eq : %U, %W
            'W' => '%V',
            // Month - no strf eq : n, t
            'F' => '%B', 'm' => '%m', 'M' => '%b',
            // Year - no strf eq : L; no date eq : %C, %g
            'o' => '%G', 'Y' => '%Y', 'y' => '%y',
            // Time - no strf eq : B, G, u; no date eq : %r, %R, %T, %X
            'a' => '%P', 'A' => '%p', 'g' => '%l', 'h' => '%I', 'H' => '%H', 'i' => '%M', 's' => '%S',
            // Timezone - no strf eq : e, I, P, Z
            'O' => '%z', 'T' => '%Z',
            // Full Date / Time - no strf eq : c, r; no date eq : %c, %D, %F, %x
            'U' => '%s',
        ];
        $strftimeformat = strtr((string) $dateFormat, $caracs);

        return strftime($strftimeformat, $datetime->getTimestamp());
    }

    public static function strftime($f, $t)
    {
        return self::encodeEnsure(strftime($f, $t));
    }

    /**
     * @see pz::decodeEnsure()
     *
     * @param        $str
     * @param string $encoding
     *
     * @return string
     */
    public static function encodeEnsure($str, $encoding = 'UTF-8')
    {
        return mb_check_encoding($str, $encoding) ? $str : mb_convert_encoding($str, $encoding, 'auto');
    }

    public static function getIniGetInBytes($val)
    {
        if (empty($val)) {
            return 0;
        }

        $val = trim($val);
        preg_match('#([0-9]+)[\s]*([a-z]+)#i', $val, $matches);

        $last = '';
        if (isset($matches[2])) {
            $last = $matches[2];
        }

        if (isset($matches[1])) {
            $val = (int) $matches[1];
        }

        switch (strtolower($last)) {
            case 'g':
            case 'gb':
                $val *= 1024;
            case 'm':
            case 'mb':
                $val *= 1024;
            case 'k':
            case 'kb':
                $val *= 1024;
        }

        return (int) $val;
    }

    // ------------------------------------------------------------------------ mimetypes

    protected static $mimetypes = [
        'html' => 'text/html',
        'zip'  => 'application/zip',
        'gif'  => 'image/gif',
        'jpg'  => 'image/jpg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'mp3'  => 'audio/mpeg',
        'eml'  => 'message/rfc822',
        'pdf'  => 'application/pdf',
        'txt'  => 'text/plain',
        'html' => 'text/html',
        'ics'  => 'text/calendar',

        'doc'  => 'application/vnd.ms-word',
        'xls'  => 'application/vnd.ms-excel',
        'ppt'  => 'application/vnd.ms-powerpoint',

        'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
        'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
        'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
        'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
        'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',

    ];

    public static function getExtensionByMimetype($mimetype)
    {
        foreach (self::$mimetypes as $ext => $mt) {
            if ($mimetype == $mt) {
                return $ext;
            }
        }

        return false;
    }

    public static function getExtensionByFilename($file_name)
    {
        $f = explode('.', $file_name);
        end($f);
        $ext = current($f);
        return $ext;
    }

    public static function getMimeTypeByFilename($file_name, $content = '')
    {
        $ext = self::getExtensionByFilename($file_name);
        if (isset(self::$mimetypes[$ext])) {
            return self::$mimetypes[$ext];
        }
        if ($content != '') {
            $file_info = new finfo(FILEINFO_MIME_TYPE);
            $mimetype = $file_info->buffer($content);

            return $mimetype;
        }

        return 'application/octet-stream';
    }

    public static function getMimetypeIconPath($mimetype)
    {
        if ($ext = self::getExtensionByMimetype($mimetype)) {
            $file = rex_path::frontend('/assets/addons/prozer/themes/blue_grey/mimetypes/' . $ext . '.png');
            if (file_exists($file)) {
                return '/assets/addons/prozer/themes/blue_grey/mimetypes/' . $ext . '.png';
            }
        }

        return '/assets/addons/prozer/themes/blue_grey/mimetypes/file.png';
    }

    public static function setDownloadHeaders($file_name, $content)
    {
        self::setHeader('cache-control', 'private');
        self::setHeader('content-type', self::getMimeTypeByFilename($file_name, $content));
        self::setHeader('filename', basename($file_name));
        self::setHeader('content-disposition', 'attachment');
        self::setHeader('content-length', strlen($content));
        self::setHeader('pragma', 'public');
        self::setHeader('expires', '0');
        self::setHeader('content-transfer-encoding', 'binary');
    }

    public static function setHeader($type, $value)
    {
        self::$header[$type] = $value;
    }

    public static function setHeaders($header)
    {
        self::$header = array_merge(self::$header, $header);
    }

    public static function getHeader($type)
    {
        if (!isset(self::$header[$type])) {
            return '';
        }

        return self::$header[$type];
    }

    public static function sendHeader()
    {
        if (self::getHeader('content-type') == '') {
            self::setHeader('content-type', 'text/html');
        }
        if (self::getHeader('charset') == '') {
            self::setHeader('charset', 'UTF-8');
        }

        $charset = self::getHeader('charset');
        if ($charset != '') {
            $charset = ' charset=' . $charset;
        }

        header('Content-Type: ' . self::getHeader('content-type') . '; ' . $charset);

        if (self::getHeader('filename') != '') {
            if (self::getHeader('content-disposition') == '') {
                self::setHeader('content-disposition', 'inline');
            }
            header('Content-Disposition: ' . self::getHeader('content-disposition') . '; filename="' . self::getHeader('filename') . '";');
        }

        if (self::getHeader('content-length') != '') {
            header('Content-Length: ' . self::getHeader('content-length'));
        }

        if (self::getHeader('pragma') != '') {
            header('Pragma: ' . self::getHeader('pragma'));
        }

        if (self::getHeader('expires') != '') {
            header('Expires: ' . self::getHeader('expires'));
        }

        if (self::getHeader('content-transfer-encoding') != '') {
            header('Content-Transfer-Encoding: ' . self::getHeader('content-transfer-encoding'));
        }

        if (self::getHeader('cache-control') == 'private') {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false); // required for certain browsers
        }
    }

    // ---------------------------------------------------------------

    public static function refreshCache()
    {
        // create / update CSS Files
        pz_labels::update();

        // TODO: refresh contact fulltext
    }
}
