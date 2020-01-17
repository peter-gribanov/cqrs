<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Tests\Command\Handler\Locator;

use GpsLab\Component\Command\Handler\Locator\DirectBindingCommandHandlerLocator;
use GpsLab\Component\Command\Command;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DirectBindingCommandHandlerLocatorTest extends TestCase
{
    /**
     * @var MockObject|Command
     */
    private $command;

    /**
     * @var callable
     */
    private $handler;

    /**
     * @var DirectBindingCommandHandlerLocator
     */
    private $locator;

    protected function setUp(): void
    {
        $this->command = $this->createMock(Command::class);
        $this->handler = function (Command $command) {
            $this->assertSame($command, $this->command);
        };
        $this->locator = new DirectBindingCommandHandlerLocator();
    }

    public function testFindHandler()
    {
        $this->locator->registerHandler(get_class($this->command), $this->handler);

        $handler = $this->locator->findHandler($this->command);
        $this->assertSame($this->handler, $handler);
    }

    public function testNoCommandHandler()
    {
        $this->locator->registerHandler('foo', $this->handler);

        $handler = $this->locator->findHandler($this->command);
        $this->assertNull($handler);
    }
}
