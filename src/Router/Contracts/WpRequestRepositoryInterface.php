<?php

declare(strict_types=1);

namespace UniversityMultilang\Router\Contracts;

interface WpRequestRepositoryInterface
{
    public function getRequestUri(): string;

    public function setRequestUri(string $uri): void;

    public function isAdmin(): bool;

    public function redirect(string $url, int $status = 301): void;
}
