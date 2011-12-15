<?PHP

class rex_xform_validate_pz_project_id extends rex_xform_validate_abstract 
{

	function enterObject()
	{

		if($this->params["send"]=="1")
		{
		
			$field = $this->getElement(2);
			
			foreach($this->obj_array as $o)
			{
				if ($o->getName() == $field)
				{
					$project_id = $o->getValue();

					$filter = array();
					$filter[] = array("field"=>"id","value"=>$project_id);
					$projects = pz::getUser()->getAllProjects($filter);

					if(count($projects) != 1) {
						$this->params["warning"][$o->getId()] = $this->params["error_class"];
						$this->params["warning_messages"][$o->getId()] = $this->getElement(3);;
					}

				}

			}

		}

		return; 
		
	}

	function getDescription()
	{
		return "pz_project_id|field|Warning Message";
	}
}
?>