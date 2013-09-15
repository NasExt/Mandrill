<?php

/**
 * This file is part of the NasExt extensions of Nette Framework
 *
 * Copyright (c) 20013 Dusan Hudak (http://dusan-hudak.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace NasExt\Mandrill;

use Nette\Mail\Message;
use Nette\Mail\MimePart;
use Nette\Templating\IFileTemplate;
use Nette\Templating\ITemplate;
use Nette\Utils\MimeTypeDetector;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

/**
 * Mandrill Message
 *
 * @author Dusan Hudak
 */
class MandrillMessage extends Message
{
	/** @internal */
	const EOL = "\r\n";
	const LINE_LENGTH = 76;

	/** @var array */
	private $mandrillMessage = array();


	/**
	 * Sets textual body.
	 * @param  string
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function setBody($body)
	{

		if ($body instanceof ITemplate) {
			$body = $body->__toString(TRUE);
		}

		$this->mandrillMessage['text'] = $body;

		return $this;
	}


	/**
	 * Sets HTML body.
	 * @param  string
	 * @param  mixed base-path or FALSE to disable parsing
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function setHtmlBody($html, $basePath = NULL)
	{
		if ($html instanceof ITemplate) {
			//$html->mail = $this;
			if ($basePath === NULL && $html instanceof IFileTemplate) {
				$basePath = dirname($html->getFile());
			}
			$html = $html->__toString(TRUE);
		}

		if ($basePath !== FALSE) {
			$cids = array();
			$matches = Strings::matchAll(
				$html,
				'#(src\s*=\s*|background\s*=\s*|url\()(["\'])(?![a-z]+:|[/\\#])(.+?)\\2#i',
				PREG_OFFSET_CAPTURE
			);
			foreach (array_reverse($matches) as $m) {
				$file = rtrim($basePath, '/\\') . '/' . $m[3][0];
				if (!isset($cids[$file])) {
					$cids[$file] = substr($this->addEmbeddedFile($file)->getHeader("Content-ID"), 1, -1);
				}
				$html = substr_replace($html,
					"{$m[1][0]}{$m[2][0]}cid:{$cids[$file]}{$m[2][0]}",
					$m[0][1], strlen($m[0][0])
				);
			}
		}
		$this->mandrillMessage['html'] = $html;

		return $this;
	}


	/**
	 * Sets the subject of the MandrillMessage.
	 * @param  string
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function setSubject($subject)
	{
		$this->mandrillMessage['subject'] = $subject;

		return $this;
	}


	/**
	 * Sets the sender of the message.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function setFrom($email, $name = NULL)
	{
		list($email, $name) = $this->formatEmail($email, $name);

		$this->mandrillMessage['from_email'] = $email;
		$this->mandrillMessage['from_name'] = $name != NULL ? $name : $email;
		return $this;
	}


	/**
	 * Adds the reply-to address.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return self
	 */
	public function addReplyTo($email, $name = NULL)
	{
		list($email, $name) = $this->formatEmail($email, $name);

		$this->setHeader('Reply-To', $email, TRUE);
		return $this;
	}


	/**
	 * Adds email recipient.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function addTo($email, $name = NULL)
	{
		list($email, $name) = $this->formatEmail($email, $name);

		if (!isset($this->mandrillMessage['to'])) {
			$this->mandrillMessage['to'] = array();
		}

		$recipient['email'] = $email;
		$recipient['name'] = $name != NULL ? $name : $email;
		$this->mandrillMessage['to'][] = $recipient;

		return $this;
	}


	/**
	 * Adds carbon copy email recipient.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function addCc($email, $name = NULL)
	{
		list($email, $name) = $this->formatEmail($email, $name);

		if (!isset($this->mandrillMessage['to'])) {
			$this->mandrillMessage['to'] = array();
		}

		$recipient['email'] = $email;
		$recipient['name'] = $name != NULL ? $name : $email;
		$this->mandrillMessage['to'][] = $recipient;

		return $this;
	}


	/**
	 * Adds blind carbon copy email recipient.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function addBcc($email, $name = NULL)
	{
		list($email, $name) = $this->formatEmail($email, $name);

		$this->mandrillMessage['bcc_address'] = $email;
		return $this;
	}


	/**
	 * Adds attachment.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return array
	 */
	public function addAttachment($file, $content = NULL, $contentType = NULL)
	{
		if (!isset($this->mandrillMessage['attachments'])) {
			$this->mandrillMessage['attachments'] = array();
		}

		$attachment = $this->createAttachment($file, $content, $contentType, 'attachment');
		$this->mandrillMessage['attachments'][] = $attachment;

		return $attachment;
	}


	/**
	 * Add tag form Mandrill Outbound info
	 * @param string $tag
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function addTag($tag)
	{
		if (!isset($this->mandrillMessage['tags'])) {
			$this->mandrillMessage['tags'] = array();
		}
		$this->mandrillMessage['tags'][] = $tag;

		return $this;
	}


	/**
	 * Whether or not to turn on open tracking for the message
	 * @param bool $value
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function setTrackOpens($value = TRUE)
	{
		$this->mandrillMessage['track_opens'] = $value;
		return $this;
	}


	/**
	 * Whether or not to turn on click tracking for the message
	 * @param bool $value
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function setTrackClicks($value = TRUE)
	{
		$this->mandrillMessage['track_clicks'] = $value;
		return $this;
	}


	/**
	 * Whether or not to turn on open tracking for the message
	 * @param bool $value
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function setImportant($value = TRUE)
	{
		$this->mandrillMessage['important'] = $value;
		return $this;
	}


	/**
	 * Whether or not to expose all recipients in to "To" header for each email
	 * @param bool $value
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function setPreserveRecipients($value = FALSE)
	{
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
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function setAsync($async = TRUE)
	{
		$this->mandrillMessage['async'] = $async;

		return $this;
	}


	/**
	 * Add another Mandrill param
	 * @param string $param
	 * @param string $value
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function setParam($param, $value)
	{
		$this->mandrillMessage[$param] = $value;

		return $this;
	}


	/**
	 * @return array
	 */
	public function getMessage()
	{
		return $this->mandrillMessage;
	}


	/**
	 * Sets a header.
	 * @param  string
	 * @param  string|array value or pair email => name
	 * @param  bool
	 * @throws InvalidArgumentException
	 * @return MandrillMessage  provides a fluent interface
	 */
	public function setHeader($name, $value, $append = FALSE)
	{
		if (!$name || preg_match('#[^a-z0-9-]#i', $name)) {
			throw new InvalidArgumentException("Header name must be non-empty alphanumeric string, '$name' given.");
		}

		if ($value == NULL) {
			if (!$append) {
				unset($this->mandrillMessage['headers'][$name]);
			}
		} elseif (is_array($value)) {
			$tmp = & $this->mandrillMessage['headers'][$name];
			if (!$append || !is_array($tmp)) {
				$tmp = array();
			}

			foreach ($value as $email => $recipient) {
				if ($recipient !== NULL && !Strings::checkEncoding($recipient)) {
					Validators::assert($recipient, 'unicode', "header '$name'");
				}
				if (preg_match('#[\r\n]#', $recipient)) {
					throw new InvalidArgumentException("Name must not contain line separator.");
				}
				Validators::assert($email, 'email', "header '$name'");
				$tmp[$email] = $recipient;
			}
		} else {
			$value = (string)$value;
			if (!Strings::checkEncoding($value)) {
				throw new InvalidArgumentException("Header is not valid UTF-8 string.");
			}
			$this->mandrillMessage['headers'][$name] = preg_replace('#[\r\n]+#', ' ', $value);
		}
		return $this;
	}


	/**
	 * Formats recipient email.
	 * @param  string
	 * @param  string
	 * @return array
	 */
	private function formatEmail($email, $name)
	{
		if (!$name && preg_match('#^(.+) +<(.*)>\z#', $email, $matches)) {
			return array($matches[2], $matches[1]);
		} else {
			return array($email, $name);
		}
	}


	/**
	 * @param $file
	 * @param $content
	 * @param $contentType
	 * @param $disposition
	 * @return array
	 * @throws FileNotFoundException
	 */
	private function createAttachment($file, $content, $contentType, $disposition)
	{
		if ($content === NULL) {
			$content = @file_get_contents($file);
			if ($content === FALSE) {
				throw new FileNotFoundException("Unable to read file '$file'.");
			}
		} else {
			$content = (string)$content;
		}

		$attachment = array(
			'type' => $contentType ? $contentType : MimeTypeDetector::fromString($content),
			'name' => Strings::fixEncoding(basename($file)),
			'content' => rtrim(chunk_split(base64_encode($content), self::LINE_LENGTH, self::EOL))
		);

		return $attachment;
	}
}
