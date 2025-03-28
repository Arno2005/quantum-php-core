<?php

namespace Quantum\Services {

    use Quantum\Mvc\QtService;

    class TestService extends QtService
    {

        public static $count = 0;

        public function __init()
        {
            self::$count++;
        }

        public function hello()
        {
            return 'Hello';
        }

    }

}

namespace Quantum\Tests\Unit\Factory {

    use Quantum\Exceptions\ServiceException;
    use Quantum\Factory\ServiceFactory;
    use Quantum\Tests\Unit\AppTestCase;
    use Quantum\Services\TestService;
    use Quantum\Mvc\QtService;

    class ServiceFactoryTest extends AppTestCase
    {

        public function setUp(): void
        {
            parent::setUp();
        }

        public function tearDown(): void
        {
            TestService::$count = 0;
            ServiceFactory::reset();
        }

        public function testServiceGetInstance()
        {
            $service = ServiceFactory::get(TestService::class);

            $this->assertInstanceOf(QtService::class, $service);

            $this->assertInstanceOf(TestService::class, $service);
        }

        public function testServiceGetAndInit()
        {
            /* Calling 3 tiems to verify __init() method works only once */

            ServiceFactory::get(TestService::class);

            $this->assertEquals(1, TestService::$count);

            ServiceFactory::get(TestService::class);

            $this->assertEquals(1, TestService::$count);

            ServiceFactory::get(TestService::class);

            $this->assertEquals(1, TestService::$count);
        }

        public function testServiceCreateInstance()
        {
            $service = ServiceFactory::create(TestService::class);

            $this->assertInstanceOf(QtService::class, $service);

            $this->assertInstanceOf(TestService::class, $service);
        }

        public function testServiceCreateAndInit()
        {
            /* Calling 3 tiems to verify __init() method works each time */

            ServiceFactory::create(TestService::class);

            $this->assertEquals(1, TestService::$count);

            ServiceFactory::create(TestService::class);

            $this->assertEquals(2, TestService::$count);

            ServiceFactory::create(TestService::class);

            $this->assertEquals(3, TestService::$count);
        }

        public function testServiceMethodCall()
        {
            $this->assertEquals('Hello', ServiceFactory::get(TestService::class)->hello());

            $this->assertEquals('Hello', ServiceFactory::create(TestService::class)->hello());
        }

        public function testServiceNotFound()
        {
            $this->expectException(ServiceException::class);

            $this->expectExceptionMessage('service_not_found');

            ServiceFactory::get(\NonExistentClass::class);
        }

    }

}