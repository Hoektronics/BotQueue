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

class Email extends Model
{
	public function __construct($id = null)
	{
		parent::__construct($id, "email_queue");
	}

	/**
	 * @param $user User
	 * @param $subject string
	 * @param $text string
	 * @param $html string
	 * @return Email string
	 */
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

		//send it right away, or queue it.
		if(!(defined("QUEUE_EMAIL") && QUEUE_EMAIL)) {
			$email->send();
		}

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
		$awsSettings = array();
		$awsSettings['key'] = AMAZON_AWS_KEY;
		$awsSettings['secret'] = AMAZON_AWS_SECRET;
		$awsSettings['region'] = AMAZON_AWS_REGION;
		$ses = Aws\Ses\SesClient::factory($awsSettings);

		if (defined('SES_USE_DKIM') && SES_USE_DKIM)
			$ses->setIdentityDkimEnabled(array(
				'Identity' => EMAIL_USERNAME,
				'DkimEnabled' => SES_USE_DKIM
			));

		//format our from and to emails.
		//$from = '"' . RR_PROJECT_NAME . '" <' . EMAIL_USERNAME . '>';
		if(defined('SES_USE_VERP') && SES_USE_VERP) {
			$split = explode('@', EMAIL_USERNAME);
			$from = $split[0] . "+" . $this->get('to_name') . "@" . $split[1];
		}
		else
			$from = EMAIL_USERNAME;

		$from = '"' . RR_PROJECT_NAME . '" <' . $from . '>';

		if ($this->get('to_name'))
			$to = '"' . $this->get('to_name') . '" <' . $this->get('to_email') . '>';
		else
			$to = $this->get('to_email');

		$response = $ses->sendEmail(array(
			'Source' => $from,
			'Destination' => array(
				'ToAddresses' => array($to),
			),
			'Message' => array(
				'Subject' => array(
					'Data' => $this->get('subject'),
					'Charset' => 'us-ascii',
				),
				'Body' => array(
					'Text' => array(
						'Data' => $this->get('text_body'),
						'Charset' => 'us-ascii',
					),
					'Html' => array(
						'Data' => $this->get('html_body'),
						'Charset' => 'us-ascii'
					)
				)
			)
		));

		return $response->hasKey("ResponseMetadata");
	}

	public function smtpSend()
	{
		$message = Swift_Message::newInstance();
		$message->setSubject($this->get('subject'));
		$message->setFrom(array(EMAIL_USERNAME => EMAIL_NAME));
		$message->setTo(array($this->get('to_email')));
		$message->setBody($this->get('html_body'), "text/html");
		// addPart doesn't currently exist
		//$message->addPart($this->get('text_body'), "text/plain");

		$transport = Swift_SmtpTransport::newInstance(EMAIL_SMTP_SERVER, EMAIL_SMTP_SERVER_PORT, 'ssl');
		$transport->setUsername(EMAIL_USERNAME);
		$transport->setPassword(EMAIL_PASSWORD);

		$mailer = Swift_Mailer::newInstance($transport);
		$result = $mailer->send($message);

		// result is 0 if no messages were sent
		return ($result != 0);
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