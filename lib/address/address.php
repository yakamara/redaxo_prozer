<?php

class pz_address extends pz_model
{
	public $vars = array();
	private $fields = array();
	static $addresses = array();


	function __construct($vars = array())
	{
		$this->setVars($vars);
	}

	static public function get($id = "")
	{
		if($id == "") return FALSE;
		$id = (int) $id;
		$sql = rex_sql::factory();
		$sql->setQuery('select * from pz_address where id='.$id.' LIMIT 2');
		$addresses = $sql->getArray();
		if(count($addresses) != 1) return FALSE;
		return new self($addresses[0]);
	}

	static public function getByUri($uri)
	{
		$sql = rex_sql::factory();
		$sql->setQuery('select * from pz_address where uri="'.$uri.'" LIMIT 2');
		$addresses = $sql->getArray();
		if(count($addresses) != 1) return FALSE;
		return new self($addresses[0]);
	}

	static public function getByEmail($email)
	{
		$sql = rex_sql::factory();
		$sql->setQuery('select * from pz_address_field where type="EMAIL" and value=? LIMIT 1',array($email));
		$sql_a = $sql->getArray();

		if(count($sql_a)>0) {
			$a = current($sql_a);
			if($address = pz_address::get($a["address_id"]))
				return $address;
		}
		return NULL;
	}

	static public function getAllByFulltext($fulltext = "")
	{
	  $filter = array();
	  if($fulltext != "")
		  $filter[] = array("field"=>"vt", "type"=>"like", "value"=>$fulltext);
	  return pz_address::getAll($filter);
	}

	static public function getAll($filter = array())
	{
		$where = array();
		$params = array();
		
	    foreach($filter as $f)
	    {
	    	switch(@$f["type"]) {
	    		case("like"):
			    	$where[] = $f["field"].' LIKE ? ';
			    	$params[] = "%".$f["value"]."%";
	    			break;
	    		case("="):
				default:
			    	$where[] = $f["field"].' = ? ';
			    	$params[] = $f["value"];
	    	}
	    }
	
	    $sql_where = '';
	    if(count($where) > 0)
	    {
			$sql_where.= ' where ('.implode(" AND ",$where).') ';
	    }
	
	  $sql = rex_sql::factory();
	  // $sql->debugsql = 1;
	  $sql->setQuery('SELECT * FROM pz_address '.$sql_where,$params);
	  $addresses = array();
	  foreach($sql->getArray() as $row)
	  {
	    $addresses[] = new self($row);
	  }
	  return $addresses;
	}

	public function getId()
	{
		return (int) $this->vars['id'];
	}

	public function getName()
	{
		return $this->vars['name'];
	}

	public function getFirstName()
	{
	  return $this->vars['firstname'];
	}

	public function getFullName()
	{
	  return implode(' ', 
		  		array_filter(
		  			array( $this->vars['prefix'], $this->vars['firstname'], $this->vars['additional_names'], $this->vars['name'], $this->vars['suffix'] )
	 	 			)
	 	 		);
	}

	public function getCompany()
	{
	  return $this->vars['company'];
	}

	public function isCompany()
	{
	  return (boolean) $this->vars['is_company'];
	}

	public function getNote()
	{
	  return $this->vars['note'];
	}

	public function getInlineImage()
	{
		$return = "";
		$photo = trim($this->getVar("photo"));
		if($photo != "")
			$return = pz_address::makeInlineImage($photo);
		if($return == "")
			$return = pz_address::getDefaultImage();
		return $return;
	}

	static public function makeInlineImage($photo, $size = "xl", $mimetype = "image/jpg") {
	
		// PHOTO;ENCODING=b;TYPE=JPEG;X-ABCROP-RECTANGLE=ABClipRect_1&0&64&480&480&EZ+ Q5v4Z5Ou9atiMTeB+8w==:
		// PHOTO;BASE64=: /9j/4AAQSkZJRgABAQAAAQABAAD//gAoCgo
		// PHOTO;ENCODING=b;TYPE=JPEG:/9j/4AAQSkZJRgABAQA
		// check possible encodings: base64_decode
		// check possible image types: JPEG
	
		$photo = str_replace(" ","", $photo);
		$photo = explode(":",$photo);
		$src = base64_decode($photo[1]);
		return pz::makeInlineImageFromSource($src, $size, $mimetype);	

	}

	static public function getDefaultImage() {
		return 	"/layout_prozer/css/user.png";
	}

	public function getFields()
	{
	  if($this->fields)
	    return $this->fields;

	  $sql = rex_sql::factory();
	  $sql->setQuery('SELECT * FROM pz_address_field WHERE address_id = ? ORDER BY type ASC, preferred DESC', array($this->getId()));
	  foreach($sql->getArray() as $row)
	  {
	    $this->fields[] = new pz_address_field($row);
	  }
	  return $this->fields;
	}

	public function saveToHistory($mode = 'update')
	{
	  $sql = rex_sql::factory();
	  $sql->setTable('pz_address_history')
	    ->setValue('address_id', $this->getId())
	    ->setValue('user_id', $this->vars['updated_user_id'])
	    ->setRawValue('stamp', 'NOW()')
	    ->setValue('mode', $mode);
	  if($mode != 'delete')
	  {
	    $data = $this->vars;
	    unset($data['vt']);
	    foreach($this->getFields() as $field)
	      $data['fields'][] = $field->getVars();
	    $sql->setValue('data', json_encode($data));
	  }
	  $sql->insert();
	}

	private function updateUriAndVT()
	{
	  $vt = array();
	  $vt[] = $this->getFullName();
	  $vt[] = $this->getCompany();
	  $vt[] = $this->getNote();
	  $vt[] = $this->getVar('additional_names');
	  $vt[] = $this->getVar('nickname');
	  foreach($this->getFields() as $field)
	  {
	    $vt[] = $field->getVar('value');
	  }
	  $sql = rex_sql::factory();
	  $sql->setTable('pz_address')
	    ->setWhere(array('id' => $this->getId()))
	    ->setValue('vt', implode(' ', $vt));
	  if($this->getVar('uri') == "")
	    $sql->setRawValue('uri', 'CONCAT(UPPER(UUID()), ".vcf")');
	  $sql->update();
	}

	public function create()
	{
	  $this->saveToHistory('create');
	  $this->updateUriAndVT();

	  pz_sabre_carddav_backend::incrementCtag();
	}

	public function update()
	{
	  $this->saveToHistory('update');
	  $this->updateUriAndVT();

	  pz_sabre_carddav_backend::incrementCtag();
	}

	public function delete()
	{
	  $this->saveToHistory('delete');

	  rex_sql::factory()->setQuery('
    	DELETE a, af
    	FROM pz_address a
			LEFT JOIN pz_address_field af
			ON a.id = af.address_id
			WHERE a.id = ?
    ', array($this->vars['id']));

	  pz_sabre_carddav_backend::incrementCtag();
	}

}