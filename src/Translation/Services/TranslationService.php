<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Services;

use UniversityMultilang\Translation\Contracts\TranslationRepositoryInterface;
use UniversityMultilang\Translation\Domain\TranslationGroupEntity;
use UniversityMultilang\Language\Services\LanguageService;
use Exception;

class TranslationService
{
    public function __construct(
        private TranslationRepositoryInterface $repository,
        private LanguageService $languageService
    ) {
    }

    public function getTranslations(int $objectId, string $type = 'post'): array
    {
        $group = $this->repository->getGroupByObjectId($objectId, $type);
        if ($group !== null) {
            return $group->getTranslations();
        }

        return [];
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \DomainException
     * @throws Exception
     */
    public function linkTranslations(int $sourceId, int $targetId, string $targetLanguageSlug, string $type = 'post'): void
    {
        if ($sourceId === $targetId) {
            throw new \InvalidArgumentException("Source ID and Target ID cannot be the same.");
        }

        $language = $this->languageService->getLanguageBySlug($targetLanguageSlug);
        if ($language === null) {
            throw new \InvalidArgumentException("Invalid language slug provided: '{$targetLanguageSlug}'.");
        }

        $group = $this->repository->getGroupByObjectId($sourceId, $type);

        if ($group === null) {
            $groupId = wp_generate_uuid4();
            $group = new TranslationGroupEntity($groupId, $type);

            // To form a valid group, we MUST add the source object too.
            $sourceLanguageSlug = $this->languageService->getLanguageSlugForObject($sourceId, $type);
            if ($sourceLanguageSlug === null) {
                throw new \DomainException("Source object ID {$sourceId} must have an assigned language before it can be translated.");
            }
            $group->addTranslation($sourceLanguageSlug, $sourceId);
        }

        // Validate that target is not already in another group
        $targetGroup = $this->repository->getGroupByObjectId($targetId, $type);
        if ($targetGroup !== null && $targetGroup->getGroupId() !== $group->getGroupId()) {
            throw new \DomainException("Target object ID {$targetId} is already part of another translation group.");
        }

        $group->addTranslation($targetLanguageSlug, $targetId);
        $this->repository->saveGroup($group);
    }

    public function unlinkTranslation(int $objectId, string $type = 'post'): void
    {
        if ($objectId <= 0) {
            throw new \InvalidArgumentException("Invalid object ID.");
        }
        $this->repository->removeFromGroup($objectId, $type);
    }
}
