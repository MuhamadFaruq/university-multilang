<?php

declare(strict_types=1);

namespace UniversityMultilang\Language\Contracts;

use UniversityMultilang\Language\Domain\LanguageEntity;

interface LanguageRepositoryInterface
{
    /**
     * Get all languages.
     *
     * @return LanguageEntity[]
     */
    public function findAll(): array;

    /**
     * Find a language by slug.
     */
    public function findBySlug(string $slug): ?LanguageEntity;

    /**
     * Find a language by ID.
     */
    public function findById(int $id): ?LanguageEntity;

    /**
     * Save a language entity (insert or update).
     *
     * @return LanguageEntity The saved language with populated ID.
     */
    public function save(LanguageEntity $language): LanguageEntity;

    /**
     * Delete a language by ID.
     */
    public function delete(int $id): bool;

    /**
     * Get the language slug associated with a specific object (post or term).
     */
    public function getLanguageSlugForObject(int $objectId, string $type): ?string;

    /**
     * Set the language for a specific object (post or term).
     *
     * @param int $objectId
     * @param string $type
     * @param string $languageSlug
     * @throws \RuntimeException
     */
    public function setLanguageForObject(int $objectId, string $type, string $languageSlug): void;
}
