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

class Email extends Model
{
	public function __construct($id = null)
	{
		parent::__construct($id, "email_queue");
	}

	public static function queue($user, $subject, $text, $html)
	{
		$to_email = $user->get('email');
		if ($user->get('name'))
			$to_name = $user->get('name');
		else
			$to_name = $user->get('username');

		return self::queue_to_email($to_email, $to_name, $subject, $text, $html, $user->id);
	}

	public static function queue_to_email($to_email, $to_name, $subject, $text, $html, $user_id = 0)
	{
		$email = new Email();
		$email->set('user_id', $user_id);
		$email->set('subject', $subject);
		$email->set('text_body', $text);
		$email->set('html_body', $html);
		$email->set('to_email', $to_email);
		$email->set('to_name', $to_name);

		$email->set('queue_date', date("Y-m-d H:i:s"));
		$email->set('status', 'queued');
		$email->save();

		//send it right away.
		//$email->send();

		return $email;
	}

	public function send()
	{
		if (EMAIL_METHOD == 'SMTP')
			$result = $this->smtpSend();
		elseif (EMAIL_METHOD == 'SES')
			$result = $this->sesSend();
		else
			throw new Exception("Invalid email method");

		if ($result) {
			$this->set('status', 'sent');
			$this->set('sent_date', date("Y-m-d H:i:s"));
			$this->save();
		}

		return $result;
	}

	public function sesSend()
	{
		require_once('AWSSDKforPHP/sdk.class.php');
		require_once('AWSSDKforPHP/services/ses.class.php');

		$ses = new AmazonSES(array(
			"key" => AMAZON_AWS_KEY,
			"secret" => AMAZON_AWS_SECRET
		));

		if (defined('SES_USE_DKIM'))
			$ses->set_identity_dkim_enabled($this->From, true);

		//format our from and to emails.
		$from = '"' . RR_PROJECT_NAME . '" <' . EMAIL_FROM . '>';
		if ($this->get('to_name'))
			$to = '"' . $this->get('to_name') . '" <' . $this->get('to_email') . '>';
		else
			$to = $this->get('to_email');

		$response = $ses->send_email($from,
			array('ToAddresses' => array($to)),
			array(
				'Subject.Data' => $this->get('subject'),
				'Body.Text.Data' => $this->get('text_body'),
				'Body.Html.Data' => $this->get('html_body'),
			)
		);

		return $response->isOK();
	}

	public function smtpSend()
	{
		/*
		  //load swift class.
			require_once(CLASSES_DIR . "Swift.php");
			 Swift_ClassLoader::load("Swift_Connection_SMTP");

			// Create the message, and set the message subject.
		$message =& new Swift_Message($this->get('subject'));

			//create the html / text body
	  $message->attach(new Swift_Message_Part($this->get('html_body'), "text/html"));
	  $message->attach(new Swift_Message_Part($this->get('text_body'), "text/plain"));

	  // Set the from address/name.
	  $from =& new Swift_Address(EMAIL_FROM, EMAIL_NAME);

	  // Create the recipient list.
	  $recipients =& new Swift_RecipientList();

	  // Add the recipient
	  $recipients->addTo($this->get('to_email'), $this->get('to_name'));

			//connect and create mailer
			$smtp =& new Swift_Connection_SMTP("smtp.gmail.com", Swift_Connection_SMTP::PORT_SECURE, Swift_Connection_SMTP::ENC_TLS);
	  $smtp->setUsername(EMAIL_FROM);
	  $smtp->setPassword(EMAIL_PASSWORD);

			$mailer = new Swift($smtp);

	  // Attempt to send the email.
	  try {
		$result = $mailer->send($message, $recipients, $from);
		$mailer->disconnect();

			  return true;
			} catch (Swift_BadResponseException $e) {
		return false;
			}
			*/

		return false;
	}

	public static function getQueuedEmails()
	{
		$sql = "SELECT id
				FROM email_queue
				WHERE status = 'queued'
				ORDER BY queue_date ASC";
		$emails = new Collection($sql);
		$emails->bindType('id', 'Email');

		return $emails;
	}
}

?>