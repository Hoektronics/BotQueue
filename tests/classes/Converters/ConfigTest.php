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
			$this->config->get("force_ssl")
		);

		$this->assertEquals(
			$this->keys["COMPANY_NAME"],
			$this->config->get("company_name")
		);

		$this->assertEquals(
			$this->keys["IS_DEV_SITE"],
			$this->config->get("dev_site")
		);

		$this->assertEquals(
			$this->keys["SITE_HOSTNAME"],
			$this->config->get("hostname")
		);

		$this->assertEquals(
			$this->keys["RR_PROJECT_NAME"],
			$this->config->get("db/name")
		);

		$this->assertEquals(
			$this->keys["RR_DB_HOST"],
			$this->config->get("db/host")
		);

		$this->assertEquals(
			$this->keys["RR_DB_PORT"],
			$this->config->get("db/port")
		);

		$this->assertEquals(
			$this->keys["RR_DB_USER"],
			$this->config->get("db/user")
		);

		$this->assertEquals(
			$this->keys["RR_DB_PASS"],
			$this->config->get("db/pass")
		);

		$this->assertEquals(
			$this->keys["AMAZON_AWS_KEY"],
			$this->config->get("aws/key")
		);

		$this->assertEquals(
			$this->keys["AMAZON_AWS_SECRET"],
			$this->config->get("aws/secret")
		);

		$this->assertEquals(
			$this->keys["EMAIL_METHOD"],
			$this->config->get("email/method")
		);

		$this->assertEquals(
			$this->keys["SES_USE_DKIM"],
			$this->config->get("email/ses_dkim")
		);

		$this->assertEquals(
			$this->keys["EMAIL_NAME"],
			$this->config->get("email/name")
		);

		$this->assertEquals(
			$this->keys["EMAIL_PASSWORD"],
			$this->config->get("email/pass")
		);

		$this->assertEquals(
			$this->keys["EMAIL_SMTP_SERVER"],
			$this->config->get("email/smtp_server")
		);

		$this->assertEquals(
			$this->keys["EMAIL_SMTP_SERVER_PORT"],
			$this->config->get("email/smtp_port")
		);

		$this->assertEquals(
			$this->keys["TRACK_SQL_QUERIES"],
			$this->config->get("track/sql")
		);

		$this->assertEquals(
			$this->keys["TRACK_CACHE_HITS"],
			$this->config->get("track/cache")
		);

		$this->assertEquals(
			$this->keys["THINGIVERSE_API_CLIENT_ID"],
			$this->config->get("thingiverse/client_id")
		);

		$this->assertEquals(
			$this->keys["THINGIVERSE_API_CLIENT_SECRET"],
			$this->config->get("thingiverse/client_secret")
		);
	}

	public function testErrorOnNonExistentKey() {
		$this->setExpectedException("Exception");

		$this->config->get("SuperNonExistentKey");
	}
}
 