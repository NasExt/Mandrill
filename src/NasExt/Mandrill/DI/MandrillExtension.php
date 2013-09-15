<?php

/**
 * This file is part of the NasExt extensions of Nette Framework
 *
 * Copyright (c) 20013 Dusan Hudak (http://dusan-hudak.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace NasExt\Mandrill\DI;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\Utils\Validators;

if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
}

if (isset(\Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']) || !class_exists('Nette\Configurator')) {
	unset(\Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']);
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

/**
 * Mandrill Extension
 *
 * @author Dusan Hudak
 */
class MandrillExtension extends CompilerExtension
{
	/** @var array */
	public $defaults = array(
		'apiKey' => NULL,
		'loggerStatus' => FALSE,
	);


	public function loadConfiguration()
	{
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


	/**
	 * @param Configurator $configurator
	 */
	public static function register(Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('mandrill', new MandrillExtension());
		};
	}
}
