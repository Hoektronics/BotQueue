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

class ConfigTest extends BotQueue_Unit_Test {
	
	/** @var array $keys */
	private $keys;
	/** @var Config $config */
	private $config;

	protected function setUp() {
		$this->keys = array();
		$this->keys["FORCE_SSL"] = false;
		$this->keys["COMPANY_NAME"] = "BotQueue";
		$this->keys["IS_DEV_SITE"] = true;
		
		$this->keys["SITE_HOSTNAME"] = "botqueue.com";
		$this->keys["RR_PROJECT_NAME"] = "BotQueue";
		
		$this->keys["RR_DB_HOST"] = "localhost";
		$this->keys["RR_DB_PORT"] = "3306";
		$this->keys["RR_DB_USER"] = "root";
		$this->keys["RR_DB_PASS"] = "";
		
		$this->keys["AMAZON_AWS_KEY"] = "";
		$this->keys["AMAZON_AWS_SECRET"] = "";
		$this->keys["AMAZON_S3_BUCKET_NAME"] = "botqueue";

		$this->keys["EMAIL_METHOD"] = "smtp";
		$this->keys["SES_USE_DKIM"] = true;
		$this->keys["EMAIL_USERNAME"] = "mailer@example.com";
		$this->keys["EMAIL_NAME"] = "BotQueue";
		$this->keys["EMAIL_PASSWORD"] = "";
		$this->keys["EMAIL_SMTP_SERVER"] = "smtp.gmail.com";
		$this->keys["EMAIL_SMTP_SERVER_PORT"] = 465;

		$this->keys["TRACK_SQL_QUERIES"] = false;
		$this->keys["TRACK_CACHE_HITS"] = false;

		$this->keys["THINGIVERSE_API_CLIENT_ID"] = "";
		$this->keys["THINGIVERSE_API_CLIENT_SECRET"] = "";

		$this->config = new Config(ConfigConverter::convertKeys($this->keys));
	}

	public function testConvertKeys() {
		$this->assertEquals(
			$this->keys["FORCE_SSL"],
			Config::get("force_ssl")
		);

		$this->assertEquals(
			$this->keys["COMPANY_NAME"],
			Config::get("company_name")
		);

		$this->assertEquals(
			$this->keys["IS_DEV_SITE"],
			Config::get("dev_site")
		);

		$this->assertEquals(
			$this->keys["SITE_HOSTNAME"],
			Config::get("hostname")
		);

		$this->assertEquals(
			$this->keys["RR_PROJECT_NAME"],
			Config::get("db/name")
		);

		$this->assertEquals(
			$this->keys["RR_DB_HOST"],
			Config::get("db/host")
		);

		$this->assertEquals(
			$this->keys["RR_DB_PORT"],
			Config::get("db/port")
		);

		$this->assertEquals(
			$this->keys["RR_DB_USER"],
			Config::get("db/user")
		);

		$this->assertEquals(
			$this->keys["RR_DB_PASS"],
			Config::get("db/pass")
		);

		$this->assertEquals(
			$this->keys["AMAZON_AWS_KEY"],
			Config::get("aws/key")
		);

		$this->assertEquals(
			$this->keys["AMAZON_AWS_SECRET"],
			Config::get("aws/secret")
		);

		$this->assertEquals(
			$this->keys["EMAIL_METHOD"],
			Config::get("email/method")
		);

		$this->assertEquals(
			$this->keys["SES_USE_DKIM"],
			Config::get("email/ses_dkim")
		);

		$this->assertEquals(
			$this->keys["EMAIL_NAME"],
			Config::get("email/name")
		);

		$this->assertEquals(
			$this->keys["EMAIL_PASSWORD"],
			Config::get("email/pass")
		);

		$this->assertEquals(
			$this->keys["EMAIL_SMTP_SERVER"],
			Config::get("email/smtp_server")
		);

		$this->assertEquals(
			$this->keys["EMAIL_SMTP_SERVER_PORT"],
			Config::get("email/smtp_port")
		);

		$this->assertEquals(
			$this->keys["TRACK_SQL_QUERIES"],
			Config::get("track/sql")
		);

		$this->assertEquals(
			$this->keys["TRACK_CACHE_HITS"],
			Config::get("track/cache")
		);

		$this->assertEquals(
			$this->keys["THINGIVERSE_API_CLIENT_ID"],
			Config::get("thingiverse/client_id")
		);

		$this->assertEquals(
			$this->keys["THINGIVERSE_API_CLIENT_SECRET"],
			Config::get("thingiverse/client_secret")
		);
	}

	public function testErrorOnNonExistentKey() {
		$this->setExpectedException("Exception");

		Config::get("SuperNonExistentKey");
	}
}
 