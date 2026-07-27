<?php

declare(strict_types=1);

namespace UniversityMultilang\Router\DTOs;

class RequestContext
{
    private string $rawUri;
    private string $path;

    public function __construct(string $rawUri)
    {
        $this->rawUri = $rawUri;
        $path = parse_url($rawUri, PHP_URL_PATH) ?: '';

        if (function_exists('home_url') || function_exists(__NAMESPACE__ . '\\home_url')) {
            $homePath = parse_url(home_url('/'), PHP_URL_PATH);
            $basePath = rtrim((string) $homePath, '/');
            if (!empty($basePath) && strpos($path, $basePath) === 0) {
                $path = substr($path, strlen($basePath));
            }
        }

        $this->path = ltrim($path, '/');
    }

    public function getRawUri(): string
    {
        return $this->rawUri;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
