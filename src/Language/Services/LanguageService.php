<?php

declare(strict_types=1);

namespace UniversityMultilang\Language\Services;

use UniversityMultilang\Language\Contracts\LanguageRepositoryInterface;
use UniversityMultilang\Language\Domain\LanguageEntity;
use Exception;

class LanguageService
{
    public function __construct(
        private LanguageRepositoryInterface $repository
    ) {
    }

    /**
     * @return LanguageEntity[]
     */
    public function getAllLanguages(): array
    {
        return $this->repository->findAll();
    }

    public function getLanguageBySlug(string $slug): ?LanguageEntity
    {
        return $this->repository->findBySlug($slug);
    }

    public function getLanguageById(int $id): ?LanguageEntity
    {
        return $this->repository->findById($id);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws Exception
     */
    public function addLanguage(string $name, string $slug, string $locale): LanguageEntity
    {
        $this->validateInput($name, $slug, $locale);

        if ($this->getLanguageBySlug($slug) !== null) {
            throw new Exception("Language with slug '{$slug}' already exists.");
        }

        $language = new LanguageEntity(0, $name, $slug, $locale);
        return $this->repository->save($language);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws Exception
     */
    public function updateLanguage(int $id, string $name, string $slug, string $locale): LanguageEntity
    {
        $this->validateInput($name, $slug, $locale);

        $existingLanguage = $this->getLanguageById($id);
        if ($existingLanguage === null) {
            throw new Exception("Language with ID '{$id}' does not exist.");
        }

        // Check if slug is being changed and if it conflicts
        $slugConflict = $this->getLanguageBySlug($slug);
        if ($slugConflict !== null && $slugConflict->getId() !== $id) {
            throw new Exception("Another language with slug '{$slug}' already exists.");
        }

        $language = new LanguageEntity($id, $name, $slug, $locale);
        return $this->repository->save($language);
    }

    public function removeLanguage(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getLanguageSlugForObject(int $objectId, string $type): ?string
    {
        return $this->repository->getLanguageSlugForObject($objectId, $type);
    }

    public function setLanguageForObject(int $objectId, string $type, string $languageSlug): void
    {
        $this->repository->setLanguageForObject($objectId, $type, $languageSlug);
    }

    private function validateInput(string $name, string $slug, string $locale): void
    {
        if (trim($name) === '') {
            throw new \InvalidArgumentException("Language name cannot be empty.");
        }
        if (trim($slug) === '') {
            throw new \InvalidArgumentException("Language slug cannot be empty.");
        }
        if (trim($locale) === '') {
            throw new \InvalidArgumentException("Language locale cannot be empty.");
        }
    }
}
