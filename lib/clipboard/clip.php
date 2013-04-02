<?php

class pz_clip extends pz_model
{

	var $vars = array();

	public function __construct($vars) 
	{
		parent::__construct($vars);

	}

	static public function get($id = "")
	{
		if($id == "") 
		  return false;
		$id = (int) $id;

		$sql = rex_sql::factory();
		$sql->setQuery('select * from pz_clipboard where id = ? LIMIT 1',array($id));

		$vars = $sql->getArray();
		if(count($vars) != 1) 
		  return false;

		return 
		  new pz_clip($vars[0]);
	}


	static public function getByUri($uri = "")
	{
		if($uri == "") 
		  return false;

		$sql = rex_sql::factory();
		$sql->setQuery('select * from pz_clipboard where uri = ? LIMIT 1',array($uri));

		$vars = $sql->getArray();
		if(count($vars) != 1) 
		  return false;

		return 
		  new pz_clip($vars[0]);
	}



  public function getClips($filter = array())
	{
		$return = pz::getFilter($filter);
		
		$sql = rex_sql::factory();
		// $sql->debugsql = 1;
		$clips_array = $sql->getArray('SELECT * FROM pz_clipboard '.$return['where_sql'].' ORDER BY id desc', $return["params"]);
		
		$clips = array();
		foreach($clips_array as $clip_array)
		{
		  if(($clip = new pz_clip($clip_array)))
		  {
		    $clips[$clip->getId()] = $clip;
		  }
		}
		
		return $clips;
	}


  // ------------ getter

	public function getId()
	{
		return $this->getVar("id");
	}

  public function getPath()
	{
		return rex_path::addonData('prozer', 'users/'.$this->getVar("user_id").'/clipboard/'.$this->getId().'.data');
	}

  public function getFilename()
	{
	  return $this->getVar("filename");
  }

  public function getContentLength()
	{
	  return (int) $this->getVar("content_length");
  }

  public function getContentType()
	{
	  // pz::getMimeTypeByFilename($file_name, $content);
	  return $this->getVar("content_type");
  }

  public function getUser()
  {
    return pz_user::get($this->getVar("user_id"));
  }

  public function getContent()
  {
		return file_get_contents($this->getPath());
  }

  public function download()
  {
    return pz::getDownloadHeader($this->getFilename(), $this->getContent());
  }

  public function getUserId()
  {
    return $this->getVar("user_id");
  }
  
  public function getInlineImage($raw = false, $image_size = "m", $image_type = "image/jpg")
  {
    return pz::makeInlineImageFromSource($this->getContent(), $image_size, $image_type, $raw);
  }
  
  public function getCreateDate()
  {
    $created = pz::getDateTime()->createFromFormat("Y-m-d H:i:s",$this->getVar("created"));
    return $created;
  }

  public function getOfflineDate()
  {
    return pz::getDateTime()->createFromFormat("Y-m-d H:i:s",$this->getVar("offline_date"));
  }

  public function getUri()
  {
    return pz::getServerUrl().'/clip/'.$this->getVar("uri").'/';
  }

  public function getDownloadLink()
  {
    return '/screen/clipboard/get/?mode=download_clip&clip_id='.$this->getId();
  }

  public function isReleased()
  {
    if($this->getVar("uri") == "")
      return false;
  
    if($this->getVar("open") != 1)
      return false;

    if( !( $offline_date = $this->getOfflineDate() ) )
      return false;

    if(pz::getDateTime()->diff($offline_date)->format("days")<0)
      return false;
  
    return true;
  }


  // ------------ actions

	public function release($online_date = NULL, $offline_date = NULL)
	{
    if (!is_a($online_date, 'DateTime'))
      $online_date = pz::getDateTime();

    if (!is_a($offline_date, 'DateTime'))
    {
      $offline_date = clone $online_date;
      $offline_date->modify('+3 months');
    }

    $s = rex_sql::factory();
		$s->setTable('pz_clipboard');
    $s->setWhere(array("id" => $this->getId()));
		$s->setValue('updated',pz::getDateTime()->format("Y-m-d H:i:s"));
		$s->setValue('online_date',$online_date->format("Y-m-d H:i:s"));
		$s->setValue('offline_date',$offline_date->format("Y-m-d H:i:s"));
	  $s->setValue('hidden',0);
	  $s->setValue('open',1);

    $uri = $this->getId().'x'.substr(sha1($this->getId().$this->getFilename().$this->getPath().$this->getVar("created")),0,10);
	  $s->setValue('uri',$uri);
		$s->update();

    $this->refresh();
    return $this;

	}

	public function unrelease()
	{
    $s = rex_sql::factory();
		$s->setTable('pz_clipboard');
    $s->setWhere(array("id" => $this->getId()));
		$s->setValue('updated',pz::getDateTime()->format("Y-m-d H:i:s"));
	  $s->setValue('open',0);
		$s->update();

    $this->refresh();
    return $this;

	}


  public function refresh()
  {
    $sql = rex_sql::factory();
    $clips_array = $sql->getArray('select * from pz_clipboard where id = ? LIMIT 1',array($this->getId()));
    
    if(count($clips_array) == 1)
    {
      $vars = $clips_array[0];
      $this->__construct($vars);
    
    }
    
  }


  public function delete()
	{
	  // TODO: prüfen ob noch in verwendung und 6 monate alt...

	  // nicht in offenen emails
	  // nicht open sein

    // nicht unmittelbar in benutzung ?!?! 
    // . problem.. gerade hingefügt und ist in email oder file oder sonstwas.. sprich älter als 1 Stunde.


    // TODO: aus dem Dateisytem löschen

    $this->saveToHistory('delete');

		$sql = rex_sql::factory();
		// $sql->debugsql = 1;
		$clips = $sql->setQuery('delete from pz_clipboard where id = ?', array($this->getId()));
		
		return true;
	}


	public function create($filename = "", $content_length = 0, $content_type = "", $hidden = TRUE, $user = NULL)
	{
	  if($user == NULL) {
	    $user = pz::getLoginUser();
	  }
	
		$s = rex_sql::factory();
		$s->setTable('pz_clipboard');
		$s->setValue('created',date("Y-m-d H:i:s"));
		$s->setValue('updated',date("Y-m-d H:i:s"));
		$s->setValue('user_id',$user->getId());
		$s->setValue('filename',$filename);
		$s->setValue('content_type',$content_type);
		$s->setValue('content_length',$content_length);
		if ($hidden) {
		  $s->setValue('hidden',1);
		} else {
		  $s->setValue('hidden',0);
		}
		$s->insert();

		$id = (int) $s->getLastId();
		return pz_clip::get($id);
	}

  static function createAsStream($stream, $filename, $content_length, $content_type)
	{
		$clip = new pz_clip(array());
		$clip = $clip->create($filename,$content_length,$content_type);
		rex_dir::create(dirname($clip->getPath()));
		$target = fopen($clip->getPath(), "w");        
    fseek($stream, 0, SEEK_SET);
    stream_copy_to_stream($stream, $target);
    fclose($target);
    return $clip;
	}

	static function createAsSource($filesource, $filename, $content_length, $content_type, $hidden)
	{
	  $clip = new pz_clip(array());
		$clip = $clip->create($filename, $content_length, $content_type, $hidden);
		rex_dir::create(dirname($clip->getPath()));
		file_put_contents($clip->getPath(), $filesource);
		return $clip;
	}

  public function saveToHistory($mode = 'update', $func = '')
  {
    $user_id = 0;
    if(pz::getUser())
      $user_id = pz::getUser()->getId();
  
    $sql = rex_sql::factory();
    $sql->setTable('pz_history')
      ->setValue('control', 'clip')
      ->setValue('func', $func)
      ->setValue('data_id', $this->getId())
      ->setValue('user_id', $user_id)
      ->setRawValue('stamp', 'NOW()')
      ->setValue('mode', $mode);
    if($mode != 'delete')
    {
      $data = $this->vars;
      $data["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
      $data["QUERY_STRING"] = $_SERVER["QUERY_STRING"];
      $data["SCRIPT_URI"] = "";
      if(isset($_SERVER["SCRIPT_URI"])) {
        $data["SCRIPT_URI"] = $_SERVER["SCRIPT_URI"];
      } else if (isset($_SERVER["SCRIPT_URI"])) {
        $data["SCRIPT_URI"] = $_SERVER["REQUEST_URI"];
      }
      $sql->setValue('data', json_encode($data));
    }
    $sql->insert();
  }




}