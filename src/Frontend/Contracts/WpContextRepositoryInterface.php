<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend\Contracts;

interface WpContextRepositoryInterface
{
    public function isSingular(): bool;

    public function isFrontPage(): bool;

    public function getQueriedObjectId(): int;

    public function getPermalink(int $postId): string;

    public function getHomeUrl(string $path = '/'): string;

    public function getPostStatus(int $postId): string;
}
