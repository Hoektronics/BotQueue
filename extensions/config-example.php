<?php
  error_reporting(E_ALL ^ E_NOTICE);
  define("FORCE_SSL", false);
  define("COMPANY_NAME", "BotQueue");
  define("IS_DEV_SITE", true);
  
	define("SITE_HOSTNAME", "botqueue.com");
	define("RR_PROJECT_NAME", "BotQueue");
	
  define("RR_DB_HOST", "localhost");
  define("RR_DB_PORT", "3306");
  define("RR_DB_USER", "root");
  define("RR_DB_PASS", "");
  define("RR_DB_NAME", "BotQueue");
  
	define("AMAZON_AWS_KEY", "");
	define("AMAZON_AWS_SECRET", "");
	define("AMAZON_S3_BUCKET_NAME", "botqueue");

  define("EMAIL_USERNAME", "mailer@example.com");
  define("EMAIL_NAME", "BotQueue");
  define("EMAIL_PASSWORD", "");
  define("EMAIL_SMTP_SERVER", "smtp.gmail.com");
  define("EMAIL_SMTP_SERVER_PORT", 465);
  	
  define('TRACK_SQL_QUERIES', false);
  define('TRACK_CACHE_HITS', false);
	
	//CacheBot::setBot(new EasyDBCache());
	CacheBot::setBot(new NoCache());
?>