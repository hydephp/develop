<?php

namespace Hyde\RealtimeCompiler\Tests\Integration;

class IntegrationTest extends IntegrationTestCase
{
    public function testWelcome()
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSeeText("You're running on HydePHP");
    }

    public function test404()
    {
        $this->get('/non-existent-page')
            ->assertStatus(404)
            ->assertSeeText('RouteNotFoundException')
            ->assertSeeText('Route [non-existent-page] not found.');
    }
}
