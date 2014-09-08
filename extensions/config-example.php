<?php
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

define("STORAGE_METHOD", "S3File");
define("AMAZON_AWS_KEY", "");
define("AMAZON_AWS_SECRET", "");
define("AMAZON_S3_BUCKET_NAME", "botqueue");

define("EMAIL_METHOD", "SMTP");
define("SES_USE_DKIM", false);
define("SES_USE_VERP", true);

define("EMAIL_USERNAME", "mailer@example.com");
define("EMAIL_NAME", "BotQueue");
define("EMAIL_PASSWORD", "");
define("EMAIL_SMTP_SERVER", "smtp.gmail.com");
define("EMAIL_SMTP_SERVER_PORT", 465);

define('TRACK_SQL_QUERIES', false);
define('TRACK_CACHE_HITS', false);

// These are registered through thingiverse
define('THINGIVERSE_API_CLIENT_ID', "");
define('THINGIVERSE_API_CLIENT_SECRET', "");

//CacheBot::setBot(new EasyDBCache());
CacheBot::setBot(new NoCache());