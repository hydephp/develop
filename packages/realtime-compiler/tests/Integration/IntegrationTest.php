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

    public function testDynamicDocumentationSearchPages()
    {
        file_put_contents($this->projectPath('_docs/index.md'), '# Documentation');
        file_put_contents($this->projectPath('_docs/installation.md'), '# Installation');

        $this->get('/docs/search')
            ->assertStatus(200)
            ->assertSeeText('Search the documentation site');

        $this->get('/docs/search.json')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson([
                [
                    'slug' => 'index',
                    'title' => 'Documentation',
                    'content' => 'Documentation',
                    'destination' => 'index.html',
                ],
                [
                    'slug' => 'installation',
                    'title' => 'Installation',
                    'content' => 'Installation',
                    'destination' => 'installation.html',
                ],
            ]);

        unlink($this->projectPath('_docs/index.md'));
        unlink($this->projectPath('_docs/installation.md'));
    }
}
