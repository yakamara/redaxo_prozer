<?PHP

class rex_xform_validate_pz_project_folder extends rex_xform_validate_abstract 
{

	function enterObject()
	{

		if($this->params["send"]=="1")
		{
			$field = $this->getElement(2);
			$folder_id = $this->getElement(3);
			$project_id = $this->getElement(4);

			foreach($this->obj_array as $o)
			{
				if ($o->getName() == $field)
				{
					$current_parent_id = (int) $o->getValue();
					if($current_parent_id == 0)
					{
						
					}else{

						// ist der parentfolder existent
						if(!($folder = pz_project_file::get($current_parent_id)) || !$folder->isDirectory()) 
						{
							// 
							$this->params["warning"][$o->getId()] = $this->params["error_class"];
							$this->params["warning_messages"][$o->getId()] = rex_i18n::msg("error_folder_name_parent");
							
						// gibt es parentfolder in diesem projekt
						}elseif($folder->getProjectId() != $project_id)
						{
							$this->params["warning"][$o->getId()] = $this->params["error_class"];
							$this->params["warning_messages"][$o->getId()] = rex_i18n::msg("error_folder_project_id");
							
						}else
						{
							$parents = $folder->getParentsIds();
							$parents[] = $folder->getId();
							if(in_array($folder_id,$parents))
							{
								$this->params["warning"][$o->getId()] = $this->params["error_class"];
								$this->params["warning_messages"][$o->getId()] = rex_i18n::msg("error_folder_in_same_folder");
								
							}

						}
						
					}

				}

			}

		}

		return; 
		
	}

	function getDescription()
	{
		return "validate|pz_project_folder|field|parent_id|Warning Message|Warning Message - wrong project";
	}
}
?>