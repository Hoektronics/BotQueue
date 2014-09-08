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

class S3File extends StorageInterface
{
	/** @var Aws\S3\S3Client $client */
	private $client;
	private $info;

	public function __construct($id = null)
	{
		parent::__construct($id, "s3_files");
		$this->client = $this->getClient(AMAZON_S3_BUCKET_NAME);
		$this->set('bucket', AMAZON_S3_BUCKET_NAME);
		$this->set('type', "application/octet-stream");
	}

	private function getClient($bucket = AMAZON_S3_BUCKET_NAME)
	{
		if ($this->client === null) {
			$this->client = Aws\S3\S3Client::factory(array(
				'key' => AMAZON_AWS_KEY,
				'secret' => AMAZON_AWS_SECRET,
				'Bucket' => $bucket
			));
		}
		return $this->client;
	}

	private function getInfo() {
		if($this->info === null) {
			$result = $this->client->headObject(array(
				'Bucket' => $this->get('bucket'),
				'Key' => $this->get('path')
			));
			$this->info = array();
			$this->info["size"] = (int)$result->get("ContentLength");
			$this->info["hash"] = substr($result->get("ETag"), 1, 32);
			$this->info["type"] = $result->get("ContentType");
		}
		return $this->info;
	}

	public function delete()
	{
		if ($this->isHydrated()) {
			$this->client->deleteObject(array(
				'Bucket' => $this->get('bucket'),
				'Key' => $this->get('path'),
			));

			parent::delete();

			return true;
		}

		return false;
	}

	public function exists()
	{
		if ($this->get('path')) {
			return $this->client->doesObjectExist(AMAZON_S3_BUCKET_NAME, $this->get('path'));
		}
		return false;
	}

	public function copy()
	{
		/* @var $new S3File */
		$new = parent::copy();


		ob_start();

		//copy our new one to its own path.
		$newPath = parent::getNiceDir($new->get('path'));
		$this->client->copyObject(array(
			'Bucket' => AMAZON_S3_BUCKET_NAME,
			'Key' => $newPath,
			'CopySource' => $this->get('bucket') . "/" . $this->get('path'),
			'ACL' => 'public-read'
		));
		$new->set('path', $newPath);
		$new->set('bucket', AMAZON_S3_BUCKET_NAME);
		$new->save();

		ob_end_clean();

		return $new;
	}


	public function getUploadFields()
	{
		$redirect = "http://" . SITE_HOSTNAME . "/upload/success";
		$acl = "public-read";
		$expiration = gmdate("Y-m-d\\TH:i:s\\Z", strtotime("+1 day"));

		//create amazons crazy policy data array.
		$policy_json = '
			{
				"expiration": "' . $expiration . '",
				"conditions": [
					{"acl": "' . $acl . '"},
					{"bucket": "' . AMAZON_S3_BUCKET_NAME . '"},
					["starts-with", "$key", "uploads/"],
					["starts-with", "$Content-Type", ""],
					["starts-with", "$Content-Disposition", ""],
					{"success_action_redirect": "' . $redirect . '"},
					["content-length-range", 1, 262144000]
				]
			}';

		//create our various encoded/signed stuff.
		$policy_json_cleaned = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $policy_json);
		$policy_encoded = base64_encode($policy_json_cleaned);
		$signature = hex2b64(hash_hmac('sha1', $policy_encoded, AMAZON_AWS_SECRET));

		$fields = array();
		$fields["AWSAccessKeyId"] = AMAZON_AWS_KEY;
		$fields["key"] = "uploads/\${filename}";
		$fields["acl"] = $acl;
		$fields["success_action_redirect"] = $redirect;
		$fields["policy"] = $policy_encoded;
		$fields["signature"] = $signature;
		$fields["Content-Type"] = "";
		$fields["Content-Disposition"] = "";

		return $fields;
	}

	/**
	 * Uploads from a local path to the S3 Bucket
	 * @param $srcPath String
	 * @param $dstPath String
	 * @return bool
	 */
	public function upload($srcPath, $dstPath)
	{
		if (!file_exists($srcPath))
			return false;
		//todo Fix type
		$this->set('type', 'application/octet-stream');
		$this->set('size', filesize($srcPath));
		$this->set('hash', md5_file($srcPath));
		$this->set('path', $dstPath);
		$this->client->putObject(array(
			'Bucket' => $this->get('bucket'),
			'Key' => $dstPath,
			'Body' => fopen($srcPath, 'r+'),
			'ACL' => "public-read"
		));
		$this->save();
		// todo Verify that it actually did work
		return true;
	}

	public function download($srcPath, $dstPath)
	{
		//make directory.
		$dir = dirname($dstPath);
		if (!file_exists($dir))
			mkdir($dir, 0777, true);
		if (!is_writable($dir))
			return false;

		$this->client->getObject(array(
			'Bucket' => $this->get('bucket'),
			'Key' => $this->get('path'),
			'SaveAs' => $dstPath
		));
		$this->save();
		// todo Verify that it actually did work
		return true;
	}

	public function getSize()
	{
		$this->getInfo();
		$this->set('size', $this->info['size']);
		return $this->get('size');
	}

	public function getHash()
	{
		$this->getInfo();
		$this->set('hash', $this->info['hash']);
		return $this->get('hash');
	}

	public function getType()
	{
		$this->getInfo();
		$this->set('type', $this->info['type']);
		return $this->get('type');
	}

	public function getDownloadURL()
	{
		if($this->get('path'))
			return $this->client->getObjectUrl($this->get('bucket'), $this->get('path'));
		return "";
	}

	public function getUploadURL()
	{
		return "https://" . AMAZON_S3_BUCKET_NAME . ".s3.amazonaws.com/";
	}

	public function moveTo($dstPath)
	{
		$this->client->copyObject(array(
			'Bucket' => AMAZON_S3_BUCKET_NAME,
			'Key' => $dstPath,
			'CopySource' => $this->get('bucket') . "/" . $this->get('path'),
			'ACL' => "public-read"
		));

		$this->client->deleteObject(array(
			'Bucket' => $this->get('bucket'),
			'Key' => $this->get('path'),
		));

		$this->set('path', $dstPath);
	}
}