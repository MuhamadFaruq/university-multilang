<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Domain;

class TranslationGroupEntity
{
    /**
     * @param string $groupId
     * @param string $type The object type, e.g., 'post' or 'term'
     * @param array<string, int> $translations Associative array of [language_slug => object_id]
     */
    public function __construct(
        private string $groupId,
        private string $type,
        private array $translations = []
    ) {
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, int>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function hasTranslation(string $languageSlug): bool
    {
        return isset($this->translations[$languageSlug]);
    }

    public function getTranslationId(string $languageSlug): ?int
    {
        return $this->translations[$languageSlug] ?? null;
    }

    /**
     * @throws \DomainException
     */
    public function addTranslation(string $languageSlug, int $objectId): void
    {
        if (isset($this->translations[$languageSlug])) {
            throw new \DomainException("A translation for language '{$languageSlug}' already exists in this group. Please remove the existing translation first.");
        }
        $this->translations[$languageSlug] = $objectId;
    }

    public function removeTranslation(string $languageSlug): void
    {
        unset($this->translations[$languageSlug]);
    }
}
