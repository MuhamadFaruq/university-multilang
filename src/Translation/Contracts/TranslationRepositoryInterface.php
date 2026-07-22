<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Contracts;

use UniversityMultilang\Translation\Domain\TranslationGroupEntity;

interface TranslationRepositoryInterface
{
    /**
     * Get the translation group for a specific object (post or term).
     *
     * @param int $objectId
     * @param string $type
     * @return TranslationGroupEntity|null
     */
    public function getGroupByObjectId(int $objectId, string $type): ?TranslationGroupEntity;

    /**
     * Save the entire translation group.
     *
     * @param TranslationGroupEntity $group
     * @throws \RuntimeException If the save operation fails.
     */
    public function saveGroup(TranslationGroupEntity $group): void;

    /**
     * Remove an object from its translation group.
     *
     * @param int $objectId
     * @param string $type
     * @throws \RuntimeException If the remove operation fails.
     */
    public function removeFromGroup(int $objectId, string $type): void;
}
