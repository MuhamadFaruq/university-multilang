<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Repositories;

use UniversityMultilang\Translation\Contracts\PostRepositoryInterface;

class WpPostRepository implements PostRepositoryInterface
{
    public function insertPost(array $postData): int
    {
        $result = wp_insert_post($postData, true);

        if (is_wp_error($result)) {
            throw new \RuntimeException("Failed to insert post: " . $result->get_error_message());
        }

        return $result;
    }

    public function getPostMeta(int $postId): array
    {
        $meta = get_post_meta($postId);
        return is_array($meta) ? $meta : [];
    }

    public function deletePostMeta(int $postId, string $metaKey): void
    {
        delete_post_meta($postId, $metaKey);
    }

    public function addPostMeta(int $postId, string $metaKey, $metaValue): void
    {
        add_post_meta($postId, $metaKey, $metaValue);
    }

    public function getPostTaxonomies(string $postType): array
    {
        return get_object_taxonomies($postType);
    }

    public function getObjectTerms(int $postId, string $taxonomy): array
    {
        $terms = wp_get_object_terms($postId, $taxonomy);
        if (is_wp_error($terms)) {
            return [];
        }
        return $terms;
    }

    public function setObjectTerms(int $postId, array $termIds, string $taxonomy): void
    {
        $result = wp_set_object_terms($postId, $termIds, $taxonomy, false);
        if (is_wp_error($result)) {
            throw new \RuntimeException("Failed to set object terms for post {$postId} in taxonomy {$taxonomy}: " . $result->get_error_message());
        }
    }
}
