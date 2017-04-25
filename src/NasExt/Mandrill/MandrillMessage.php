<?php

/**
 * This file is part of the NasExt extensions of Nette Framework
 * Copyright (c) 20013 Dusan Hudak (http://dusan-hudak.com)
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace NasExt\Mandrill;

use Nette\Mail\Message;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

/**
 * Mandrill Message
 * @author Dusan Hudak
 */
class MandrillMessage extends Message {

	/** @var array */
	private $mandrillMessage = array();

	/**
	 * Sets textual body.
	 * @param string $body
	 * @return $this
	 */
	public function setBody($body) {
		parent::setBody($body);
		$this->mandrillMessage['text'] = $this->body;

		return $this;
	}

	/**
	 * Sets HTML body.
	 * @param string     $html
	 * @param null|mixed $basePath base-path or FALSE to disable parsing
	 * @return $this
	 */
	public function setHtmlBody($html, $basePath = NULL) {
		parent::setHtmlBody($html, $basePath);
		$this->mandrillMessage['html'] = $this->getHtmlBody();

		return $this;
	}

	/**
	 * Sets the subject of the MandrillMessage.
	 * @param string $subject
	 * @return $this
	 */
	public function setSubject($subject) {
		$this->mandrillMessage['subject'] = $subject;

		return $this;
	}

	/**
	 * Sets the sender of the message.
	 * @param string      $email email or format "John Doe" <doe@example.com>
	 * @param string|null $name
	 * @return $this
	 */
	public function setFrom($email, $name = NULL) {
		list($email, $name) = $this->formatEmail($email, $name);

		$this->mandrillMessage['from_email'] = $email;
		$this->mandrillMessage['from_name'] = $name != NULL ? $name : $email;
		return $this;
	}

	/**
	 * Adds the reply-to address.
	 * @param string      $email email or format "John Doe" <doe@example.com>
	 * @param string|null $name
	 * @return $this
	 * @throws InvalidArgumentException
	 */
	public function addReplyTo($email, $name = NULL) {
		list($email, $name) = $this->formatEmail($email, $name);

		$this->setHeader('Reply-To', $email, TRUE);
		return $this;
	}

	/**
	 * Adds email recipient.
	 * @param string      $email email or format "John Doe" <doe@example.com>
	 * @param string|null $name
	 * @param string      $type
	 * @return $this
	 */
	public function addTo($email, $name = NULL, $type = 'to') {
		list($email, $name) = $this->formatEmail($email, $name);

		if (!isset($this->mandrillMessage['to'])) {
			$this->mandrillMessage['to'] = array();
		}

		$recipient['email'] = $email;
		$recipient['name'] = $name != NULL ? $name : $email;
		$recipient['type'] = $type;
		$this->mandrillMessage['to'][] = $recipient;

		return $this;
	}

	/**
	 * Adds carbon copy email recipient.
	 * @param string      $email email or format "John Doe" <doe@example.com>
	 * @param string|null $name
	 * @return $this
	 */
	public function addCc($email, $name = NULL) {
		list($email, $name) = $this->formatEmail($email, $name);

		if (!isset($this->mandrillMessage['to'])) {
			$this->mandrillMessage['to'] = array();
		}

		$recipient['email'] = $email;
		$recipient['name'] = $name != NULL ? $name : $email;
		$recipient['type'] = 'cc';
		$this->mandrillMessage['to'][] = $recipient;

		return $this;
	}

	/**
	 * Adds blind carbon copy email recipient.
	 * @param string      $email email or format "John Doe" <doe@example.com>
	 * @param string|null $name
	 * @return $this
	 */
	public function addBcc($email, $name = NULL) {
		list($email, $name) = $this->formatEmail($email, $name);

		if (!isset($this->mandrillMessage['to'])) {
			$this->mandrillMessage['to'] = array();
		}

		$recipient['email'] = $email;
		$recipient['name'] = $name != NULL ? $name : $email;
		$recipient['type'] = 'bcc';
		$this->mandrillMessage['to'][] = $recipient;

		return $this;
	}

	/**
	 * Adds attachment.
	 * @param string      $file
	 * @param string|null $content
	 * @param string|null $contentType
	 * @return array
	 * @throws FileNotFoundException
	 */
	public function addAttachment($file, $content = NULL, $contentType = NULL) {
		if (!isset($this->mandrillMessage['attachments'])) {
			$this->mandrillMessage['attachments'] = array();
		}

		$attachment = $this->createAttachment($file, $content, $contentType);
		$this->mandrillMessage['attachments'][] = $attachment;

		return $attachment;
	}

	/**
	 * Add tag form Mandrill Outbound info
	 * @param string $tag
	 * @return $this
	 */
	public function addTag($tag) {
		if (!isset($this->mandrillMessage['tags'])) {
			$this->mandrillMessage['tags'] = array();
		}
		$this->mandrillMessage['tags'][] = $tag;

		return $this;
	}

	/**
	 * Whether or not to turn on open tracking for the message
	 * @param bool $value
	 * @return $this
	 */
	public function setTrackOpens($value = TRUE) {
		$this->mandrillMessage['track_opens'] = $value;
		return $this;
	}

	/**
	 * Whether or not to turn on click tracking for the message
	 * @param bool $value
	 * @return $this
	 */
	public function setTrackClicks($value = TRUE) {
		$this->mandrillMessage['track_clicks'] = $value;
		return $this;
	}

	/**
	 * Whether or not to turn on open tracking for the message
	 * @param bool $value
	 * @return $this
	 */
	public function setImportant($value = TRUE) {
		$this->mandrillMessage['important'] = $value;
		return $this;
	}

	/**
	 * Whether or not to expose all recipients in to "To" header for each email
	 * @param bool $value
	 * @return $this
	 */
	public function setPreserveRecipients($value = FALSE) {
		$this->mandrillMessage['preserve_recipients'] = $value;
		return $this;
	}

	/**
	 * Enable a background sending mode that is optimized for bulk sending.
	 * In async mode, messages/send will immediately return a status of "queued" for every recipient.
	 * To handle rejections when sending in async mode, set up a webhook for the 'reject' event.
	 * Defaults to false for messages with no more than 10 recipients;
	 * messages with more than 10 recipients are always sent asynchronously, regardless of the value of async.
	 * @param bool $async
	 * @return $this
	 */
	public function setAsync($async = TRUE) {
		$this->mandrillMessage['async'] = $async;

		return $this;
	}

	/**
	 * Add another Mandrill param
	 * @param string $param
	 * @param string $value
	 * @return $this
	 */
	public function setParam($param, $value) {
		$this->mandrillMessage[$param] = $value;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getMessage() {
		return $this->mandrillMessage;
	}

	/**
	 * Sets a header.
	 * @param string       $name
	 * @param array|string $value value or pair email => name
	 * @param bool         $append
	 * @return $this
	 * @throws InvalidArgumentException
	 * @throws \Nette\Utils\AssertionException
	 */
	public function setHeader($name, $value, $append = FALSE) {
		if (!$name || preg_match('#[^a-z0-9-]#i', $name)) {
			throw new InvalidArgumentException("Header name must be non-empty alphanumeric string, '$name' given.");
		}

		if ($value == NULL) { // intentionally ==
			if (!$append) {
				unset($this->mandrillMessage['headers'][$name]);
			}
		} elseif (is_array($value)) { // email
			$tmp = &$this->mandrillMessage['headers'][$name];
			if (!$append || !is_array($tmp)) {
				$tmp = [];
			}

			foreach ($value as $email => $recipient) {
				if ($recipient === NULL) {
					// continue
				} elseif (!Strings::checkEncoding($recipient)) {
					Validators::assert($recipient, 'unicode', "header '$name'");
				} elseif (preg_match('#[\r\n]#', $recipient)) {
					throw new InvalidArgumentException('Name must not contain line separator.');
				}
				Validators::assert($email, 'email', "header '$name'");
				$tmp[$email] = $recipient;
			}
		} else {
			$value = (string)$value;
			if (!Strings::checkEncoding($value)) {
				throw new InvalidArgumentException('Header is not valid UTF-8 string.');
			}
			$this->mandrillMessage['headers'][$name] = preg_replace('#[\r\n]+#', ' ', $value);
		}
		return $this;
	}

	/**
	 * Formats recipient email.
	 * @param string $email
	 * @param string $name
	 * @return array
	 */
	private function formatEmail($email, $name) {
		if (!$name && preg_match('#^(.+) +<(.*)>\z#', $email, $matches)) {
			return array($matches[2], $matches[1]);
		} else {
			return array($email, $name);
		}
	}

	/**
	 * @param string $file
	 * @param string $content
	 * @param string $contentType
	 * @return array
	 * @throws FileNotFoundException
	 */
	private function createAttachment($file, $content, $contentType) {
		if ($content === NULL) {
			$content = @file_get_contents($file); // @ is escalated to exception
			if ($content === FALSE) {
				throw new FileNotFoundException("Unable to read file '$file'.");
			}
		} else {
			$content = (string)$content;
		}

		$attachment = array(
			'type' => $contentType ? $contentType : finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $content),
			'name' => Strings::fixEncoding(basename($file)),
			'content' => (string)$content,
		);

		return $attachment;
	}
}
