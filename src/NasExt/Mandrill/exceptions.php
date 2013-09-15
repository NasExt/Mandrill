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

/**
 * Common interface for caching mandrill exceptions
 *
 * @author Dusan Hudak
 */
interface Exception
{

}

/**
 * Class HttpException
 */
class HttpException extends \Exception implements Exception
{

}

/**
 * Class MandrillException
 */
class MandrillException extends \Exception implements Exception
{

}

/**
 * Class FileNotFoundException
 */
class FileNotFoundException extends \Exception implements Exception
{

}

/**
 * Class InvalidArgumentException
 */
class InvalidArgumentException extends \Exception implements Exception
{

}
