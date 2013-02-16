<?php

	class MyOAuthProvider
	{
		public $oauth;
		public $consumer;
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
		
		public static function generateToken()
		{
			return sha1(OAuthProvider::generateToken(20));
		}
		
		/**
		 * This function checks if the consumer exist in the DB and that it is active
		 * You can modify it at your will but you __HAVE TO__ set $provider->consumer_secret to the right value or the signature will fail
		 * It's called by OAuthCheckRequest()
		 * @param $provider
		 */
		public function checkConsumer($provider)
		{
			$return = OAUTH_CONSUMER_KEY_UNKNOWN;
			
			$c = OAuthConsumer::findByKey($provider->consumer_key);
			if($c->isHydrated())
			{
				if(!$c->isActive())
				{
					$return = OAUTH_CONSUMER_KEY_REFUSED;
				}
				else 
				{
					$this->consumer = $c;
					$provider->consumer_secret = $this->consumer->get('consumer_secret');
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
			$token = OAuthToken::findByKey($provider->token);
			
			if(!$token->isHydrated())
				return OAUTH_TOKEN_REJECTED;
			elseif($token->get('type') == 1 && $token->get('verifier') != $provider->verifier)
				return OAUTH_VERIFIER_INVALID;
			else
			{
				if($token->get('type') == 2)
				{
					/* if this is an access token we register the user to the provider for use in our api */
					$this->user = $token->getUser();
					User::$me = $token->getUser();
				}

				$provider->token_secret = $token->get('token_secret');
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
		  //give them an hour!
			if($this->oauth->timestamp < time() - 60*60)
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
