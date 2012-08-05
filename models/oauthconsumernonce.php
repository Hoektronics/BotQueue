<?php
	class OAuthConsumerNonce extends Model
	{
		
		public function __construct($id = null)
		{
			parent::__construct($id, "oauth_consumer_nonce");
		}		
	}
?>
