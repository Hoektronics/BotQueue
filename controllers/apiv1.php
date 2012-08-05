<?
  /*
    This file is part of BotQueue.

    BotQueue is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    BotQueue is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with BotQueue.  If not, see <http://www.gnu.org/licenses/>.
  */

	class APIV1Controller extends Controller
	{
		public $api_version = "1.0";
		
		public function main()
		{
			$this->setArg('content');
		}
		
		//not sure what this stuff does.  this was in the oauth login page docs.
		public function authenticate()
		{
			$request_token = Token::findByToken($_REQUEST['oauth_token']);
			if(is_object($request_token)&&$request_token->isRequest()){
				if(is_object($user)){
					$request_token->setVerifier(Provider::generateVerifier());
					$request_token->setUser($user);
					header("location: ".$request_token->getCallback()."?&oauth_token=".$_REQUEST['oauth_token']."&oauth_verifier=".$request_token->getVerifier());
				} else {
					echo "User not found !";
				}
			} else {
				echo "The specified token does not exist";
			}
		}
		
		public function request_token()
		{
			$provider = new MyOAuthProvider();
			$provider->setRequestTokenQuery();
			$provider->checkRequest();
			echo $provider->generateRequestToken();			
		}

		public function access_token()
		{
			$provider = new MyOAuthProvider();
			$provider->checkRequest();
			echo $provider->generateAccessToken();
		}
		
		public function create_consumer()
		{
			$consumer = MyOAuthProvider::createConsumer();
			?>
			<h1>New consumer</h1>
			<strong>Key : </strong> <?php echo $consumer->getKey()?><br />
			<strong>Secret : </strong> <?php echo $consumer->getSecretKey()?>
			<?			
		}
		
		public function api_call()
		{
			$provider = new MyOAuthProvider();

			/* this is a basic api call that will return the id of an authenticated user */
			$provider->checkRequest();
			try {
				echo $provider->getUser()->getId();
			} catch(Exception $E){
				echo $E;
			}
		}
	}
?>