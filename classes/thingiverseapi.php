<?
  class ThingiverseAPI
  {
    function __construct($client_id, $client_secret, $user_token = False)
    {
      $this->oauth_url = "https://www.thingiverse.com/login/oauth/access_token";
      $this->api_url = "https://api.thingiverse.com";
      
      $this->client_id = $client_id;
      $this->client_secret = $client_secret;
      $this->user_token = $user_token;
    }
    
    public function exchange_token($code)
    {
      //our parameters for the call
      $data = array(
        'client_id' => $this->client_id,
        'client_secret' => $this->client_secret,
        'code' => $code
      );
      
      //url-ify the data for the POST
      foreach($data as $key => $value)
        $fields_string .= $key . '=' . $value . '&';
      rtrim($fields_string, '&');

      //open connection
      $ch = curl_init();

      //set the url, number of POST vars, POST data
      curl_setopt($ch, CURLOPT_URL, $this->oauth_url);
      curl_setopt($ch, CURLOPT_POST, count($fields));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      //execute post
      $result = rawurldecode(curl_exec($ch));
      
      //close connection
      curl_close($ch);
      
      //find our code.
      $matches = array();
      if (preg_match('/^access_token=([0-9a-f]+)/i', $result, $matches))
        return $matches[1];        
      else
        return false;
    }
    
    public function make_call($path, $data = array(), $method = "GET")
    {
      //our parameters for the call
      $data['client_id'] = $this->client_id;
      $data['client_secret'] = $this->client_secret;
      $data['access_token'] = $this->user_token;
      
      //url-ify the data for the call
      foreach($data as $key => $value)
        $fields_string .= $key . '=' . $value . '&';
      rtrim($fields_string, '&');

      //open connection
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      //what type of request?
      if ($method == "GET")
      {
        curl_setopt($ch, CURLOPT_URL, $this->api_url . $path . '?' . $fields_string);
      }
      elseif ($method == "POST" || $method == "PATCH" || $method == "DELETE")
      {
        if ($method == "PATCH")
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH" ); 
        if ($method == "DELETE")
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 
        
        curl_setopt($ch, CURLOPT_URL, $this->api_url . $path);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
      }

      //execute post
      $result = curl_exec($ch);
      $info = curl_getinfo($ch);
      
      //close connection
      curl_close($ch);
      
      //send our data back
      return json::decode($result);
    }
  }
?>