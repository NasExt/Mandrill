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

/**
 * Mandrill Mailer
 *
 * @author Dusan Hudak
 */
class MandrillMailer
{
	/** @var  Mandrill */
	private $mandrill;


	/**
	 * @param Mandrill $mandrill
	 */
	public function __construct(Mandrill $mandrill)
	{
		$this->mandrill = $mandrill;
	}


	/**
	 * Send email via Mandrill.
	 * @param MandrillMessage $message
	 * @return string Result from Mandrill
	 */
	public function send(MandrillMessage $message)
	{
		$request = $message->getMessage();
		return $this->mandrill->callApi('/messages/send', $request);
	}


	/**
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->mandrill->getLogger();
	}
}
