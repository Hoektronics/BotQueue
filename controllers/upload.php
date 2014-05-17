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
            if (!$url)
                throw new Exception("You must pass in the URL parameter!");

            $matches = array();
            if (preg_match("/thingiverse.com\\/thing:([0-9]+)/i", $url, $matches)) {
                $thing_id = $matches[1];

                //echo "found: $thing_id<Br/>";

                // TODO: We need to define a thingiverse api client ID, or get it when the user
                // authenticates it.
                $api = new ThingiverseAPI(THINGIVERSE_API_CLIENT_ID, THINGIVERSE_API_CLIENT_SECRET, User::$me->getThingiverseToken());

                //load thingiverse data.
                $thing = $api->make_call("/things/{$thing_id}");
                $files = $api->make_call("/things/{$thing_id}/files");

                //echo "<pre>";
                //print_r($thing);
                //print_r($files);
                //echo "</pre>";

                //open zip file.
                $zip_path = tempnam("/tmp", "BQ");
                $zip = new ZipArchive();
                if ($zip->open($zip_path, ZIPARCHIVE::CREATE)) {
                    //echo "opened $zip_path<br/>";

                    //pull in all our files.
                    foreach ($files AS $row) {
                        if (preg_match("/\\.(stl|obj|amf|gcode)$/i", $row->name)) {
                            $data = Utility::downloadUrl($row->public_url);
                            //echo "downloaded " . $data['realname'] . " to " . $data['localpath'] . "<br/>";

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

                    //echo "uploading $zip_path to $path<br/>";

					$file->upload($zip_path, $path);
                    $this->_handleZipFile($zip_path, $file);

                    $this->forwardToUrl("/job/create/file:{$file->id}");
                } else
                    throw new Exception("Unable to open zip {$zip_path} for writing.");
            } else {
                $data = Utility::downloadUrl($url);

                //does it match?
                if (!preg_match("/\\.(stl|obj|amf|gcode|zip)$/i", $data['realname']))
                    throw new Exception("The file <a href=\"".$url."\">{$data['realname']}</a> is not valid for printing.");

                $file = Storage::newFile();
                $file->set('user_id', User::$me->id);
                $file->set('source_url', $url);
				$file->upload($data['localpath'],
					StorageInterface::getNiceDir($data['realname']));

                //is it a zip file?  do some magic on it.
                if (!preg_match("/\\.zip$/i", $data['realname']))
                    $this->_handleZipFile($data['localpath'], $file);

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
            //some basic error checking.
            if (!preg_match('/(gcode|stl|obj|amf|zip)$/i', $this->args('key')))
                throw new Exception("Only .gcode, .stl, .obj, .amf, and .zip files are allowed at this time.");

            //make our file.
            $file = $this->_createFile();

            //is it a zip file?  do some magic on it.
            if (preg_match("/\\.zip$/i", $this->args('key'))) {
                $path = tempnam("/tmp", "BQ");
				$file->download($file->get('path'), $path);
                $this->_handleZipFile($path, $file);
            }

            Activity::log("uploaded a new file called " . $file->getLink() . ".");

            //send us to step 2.
            $this->forwardToUrl("/job/create/file:{$file->id}");
        } //did anything go wrong?
        catch (Exception $e) {
            $this->setTitle("Upload File - Error");
            $this->set('megaerror', $e->getMessage());
        }
    }

    private function _createFile()
    {
        //format the name and stuff
        $filename = basename($this->args('key'));
        $filename = str_replace(" ", "_", $filename);
        $filename = preg_replace("/[^-_.[0-9a-zA-Z]/", "", $filename);
        $path = "assets/" . StorageInterface::getNiceDir($filename);

        //create new file and upload it
        $file = Storage::newFile();
		$file->set('path', $this->args('key'));
		$file->moveTo($path);
		$file->getHash();
		$file->getSize();
		$file->getType();
        $file->set('user_id', User::$me->id);
        $file->set('add_date', date('Y-m-d H:i:s'));
        $file->save();

        return $file;
    }

    private function _handleZipFile($zip_path, $zip_file)
    {
        $za = new ZipArchive();
        $za->open($zip_path);

        for ($i = 0; $i < $za->numFiles; $i++) {
            //look up file info.
            $filename = $za->getNameIndex($i);

            //okay, is it a supported file?
            if (preg_match('/(gcode|stl|obj|amf)$/i', $filename)) {
                $temp_file = tempnam("/tmp", "BQ");
                copy("zip://" . $zip_path . "#" . $filename, $temp_file);

                //format for upload
                $filename = str_replace(" ", "_", $filename);
                $filename = preg_replace("/[^-_.[0-9a-zA-Z]/", "", $filename);
                $path = "assets/" . StorageInterface::getNiceDir($filename);

                //create our file
                $file = Storage::newFile();
                $file->set('parent_id', $zip_file->id);
                $file->set('user_id', User::$me->id);
                $file->upload($temp_file, $path);
            }
        }

        //exit;
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