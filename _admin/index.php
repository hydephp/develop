<?php

declare(strict_types=1);

/**
 * The HydePHP CMS Admin Panel Router
 *
 * This file is the main entry point for the HydePHP CMS Admin Panel.
 *
 * It serves as a simple router for the static admin panel, allowing us to inject
 * HydePHP configuration data into the admin panel's configuration data.
 *
 * To start the admin server, run the following command from your project root:
 * > php -S localhost:3000 /_admin/index.php # The port can be changed
 *
 * Next, visit http://localhost:3000/admin in your web browser.
 */

(new AdminRouter())->handle(rtrim($_SERVER['REQUEST_URI'] ?? '/', '/') ?: '/');

class AdminRouter
{
    const ROUTES = [
        '/' => [AdminController::class, 'redirectToIndex'],
        '/admin' => [AdminController::class, 'index'],
    ];

    public function handle(string $uri): void
    {
        if (isset(self::ROUTES[$uri])) {
            [$controller, $method] = self::ROUTES[$uri];

            echo (new $controller())->$method();
        } else {
            exit(http_response_code(404));
        }
    }
}

class AdminController
{
    public function redirectToIndex(): void
    {
        header('Location: /admin');
    }

    public function index(): string
    {
        return file_get_contents(__DIR__ . '/index.html');
    }
}
