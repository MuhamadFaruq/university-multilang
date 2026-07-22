<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Contracts;

interface PostRepositoryInterface
{
    /**
     * Insert a new post.
     *
     * @param array $postData Array of post data.
     * @return int The ID of the newly created post.
     * @throws \RuntimeException If post creation fails.
     */
    public function insertPost(array $postData): int;

    /**
     * Get all post meta for a given post ID.
     *
     * @param int $postId
     * @return array
     */
    public function getPostMeta(int $postId): array;

    /**
     * Delete a specific post meta.
     *
     * @param int $postId
     * @param string $metaKey
     * @return void
     */
    public function deletePostMeta(int $postId, string $metaKey): void;

    /**
     * Add post meta.
     *
     * @param int $postId
     * @param string $metaKey
     * @param mixed $metaValue
     * @return void
     */
    public function addPostMeta(int $postId, string $metaKey, $metaValue): void;

    /**
     * Get all taxonomies associated with a post type.
     *
     * @param string $postType
     * @return string[] Array of taxonomy names.
     */
    public function getPostTaxonomies(string $postType): array;

    /**
     * Get terms associated with a post for a specific taxonomy.
     *
     * @param int $postId
     * @param string $taxonomy
     * @return \WP_Term[]
     */
    public function getObjectTerms(int $postId, string $taxonomy): array;

    /**
     * Set object terms for a post.
     *
     * @param int $postId
     * @param int[] $termIds
     * @param string $taxonomy
     * @return void
     * @throws \RuntimeException If setting terms fails.
     */
    public function setObjectTerms(int $postId, array $termIds, string $taxonomy): void;
}
