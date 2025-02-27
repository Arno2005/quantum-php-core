<?php

namespace Quantum\Tests\Unit\Libraries\ResourceCache;

use Quantum\Libraries\ResourceCache\ViewCache;
use Quantum\Tests\Unit\AppTestCase;

class ViewCacheTest extends AppTestCase
{
    private $viewCache;

    private $route = '/current-route';

    private $content = <<<HEREDOC
    <div>
        <span>Test html content</span>span>
    </div>
HEREDOC;

    public function setUp(): void
    {
        parent::setUp();

        $this->viewCache = ViewCache::getInstance();
        $this->viewCache->setup();
    }

    public function tearDown(): void
    {
        $this->viewCache->delete($this->route);
    }

    public function testSetAndGetViewCache()
    {
        $this->viewCache->set($this->route, $this->content);

        $viewCache = $this->viewCache->get($this->route);

        $this->assertEquals($this->content, $viewCache);
    }

    public function testViewCacheContentMinification()
    {

        $this->viewCache->enableMinification(true);

        $this->viewCache->set($this->route, $this->content);

        $content = $this->viewCache->get($this->route);

        $minifiedContent = '<div><span>Test html content</span>span> </div>';

        $this->assertEquals($minifiedContent, $content);
    }

    public function testExistsViewCache()
    {
        $this->viewCache->set($this->route, $this->content);

        $this->assertTrue($this->viewCache->exists($this->route));
    }

    public function testDeleteViewCache()
    {
        $this->viewCache->set($this->route, $this->content);

        $this->assertTrue($this->viewCache->exists($this->route));

        $this->viewCache->delete($this->route);

        $this->assertFalse($this->viewCache->exists($this->route));
    }

    public function testGetNonExistentViewCache()
    {
        $viewCache = $this->viewCache->get('/non-existent-route');

        $this->assertNull($viewCache);
    }

    public function testViewCacheIsExpired()
    {
        $this->viewCache->setTtl(1);

        $this->viewCache->set($this->route, $this->content);

        sleep(2);

        $viewCache = $this->viewCache->get($this->route);

        $this->assertNull($viewCache);
    }

    public function testEnableDisableViewCache()
    {
        $this->assertFalse($this->viewCache->isEnabled());

        $this->viewCache->enableCaching(true);

        $this->assertTrue($this->viewCache->isEnabled());

        $this->viewCache->enableCaching(false);

        $this->assertFalse($this->viewCache->isEnabled());
    }

}