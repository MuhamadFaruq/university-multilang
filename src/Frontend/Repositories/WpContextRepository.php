<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend\Repositories;

use UniversityMultilang\Frontend\Contracts\WpContextRepositoryInterface;

class WpContextRepository implements WpContextRepositoryInterface
{
    public function isSingular(): bool
    {
        return is_singular();
    }

    public function isFrontPage(): bool
    {
        return is_front_page() || is_home();
    }

    public function getQueriedObjectId(): int
    {
        return (int) get_queried_object_id();
    }

    public function getPermalink(int $postId): string
    {
        $url = get_permalink($postId);
        return $url !== false ? $url : '';
    }

    public function getHomeUrl(string $path = '/'): string
    {
        return home_url($path);
    }

    public function getPostStatus(int $postId): string
    {
        $status = get_post_status($postId);
        return $status !== false ? $status : '';
    }
}
