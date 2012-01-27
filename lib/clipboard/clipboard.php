<?php

class pz_clipboard
{

	static $clipboards = array();

	static function getByUserId( $user_id = 0 )
	{
		if(isset(self::$clipboards[$user_id]))
			return self::$clipboards[$user_id];
		
		$user = pz_user::get($user_id);
		if($user === null)
			return false;
		
		self::$clipboards[$user_id] = new self();
		self::$clipboards[$user_id]->user = $user;
		
		return self::$clipboards[$user_id];
	}

	public function getClips($filter = array())
	{
		
		$where = array();
		$where[] = 'user_id = ?';
		$where[] = 'hidden = ?';
		$params = array();
		$params[] = $this->user->getId();
		$params[] = 0;
		
		$return = pz::getFilter($filter, $where, $params);
		
		$sql = rex_sql::factory();
		// $sql->debugsql = 1;
		$files = $sql->getArray('SELECT c.* FROM pz_clipboard as c '.$return['where_sql'].' ORDER BY c.id desc', $return["params"]);
		return $files;
	}

	static function deleteClipById($clip_id)
	{
		$sql = rex_sql::factory();
		// $sql->debugsql = 1;
		$clips = $sql->setQuery('delete from pz_clipboard where id = ?', array($clip_id));
		return true;
	}


	static function getClipById($clip_id, $user_id = 0)
	{
		if($user_id == 0)
			$user_id = pz::getUser()->getId();
			
		$sql = rex_sql::factory();
		// $sql->debugsql = 1;
		$clips = $sql->getArray('SELECT c.* FROM pz_clipboard as c where user_id = ? and id = ? LIMIT 2', array($user_id,$clip_id));
		if(count($clips) == 1) {
			return $clips[0];
		}
		return false;
	}

	static function getPath($clip_id,$user_id = 0)
	{
		if($user_id == 0) $user_id = pz::getUser()->getId();
		return rex_path::addonData('prozer', 'users/'.$user_id.'/clipboard/'.$clip_id.'.data');
	}

	/* Creates Clip with ID */
	public function getClipname($filename, $content_length, $content_type, $hidden = TRUE)
	{
		$s = rex_sql::factory();
		$s->setTable('pz_clipboard');
		$s->setValue('created',date("Y-m-d H:i:s"));
		$s->setValue('updated',date("Y-m-d H:i:s"));
		$s->setValue('user_id',$this->user->getId());
		$s->setValue('filename',$filename);
		$s->setValue('content_type',$content_type);
		$s->setValue('content_length',$content_length);
		if($hidden) 	$s->setValue('hidden',1);
		else			$s->setValue('hidden',0);
		$s->insert();

		$id = (int) $s->getLastId();
		$path = pz_clipboard::getPath($id,$this->user->getId());

		$return = array('id' => $id, 'path' => $path, 'filename' => $filename);

		return $return;
	}

	public function addClipAsStream($stream, $filename, $content_length, $content_type)
	{
		$clipdata = $this->getClipname($filename,$content_length,$content_type);
		rex_dir::create(dirname($clipdata["path"]));
		$target = fopen($clipdata["path"], "w");        
        fseek($stream, 0, SEEK_SET);
        stream_copy_to_stream($stream, $target);
        fclose($target);
        unset($clipdata["path"]);
        return $clipdata;
	}

	public function addClipAsSource($filesource, $filename, $content_length, $content_type, $hidden)
	{
		$clipdata = $this->getClipname($filename, $content_length, $content_type, $hidden);
		rex_dir::create(dirname($clipdata["path"]));
		file_put_contents($clipdata["path"], $filesource);
		return $clipdata;
	}



}






