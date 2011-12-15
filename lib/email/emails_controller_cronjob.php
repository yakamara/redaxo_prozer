<?php

class pz_emails_controller_cronjob extends pz_controller{

	function controller($func = "") {

		$user_id = rex_request("user_id","int",0);
		if($user_id > 0)
			$f = "single";
		else
			$f = "all";

		$return = "";
		$return .= "<br />".rex_i18n::msg("email_account_download_emails").$f;

		switch($f) {
		
			case("single"):
				$email_accounts = pz_email_account::getAccounts($user_id, 1);
				foreach($email_accounts as $email_account) {
					$return .= "<br />".$email_account->downloadEmails();
				}
				break;

			case("all"):
				$email_accounts = pz_email_account::getAccounts("",1);
				foreach($email_accounts as $email_account) {
					$return .= "<br />d".$email_account->downloadEmails();
				}
				break;
		
		}

		return $return." - abgeschlossen";
	
	}

}