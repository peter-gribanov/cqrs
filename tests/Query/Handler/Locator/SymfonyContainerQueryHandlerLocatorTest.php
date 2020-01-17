<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Tests\Query\Handler\Locator;

use GpsLab\Component\Query\Handler\Locator\SymfonyContainerQueryHandlerLocator;
use GpsLab\Component\Query\Query;
use GpsLab\Component\Tests\Fixture\Query\ContactByNameQuery;
use GpsLab\Component\Tests\Fixture\Query\Handler\ContactByNameHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SymfonyContainerQueryHandlerLocatorTest extends TestCase
{
    /**
     * @var MockObject|ContainerInterface
     */
    private $container;

    /**
     * @var MockObject|Query
     */
    private $query;

    /**
     * @var callable
     */
    private $handler;

    /**
     * @var SymfonyContainerQueryHandlerLocator
     */
    private $locator;

    protected function setUp(): void
    {
        $this->query = $this->createMock(Query::class);
        $this->handler = function (Query $query): void {
            $this->assertEquals($query, $this->query);
        };
        $this->container = $this->createMock(ContainerInterface::class);
        $this->locator = new SymfonyContainerQueryHandlerLocator();
    }

    public function testFindHandler(): void
    {
        $this->locator->setContainer($this->container);
        $service = 'foo';

        $this->container
            ->expects($this->exactly(2))
            ->method('get')
            ->with($service)
            ->will($this->returnValue($this->handler))
        ;

        $this->locator->registerService(get_class($this->query), $service);

        $handler = $this->locator->findHandler($this->query);
        $this->assertEquals($this->handler, $handler);

        // double call ContainerInterface::get()
        $handler = $this->locator->findHandler($this->query);
        $this->assertEquals($this->handler, $handler);
    }

    public function testFindHandlerServiceInvoke(): void
    {
        $this->locator->setContainer($this->container);
        $service = 'foo';
        $query = new ContactByNameQuery();
        $handler_obj = new ContactByNameHandler();
        $method = 'handleContactByName';

        $this->container
            ->expects($this->exactly(2))
            ->method('get')
            ->with($service)
            ->will($this->returnValue($handler_obj))
        ;

        $this->locator->registerService(ContactByNameQuery::class, $service, $method);

        $handler = $this->locator->findHandler($query);
        $this->assertEquals([$handler_obj, $method], $handler);

        // double call ContainerInterface::get()
        $handler = $this->locator->findHandler($query);
        $this->assertEquals([$handler_obj, $method], $handler);

        // test exec handler
        $handler($query);
        $this->assertEquals($query, $handler_obj->query());
    }

    public function testNoQueryHandler(): void
    {
        $this->locator->setContainer($this->container);
        $service = 'foo';

        $this->container
            ->expects($this->exactly(1))
            ->method('get')
            ->with($service)
            ->will($this->returnValue(null))
        ;

        $this->locator->registerService(get_class($this->query), $service);

        $handler = $this->locator->findHandler($this->query);
        $this->assertNull($handler);
    }

    public function testHandlerIsNotAQueryHandler(): void
    {
        $this->locator->setContainer($this->container);
        $service = 'foo';

        $this->container
            ->expects($this->exactly(1))
            ->method('get')
            ->with($service)
            ->will($this->returnValue(new \stdClass()))
        ;

        $this->locator->registerService(get_class($this->query), $service);

        $handler = $this->locator->findHandler($this->query);
        $this->assertNull($handler);
    }

    public function testNoAnyCommandHandler(): void
    {
        $this->locator->setContainer($this->container);
        $handler = $this->locator->findHandler($this->query);
        $this->assertNull($handler);
    }

    public function testNoContainer(): void
    {
        $service = 'foo';

        $this->locator->registerService(get_class($this->query), $service);

        $handler = $this->locator->findHandler($this->query);
        $this->assertNull($handler);
    }
}
