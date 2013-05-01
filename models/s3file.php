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

	class S3File extends Model
	{
		public function __construct($id = null, $table = null)
		{
			if ($table === null)
				parent::__construct($id, "s3_files");
			else
				parent::__construct($id, $table);
		}
		
		public function delete()
		{
			if ($this->isHydrated())
			{
				$s3 = new S3(AMAZON_AWS_KEY, AMAZON_AWS_SECRET);
				$s3->deleteObject($this->get('bucket'), $this->get('path'));
			}
			
			parent::delete();
		}
		
		public function uploadFile($file, $path, $acl = S3::ACL_PUBLIC_READ, $save = true)
		{
			//is it a real file?
			if (file_exists($file))
			{
				//do the actual upload.
				$s3 = new S3(AMAZON_AWS_KEY, AMAZON_AWS_SECRET);
				$result = $s3->putObjectFile($file, AMAZON_S3_BUCKET_NAME, $path, S3::ACL_PUBLIC_READ);

				//echo "Uploading {$file} to " . AMAZON_S3_BUCKET_NAME . ":{$path}\n";

				//get our info for saving
				$info = $s3->getObjectInfo(AMAZON_S3_BUCKET_NAME, $path, true);

				//save our info.
				$this->set('hash', $info['hash']);
				$this->set('size', $info['size']);
				$this->set('type', $info['type']);
				$this->set('bucket', AMAZON_S3_BUCKET_NAME);
				$this->set('path', $path);
				$this->set('add_date', date("Y-m-d H:i:s"));
	
				//for non db accessible scripts.			
				if ($save)
					$this->save();

				//yay!
				if ($result !== false)
					return true;
			}
			else
				throw new Exception("S3 Upload: no file found at: '$file'\n");
			
			//fail!
			return false;
		}
		
		public function uploadUrl($url, $s3_path, $acl = S3::ACL_PUBLIC_READ, $save = true)
		{
			//where to download to?
			$path = tempnam('/tmp', 'fuuu');

			//set it up for download
			$ch = curl_init();
			$fp = fopen($path, "w");
		  curl_setopt($ch, CURLOPT_URL, $url);
		  curl_setopt($ch, CURLOPT_FILE, $fp);
		  curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      
			//download it!
		  if (!curl_exec($ch)) {
		    trigger_error("Error downloading $url " . curl_error($ch));
			}

      curl_close($ch);
			fclose($fp);

			//actually upload it!
			$this->uploadFile($path, $s3_path, $acl, $save);

			//where do we download it?
			return $path;
		}
		
		public function exists($bucket = null, $file = null)
		{
		  if ($bucket === null)
		    $bucket = $this->get('bucket');
		  if ($file === null)
		    $file = $this->get('path');
		    
	    $s3 = new S3(AMAZON_AWS_KEY, AMAZON_AWS_SECRET);
			$result = $s3->getObjectInfo($bucket, $file, false);
			
			return $result;
		}
		
		public static function createHashDirectory()
		{
			$hash = sha1(mt_rand() . mt_rand() . mt_rand() . mt_rand());
			
			$directory  = substr($hash, 0, 2);
			$directory .= "/";
			$directory .= substr($hash, 2, 2);
			$directory .= "/";
			$directory .= substr($hash, 4, 2);
			$directory .= "/";
			$directory .= substr($hash, 6, 2);
			$directory .= "/";
			$directory .= substr($hash, 8, 2);

			return $directory;
		}
		
		public static function getNiceDir($path)
		{
			$dir = self::createHashDirectory();
			$file = self::removeHash($path);
			
			return "$dir/$file";
		}
		
		public static function removeHash($file)
		{
			$file = basename($file);
			$file = preg_replace("/[0-9a-f]{32}-/i", "", $file);
			
			return $file; 
		}
		
		public function getRealUrl()
		{
			if (FORCE_SSL)
				$protocol = 'https://';
			else
				$protocol = 'http://';
				
			return $protocol . $this->get('bucket') . ".s3.amazonaws.com/" . $this->get('path');
		}

		public function getRealDownloadUrl() {
		  $s3 = new S3(AMAZON_AWS_KEY, AMAZON_AWS_SECRET);
			return $s3->getAuthenticatedDownloadURL($this->get('bucket'), $this->get('path'), 3600);
		}
		
		public function downloadToPath($path)
		{
			//make directory.
			$dir = dirname($path);
			if (!file_exists($dir))
				mkdir($dir, 0777, true);
			if (!is_writable($dir))
				return false;
			
			//load up S3 for download
			$s3 = new S3(AMAZON_AWS_KEY, AMAZON_AWS_SECRET);
			$result = $s3->getObject($this->get('bucket'), $this->get('path'), $path);
			
			//did it work?
			if ($result !== false)
				return true;

			//fail :-/
			return false;
		}
		
		public function copy()
		{
			$new = parent::copy();
			

			ob_start();
			
			//copy our new one to its own path.
			$newPath = $new->getNiceDir($new->get('path'));
			$new->copyToPath($newPath);
			$new->set('path', $newPath);
			$new->set('bucket', AMAZON_S3_BUCKET_NAME);
			$new->save();
			
			ob_end_clean();
			
			return $new;
		}
		
		public function copyToPath($path)
		{
			//load up S3 for download
			$s3 = new S3(AMAZON_AWS_KEY, AMAZON_AWS_SECRET);
			$result = $s3->copyObject(
				$this->get('bucket'), $this->get('path'),
				AMAZON_S3_BUCKET_NAME, $path,
				S3::ACL_PUBLIC_READ
			);
			
			return $result;
		}
		
		public function copyToBucket($bucket)
		{
			//load up S3 for download
			$s3 = new S3(AMAZON_AWS_KEY, AMAZON_AWS_SECRET);
			$result = $s3->copyObject($this->get('bucket'), $this->get('path'), $bucket, $this->get('path'), S3::ACL_PUBLIC_READ);
			
			$this->set('bucket', $bucket);
			$this->save();
			
			return $result;
		}
		
		public function getBasename()
		{
			return basename($this->get('path'));
		}
		
		public function getExtension()
		{
			$data = pathinfo($this->get('path'));
			
			return $data['extension'];
		}
		
		public function getName()
		{
			return $this->getBasename();
		}
		
		public function getUrl()
		{
			return "/file:{$this->id}";
		}
		
		public function getUser()
		{
			return new User($this->get('user_id'));
		}
		
		public function getAPIData()
		{
			$d = array();
			$d['id'] = $this->id;
			$d['name'] = $this->getName();
			$d['url'] = $this->getRealUrl();
			$d['type'] = $this->get('type');
			$d['md5'] = $this->get('hash');
			$d['size'] = $this->get('size');

			return $d;
		}
		
		public function isGCode()
		{
		  return preg_match("/(g|gcode)$/i", $this->get('path'));
		}
		
		public function is3DModel()
		{
		  return preg_match("/(stl|obj|amf)$/i", $this->get('path'));
		}
		
		public function getJobs()
		{
		  $sql = "
		    SELECT id
		    FROM jobs
		    WHERE source_file_id = '". db()->escape($this->id) ."'
		      OR file_id = '". db()->escape($this->id) ."'
		    ORDER BY id DESC
		  ";
		  
		  return new Collection($sql, array('Job' => 'id'));
		}
		
		public function getChildren()
		{
		  $sql = "
		    SELECT id
		    FROM s3_files
		    WHERE parent_id = '" . db()->escape($this->id) . "'
		    ORDER BY id DESC
		  ";
		  
		  return new Collection($sql, array('S3File' => 'id'));
		}
	}
?>
