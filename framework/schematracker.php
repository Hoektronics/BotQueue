<?
	class SchemaTracker
	{
		public function __construct()
		{
		}

		// Gets the all the SQL for all the changes needed to bring this
		// instance up to date.  Called by the bin/migrate utility.
		public function getChanges()
		{
			// get list of changes
			$changes = $this->getChangesList();
			$sql = "";

			foreach ($changes as $change) {
				$sql .= $this->getChange($change);
			}

			return $sql;
		}

		// Get a change from the end-user using their editor and registers it in the
		// sql directory.  Called by the bin/schema-change utility.
		public function addChange() 
		{
			// Get the path of temp file which contains the change or stop
			$in_path = $this->readChangeFromEditor();

			if ($in_path) {
				// Generate filename and path
				$out_filename = rtrim(`whoami`) . "-" . time() . ".sql";
				$out_path = __DIR__ . "/../sql/$out_filename";

				// Copy that to our dir
				copy($in_path,$out_path);

				// Clean up
				unlink($in_path);

				print("Schema change tracked as sql/$out_filename.  Please use \"bin/migrate | mysql\" to commit to local database.\n");
				return TRUE;
			}

			print("No changes found. Aborting.\n");
			return FALSE;
		}

		// Compares the list of changes in the sql directory to the list of
		// completed changes in the database and returns a list of changes
		// that still need to be run.
		public function getChangesList() 
		{
			// get completed change lookup
			$lookup = $this->getCompletedChangesLookup();

			// list all files in sql directory
			$files = $this->getFileList();

			// ensure the files are in order
			$sorted = $this->sortFileList($files);

			// grep for files that are not in the lookup
			$filtered = array_filter($sorted, function($a) use ($lookup) { return ! array_key_exists($a, $lookup); });

			// return that list
			return $filtered;
		}

		public function sortFileList($files) 
		{
			usort($files, function($a,$b) { 
				$a = preg_replace('/.*-(\d{10})\..*/','$1',$a);
				$b = preg_replace('/.*-(\d{10})\..*/','$1',$b);

				if ($a === $b) {
					return 0;
				}
				else if ($a > $b) {
					return 1;
				}
				else {
					return -1;
				}
			});

			return $files;
		}

		// Gets a lookup table of all changes that have been completed
		private function getCompletedChangesLookup()
		{
			// select name from schema_changes
			$sql = "
				SELECT name
				FROM schema_changes
			";
		
			// get all rows
			$rows = db()->getArray($sql);
			$lookup = array();

			// map rows into a hash TODO is there a way to do this with array_map?
			if ($rows) {
				foreach ($rows as $row)
				{
					$lookup[$row['name']]=$row;
				}
			}
				
			// return hash
			return $lookup;
		}

		// Get a list of all the files in the sql directory that look like sql.
		private function getFileList() 
		{
			$results = array();
			$path = __DIR__ . "/../sql";

			if ($handle = opendir($path)) 
			{
				while (false !== ($entry = readdir($handle))) 
				{
					if (preg_match('/\.sql$/',$entry)) 
					{
						$results[] = $entry;
					}
				}

				closedir($handle);
			}

			return $results;
		}

		// Read in a sql change and include the schema_change component.
		private function getChange($name)
		{
			$path = __DIR__ . "/../sql/$name";

			$sql = file_get_contents($path);

			$sql .= "\nINSERT INTO schema_changes SET name='$name';\n";

			return $sql;
		}

		// Execute your favorite editor on a temp file and return the path to that file
		private function readChangeFromEditor() 
		{
			$editor = getenv('EDITOR');
			$path = $this->getTempFile();
			$resource = proc_open($editor." ".$path, $descriptor=array(), $pipes=array());

			if ($resource === FALSE)
				return FALSE;

			proc_close($resource);

			if (file_exists($path) && filesize($path)>0) 
				return $path;
		
			if (file_exists($path))
				unlink($path);

			return FALSE;
		}

		// I want a tempfile that ends in .sql so I get syntax highlighting.  Sorry tempnam().
		private function getTempFile() {
			$tmp_path = "/tmp/schema-change-".microtime(TRUE).".sql";

			while(file_exists($tmp_path)) 
				$tmp_path = "/tmp/schema-change-".microtime(TRUE).".sql";

			return $tmp_path;
		}
	}
?>
