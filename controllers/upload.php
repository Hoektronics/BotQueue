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

		$this->set('form', $form);
	}

	public function url()
	{
		$this->assertLoggedIn();

		$form = $this->_createUrlForm();
		$this->set('form', $form);

		$this->setTitle("Create Job from URL");

		if($form->checkSubmitAndValidate($this->args())) {
			try {
				//did we get a url?
				$url = $this->args('url');
				if (!$url) {
					if ($_SESSION['thing_url']) {
						$url = $_SESSION['thing_url'];
						unset($_SESSION['thing_url']);
					} else
						throw new Exception("You didn't give us a URL");
				}

				if (preg_match("/thingiverse.com\\/thing:([0-9]+)/i", $url, $matches)) {
					$file = $this->_handleThingiverseLinks($url);
				} else {
					$tempFile = ServerFile::downloadFromUrl($url);

					if($tempFile === null)
						throw new Exception("We can't seem to access that file");

					//does it match?
					if (!$tempFile->isKnownType() && !$tempFile->isZip())
						throw new Exception("The file <a href=\"" . $url . "\">{$tempFile->getName()}</a> is not valid for printing.");

					$file = Storage::newFile();
					$file->set('user_id', User::$me->id);
					$file->set('source_url', $url);
					$file->uploadNice($tempFile->getFile(), $tempFile->getName());

					//is it a zip file?  do some magic on it.
					if ($tempFile->isZip()) {
						FileUploadHandler::_handleZipFile($tempFile->getFile(), $file);
					}
				}

				Activity::log("uploaded a new file called " . $file->getLink() . ".");

				//send us to step 2.
				$this->forwardToUrl("/job/create/file:{$file->id}");
			} catch (Exception $e) {
				/** @var FormField $field */
				$field = $form->get('url');
				$field->error($e->getMessage());
			}
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
	 * @return Form
	 */
	private function createFileForm()
	{
		/** @var Form $form */
		$form = new Form('file', true);
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

	private function _createUrlForm()
	{
		$form = new Form('url');

		$form->add(
			UrlField::name("url")
		);

		$form->setSubmitText("Go!");
		$form->action = "/upload";

		return $form;
	}

	/**
	 * @param $url
	 * @return StorageInterface
	 * @throws Exception
	 */
	private function _handleThingiverseLinks($url)
	{
		$matches = array();
		if (preg_match("/thingiverse.com\\/thing:([0-9]+)/i", $url, $matches)) {
			$thing_id = $matches[1];

			if (!defined('THINGIVERSE_API_CLIENT_ID') && !defined('THINGIVERSE_API_CLIENT_SECRET'))
				throw new Exception("This site has not set up the Thingiverse api.");

			$thingiverse_token = User::$me->getThingiverseToken();
			if ($thingiverse_token === '') {
				$this->forwardToURL("/thingiverse/url/" . base64_encode(serialize($url)));
			}

			$api = new ThingiverseAPI(THINGIVERSE_API_CLIENT_ID, THINGIVERSE_API_CLIENT_SECRET, User::$me->getThingiverseToken());

			//load thingiverse file
			$zip_file = $api->download_thing($thing_id);

			if($zip_file !== null && $zip_file->isZip()) {
				$storage_file = Storage::newFile();
				$storage_file->set('user_id', User::$me->id);
				$storage_file->set('source_url', $url);
				$storage_file->uploadNice($zip_file->getFile(), $zip_file->getName());

				FileUploadHandler::_handleZipFile($zip_file->getFile(), $storage_file);

				return $storage_file;
			} else {
				throw new Exception("We can't seem to access that file");
			}
		} else {
			throw new Exception("That is not a valid Thingiverse link");
		}
	}
}