<?php

namespace Quantum\Services {

    use Quantum\Service\QtService;

    class TestService extends QtService
    {

        public static $count = 0;

        public function __construct()
        {
            self::$count++;
        }

        public function hello()
        {
            return 'Hello';
        }
    }
}

namespace Quantum\Tests\Unit\Service\Factories {

    use Quantum\Service\Exceptions\ServiceException;
    use Quantum\Service\Factories\ServiceFactory;
    use Quantum\Tests\Unit\AppTestCase;
    use Quantum\Services\TestService;
    use Quantum\Service\QtService;

    class ServiceFactoryTest extends AppTestCase
    {

        public function setUp(): void
        {
            parent::setUp();
        }

        public function tearDown(): void
        {
            TestService::$count = 0;
        }

        public function testServiceFactoryGetInstance()
        {
            $service = ServiceFactory::get(TestService::class);

            $this->assertInstanceOf(QtService::class, $service);

            $this->assertInstanceOf(TestService::class, $service);
        }

        public function testServiceFactoryVerifySingletonInstance()
        {
            $testServiceOne = ServiceFactory::get(TestService::class);

            $this->assertEquals(0, TestService::$count);

            $testServiceTwo = ServiceFactory::get(TestService::class);

            $this->assertEquals(0, TestService::$count);

            $this->assertSame($testServiceOne, $testServiceTwo);
        }

        public function testServiceFactoryCreateInstance()
        {
            $service = ServiceFactory::create(TestService::class);

            $this->assertInstanceOf(QtService::class, $service);

            $this->assertInstanceOf(TestService::class, $service);
        }

        public function testServiceFactoryVerifyFreshInstance()
        {
            $testServiceOne = ServiceFactory::create(TestService::class);

            $this->assertEquals(1, TestService::$count);

            $testServiceTwo = ServiceFactory::create(TestService::class);

            $this->assertEquals(2, TestService::$count);

            $this->assertNotSame($testServiceOne, $testServiceTwo);
        }

        public function testServiceFactoryServiceMethodCall()
        {
            $this->assertEquals('Hello', ServiceFactory::get(TestService::class)->hello());

            $this->assertEquals('Hello', ServiceFactory::create(TestService::class)->hello());
        }

        public function testServiceFactoryServiceNotFound()
        {
            $this->expectException(ServiceException::class);

            $this->expectExceptionMessage('service_not_found');

            ServiceFactory::get(\NonExistentClass::class);
        }
    }
}