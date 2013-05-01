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

	class UploadController extends Controller
	{
		public function home()
		{
			$this->assertLoggedIn();
			
			$this->setTitle("Step 1 of 2: Choose File to Print");
		}
		
		public function uploader()
		{
			$payload = base64_encode(serialize($this->args('payload')));
			
			$this->setArg('label');

			//where you want me go?
			$redirect = "http://" . SITE_HOSTNAME . "/upload/success/$payload";
			$acl = "public-read";
			$expiration = gmdate("Y-m-d\TH:i:s\Z", strtotime("+1 day"));
			
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
				
			//okay, set our view vars.
			$this->set('redirect', $redirect);
			$this->set('acl', $acl);
			$this->set('policy', $policy_encoded);
			$this->set('signature', $signature);
		}
		
		public function url()
		{
		  $this->assertLoggedIn();
		  
		  $this->setTitle("Create Job from URL");
		  
		  try
		  {
		    //did we get a url?
  		  $url = $this->args('url');
  		  if (!$url)
  		    throw new Exception("You must pass in the URL parameter!");

        $data = Utility::downloadUrl($url);

        //does it match?
        if (!preg_match("/\.(stl|obj|amf|gcode|zip)$/i", $data['realname']))
          throw new Exception("The file <a href=\"$url\">{$data[realname]}</a> is not valid for printing.");
          
        $s3 = new S3File();
        $s3->set('user_id', User::$me->id);
        $s3->set('source_url', $url);
        $s3->uploadFile($data['localpath'], S3File::getNiceDir($data['realname']));
        
        //is it a zip file?  do some magic on it.
        if (!preg_match("/\.zip$/i", $data['realname']))
          $this->_handleZipFile($data['localpath'], $s3);
        
  			Activity::log("uploaded a new file called " . $s3->getLink() . ".");

  			//send us to step 2.
 			  $this->forwardToUrl("/job/create/file:{$s3->id}");
		  }
		  catch (Exception $e)
		  {
		    $this->set('megaerror', $e->getMessage());
		  }
		}
		
		public function success()
		{
			$this->assertLoggedIn();

			//get our payload.
			$payload = unserialize(base64_decode($this->args('payload')));
			
			//handle our upload
			try
			{
				//some basic error checking.
				if (!preg_match('/(gcode|stl|obj|amf|zip)$/i', $this->args('key')))
					throw new Exception("Only .gcode, .stl, .obj, and .amf files are allowed at this time.");

				//make our file.
				$info = $this->_lookupFileInfo();
				$file = $this->_createS3File();
				
				//is it a zip file?  do some magic on it.
        if (preg_match("/\.zip$/i", $this->args('key')))
        {
          $path = tempnam("/tmp", "BQ");
          $file->downloadToPath($path);
          $this->_handleZipFile($path, $file);
        }

				Activity::log("uploaded a new file called " . $file->getLink() . ".");
				
				//send us to step 2.
				$this->forwardToUrl("/job/create/file:{$file->id}");
			}
			//did anything go wrong?
			catch (Exception $e)
			{
				$this->setTitle("Upload File - Error");
				$this->set('megaerror', $e->getMessage());
			}				
		}

		private function _lookupFileInfo()
		{
			//look up our real info.
			$s3 = new S3(AMAZON_AWS_KEY, AMAZON_AWS_SECRET);
			$info = $s3->getObjectInfo($this->args('bucket'), $this->args('key'), true);
			
			if ($info['size'] == 0)
			{
				//capture for debug
				ob_start();
				
				var_dump($args);
				var_dump($info);
				
				//try it again.
				sleep(1);
				$info = $s3->getObjectInfo($this->args('bucket'), $this->args('key'), true);
				var_dump($info);
				
				//still bad?
				if ($info['size'] == 0)
				{
					$text = ob_get_contents();
					$html = "<pre>{$text}</pre>";
					
					//email the admin
					$admin = User::byUsername('hoeken');
					Email::queue($admin, "upload fail", $text, $html);

					//show us.
					if (User::isAdmin())
					{
						@ob_end_clean();
						
						echo "'failed' file upload:<br/><br/>$html";
						exit;
					}

					//$this->set('megaerror', "You cannot upload a blank/empty file.");
				}
				
				@ob_end_clean();
			}
			
			//send it back.
			return $info;
		}
		
		private function _createS3File()
		{
			//format the name and stuff
			$filename = basename($this->args('key'));
			$filename = str_replace(" ", "_", $filename);
			$filename = preg_replace("/[^-_.[0-9a-zA-Z]/", "", $filename);
			$path = "assets/" . S3File::getNiceDir($filename);

			//check our info out.
			$info = $this->_lookupFileInfo();
	
			//create new s3 file
			$file = new S3File();
			$file->set('user_id', User::$me->id);
			$file->set('type', $info['type']);
			$file->set('size', $info['size']);
			$file->set('hash', $info['hash']);
			$file->set('add_date', date('Y-m-d H:i:s'));
			$file->set('bucket', AMAZON_S3_BUCKET_NAME);
			$file->set('path', $path);
			$file->save();

			//copy to new location in s3.
			$s3 = new S3(AMAZON_AWS_KEY, AMAZON_AWS_SECRET);
			$s3->copyObject($this->args('bucket'), $this->args('key'), AMAZON_S3_BUCKET_NAME, $path, S3::ACL_PUBLIC_READ);

			//remove the uploaded file.
			$s3->deleteObject($this->args('bucket'), $this->args('key'));
			
			return $file;
		}
		
		private function _handleZipFile($path, $file)
		{
		  $za = new ZipArchive(); 
      $za->open($path); 

      for($i = 0; $i<$za->numFiles; $i++)
      {
        //look up file info.
        $filename = $za->getNameIndex($i);

        //okay, is it a supported file?
				if (preg_match('/(gcode|stl|obj|amf)$/i', $filename))
				{
          $temp = tempnam("/tmp", "BQ");
          copy("zip://".$path."#".$filename, $temp);

          //format for s3
          $s3_filename = str_replace(" ", "_", $filename);
    			$s3_filename = preg_replace("/[^-_.[0-9a-zA-Z]/", "", $filename);
    			$s3_path = "assets/" . S3File::getNiceDir($filename);

          //create our s3 file
          $s3 = new S3File();
          $s3->set('parent_id', $file->id);
          $s3->set('user_id', User::$me->id);
          $s3->uploadFile($temp, $s3_path);
          
          //echo "$filename - $s3_path<br/>";
				}
      }
      
      //exit;
		}
	}
	
	// Function to help sign the policy
	function hex2b64($str)
	{
		$raw = '';
		for ($i=0; $i < strlen($str); $i+=2)
		{
			$raw .= chr(hexdec(substr($str, $i, 2)));
		}
		return base64_encode($raw);
	}
?>
