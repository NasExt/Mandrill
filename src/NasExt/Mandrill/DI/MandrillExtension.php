<?php

/**
 * This file is part of the NasExt extensions of Nette Framework
 * Copyright (c) 20013 Dusan Hudak (http://dusan-hudak.com)
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace NasExt\Mandrill\DI;

use Nette\DI\CompilerExtension;
use Nette\Utils\Validators;

/**
 * Mandrill Extension
 * @author Dusan Hudak
 */
class MandrillExtension extends CompilerExtension {

	/** @var array */
	public $defaults = array(
		'apiKey' => NULL,
		'loggerStatus' => '%debugMode%',
	);

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();

		$config = $this->getConfig($this->defaults);
		Validators::assert($config['apiKey'], 'string', 'Api key');

		$builder->addDefinition($this->prefix('mandrill'))
			->setClass('NasExt\Mandrill\Mandrill')
			->setArguments(array($config['apiKey']))
			->addSetup('setLoggerStatus', array($config['loggerStatus']));

		$builder->addDefinition($this->prefix('mailer'))
			->setClass('NasExt\Mandrill\MandrillMailer');
	}
}
