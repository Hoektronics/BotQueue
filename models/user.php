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

class User extends Model
{
    /* @var $me User */
    public static $me = null;

    public function __construct($id = null)
    {
        parent::__construct($id, "users");
    }

    public function getThingiverseToken() {
        return $this->get('thingiverse_token');
    }

    public static function authenticate()
    {
        //are we already authenticated?
        if (array_key_exists('userid', $_SESSION)) {
            //attempt to load our user.
            self::$me = new User($_SESSION['userid']);

            //if it fails, nuke it.
            if (!self::$me->isHydrated()) {
                self::$me = null;
                unset($_SESSION['userid']);
            } else {
                // uncomment this section to temporarily lock down the site and prevent logins
                // if (!self::$me->isAdmin()) {
                //    self::$me = null;
                //    unset($_SESSION['userid']);
                //  }
            }
        } //okay, how about our cookie?
        else if (array_key_exists('token', $_COOKIE))
            self::loginWithToken($_COOKIE['token']);

        //if that user wasn't found, bail!
        if (self::$me instanceOf User && !self::$me->isHydrated())
            self::$me = null;

        if (User::isLoggedIn())
            self::$me->setActive();
    }

    public static function loginWithToken($token, $createSession = true)
    {
        $data = json_decode(base64_decode($token), true);

        if (is_array($data) && $data['id'] && $data['token']) {
            $user = new User($data['id']);

            if ($user->isHydrated()) {
                if ($user->checkToken($data['token'])) {
                    self::createLogin($user, $createSession);
                }
            }
        }
    }

    public static function login($username, $password)
    {
        //find the user/pass combo.
        $user = User::byUsernameAndPassword($username, $password);
        if ($user->isHydrated())
            self::createLogin($user);
    }

    /**
     * @param User $user
     * @param bool $createSession
     */
    public static function createLogin($user, $createSession = true)
    {
        self::$me = $user;

        if ($createSession == true)
            $_SESSION['userid'] = $user->id;
    }

    public function canEdit()
    {
        return ($this->isMe() || User::isAdmin());
    }

    public static function isAdmin($type = '')
    {
        if ($type == '') {
            $type = 'is_admin';
        } else {
            $type = "is_{$type}_admin";
        }

        if (self::isLoggedIn() && (self::$me->get("is_admin") || self::$me->get($type))) {
            return true;
        } else {
            return false;
        }
    }

    public static function isLoggedIn()
    {
        return (self::$me instanceOf User && self::$me->isHydrated());
    }

    public static function forceLogOut()
    {
        //remove our token, if we got one.
        if ($_COOKIE['token']) {
            $data = json_decode(base64_decode($_COOKIE['token']), true);
            $token = Token::byToken($data['token']);
            $token->delete();
        }

        //unset specific variables.
        setcookie('token', '', time() - 420000, '/', SITE_HOSTNAME, FORCE_SSL, true);
        unset($_SESSION['userid']);
        unset($_SESSION['CSRFToken']);

        //nuke the session.
        if (isset($_COOKIE[session_name()]))
            setcookie(session_name(), '', time() - 420000, '/', SITE_HOSTNAME, FORCE_SSL, true);

        session_unset();
        session_destroy();
    }

    public function checkToken($t)
    {
        $token = Token::byToken($t);
        if ($token->isHydrated() && $token->get('user_id') == $this->id)
            return true;
        else
            return false;
    }

    public function createToken()
    {
        $hash = sha1(microtime() . mt_rand() . "salty bastard");

        $token = new Token();
        $token->set('user_id', $this->id);
        $token->set('hash', $hash);
        $token->set('expire_date', date("Y-m-d H:i:s", strtotime("+1 year")));
        $token->save();

        return $token;
    }

    public static function hashPass($pass)
    {
        return sha1($pass);
    }

    public static function byUsername($username)
    {
        //look up the token
        $sql = "SELECT id
				FROM users
				WHERE username = ?";
        $id = db()->getValue($sql, array($username));

        //send it!
        return new User($id);
    }

    public static function byUsernameAndPassword($username, $password)
    {
        $pass_hash = sha1($password);

        //look up the combo.
        $sql = "SELECT id
				FROM users
				WHERE username = ?
				AND pass_hash = ?";
        $id = db()->getValue($sql, array($username, $pass_hash));

        //send it!
        return new User($id);
    }

    public static function byEmail($email)
    {
        //look up the token
        $sql = "SELECT id
				FROM users
				WHERE email = ?";
        $id = db()->getValue($sql, array($email));

        //send it!
        return new User($id);
    }

    public function getUrl()
    {
        return "/" . $this->get('username');
    }

    public function getName()
    {
        return $this->get('username');
    }

    public function setActive()
    {
        if (User::isLoggedIn()) {
            $last_seen = strtotime($this->get('last_active'));

            if (time() - $last_seen > 300) {
                $this->set('last_active', date("Y-m-d H:i:s"));
                $this->save();
            }
        }
    }

    public function isMe()
    {
        if (User::isLoggedIn() && User::$me->id == $this->id)
            return true;

        return false;
    }

    public function delete()
    {
        //okay, nuke us now.
        parent::delete();
    }

    public function getActivityStream()
    {
        return Activity::getStream($this);
    }

    public function getQueues()
    {
        $sql = "SELECT id
				FROM queues
				WHERE user_id = ?
				ORDER BY name";

		$queues = new Collection($sql, array($this->id));
		$queues->bindType('id', 'Queue');

		return $queues;
    }

    public function getDefaultQueue()
    {
        $sql = "SELECT id FROM queues
				WHERE name = 'Default'
				AND user_id = ?";
        $q = new Queue(db()->getValue($sql, array($this->id)));

        if (!$q->isHydrated()) {
            $sql = "SELECT id FROM queues ORDER BY id LIMIT 1";
            $q = new Queue(db()->getValue($sql));
        }

        return $q;
    }

    public function getBots()
    {
        $sql = "SELECT id, job_id
				FROM bots
				WHERE user_id = ?
				ORDER BY name";

		$bots = new Collection($sql, array($this->id));
		$bots->bindType('id', 'Bot');
		$bots->bindType('job_id', 'Job');

		return $bots;
    }

    public function getActiveBots()
    {
        $sql = "SELECT id, job_id
				FROM bots
				WHERE user_id = ?
				AND status != 'retired'
				ORDER BY name";

		$bots = new Collection($sql, array($this->id));
		$bots->bindType('id', 'Bot');
		$bots->bindType('job_id', 'Job');

		return $bots;
    }

    public function getJobs($status = null, $sortField = 'user_sort', $sortOrder = 'ASC')
    {
		$sql = "SELECT id FROM jobs WHERE user_id = ? ";

		$data = array($this->id);

		if($status !== null) {
			$sql .= "AND status = ? ";
			$data[] = $status;
		}

		$sql .= "ORDER BY {$sortField} ". $sortOrder;

		$jobs = new Collection($sql, $data);
		$jobs->bindType('id', 'Job');

		return $jobs;
    }

    public function getAuthorizedApps()
    {
        $sql = "SELECT id, consumer_id
				FROM oauth_token
				WHERE user_id = ?
				AND type = ?
				ORDER BY id";

		$apps = new Collection($sql, array($this->id, OAuthToken::$ACCESS));
		$apps->bindType('id', 'OAuthToken');
		$apps->bindType('consumer_id', 'OAuthConsumer');

		return $apps;
    }

    public function getMyApps()
    {
        $sql = "SELECT id
				FROM oauth_consumer
				WHERE user_id = ?
				ORDER BY name";

		$apps = new Collection($sql, array($this->id));
		$apps->bindType('id', 'OAuthConsumer');

		return $apps;
    }

    public function getErrorLog()
    {
        $sql = "SELECT id
		    	FROM error_log
		    	WHERE user_id = ?
		    	ORDER BY error_date DESC";

		$logs = new Collection($sql, array($this->id));
		$logs->bindType('id', 'ErrorLog');
    }

    public function getMySliceConfigs()
    {
        $sql = "SELECT id, engine_id
				FROM slice_configs
				WHERE user_id = ?
				ORDER BY engine_id DESC";

		$configs = new Collection($sql, array($this->id));
		$configs->bindType('id', 'SliceConfig');
		$configs->bindType('engine_id', 'SliceEngine');

		return $configs;
    }
}