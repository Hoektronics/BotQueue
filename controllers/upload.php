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

class UploadController extends Controller
{
	public function home()
	{
		$this->assertLoggedIn();

		$this->setTitle("Step 1 of 2: Choose File to Print");
	}

	public function uploader()
	{
		$form = $this->createFileForm();

		$this->setArg('label');
		$this->set('form', $form);
	}

	public function url()
	{
		$this->assertLoggedIn();

		$this->setTitle("Create Job from URL");

		try {
			//did we get a url?
			$url = $this->args('url');
			if (!$url) {
				if($_SESSION['thing_url']) {
					$url = $_SESSION['thing_url'];
					unset($_SESSION['thing_url']);
				}
				else throw new Exception("You must pass in the URL parameter!");
			}

			$matches = array();
			if (preg_match("/thingiverse.com\\/thing:([0-9]+)/i", $url, $matches)) {
				$thing_id = $matches[1];

				if(!defined('THINGIVERSE_API_CLIENT_ID') && !defined('THINGIVERSE_API_CLIENT_SECRET'))
					throw new Exception("This site has not set up the Thingiverse api.");

				$thingiverse_token = User::$me->getThingiverseToken();
				if($thingiverse_token === '') {
					$this->forwardToURL("/thingiverse/url/".base64_encode(serialize($url)));
				}

				$api = new ThingiverseAPI(THINGIVERSE_API_CLIENT_ID, THINGIVERSE_API_CLIENT_SECRET, User::$me->getThingiverseToken());

				//load thingiverse data.
				$thing = $api->make_call("/things/{$thing_id}");
				$files = $api->make_call("/things/{$thing_id}/files");

				//open zip file.
				$zip_path = tempnam("/tmp", "BQ");
				$zip = new ZipArchive();
				if ($zip->open($zip_path, ZIPARCHIVE::CREATE)) {
					//pull in all our files.
					foreach ($files AS $row) {
						if (preg_match("/\\.(".ACCEPTABLE_FILES.")$/i", $row->name)) {
							$data = Utility::downloadUrl($row->public_url);
							$zip->addFile($data['localpath'], $data['realname']);
						}
					}
					$zip->close();

					//create zip name.
					$filename = basename($thing->name . ".zip");
					$filename = str_replace(" ", "_", $filename);
					$filename = preg_replace("/[^-_.[0-9a-zA-Z]/", "", $filename);
					$path = "assets/" . StorageInterface::getNiceDir($filename);

					//okay, upload it and handle it.
					$file = Storage::newFile();
					$file->set('user_id', User::$me->id);
					$file->set('source_url', $url);

					$file->upload($zip_path, $path);
					FileUploadHandler::_handleZipFile($zip_path, $file);

					$this->forwardToUrl("/job/create/file:{$file->id}");
				} else
					throw new Exception("Unable to open zip {$zip_path} for writing.");
			} else {
				$data = Utility::downloadUrl($url);

				//does it match?
				if (!preg_match("/\\.(".ACCEPTABLE_FILES."|zip)$/i", $data['realname']))
					throw new Exception("The file <a href=\"" . $url . "\">{$data['realname']}</a> is not valid for printing.");

				$file = Storage::newFile();
				$file->set('user_id', User::$me->id);
				$file->set('source_url', $url);
				$file->upload($data['localpath'],
					StorageInterface::getNiceDir($data['realname']));

				//is it a zip file?  do some magic on it.
				if (!preg_match("/\\.zip$/i", $data['realname']))
					FileUploadHandler::_handleZipFile($data['localpath'], $file);

				Activity::log("uploaded a new file called " . $file->getLink() . ".");

				//send us to step 2.
				$this->forwardToUrl("/job/create/file:{$file->id}");
			}
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function success()
	{
		$this->assertLoggedIn();

		//handle our upload
		try {
			$file = FileUploadHandler::fromName($this->args('key'));
			Activity::log("uploaded a new file called " . $file->getLink() . ".");

			//send us to step 2.
			$this->forwardToUrl("/job/create/file:{$file->id}");
		} //did anything go wrong?
		catch (Exception $e) {
			$this->setTitle("Upload File - Error");
			$this->set('megaerror', $e->getMessage());
		}
	}

	/**
	 * @return string
	 */
	private function createFileForm()
	{
		$form = new Form('form', true);
		/** @var StorageInterface $file */
		$file = Storage::newFile();
		$fields = $file->getUploadFields();

		foreach ($fields as $name => $value) {
			$form->add(
				HiddenField::name($name)
					->value($value)
			);
		}

		$form->add(
			UploadField::name("file")
		);

		$form->setSubmitText("Upload File");
		$form->action = $file->getUploadURL();

		return $form;
	}
}

// Function to help sign the policy
function hex2b64($str)
{
	$raw = '';
	for ($i = 0; $i < strlen($str); $i += 2) {
		$raw .= chr(hexdec(substr($str, $i, 2)));
	}
	return base64_encode($raw);
}
