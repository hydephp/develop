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

    public function testNestedIndexPageRouting()
    {
        if (! is_dir($this->projectPath('_pages/about'))) {
            mkdir($this->projectPath('_pages/about'), 0755, true);
        }

        file_put_contents($this->projectPath('_docs/index.md'), '# Documentation');
        file_put_contents($this->projectPath('_pages/about/index.md'), '# About');
        file_put_contents($this->projectPath('_pages/about/contact.md'), '# Contact');

        $this->get('/docs/index.html')->assertStatus(200)->assertSeeText('Documentation');
        $this->get('/about/index.html')->assertStatus(200)->assertSeeText('About');
        $this->get('/about/contact.html')->assertStatus(200)->assertSeeText('Contact');

        $this->get('/docs/index')->assertStatus(200)->assertSeeText('Documentation');
        $this->get('/about/index')->assertStatus(200)->assertSeeText('About');
        $this->get('/about/contact')->assertStatus(200)->assertSeeText('Contact');

        $this->get('/docs/')->assertStatus(200)->assertSeeText('Documentation');
        $this->get('/about/')->assertStatus(200)->assertSeeText('About');
        $this->get('/about/contact/')->assertStatus(200)->assertSeeText('Contact');

        $this->get('/docs')->assertStatus(200)->assertSeeText('Documentation');
        $this->get('/about')->assertStatus(200)->assertSeeText('About');
        $this->get('/about/contact')->assertStatus(200)->assertSeeText('Contact');

        unlink($this->projectPath('_docs/index.md'));
        unlink($this->projectPath('_pages/about/index.md'));
        unlink($this->projectPath('_pages/about/contact.md'));
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
