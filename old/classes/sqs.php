<?php
/**
 * This file contains the code for the SQS client.
 *
 * Copyright 2006-2007 Intellispire.
 * Copyright 2008 Zach Hoeken
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0 
 *
 * Unless required by applicable law or agreed to in writing, 
 * software distributed under the License is distributed on an 
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, 
 * either express or implied. See the License for the specific 
 * language governing permissions and limitations under the License.  
 *
 * @category   Web Services
 * @package    SQS 
 * @author     Nick Temple <Nick.Temple@intellispire.com>  Original Author
 * @author     Zach Hoeken <hoeken@gmail.com> Updated for the latest version of SQS
 * @copyright  2006 Nick Temple
 * @license    http://www.intellispire.com/license.html 
 * @link       http://www.intellispire.com/
 */

/**
 * The Simple Queue Service.
 *
 * All functions return the result or TRUE on success,
 * null or false on failure.
 *
 * You can check the exact status using $SQS->statuscode, which should be "Success".
 * for successful transactions.
 * The last requestid and errormsg are also stored.
 *
 * This implementation automatically stores the activeQueue, which can be change
 * by calling createQueue to get a new (or existing queue), or setActiveQueue if you
 * already have queue URL.
 *
 * The permissions system has not been tested.
 */
 
require_once("HTTP/Request.php");

class SQS
{
	private $access_key;
	private $secret;
	private $activeQueue;

	//what we got back from the server
	public $result;

	//error types
	public $errorType = null;
	public $errorCode = null;
	public $errorMessage = null;
	public $errorDetail = null;
	
	//success types
	public $requestId = null;
	public $requestData = null;

	public function __construct($a, $s)
	{
		$this->access_key = $a;
		$this->secret = $s;
	}

	public function getError()
	{
		$str = "Type: {$this->errorType}\n";
		$str .= "Code: {$this->errorCode}\n";
		$str .= "Message: {$this->errorMessage}\n";
		if ($this->errorDetail !== null)
			$str .= "Details: {$this->errorDetail}\n";
		
		return $str;
	}

	public function setActiveQueue($q)
	{
		$this->activeQueue = $q;
	}

	public function ListQueues($QueueNamePrefix = null)
	{
		//format params
		$params = array();
		if ($QueueNamePrefix !== null)
			$params['QueueNamePrefix'] = $QueueNamePrefix;

		//make the call.
		if ($this->_call('ListQueues', $params))
			return $this->requestData;
		else
			return null; 
	}

	public function CreateQueue($QueueName, $setActive = true, $DefaultVisibilityTimeout = null)
	{
		//format params
		$params = array();
		$params['QueueName'] = $QueueName;
		if ($DefaultVisibilityTimeout !== null)
			$params['DefaultVisibilityTimeout'] = $DefaultVisibilityTimeout;

		//make the call
		if ($this->_call('CreateQueue', $params))
		{
			//handle the response
			if ($setActive)
				$this->activeQueue = $this->requestData;

			return $this->requestData;
		}
		//fail.
		else
			return null;
	}    

	public function DeleteQueue($QueueName = null)
	{
		if ($QueueName !== null)
			$this->setActiveQueue($QueueName);

		if ($this->_call('DeleteQueue'))
			return true;
		else
			return false;
	}

	public function SendMessage($MessageBody)
	{
		$params = array();
		$params['MessageBody'] = $MessageBody;

		//hash it for error checking.		
		$md5 = md5($MessageBody);

		if ($this->_call('SendMessage', $params))
		{
			//only if it matches!
			if ($md5 == $this->requestData['MD5'])
				return true;	
		}

		return null;
	}

	// Returns 0 or 1 messages
	public function ReceiveMessage($VisibilityTimeout = null)
	{
		//format our params
		$params = array();
		if ($VisibilityTimeout !== null)
			$params['VisibilityTimeout'] = $VisibilityTimeout;

		//make the call.
		if ($this->_call('ReceiveMessage', $params))
		{
			return $this->requestData;
		}
		else
			return null; 
	}


	// Returns 0 or more messages (up to a maximum of 10), formatted as an array of messages
	public function ReceiveMessages($NumberOfMessages = null, $VisibilityTimeout = null)
	{
		//setup our paramters
		$params = array();
		if ($NumberOfMessages !== null)
		{
			$NumberOfMessages = (int)$NumberOfMessages;
			$NumberOfMessages = min($NumberOfMessages, 10);
			if ($NumberOfMessages > 0)
				$params['MaxNumberOfMessages'] = $NumberOfMessages;
		}
		if ($VisibilityTimeout !== null)
			$params['VisibilityTimeout'] = $VisibilityTimeout;
			
		//make the call.
		if ($this->_call('ReceiveMessage', $params))
		{
			return $this->requestData;
		}
		else
			return null;
	}

	public function DeleteMessage($ReceiptHandle)
	{
		$params = array();
		$params['ReceiptHandle'] = $ReceiptHandle;
		
		return $this->_call('DeleteMessage', $params);
	}
	
	public function GetQueueAttributes($AttributeName = null)
	{
		$params = array();
		if ($AttributeName === null)
			$AttributeName = 'All';
		$params['AttributeName'] = $AttributeName;
		
		if ($this->_call('GetQueueAttributes', $params))
		{
			return $this->requestData;		
		}
		else
			return null;
	}
	
	public function SetQueueAttributes($AttributeName, $AttributeValue)
	{
		$params = array();
		$params['Attribute.Name'] = $AttributeName;
		$params['Attribute.Value'] = $AttributeValue;
		
		return $this->_call('SetQueueAttributes', $params);
	}

	private function _call($action, $params = array())
	{
		// Configure our parameters.
		$params['Action'] = $action;
		$params['AWSAccessKeyId'] = $this->access_key;
		$params['Version'] = '2008-01-01';
		$params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');   
		$params['SignatureVersion'] = 1;
		$params['Signature'] = $this->_getSignature($params);

		//create our request string.
        $pairs = array();
		foreach ($params as $name => $value)
			if ($value)
				$pairs[] = $name . '=' . urlencode($value);
		//sort($pairs);
		$request = implode('&', $pairs);

		//figure out where to send the request
		if ($action == 'CreateQueue' || $action == 'ListQueues')
			$endpoint = "http://queue.amazonaws.com/";
		else
		{
			//if we have a url, just use that
			if (strpos($this->activeQueue, ':') !== false)
				$endpoint = $this->activeQueue;
			//otherwise, create it.
			else
				$endpoint = "http://queue.amazonaws.com/" . $this->activeQueue;
		}
		
		//get it, parse it, and send it back.
		$result = $this->_makeRequest($endpoint, $request);
		$this->result = $result;
		return $this->_parseResult($result, $action);
	}
	
	private function _parseResult($result, $action)
	{
		$xml = simplexml_load_string($result);

		//was it an error?
		if ($xml->ErrorResponse)
		{
			$this->errorType = (string)$xml->ErrorResponse->Error->Type;
			$this->errorCode = (string)$xml->ErrorResponse->Error->Code;
			$this->errorMessage = (string)$xml->ErrorResponse->Error->Message;
			if ($xml->ErrorResponse->Error->Detail)
				$this->errorDetail = (string)$xml->ErrorResponse->Error->Detail;
			
			$this->requestId = (string)$xml->ErrorResponse->RequestID;
			
			return false;
		}
		else
		{
			$this->requestId = (string)$xml->ResponseMetadata->RequestId;

			$topLevel = $action . "Result";
			
			//snag the queue url for creation.
			if ($action == 'CreateQueue')
				$this->requestData = (string)$xml->$topLevel->QueueUrl;

			//snag all the queue urls for lists
			else if ($action == 'ListQueues')
			{
				$this->requestData = array();
				foreach ($xml->$topLevel->QueueUrl AS $url)
					$this->requestData[] = (string)$url;
			}
			
			//snag our info from sending messagse
			else if ($action == 'SendMessage')
			{
				$this->requestData = array(
					'MD5' => $xml->$topLevel->MD5OfMessageBody,
					'MessageId' => $xml->$topLevel->MessageId
				);
			}
			
			//did we get any messages back?
			else if ($action == 'ReceiveMessage')
			{
				$this->requestData = array();

				if (count($xml->$topLevel->Message) > 1)
				{
					foreach ($xml->$topLevel->Message AS $message)
					{
						//only add ones that have the right md5.  silently ignore fails, as they'll eventually time-out and be re-sent.
						$md5 = md5((string)$message->Body);
						if ($md5 == (string)$message->MD5OfBody)
						{
							$this->requestData[] = array(
								'MessageId' 	=> (string)$message->MessageId,
								'ReceiptHandle'	=> (string)$message->ReceiptHandle,
								'MD5' 			=> (string)$message->MD5OfBody,
								'Body'			=> (string)$message->Body
							);
						}
					}
				}
				else if ($xml->$topLevel->Message)
				{
					//only add ones that have the right md5.  silently ignore fails, as they'll eventually time-out and be re-sent.
					$md5 = md5((string)$xml->$topLevel->Message->Body);
					if ($md5 == (string)$xml->$topLevel->Message->MD5OfBody)
					{
						$this->requestData[] = array(
							'MessageId' 	=> (string)$xml->$topLevel->Message->MessageId,
							'ReceiptHandle'	=> (string)$xml->$topLevel->Message->ReceiptHandle,
							'MD5'			=> (string)$xml->$topLevel->Message->MD5OfBody,
							'Body'			=> (string)$xml->$topLevel->Message->Body
						);
					}
				}
			}
			
			//attributes of the specified queue?
			else if ($action == 'GetQueueAttributes')
			{
				$this->requestData = array();
				
				if (count($xml->$topLevel->Attribute) > 1)
				{
					foreach ($xml->$topLevel->Attribute AS $attribute)
						$this->requestData[(string)$attribute->Name] = (string)$attribute->Value;
				}
				else if ($xml->$topLevel->Attribute)
					$this->requestData[(string)$xml->$topLevel->Attribute->Name] = (string)$xml->$topLevel->Attribute->Value;
			}
				
			return true;
		}
	}
	
	private function _makeRequest($url, $params)
	{
		/*
		//get our results
		$req = & new HTTP_Request($url);
		$req->setMethod('GET');
		$req->addRawQueryString($params);
		$req->sendRequest();
		return $req->getResponseBody();
		*/
		
		//prepare our options
		$options = array(
			CURLOPT_RETURNTRANSFER => true,      // return web page
			CURLOPT_HEADER         => false,     // don't return headers
			CURLOPT_FOLLOWLOCATION => true,      // follow redirects
			CURLOPT_ENCODING       => "",        // handle all encodings
			CURLOPT_USERAGENT      => "SQS/php", // who am i
			CURLOPT_AUTOREFERER    => true,      // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,       // timeout on connect
			CURLOPT_TIMEOUT        => 120,       // timeout on response
			CURLOPT_MAXREDIRS      => 10,        // stop after 10 redirects
			CURLOPT_POST           => 1,         // use POST
			CURLOPT_POSTFIELDS     => $params    // our post params
		);

		//make the request
		$ch = curl_init($url);
		curl_setopt_array($ch, $options);
		$content = curl_exec($ch);
		$err = curl_errno($ch);
		$errmsg = curl_error($ch);
		$header = curl_getinfo($ch);
		curl_close($ch);

		//snag our info.
		$header['errno']   = $err;
		$header['errmsg']  = $errmsg;
		$header['content'] = $content;

		return $content;
	}
	
	private function _getSignature($params)
	{
		//create our signing string
		foreach ($params as $name => $value)
			if ($value)
				$toSign[] = $name . $value;

		//actually do the signing.
		natcasesort($toSign);
		$stringToSign = implode('', $toSign);

		return $this->_getHash($stringToSign);
	}
	  
  	/**
	* Creates a HMAC-SHA1 hash
	*
	* This uses the hash extension if loaded
	*
	* @param string $string String to sign
	* @return string
	*/
	private function _getHash($string)
	{
		return base64_encode(
			extension_loaded('hash') ?
				hash_hmac('sha1', $string, $this->secret, true) : 
				pack('H*', sha1((str_pad($this->secret, 64, chr(0x00)) ^ (str_repeat(chr(0x5c), 64))) .
				pack('H*', sha1((str_pad($this->secret, 64, chr(0x00)) ^ (str_repeat(chr(0x36), 64))) . 
				$string))))
		);
	}
}

?>
