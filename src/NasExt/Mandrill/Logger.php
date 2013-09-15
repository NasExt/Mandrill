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

use Nette\Object;

/**
 * Logger
 *
 * @author Dusan Hudak
 */
class Logger extends Object
{
	/** @var  string */
	private $callTo;

	/** @var  string */
	private $curlBuffer;

	/** @var   float */
	private $executeTime;

	/** @var  string json format */
	private $request;

	/** @var  string json format */
	private $response;


	/**
	 * @param string $callTo
	 */
	public function setCallTo($callTo)
	{
		$this->callTo = $callTo;
	}


	/**
	 * @return string
	 */
	public function getCallTo()
	{
		return $this->callTo;
	}


	/**
	 * @param string $curlBuffer
	 */
	public function setCurlBuffer($curlBuffer)
	{
		$this->curlBuffer = $curlBuffer;
	}


	/**
	 * @return string
	 */
	public function getCurlBuffer()
	{
		return $this->curlBuffer;
	}


	/**
	 * @param float $executeTime
	 */
	public function setExecuteTime($executeTime)
	{
		$this->executeTime = number_format($executeTime * 1000, 2) . 'ms';
	}


	/**
	 * @return float
	 */
	public function getExecuteTime()
	{
		return $this->executeTime;
	}


	/**
	 * @param string $request
	 */
	public function setRequest($request)
	{
		$this->request = $request;
	}


	/**
	 * @return string
	 */
	public function getRequest()
	{
		return $this->request;
	}


	/**
	 * @param string $response
	 */
	public function setResponse($response)
	{
		$this->response = $response;
	}


	/**
	 * @return string
	 */
	public function getResponse()
	{
		return $this->response;
	}
}
