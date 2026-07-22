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
