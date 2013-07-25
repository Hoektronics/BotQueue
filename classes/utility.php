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

class Utility 
{  
  public static $currencyNames = array(
    'AUD' => 'Australian dollar',
    'CAD' => 'Canadian dollar',
    'CNY' => 'Chinese yuan',
    'EUR' => 'Euro',
    'GBP' => 'Pound sterling',
    'HKD' => 'Hong Kong dollar',
    'JPY' => 'Japanese yen',
    'USD' => 'United States dollar'
  );
  
  public static $currencySymbols = array(
    'AUD' => 'A$',
    'CAD' => 'C$',
    'CNY' => '&#x5143;',
    'EUR' => '&#x20ac;',
    'GBP' => '&#xa3;',
    'HKD' => 'HK$',
    'JPY' => '&#xa5;',
    'USD' => '$'
  );

  public function convertCurrency($amount, $from, $to = "USD") {
    if ($from == $to) {
      $converted_amount = $amount;
    } else {
      $url = "http://www.google.com/ig/calculator?q={$amount}{$from}=?{$to}";
    
      $json = file_get_contents($url);

      $json = preg_replace('/(\w+):/i', '"\1":', $json);
      $json = preg_replace('/\xA0/', '', $json);

      $result = json_decode($json);

      $converted_amount = explode(" ", $result->{"rhs"});
      $converted_amount = $converted_amount[0];
    }
    
    return (float)$converted_amount;
  }
  
  public function formatSeconds($seconds) {
    if ($seconds == 0) {
      $time = "Unknown";
    } else {
      $time = Utility::distance_of_time_in_words(time() - $seconds, time(), true);
    }
    
    return $time;
  }
  
  // Returns the distance of time in words between two dates
  public function distance_of_time_in_words($from_time, $to_time = null, $include_seconds = false)
  {
    $to_time = $to_time? $to_time: time();

    $distance_in_minutes = floor(abs($to_time - $from_time) / 60);
    $distance_in_seconds = floor(abs($to_time - $from_time));

    $string = '';
    $parameters = array();

    if ($distance_in_minutes <= 1)
    {
      if (!$include_seconds)
      {
        $string = $distance_in_minutes == 0 ? 'less than a minute' : '1 minute';
      }
      else
      {
        if ($distance_in_seconds <= 5)
        {
          $string = 'less than 5 seconds';
        }
        else if ($distance_in_seconds >= 6 && $distance_in_seconds <= 10)
        {
          $string = 'less than 10 seconds';
        }
        else if ($distance_in_seconds >= 11 && $distance_in_seconds <= 20)
        {
          $string = 'less than 20 seconds';
        }
        else if ($distance_in_seconds >= 21 && $distance_in_seconds <= 40)
        {
          $string = 'half a minute';
        }
        else if ($distance_in_seconds >= 41 && $distance_in_seconds <= 59)
        {
          $string = 'less than a minute';
        }
        else
        {
          $string = '1 minute';
        }
      }
    }
    else if ($distance_in_minutes >= 2 && $distance_in_minutes <= 44)
    {
      $string = '%minutes% minutes';
      $parameters['%minutes%'] = $distance_in_minutes;
    }
    else if ($distance_in_minutes >= 45 && $distance_in_minutes <= 89)
    {
      $string = 'about 1 hour';
    }
    else if ($distance_in_minutes >= 90 && $distance_in_minutes <= 1439)
    {
      $string = 'about %hours% hours';
      $parameters['%hours%'] = round($distance_in_minutes / 60);
    }
    else if ($distance_in_minutes >= 1440 && $distance_in_minutes <= 2879)
    {
      $string = '1 day';
    }
    else if ($distance_in_minutes >= 2880 && $distance_in_minutes <= 43199)
    {
      $string = '%days% days';
      $parameters['%days%'] = round($distance_in_minutes / 1440);
    }
    else if ($distance_in_minutes >= 43200 && $distance_in_minutes <= 86399)
    {
      $string = 'about 1 month';
    }
    else if ($distance_in_minutes >= 86400 && $distance_in_minutes <= 525959)
    {
      $string = '%months% months';
      $parameters['%months%'] = round($distance_in_minutes / 43200);
    }
    else if ($distance_in_minutes >= 525960 && $distance_in_minutes <= 1051919)
    {
      $string = 'about 1 year';
    }
    else
    {
      $string = 'over %years% years';
      $parameters['%years%'] = floor($distance_in_minutes / 525960);
    }

    return strtr($string, $parameters);
  }
  
	public function log($msg) {
	  trigger_error("LOG: " . $msg, E_USER_NOTICE);
	}

	public static function inputValue($value)
	{
		$value = self::sanitize($value);
		
		return $value;
	}
	
	public function cleanAndPretty($text)
	{
	  $text = self::sanitize($text);
	  $text = nl2br($text);
	  
	  return $text;
	}
	
	public function formatNumber($number)
	{
		if (round($number) == $number)
				return number_format($number, 0);
		else
			return $number;
	}
	
	public static function createShortcodeFromInt($value)
	{
		if ($value == (int)$value)
		{
			$value = (int)$value;
			$code = '+' . base_convert($value, 10, 36) . '+';
			$code = strtoupper($code);
			
			return $code;
		}
		else
			return null;
	}
	
	public static function createIntFromShortcode($code)
	{
		if (preg_match('/+([0-9A-Z]{1,10})\+/', $code, $matches))
		{
			$value = base_convert($code, 36, 10);
			return $value;
		}
		else
			return null;
	}
	
	public static function generateBarcode($text)
	{
		require_once('PEAR/Image/Barcode.php');
		Image_Barcode::draw($text, 'Code39', 'png');
	}

	public static function sanitize($value)
	{
    // $value = mb_convert_encoding($value, "UTF-8");
    $value = htmlentities($value, ENT_QUOTES, 'UTF-8');
		$value = str_replace('&amp;lt;', '&lt;', $value);
		
		return $value;
	}
	
	public static function XMLSanitize($value)
	{
		$value = str_replace('&', '&amp;', $value);
		$value = preg_replace("#[\\x00-\\x1f]#msi", ' ', $value);
		$value = iconv('UTF-8', 'UTF-8//IGNORE', $value);
		
		return $value;
	}
	
	public static function firstDayOfWeek($datetime)
	{
	    // Return the previous Monday @ 00:00:00
	    
	    $week = date('W', $datetime) - 1;
	    $year = date('Y', $datetime);
	    
	    return strtotime("1/1/{$year} 00:00:00") + ($week * 7 * 24 * 60 * 60);
	}

	public static function dayOfWeek($numeric_day) 
	{
		$days = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
		return $days[$numeric_day];
	}

	public function newformatDate($date)
	{
	  if (strtotime($date) < 0)
		  return 'unknown';
		else
		  return date("F j, Y, G:i", strtotime($date));	
	}


	public function formatDate($date)
	{
	  if (strtotime($date) < 0)
		  return 'unknown';
		else
			return date("M j, Y", strtotime($date));	
	}

	public function formatDateTime($date)
	{
		if (strtotime($date) < 0)
		  return 'unknown';
		else
		  return date("M j, Y // G:i", strtotime($date));	
	}

	public static function W3CDate($datetime) 
	{
		if (strtotime($datetime) < 0)
		  return 'unknown';
		else
      return date("Y-m-d\TH:i:sP", $datetime);
	}
	
	public static function relativeDays($datetime)
	{
		//calculations
		$elapsed = time() - strtotime($datetime);
		$days = floor(abs($elapsed / Cache::TIME_ONE_DAY));
		
		if (strtotime($datetime) < 0)
		  return 'unknown';
		
		//special formatting...
		if ($days == 0)
			return "today";
		else if ($days == 1)
		{
			if ($elapsed < 0)
				return "tomorrow";
			else
				return "yesterday";
		}
		else if ($days > 1)
		{
			if ($elapsed < 0)
				return "in $days days";
			else
				return "$days days ago";
		}
		else
			return "???";
	}
	
	public static function getElapsed($datediff)
	{
		$min = 	  round($datediff / (60));
		$hours =  round($datediff / (60 * 60), 1);
		$days =   round($datediff / (60 * 60 * 24), 2);
		$months = round($datediff / (60 * 60 * 24 * 31), 2);
		$years =  round($datediff / (60 * 60 * 24 * 365), 2);
		
		if ($datediff < 100) { // seconds
			if ($datediff == 0) return "none";
			return round($datediff) . " second".self::pluralizer($datediff>1);
		} else if ($min < 100) {
			return "$min min".self::pluralizer($min>1);
		} else if ($hours < 100) {
			return "$hours hour".self::pluralizer($hours>1);
		} else if ($days < 10) {
			return "$days day".self::pluralizer($days>1);
	    } else if ($months < 12) {
			return "$months month".self::pluralizer($months>1);
		} else {
			return "$years year".self::pluralizer($years>1);
		}
		
		return false;
	}	
	
	public static function getHours($datediff)
	{
  	$hours =  round($datediff / (60 * 60));
		return "$hours hour".self::pluralizer($hours>1);
	}
	
	public static function getTimeAgo($datetime)
	{
		if (trim($datetime) == "" || trim($datetime) == "0000-00-00 00:00:00")
			return 'unknown';

		$datediff = strtotime(date("Y-m-d H:i:s")) - strtotime($datetime);
	
		$min = 	  round($datediff / (60));
		$hours =  round($datediff / (60 * 60));
		$days =   round($datediff / (60 * 60 * 24));
		$months = round($datediff / (60 * 60 * 24 * 31));
		$years =  round($datediff / (60 * 60 * 24 * 365));
		
		//echo "$min $hours $days $months $years "; 
		
		if (strtotime($datetime) < 0) {
		  return 'unknown';
		} else if ($datediff < 60) { // seconds
			if ($datediff == 0) return "just now";
			return "$datediff second".self::pluralizer($datediff>1)." ago";
		} else if ($min < 60) {
			return "$min minute".self::pluralizer($min>1)." ago";
		} else if ($hours < 24) {
			return "$hours hour".self::pluralizer($hours>1)." ago";
		} else if ($days < 31) {
			return "$days day".self::pluralizer($days>1)." ago";
	    } else if ($months < 12) {
			return "$months month".self::pluralizer($months>1)." ago";
		} else {
			return "$years year".self::pluralizer($years>1)." ago";
		}
		
		return false;
	}
	
	public static function relativeTime($datetime)
	{
		if (trim($datetime) == "")
			return 'unknown';
		
		$from = strtotime(date("Y-m-d H:i:s"));
		$to = strtotime($datetime);
		$datediff = $from - $to;

    if ($to < 0)
      return 'unknown';
		
		$min = 	  round(abs($datediff) / (60));
		$hours =  round(abs($datediff)  / (60 * 60));
		$days =   round(abs($datediff)  / (60 * 60 * 24));
		$months = round(abs($datediff)  / (60 * 60 * 24 * 31));
		$years =  round(abs($datediff)  / (60 * 60 * 24 * 365));
		
    // Utility::log("$datetime datediff = $min $hours $days $months $years "); 
		
		if ($datediff >= 0)
		{
			if ($datediff < 60){ // seconds
				if ($datediff == 0) return "just now";
				return "$datediff second".self::pluralizer($datediff>1)." ago";
			} else if ($min < 60) {
				return "$min minute".self::pluralizer($min>1)." ago";
			} else if ($hours < 24) {
				return "$hours hour".self::pluralizer($hours>1)." ago";
			} else if ($days < 31) {
				return "$days day".self::pluralizer($days>1)." ago";
		    } else if ($months < 12) {
				return "$months month".self::pluralizer($months>1)." ago";
			} else {
				return "$years year".self::pluralizer($years>1)." ago";
			}
		}
		else
		{
			$datediff = abs($datediff);
			if ($datediff < 60){ // seconds
				return "in $datediff second".self::pluralizer($datediff>1);
			} else if ($min < 60) {
				return "in $min minute".self::pluralizer($min>1);
			} else if ($hours < 24) {
				return "in $hours hour".self::pluralizer($hours>1);
			} else if ($days < 31) {
				return "in $days day".self::pluralizer($days>1);
		    } else if ($months < 12) {
				return "in $months month".self::pluralizer($months>1);
			} else {
				return "in $years year".self::pluralizer($years>1);
			}
		}
		
		return false;
	}

	public static function relativeDate($datetime)
	{
		if (trim($datetime) == "")
			return 'unknown';
		
		$from = strtotime(date("Y-m-d"));
		$to = strtotime($datetime);
		$datediff = $from - $to;
	
		$min = 	  round(abs($datediff) / (60));
		$hours =  round(abs($datediff)  / (60 * 60));
		$days =   round(abs($datediff)  / (60 * 60 * 24));
		$months = round(abs($datediff)  / (60 * 60 * 24 * 31));
		$years =  round(abs($datediff)  / (60 * 60 * 24 * 365));
		
    // Utility::log("$datetime datediff = $min $hours $days $months $years "); 
		
    if ($to < 0)
      return 'unknown';
		
		if ($datediff >= 0)
		{
			if ($days == 0)
				return "today";
			else if ($days < 31) {
				return "$days day".self::pluralizer($days>1)." ago";
		    } else if ($months < 12) {
				return "$months month".self::pluralizer($months>1)." ago";
			} else {
				return "$years year".self::pluralizer($years>1)." ago";
			}
		}
		else
		{
			$datediff = abs($datediff);
			if ($days == 0)
				return "today";
			else if ($days < 31) {
				return "in $days day".self::pluralizer($days>1);
		    } else if ($months < 12) {
				return "in $months month".self::pluralizer($months>1);
			} else {
				return "in $years year".self::pluralizer($years>1);
			}
		}
		
		return false;
	}
	
	public static function filesizeFormat($bytes, $format = '', $force = '')
	{
        $force = strtoupper($force);
        $defaultFormat = '%01d %s';
        if (strlen($format) == 0)
            $format = $defaultFormat;

        $bytes = max(0, (int) $bytes);

        $units = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');

        $power = array_search($force, $units);

        if ($power === false)
            $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return sprintf($format, $bytes / pow(1024, $power), $units[$power]);
    }
	
	/**
	 * You can pass 'you', 'your', 'yours' and the user object and will use the right gender pronouns 
	 * according to the currently logged in user.
	 */
	public static function convertPronoun($pronoun, $user, $last_word = false) 
	{
		if ( !is_object($user) ) return false;
		
		$capitalize = strtoupper(substr($pronoun,0,1)) == substr($pronoun,0,1);
		$pronoun = strtolower($pronoun);
		$cur_user = VimeoApplication::currentUser();
		if ($cur_user->id == $user->id) {
			if ($capitalize) $pronoun = ucfirst($pronoun);
			return $pronoun;
		}

		$gender = $user->get('gender');
		if (!$gender) $gender = 'n';
		
		switch($pronoun)
		{
			case 'you':
				$p = $gender == 'm' ? 'he' : 'she';
				if ($gender == 'n') $p = $user->get('display_name');
				if ($last_word)  {
					$p = $gender == 'm' ? 'him' : 'her';
					if ($gender == 'n') $p = 'them';
				}
				break;
			case 'your':
				$p = $gender == 'm' ? 'his' : 'her';
				if ($gender == 'n') $p = 'their';
				if ($last_word)  {
					$p = $gender == 'm' ? 'him' : 'her';
					if ($gender == 'n') $p = 'them';
				}
				break;
			case 'yours':
				$p = $gender == 'm' ? 'his' : 'hers';
				if ($gender == 'n') $p = 'their';
				break;
		}
		
		if ($capitalize) $p = ucfirst($p);
	
		return $p;
	}
	
	public static function pluralizer($bln, $suffix='s')
	{
		return $bln ? $suffix : '';
	}
	
	public static function pluralizeWord($word, $count, $suffix = 's')
	{
		$count = (int)$count;
		if( substr($word, strlen($word)-1, 1) == $suffix ) { // is plural already
			$word = substr($word, 0, -1 * strlen($suffix)); // normalize to singular
		}
		
		if( $count != 1 ) {
			$word .= $suffix;
		}
			
		return $word;
	}
	
	public static function pluralizeIt($word, $count, $suffix = 's')
	{
		echo "$count " . self::pluralizeWord($word, $count, $suffix);
	}

	public static function possessive($word)
	{
		if (ereg("s$", $word)) 
			return "{$word}'";
		else 
			return "{$word}'s";
	}
	
	public static function userPossessive($user)
	{
		if(VimeoApplication::currentUser()->id == $user->id)
			return "My";
		else
			return Utility::possessive($user->get('display_name'));
	}
	
	/**
	 * turn search query into a list of clean words
	 *
	 * @param unknown_type $str
	 * @return unknown
	 */
	public static function normalizeSearch($str, $split_words = true)
	{
		//		if($split_words)
		//			$str   = str_replace(' ','_',trim($str));
		$words = explode(' ',$str);
		
		$i = 0;
		foreach ($words as $word) {
			$words[$i]  = preg_replace("/[^A-Za-z0-9\ @\.]/", "", trim($word));
			$i++;
		}
		
		return $words;
	}
	
	/**
	 * Normalizes search and returns string formatted for sql 
	 * given an array of fields.
	 *
	 * @param string $str
	 * @param array $fields
	 * @param boolean $split_words ... set this to False if you want to include spaces	 
	 */
	public static function getSearchQuery($str, $fields, $split_words = true)
	{
		if ( !is_array($fields) ) $fields[] = $fields;
		
		$words = Utility::normalizeSearch($str, $split_words);

		$query = "";
		foreach( $fields as $field ) 
		{
			foreach ( $words as $word ) { 
				if( !eregi("^@", $field) ) {
					$query .= " $field LIKE '%$word%' OR ";
				} else {
					$query .= " ".substr($field, 1, strlen($field)-1)." = '$word' OR ";
				}
			}
		}
		
		return ' ( ' . substr($query,0,-3) . ' ) '; 
	}
	
	public static function hiliteText($needle, $haystack) 
	{
	    return str_ireplace($needle, "<span style=\"color:#F75342; border-bottom: 1px dotted #ccc;\">{$needle}</span>", $haystack);
	}
	
	/**
	 * pass string and length
	 *
	 * @param unknown_type $string
	 * @param unknown_type $length
	 * @param unknown_type $type
	 * @return unknown
	 */
	public static function shortenString($string, $length, $end_str = '&hellip;', $mode = 1) {
		$i = 1;

		// If it's in all caps, we'll show even less
		if (!ereg("[a-z]", $string)) $length = ($length * .5);
		
		if (strlen($string) > $length + strlen($end_str)) {
			if ( $mode == 1 ) {
				while (substr($string, $length, $i) != ' ') {
					if ($length > strlen($string)+5) return $string;
					$length++;
				}
			}
			return substr($string, 0, $length).$end_str;
		}
		return $string;
	}
	
	public static function getAge($datetime)
	{
		if(trim($datetime) == '') return false;
		$datediff = strtotime(date("Y-m-d H:i:s")) - strtotime("{$datetime} 00:00:00");
	
		$years =  floor($datediff / (60 * 60 * 24 * 365));
		
		if($years > 0) {
			return $years;
		}
		
		return false;
	}
	
	// This builds directories with max. 1000 files.
	public static function makeSmallDirectory($id)
	{
		//vast majority of cases
		if (strlen($id) > 3)
		{
			$directory = str_split($id, 2);
	        if (strlen($id) % 2 > 0 && count($directory) > 2)
	            array_pop($directory);
	        array_pop($directory);

			return "/" . join("/", $directory) . "/";
		}

		//edge cases.
		if (!strlen($id))
			return "/";
		if(strlen($id) <= 2)
			return "/{$id}/";
		if (strlen($id) == 3)
			return "/" . substr($id, 0, 2) . "/";
	}
	
	public static function checkUrl($url)
	{
		// SCHEME
		$urlregex = "^(https?|ftp)\:\/\/";

		// USER AND PASS (optional)
		$urlregex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";

		// HOSTNAME OR IP
		$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*";  // http://x = allowed (ex. http://localhost, http://routerlogin)
		//$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)+";  // http://x.x = minimum
		//$urlregex .= "([a-z0-9+\$_-]+\.)*[a-z0-9+\$_-]{2,3}";  // http://x.xx(x) = minimum
		//use only one of the above

		// PORT (optional)
		$urlregex .= "(\:[0-9]{2,5})?";
		// PATH  (optional)
		$urlregex .= "(\/([.a-z0-9+%\$_-]\.?)+)*\/?";
		// GET Query (optional)
		$urlregex .= "(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?";
		// ANCHOR (optional)
		$urlregex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?\$";

		// check
		return eregi($urlregex, $url); 	
	}
	
	public static function getEmailPrefix($email) 
	{
		if ( eregi("^([_a-z0-9-]+((\+)?(\.)?[_a-z0-9-]+)*)@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email, $regs) )
			return $regs[1];
	}
	
	public static function prettyCommas($arr) 
	{
		$length = count($arr);
		
		for ($i = 0; $i < $length; $i++) 
		{
			if ( trim($arr[$i]) != '' ) 
			{
				if ( $i == $length - 1 && $length > 1) 
				{
					$str .= " and {$arr[$i]}";
				} 
				elseif ( $length == 1 ) 
				{
					return $arr[$i];	
				}
				else 
				{
					if(  $i == $length - 2) 
					{
						$str .= " {$arr[$i]} ";
					} 
					else 
					{
						$str .= " {$arr[$i]}, ";
					}
				}
			}
		}
		
		return $str;
	}
	
	public static function prettySQL($query)
	{
		//apply some basic formatting.
		$query = str_replace(" SET ", "\nSET ", $query);
		$query = str_replace(" AND ", "\nAND ", $query);
		$query = str_replace(" WHERE ", "\nWHERE ", $query);
		$query = str_replace(" VALUES ", "\nVALUES ", $query);
				
		$data = explode("\n", $query);
		if (count($data))
		{
			foreach ($data AS $line)
			{
				$line = trim($line);
				
				if ($line != '')
					$formatted[] = trim($line);
			}
			$query = implode("<br/>\n", $formatted);
		}	
		
		return $query;
	}
	
	/**
	 * explodes a string and strips out any empty values
	 *
	 * @param string $separator
	 * @param string $str
	 * @return array
	 */
	public static function cleanExplode($separator, $str)
	{
		if ( $str ) {
			$arr = explode($separator, $str);
			foreach ( $arr as $val ) {
				if ( !empty($val) ) {
					$new_str .= $val.',';
				}
			}
			return explode($separator, substr($new_str, 0, -1));
		}
		return false;
	}
	
	public static function getIP()
	{
		return (getenv(HTTP_X_FORWARDED_FOR)) ?  getenv(HTTP_X_FORWARDED_FOR) :  getenv(REMOTE_ADDR);
	}
	
	public function stripAllCaps($string) {
		//if its in all caps
		if (!ereg("[a-z]", $string)) {
			$string = strtolower($string);
			$string = ucfirst($string);
		}
		return $string;
	}
	
	public static function br2nl($text)
	{
	    return preg_replace('/<br\\s*?\/??>/i', '', $text);
	}
	
	public static function formatBigNumber($number)
	{
		$number = (int)$number;
		
		// 0 - 9,999
		if ($number < 10000)
			return number_format($number);
			
		// 10K -> 99.9K and lower
		if ($number < 100000)
			return round($number / 1000, 1) . "K";
			
		// 100K -> 999K
		if ($number < 1000000)
			return round($number / 1000) . "K";
			
		// 1M -> 99.9M
		if ($number < 100000000)
			return round($number / 1000000, 1) . "M";
			
		// 100M -> 999M
		if ($number < 1000000000)
			return round($number / 1000000) . "M";
			
		// so big!
		return "<strong>&#8734;</strong>";
	}
	
	public static function getExtension($file)
	{
		$arr = explode(".", $file);
		$ext = array_pop($arr);

		//BAD!  dont do this.  do it where you need it.
		//$ext = strtolower($ext);
		
		return $ext;
	}
	
	public function formatDollars($amt, $symbol = null, $decimal_places = 2)
	{
	  if ($symbol == null) {
	    $symbol = Utility::$currencySymbols[DEFAULT_CURRENCY];
    }
		return $symbol . number_format($amt, $decimal_places);
	}
	
	public function convertGramsToImperial($g, $precision = 2)
	{
		$gramsInLb = 453.59237;
		$gramsInOz = 28.3495231;
		
		if ($g < $gramsInLb)
		{
			$oz = $g / $gramsInOz;
			
			return number_format($oz, $precision) . ' oz';
		}
		else
		{
			$lbs = $g / $gramsInLb;
			
			return number_format($lbs, $precision) . ' lbs';
		}
	}
	
	public function gramsToLBS($g)
	{
		return $g / 453.59237;
	}

	public function gramsToOz($g) {
		return $g / 28.3495231;
	}

	public function ozToGrams($oz) {
		return $oz * 28.3495231;
	}

  public function lbsToGrams($lb) {
    return $lb * 453.59237;
  }
	
	public function validDateString($datetime) {
	  // in 32bit php, dates >= 2038 come up as 1969
    // echo var_dump(PHP_INT_SIZE === 8);
	  if (strftime('%Y', strtotime($datetime)) == '1969') {
	    return false;
    } else {
      return true;
	  }
	}
	
	public function truncate($str, $length = 20) {
	  if (strlen($str) > $length) {
	    $str = substr($str, 0, $length-1) . '...';
	  }
	  
	  return $str;
	}
	
	public function site_url() {
	  $url = "";
	  
	  if (FORCE_SSL) {
	    $url .= "https://";
    } else {
      $url .= "http://";
	  }
	  
	  $url .= SITE_HOSTNAME;
	  
	  return $url;
	}
	
	public static function downloadUrl($url)
	{
    $tempFile = tempnam('/tmp', 'BOTQUEUE-');
    $fileTarget = fopen($tempFile, 'w');
    $headerFile = tmpfile();

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_WRITEHEADER, $headerFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_FILE, $fileTarget);
    curl_exec($ch);

    if(!curl_errno($ch))
    {
      $realName = basename($url);
      
      //check to see if we got a better name here.
      rewind($headerFile);
      $headers = stream_get_contents($headerFile);
      if(preg_match("/Content-Disposition: .*filename=[\"']?([^ ]+)[\"']?/", $headers, $matches))
        $realName = basename(trim($matches[1]));
      if(preg_match("/Location: (.*)/", $headers, $matches))
        $realName = basename(trim($matches[1]));
      //format the info for our the caller.
      $return = array(
        'localpath' => $tempFile,
        'realname' => $realName
      );
    }
    else
      $return = False;

    //clean up.
    curl_close($ch);
    fclose($headerFile);
    fclose($fileTarget);

    return $return;
	}
}

// mainly for use on things like cycling between the background colors on tables
// Usage: $bg = new Cycle("","highlight"); for each <tr class="<?= $bg->next();
class Cycle {
  public $data;
  public $pos;
  
	function __construct() {
    $this->data = func_get_args();
    $this->pos = 0;
  }
  
  public function next() {
    $this->pos++;
    $this->pos = $this->pos + 1 > count($this->data) ? 0 : $this->pos;
    return $this->data[$this->pos];
  }
}
?>