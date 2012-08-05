<?php

	class MyOAuthProvider
	{
		
		private $oauth;
		private $consumer;
		private $oauth_error;
		private $user;
		
		public function __construct()
		{
			/* create our instance */
			$this->oauth = new OAuthProvider();
			
			/* setup check functions */
			$this->oauth->consumerHandler(array($this,'checkConsumer'));
			$this->oauth->timestampNonceHandler(array($this,'checkNonce'));
			$this->oauth->tokenHandler(array($this,'checkToken'));
			
		}
		
		public static function createConsumer()
		{
			$key = sha1(OAuthProvider::generateToken(20,true));
			$secret = sha1(OAuthProvider::generateToken(20,true));
			
			return OAuthConsumer::create($key,$secret);
		}
		
		/**
		 * This function check the handlers that we added in the constructor
		 * and then checks for a valid signature
		 */
		public function checkRequest(){
			/* now that everything is setup we run the checks */
			try{
				$this->oauth->checkOAuthRequest();
			} catch(OAuthException $E){
				echo OAuthProvider::reportProblem($E);
				$this->oauth_error = true;
			}
		}
		
		/**
		 * This function is called when you are requesting a request token
		 * Basically it disabled the tokenHandler check and force the oauth_callback parameter
		 */
		public function setRequestTokenQuery()
		{
			$this->oauth->isRequestTokenEndpoint(true); 
			$this->oauth->addRequiredParameter("oauth_callback");
		}
		
		/**
		 * This function generates a Request token
		 * and save it in the db
		 * then returns the oauth_token, oauth_token_secret & the authentication url
		 * Please note that the authentication_url is not part of the oauth protocol but I added it to show you how to add extra parameters
		 */
		public function generateRequestToken()
		{	
			if($this->oauth_error)
				return false;
			
			$token = sha1(OAuthProvider::generateToken(20,true));
			$token_secret = sha1(OAuthProvider::generateToken(20,true));
			
			$callback = $this->oauth->callback;
			
			OAuthToken::createRequestToken($this->consumer, $token, $token_secret, $callback);
		
			//todo: this is weird.  take it out?
			return "authentification_url=".$this->authentification_url."&oauth_token=".$token."&oauth_token_secret=".$token_secret."&oauth_callback_confirmed=true";
			
		}
		
		/**
		 * This function generates a Access token saves it in the DB and return it
		 * In that process it also removes the request token used to get that access token
		 */
		public function generateAccesstoken()
		{
			if($this->oauth_error)
				return false;
			
			$access_token = sha1(OAuthProvider::generateToken(20,true));
			$secret = sha1(OAuthProvider::generateToken(20,true));
			
			$token = OAuthToken::findByToken($this->oauth->token);
			$token->changeToAccessToken($access_token, $secret);
			
			return "oauth_token=".$access_token."&oauth_token_secret=".$secret;
		}
		
		/**
		 * This function generates a verifier and returns it
		 */
		public function generateVerifier()
		{
			$verifier = sha1(OAuthProvider::generateToken(20,true));
			return $verifier;
		}
		
		/* handlers */
		
		/**
		 * This function checks if the consumer exist in the DB and that it is active
		 * You can modify it at your will but you __HAVE TO__ set $provider->consumer_secret to the right value or the signature will fail
		 * It's called by OAuthCheckRequest()
		 * @param $provider
		 */
		public function checkConsumer($provider)
		{
			$return = OAUTH_CONSUMER_KEY_UNKNOWN;
			
			$aConsumer = OAuthConsumer::findByKey($provider->consumer_key);
			
			if($aConsumer->isHydrated())
			{
				if(!$aConsumer->isActive())
				{
					$return = OAUTH_CONSUMER_KEY_REFUSED;
				}
				else 
				{
					$this->consumer = $aConsumer;
					$provider->consumer_secret = $this->consumer->getSecretKey();
					$return = OAUTH_OK;
				}
			}
			
			return $return;
		}
		
		/**
		 * This function checks the token of the client
		 * Fails if token not found, or verifier not correct
		 * Once again you __HAVE TO__ set the $provider->token_secret to the right value or the signature will fail
		 * It's called by OAuthCheckRequest() unless the client is getting a request token
		 * @param unknown_type $provider
		 */
		public function checkToken($provider)
		{
			$token = OAuthToken::findByToken($provider->token);
			
			if(is_null($token))
			{ // token not found
				return OAUTH_TOKEN_REJECTED;
			}
			elseif($token->getType() == 1 && $token->getVerifier() != $provider->verifier)
			{ // bad verifier for request token
				return OAUTH_VERIFIER_INVALID;
			}
			else
			{
				if($token->getType() == 2)
				{
					/* if this is an access token we register the user to the provider for use in our api */
					$this->user = $token->getUser();
				}
				$provider->token_secret = $token->getSecret();
				return OAUTH_OK;
			}
		}
		
		/**
		 * This function check both the timestamp & the nonce
		 * The timestamp has to be less than 5 minutes ago (this is not oauth protocol so feel free to change that)
		 * And the nonce has to be unknown for this consumer
		 * Once everything is OK it saves the nonce in the db
		 * It's called by OAuthCheckRequest()
		 * @param $provider
		 */
		public function checkNonce($provider)
		{
			if($this->oauth->timestamp < time() - 5*60)
				return OAUTH_BAD_TIMESTAMP;
			elseif($this->consumer->hasNonce($provider->nonce,$this->oauth->timestamp))
				return OAUTH_BAD_NONCE;
			else
			{
				$this->consumer->addNonce($this->oauth->nonce);
				return OAUTH_OK;
			}
		}
		
		public function getUser()
		{
			if(is_object($this->user))
				return $this->user;
			else
				throw new Exception("User not authenticated");
		}
		
	}
?>
