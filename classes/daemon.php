<?
	require_once("PEAR/Log.php");
	
	class Daemon
	{
		protected $pid = 0;
		protected $pidFile = '/tmp/daemon.pid';
		protected $quit = 0;
		public static $logger;
		
		public function __construct($pidFile = null, $logfile = null)
		{
			if ($pidFile !== null)
				$this->pidFile = $pidFile;
				
			if ($logfile === null)
				$logfile = '/var/log/php-daemon';
				
			//prepare our logger
			$conf = array(
				'lineFormat' => '[%{timestamp}] [%{priority}] %{message}',
				'timeFormat' => '%Y-%m-%d %T'
			);

			self::$logger = &Log::singleton('file', $logfile, 'daemon', $conf);
		}
		
/*
		public static function isPIDRunning($pid)
		{
			$pid = (int)$pid;
			
			$cmd = "ps --pid $pid -o pid --no-headers";
			$got_pid = system($cmd);
			
			var_dump($got_pid);
		}
*/
		
		public function run()
		{
			//prepare to run.
			$this->init();
			$this->attachSignalHandler();
			$this->daemonize();

			while(!$this->quit) {
				$this->loop();
			}
			
			if(posix_getpid() == $this->pid)
			    unlink($this->pidFile);
		}
		
		public function isRunning($log = true)
		{
			//check for PID
		    if(file_exists($this->pidFile))
			{
				//read our PID
		        $fp = fopen($this->pidFile, "r");
		        $pid = fgets($fp, 1024);
		        fclose($fp);

				//alreading running?
				if(posix_kill($pid, 0))
				{
					if ($log)
			            self::$logger->log("Server already running with PID: $pid", PEAR_LOG_ERR);
					return true;
		        }

				//nope, try and remove it.
		        self::$logger->log("Removing PID file for defunct server process $pid", PEAR_LOG_NOTICE);
		        if(!unlink($this->pidFile))
				{
		            self::$logger->log("Cannot unlink PID file {$this->pidFile}", PEAR_LOG_ERR);
					return true;
		        }
		    }
		    
		    return false;
		}
		
		protected function loop()
		{
		}
		
		protected function init()
		{
			set_time_limit(0);
			ob_implicit_flush();
		}
		
		protected function attachSignalHandler()
		{
			pcntl_signal(SIGCHLD, "sig_handler");
			pcntl_signal(SIGTERM, "sig_handler");
			pcntl_signal(SIGINT, "sig_handler");
			
			set_error_handler('my_error_handler');
		}
		
		protected function daemonize()
		{
			//if we're running, exit.
			if ($this->isRunning())
				exit;

			//get our PID file.
			$fh = $this->openPidFile();
			$this->pid = $this->becomeDaemon();
			fputs($fh, $this->pid);
			fclose($fh);

			self::$logger->log("Daemon started with PID {$this->pid}.", PEAR_LOG_INFO);
		}
		
		protected function becomeDaemon()
		{
		    $child = pcntl_fork();
		    if($child)
		        exit; // kill parent

		    posix_setsid(); // become session leader
		    chdir("/");
		    umask(0); // clear umask
		    return posix_getpid();
		}

		protected function openPidFile()
		{
			//try and open it.
		    if($fp = fopen($this->pidFile,"w"))
		        return $fp;
		    else
			{
		        self::$logger->log("Unable to open PID file {$this->pidFile} for writing.", PEAR_LOG_ERR);
		        exit;
		    }
		}

		public function changeIdentity($uid, $gid)
		{
		    if(!posix_setgid($gid))
			{
		        self::$logger->log("Unable to setgid to $gid!", PEAR_LOG_ERR);
		        unlink($this->pidFile);
		        exit;
		    }

		    if(!posix_setuid($uid))
			{
				self::$logger->log("Unable to setuid to $uid!", PEAR_LOG_ERR);
		        unlink($this->pidFile);
		        exit;
		    }
		}

		public function signalHandler($signo)
		{
			switch($signo)
			{
				// handle shutdown tasks
				case SIGTERM:
					self::$logger->log("Terminated, exiting.", PEAR_LOG_INFO);
					unlink($this->pidFile);
					exit;
					break;

				// handle restart tasks
				case SIGHUP:
					break;

				case SIGCHLD:
					while(pcntl_waitpid(-1, $status, WNOHANG) > 0) {}
					break;

				case SIGINT:
					self::$logger->log("Interrupted, exiting.", PEAR_LOG_INFO);
					unlink($this->pidFile);
					exit;
					break;

				default:
					// not implemented yet...
					self::$logger->log("Unknown signal $signo.", PEAR_LOG_INFO);
					break;
			}
		}
	}
	
	// tick use required as of PHP 4.3.0
	declare(ticks = 1);
	
	function sig_handler($signo)
	{
		global $daemon;
		
		$daemon->signalHandler($signo);
	}
	
	function my_error_handler($errno, $errstr, $errfile, $errline)
	{
		//dont show errors if we're turned off.
		if (error_reporting() == 0)
			return;
			
		$line = "[$errno] $errstr on line $errline in $errfile";
		
		switch ($errno)
		{
			case E_WARNING:
			case E_USER_ERROR:
			case E_USER_WARNING:
				//actually log it.
				Daemon::$logger->log($line, PEAR_LOG_WARNING);
				
				//debug our backtrace.
				$backtrace = debug_backtrace();
				array_shift($backtrace);
				array_shift($backtrace);
				if (!empty($backtrace))
				{
					foreach ($backtrace AS $key => $row)
					{
						$line = "$key: function " . $row['function'] . " on line " . $row['line'] . " in " . $row['file'];
						Daemon::$logger->log($line, PEAR_LOG_DEBUG);
					}
				}
				break;

			case E_NOTICE:
			case E_USER_NOTICE:
				//Daemon::$logger->log($line, PEAR_LOG_NOTICE);
				break;
		}

		/* Don't execute PHP internal error handler */
		return true;
	}
?>
