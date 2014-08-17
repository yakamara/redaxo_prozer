<?PHP

class rex_xform_validate_pz_auth extends rex_xform_validate_abstract 
{

	function enterObject()
	{
		return;
	}
	
	function postValueAction() {
		
		$login = "";
		$psw = "";
		$stay = 0;
		$error_id_field = "";

		$msg = $this->getElement(5);

		if($this->params["send"]=="1")
		{
			foreach($this->params["value_pool"]["sql"] as $k => $v)
			{
				if($k == $this->getElement(2)) 
				{
					$login = $v;
				}elseif($k == $this->getElement(3)) 
				{
					$psw = $v;
				}elseif($k == $this->getElement(4)) 
				{
					$stay = $v;
				}
			}

			if($login == "" OR $psw == "") 
			{
				$msg = $this->getElement(6);
			
			}else {

				$pz_login = new pz_login();
				$pz_login->setLogin($login, $psw);
				if($stay == 1) 
				{ 
					$pz_login->setStayLoggedIn(true);
				}
				if(!$pz_login->checkLogin())
				{
					$msg = $this->getElement(7);
				}else
				{
					// header("Location:/".pz::$mediaview."/"); exit;
					$msg = "";
          if(pz::getUser())
          {
            pz::getUser()->saveToHistory('login');
          }
				}
			}

		}
		
		if($msg != "") {
			$this->params["warning"][] = $this->params["error_class"];
			$this->params["warning_messages"][] = $msg;
			
		}
		
	}

	function getDescription()
	{
		return "pz_auth";
	}
}
?>