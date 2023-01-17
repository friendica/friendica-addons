<?php

namespace Friendica\Addon\monolog\tests;

use Friendica\Addon\monolog\src\Factory\Monolog;
use Friendica\Test\src\Core\Logger\AbstractLoggerTest;
use Friendica\Test\Util\VFSTrait;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use Psr\Log\LogLevel;

require_once __DIR__ . '/../vendor/autoload.php';

class MonologTest extends AbstractLoggerTest
{
	use VFSTrait;

	/** @var vfsStreamFile */
	protected $logfile;

	protected function setUp(): void
	{
		parent::setUp();

		$this->setUpVfsDir();
	}

	protected function getContent()
	{
		return $this->logfile->getContent();
	}

	protected function getInstance($level = LogLevel::DEBUG)
	{
		$this->logfile = vfsStream::newFile('friendica.log')
								  ->at($this->root);

		$this->config->shouldReceive('get')->with('system', 'logfile')->andReturn($this->logfile->url())->once();
		$this->introspection->shouldReceive('addClasses')->with(['Monolog\\']);

		$loggerFactory = new Monolog($this->config, $this->introspection, 'test', $level);
		return $loggerFactory->create();
	}

	/**
	 * Test if a log entry is correctly interpolated
	 *
	 * @note - override the base class, because Monolog adds an "array" prefix to the PsrInterpolate when using arrays
	 */
	public function testPsrInterpolate()
	{
		$logger = $this->getInstance();

		$logger->emergency('A {psr} test', ['psr' => 'working']);
		$logger->alert('An {array} test', ['array' => ['it', 'is', 'working']]);
		$text = $this->getContent();
		self::assertStringContainsString('A working test', $text);
		self::assertStringContainsString('An array["it","is","working"] test', $text);
	}

	/**
	 * Test a message with an exception
	 *
	 * @note - override the base class, because Monolog has an own formatter logic for printing exceptions
	 */
	public function testExceptionHandling()
	{
		$e = new \Exception("Test String", 123);
		$eFollowUp = new \Exception("FollowUp", 456, $e);

		$assertion = '[object] (Exception(code: 456)';

		$logger = $this->getInstance();
		$logger->alert('test', ['e' => $eFollowUp]);
		$text = $this->getContent();

		self::assertLogline($text);

		self::assertStringContainsString($assertion, $this->getContent());
	}
}
