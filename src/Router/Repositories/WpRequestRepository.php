<?php

declare(strict_types=1);

namespace UniversityMultilang\Router\Repositories;

use UniversityMultilang\Router\Contracts\WpRequestRepositoryInterface;

class WpRequestRepository implements WpRequestRepositoryInterface
{
    public function getRequestUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '';
    }

    public function setRequestUri(string $uri): void
    {
        $_SERVER['REQUEST_URI'] = $uri;
    }

    public function isAdmin(): bool
    {
        return is_admin();
    }

    public function redirect(string $url, int $status = 301): void
    {
        wp_redirect($url, $status);
        exit;
    }
}
