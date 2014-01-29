<?php

class rex_xform_value_pz_calendar_event_attendees extends rex_xform_value_abstract
{

	function enterObject()
	{

		$html_id = $this->getHTMLId();
    	$name = $this->getName();
		$fragment = $this->params['fragment'];

		$attendees = array();
		$attendee_labels = pz_calendar_attendee::getStatusArray();

		$my_user_id = pz::getUser()->getId();
		
		if($this->params["send"] == 1)
		{
			$calendar_event_attendees_field_status = rex_request("calendar_event_attendees_field_status","array");
			$calendar_event_attendees_field_emails = rex_request("calendar_event_attendees_field_email","array");
			$calendar_event_attendees_field_names = rex_request("calendar_event_attendees_field_name","array");
			$calendar_event_attendees_field_user_ids = rex_request("calendar_event_attendees_field_user_id","array");
			foreach($calendar_event_attendees_field_user_ids as $k => $v) {
				if($calendar_event_attendees_field_user_ids[$k] != "")
					if($my_user_id != $calendar_event_attendees_field_user_ids[$k])
					{
						$attendees[] = array(
							"status" => @$calendar_event_attendees_field_status[$k],
							"email" => @$calendar_event_attendees_field_emails[$k],
							"name" => @$calendar_event_attendees_field_names[$k],
							"user_id" => @$calendar_event_attendees_field_user_ids[$k]
						);
					}
			}
		}else
		{
			if($this->params["main_id"] != "" && $event = pz_calendar_event::get($this->params["main_id"])) 
			{
				$as = pz_calendar_attendee::getAll($event);
				if(is_array($as)) {
					foreach($as as $a) {
						
						$a_status = $a->getStatus();
						// $pz_attandee_init = $this->getElement(3);
						
						if($this->getElement(3) == 1)
							$a_status = 'NEEDS-ACTION';						
						
						$attendees[] = array(
							"status" => $a_status,
							"email" => $a->getEmail(),
							"name" => $a->getName(),
							"user_id" => $a->getUserId()
						);
					}
				}
			}		
		}

		$output = '';

		// Attendees

		$user_select = new rex_select();
		$user_select->setSize(1);
		$user_select->setStyle("width:250px;");
		$user_select->setName("calendar_event_attendees_field_user_id[]");
		$user_select->addOption(rex_i18n::msg('please_choose'),'');
		foreach(pz::getUser()->getUsers() as $user) 
			$user_select->addOption($user->getName(),$user->getId());

		$f = new rex_fragment();
		$f->setVar('before', "", false);
		$f->setVar('after', "", false);
		$f->setVar('extra', "", false);
		$f->setVar('name', $name, false);
		$f->setVar('class', "attendee_field", false);

		$attendees_output = ''; // '<h2 class="hl2">' . rex_i18n::msg("calendar_event_attendees") . '</h2>';
		foreach($attendees as $attendee) 
		{

			$select = new rex_select();
			$select->setSize(1);
			$select->setStyle("width:80px;");
			$select->setName("calendar_event_attendees_field_status[]");
			foreach($attendee_labels as $label) 
				$select->addOption(rex_i18n::msg('calendar_event_attendee_'.strtolower($label)),$label);
			
			if(!in_array($attendee["status"],$attendee_labels))
				$select->addOption($attendee["status"],$attendee["status"]);
			$select->setSelected($attendee["status"]);

			$attandee_user_select = clone $user_select;
			$attandee_user_select->setSelected($attendee["user_id"]);

			$label = '<label class="'.$this->getHTMLClass().'">' . $select->get() . '</label>';	
			// $field = '<input style="width:140px;" class="'.$this->getHTMLClass().'" type="text" name="calendar_event_attendees_field_email[]" value="'.htmlspecialchars($attendee["email"]).'" />';
			// $field .= '<input style="width:110px;" class="'.$this->getHTMLClass().'" type="text" name="calendar_event_attendees_field_name[]" value="'.htmlspecialchars($attendee["name"]).'" />';
			$field = $attandee_user_select->get();

			$f->setVar('label', $label, false);
			$f->setVar('field', $field, false);
			$f->setVar('class', "phone_field", false);
			$attendees_output .= $f->parse($fragment);
			
		}
		
		$select = new rex_select();
		$select->setSize(1);
		$select->setStyle("width:80px;");
		$select->setName("calendar_event_attendees_field_status[]");
		foreach($attendee_labels as $label) 
			$select->addOption(rex_i18n::msg('calendar_event_attendee_'.strtolower($label)),$label);
		$select->setSelected("NEEDS-ACTION");
		$label = '<label class="'.$this->getHTMLClass().'" >' . $select->get() . '</label>';	
		// $field = '<input style="width:140px;" class="'.$this->getHTMLClass().'" type="text" placeholder="'.rex_i18n::msg("email").'" name="calendar_event_attendees_field_email[]" value="" />';
		// $field .= '<input style="width:110px;" class="'.$this->getHTMLClass().'" type="text" placeholder="'.rex_i18n::msg("name").'" name="calendar_event_attendees_field_name[]" value="" />';
		$field = $user_select->get();
		
		$f->setVar('label', $label, false);
		$f->setVar('field', $field, false);
		$f->setVar('html_id', $this->getHTMLId("attendee_hidden"), false);
		$attendees_output .= '<div id="'.$this->getHTMLId("attendee_hidden_div").'" class="hidden">'.$f->parse($fragment).'</div>';

		$field = '<a class="bt5" href="javascript:void(0);" onclick="
						inp = $(\'#'.$this->getHTMLId("attendee_hidden").'\').clone();
						inp.attr({ id: \'\' });
						$(\'#'.$this->getHTMLId("attendee_hidden_div").'\').before(inp);		
						">+ '.rex_i18n::msg("add_attendee").'</a>';
		$f = new rex_fragment();
		$f->setVar('label', '<label></label>', false);
		$f->setVar('field', $field, false);
		$attendees_output .= $f->parse($fragment);

		$output = '<div class="pz_address_fields_attandees">'.$attendees_output.'</div>';
		
		$this->params["form_output"][$this->getId()] = $output;
		
		$this->setValue($attendees);
		$this->params["value_pool"]["email"][$this->getName()] = $this->getValue();
		$this->params["value_pool"]["sql"][$this->getName()] = $this->getValue();

		return;

	}

}

?>