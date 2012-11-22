<?php

class pz_emails_controller_api extends pz_controller
{

	function controller($func = "") 
	{
	
	  // $func = download

		$user_id = rex_request("user_id","int",0);
		if($user_id > 0)
			$f = "download_single";
		else
			$f = "download_all";

		$return = "";
		switch($f) 
		{
      case("download_single"):
				$email_accounts = pz_email_account::getAccounts($user_id, 1);
				foreach($email_accounts as $email_account) {
					$return .= "<br />".$email_account->downloadEmails();
				}
				break;

			case("download_all"):
				$email_accounts = pz_email_account::getAccounts("",1);
				foreach($email_accounts as $email_account) {
					$return .= "<br />".$email_account->downloadEmails();
				}
				break;
		
		}

		return $return;
	
	}

}