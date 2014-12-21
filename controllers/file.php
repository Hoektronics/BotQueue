<?

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

class FileController extends Controller
{
	public function local()
	{
		// Local file upload
		try {
			if (empty($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name']))
				throw new Exception("Something went wrong with uploading this file. The file may be too large to upload.");
			$tmp_file = $_FILES['file'];
			$dst = STORAGE_PATH . "/uploads/" . $tmp_file["name"];
			$this->ensureGoodFile($tmp_file);

			if (file_exists($dst)) {
				unlink($dst);
			}
			// Create the directory structure if it doesn't exist
			if (!is_dir(dirname($dst))) {
				mkdir(dirname($dst), 0777, true);
			}
			rename($tmp_file["tmp_name"], $dst);
			$file = FileUploadHandler::fromName("uploads/" . $tmp_file["name"]);

			Activity::log("uploaded a new file called " . $file->getLink() . ".");

			//send us to step 2.
			$this->forwardToUrl("/job/create/file:{$file->id}");
		} catch (Exception $e) {
			$this->set('megaerror', $e->getMessage());
		}
	}

	public function download()
	{

		try {
			$path = $this->args('id');
			$file = STORAGE_PATH . "/" . $path;

			// If the file isn't there, kill the request with fire.
			if (!is_file($file)) {
				http_response_code(404);
				die();
			}

			//get our headers ready.
			header('Content-Description: File Transfer');
			// todo Fix this once we can actually know the content
//			if ($file->get('type'))
//				header('Content-Type: ' . $file->get('type'));
//			else
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($path));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));

			//kay, send it
			readfile($file);
			exit;
		} catch (Exception $e) {
			die($e);
			//$this->set('megaerror', $e->getMessage());
		}
	}

	/**
	 * @param $file
	 * @throws Exception
	 */
	private function ensureGoodFile($file)
	{
		if ($file['size'] == 0 && $file['error'] == 0)
			$file['error'] = UPLOAD_ERR_EMPTY;

		if ($file['error'] == 0)
			return;

		$upload_errors = array(
			UPLOAD_ERR_OK => "No errors.",
			UPLOAD_ERR_INI_SIZE => "Larger than upload_max_filesize.",
			UPLOAD_ERR_FORM_SIZE => "Larger than form MAX_FILE_SIZE.",
			UPLOAD_ERR_PARTIAL => "Partial upload.",
			UPLOAD_ERR_NO_FILE => "No file.",
			UPLOAD_ERR_NO_TMP_DIR => "No temporary directory.",
			UPLOAD_ERR_CANT_WRITE => "Can't write to disk.",
			UPLOAD_ERR_EXTENSION => "File upload stopped by extension.",
			UPLOAD_ERR_EMPTY => "File is empty." // add this to avoid an offset
		);

		throw new Exception("File upload failed: " . $upload_errors[$file['error']]);
	}
}
