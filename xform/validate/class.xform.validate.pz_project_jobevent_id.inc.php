<?PHP

class rex_xform_validate_pz_project_jobevent_id extends rex_xform_validate_abstract 
{

	function enterObject()
	{

		if($this->params["send"]=="1")
		{
		
		  $error_msg = " ";
		  $booked = $this->getElement(3);

			foreach($this->obj_array as $o) {
				if ($o->getName() == $this->getElement(2)) {
					$project_id = $o->getValue();
				}
			}
			
      $error = false;


      if(!isset($project_id)) {
        $error = true;

      } else if (!isset($booked)) {
        $error = true;

      } else {
  			$filter = array();
  			$filter[] = array("field"=>"id","value"=>$project_id);
        if($booked == 1) {
  			  $projects = pz::getUser()->getCalendarJobsProjects($filter);
          $error_msg = $this->getElement(4);

        }else {
  			  $projects = pz::getUser()->getCalendarProjects($filter);
          $error_msg = $this->getElement(5);

        }

  			if(count($projects) != 1) {
          $error = true;  
  			}
        
      }

      if ($error) {
				$this->params["warning"][$o->getId()] = $this->params["error_class"];
				$this->params["warning_messages"][$o->getId()] = $error_msg;
      }			

		}

		return; 
		
	}

	function getDescription()
	{
		return "pz_project_jobevent_id|project_id_field|booked_field|Warning Message";
	}
}
?>