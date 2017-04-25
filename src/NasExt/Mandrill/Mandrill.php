<?php

/**
 * This file is part of the NasExt extensions of Nette Framework
 * Copyright (c) 20013 Dusan Hudak (http://dusan-hudak.com)
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace NasExt\Mandrill;

use Nette\Object;

/**
 * Mandrill service for Nette
 * @author Dusan Hudak
 */
class Mandrill extends Object {

	/** @var array */
	private static $errorMap = array();

	/** @var string */
	private $apiKey;

	/**
	 * Mandrill API endpoint
	 * @var string
	 */
	private $apiEndpoint = "https://mandrillapp.com/api/1.0";

	/**
	 * Input and output format
	 * @var string
	 */
	private $apiFormat = 'json';

	/** @var bool */
	private $loggerStatus = FALSE;

	/** @var Logger */
	private $logger;

	/**
	 * @param string $apiKey
	 */
	public function __construct($apiKey) {
		$this->apiKey = $apiKey;
	}

	/**
	 * @param bool $value
	 */
	public function setLoggerStatus($value) {
		$this->loggerStatus = $value;
	}

	/**
	 * @return Logger
	 */
	public function getLogger() {
		return $this->logger;
	}

	/**
	 * Call Mandrill API
	 * @param string $url
	 * @param array  $request
	 * @return string Result from Mandrill
	 * @throws HttpException
	 * @throws MandrillException
	 */
	public function callApi($url, array $request) {
		$request = array('message' => $request);
		$request['key'] = $this->apiKey;
		$request = json_encode($request);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mandrill-Nette-PHP/0.2');
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 600);
		curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint . $url . '.' . $this->apiFormat);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/' . $this->apiFormat));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_VERBOSE, $this->loggerStatus);

		if ($this->loggerStatus) {
			$this->logger = new Logger();

			$this->logger->setCallTo($this->apiEndpoint . $url . '.' . $this->apiFormat);
			$this->logger->setRequest($request);
			$curlBuffer = fopen('php://temp', 'w+');
			curl_setopt($ch, CURLOPT_STDERR, $curlBuffer);
			$start = microtime(TRUE);
		}

		$responseBody = curl_exec($ch);
		$info = curl_getinfo($ch);

		if ($this->loggerStatus) {
			$time = microtime(TRUE) - $start;
			rewind($curlBuffer);
			$this->logger->setCurlBuffer(stream_get_contents($curlBuffer));
			fclose($curlBuffer);
			$this->logger->setExecuteTime($time);
			$this->logger->setResponse($responseBody);
		}

		if (curl_error($ch)) {
			throw new HttpException("API call to $url failed: " . curl_error($ch));
		}

		$result = json_decode($responseBody, TRUE);
		if ($result === NULL) {
			throw new MandrillException('We were unable to decode the JSON response from the Mandrill API: ' . $responseBody);
		}

		if (floor($info['http_code'] / 100) >= 4) {
			throw $this->castError($result);
		}

		curl_close($ch);
		return $result;
	}

	/**
	 * @param string $result
	 * @return mixed
	 * @throws MandrillException
	 */
	private function castError($result) {
		if ($result['status'] !== 'error' || !$result['name']) {
			throw new MandrillException('We received an unexpected error: ' . json_encode($result));
		}

		$class = (isset(self::$errorMap[$result['name']])) ? self::$errorMap[$result['name']] : MandrillException::class;
		return new $class($result['message'], $result['code']);
	}
}
