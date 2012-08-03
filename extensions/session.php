<?
	session_start();
	User::authenticate();
	
	//handle any login payloads.
	if (User::isLoggedIn())
	{
		if (!empty($_SESSION['payload']))
		{
			$payload = $_SESSION['payload'];
			if ($payload['type'] == 'redirect')
			{
				header("Location: " . $payload['data']);
				unset($_SESSION['payload']);
				exit;
			}
		}
	}
?>