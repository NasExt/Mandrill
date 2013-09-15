NasExt/Mandrill
===========================

This extension is API of the [Mandrill](http://mandrill.com) for Nette Framework.

Requirements
------------

NasExt/Mandrill requires PHP 5.3.2 or higher.

- [Nette Framework 2.0.x](https://github.com/nette/nette)


Installation
------------

The best way to install NasExt/Mandrill is using  [Composer](http://getcomposer.org/):

```sh
$ composer require nasext/mandrill:@dev
```

Enable the extension using your neon config.

```yml
nasext.mandrill:
	apiKey: "PrPsqh1234567890"
	loggerStatus: TRUE

extensions:
	nasext.mandrill: NasExt\Mandrill\DI\MandrillExtension
```

Send message
--------------------

```php
/** @var \NasExt\Mandrill\MandrillMailer */
private $mailer;

$msg = new \NasExt\Mandrill\MandrillMessage();
$msg->setBody('Message body');
$msg->setFrom('John Doe', 'john.doe@example.com')
	->setSubject('Test message')
	->addTo('Peter Doe', 'peter.doe@example.com')
	->addReplyTo('John Doe', 'john.doe@example.com')
	->addCc('Jack Doe', 'jack.doe@example.com')
    ->addCc('Emil Doe', 'emil.doe@example.com')
    ->addBcc('Thomas Doe', 'thomas.doe@example.com')
$this->mailer->send($msg);
```

we can specifiy addressee in three ways::
```php
->setFrom('John Doe', 'john.doe@example.com')
->setFrom('John Doe <john.doe@example.com>')
->setFrom('john.doe@example.com>')
```

Send attachment
--------------------

```php
$msg = new \NasExt\Mandrill\MandrillMessage();
$msg->setBody('Message body');
$msg->setFrom('John Doe', 'john.doe@example.com>')
	->setSubject('Test message')
	->addTo('Peter Doe', 'peter.doe@example.com>')
	->addReplyTo('John Doe', 'john.doe@example.com>')
	->addAttachment('path/to/example.zip');

$this->mailer->send($msg);
```

or you can use:
```php
->addAttachment('example.txt', 'Hello John!');
->addAttachment('info.zip', file_get_contents('path/to/example.zip'));
```

Send message with template
--------------------
```php
$template = new Nette\Templating\FileTemplate('email.latte');
$template->registerFilter(new Nette\Latte\Engine);
$template->orderId = 123;
...
$msg->setHTMLBody($template);
..
$this->mailer->send($msg);
```


Logger
--------------------
```php
..
$this->mailer->send($msg);
\Nette\Diagnostics\Debugger::dump($this->mailer->getLogger());
```

NasExt/Mandrill supports:
```php
->addTag('test-message')
->setTrackOpens()
->setTrackClicks()
->setPreserveRecipients()
->setImportant()
->setAsync()
```


-----

Repository [http://github.com/nasext/mandrill](http://github.com/nasext/mandrill).