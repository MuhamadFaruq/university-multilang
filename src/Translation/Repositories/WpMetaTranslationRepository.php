<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Repositories;

use UniversityMultilang\Translation\Contracts\TranslationRepositoryInterface;
use UniversityMultilang\Translation\Domain\TranslationGroupEntity;
use UniversityMultilang\Language\Contracts\LanguageRepositoryInterface;

class WpMetaTranslationRepository implements TranslationRepositoryInterface
{
    public const META_GROUP_ID = '_uml_translation_group_id';
    public const CACHE_GROUP = 'uml_translation_cache';

    public function __construct(
        private LanguageRepositoryInterface $languageRepository
    ) {
    }

    public function getGroupByObjectId(int $objectId, string $type): ?TranslationGroupEntity
    {
        $metaType = $type === 'term' ? 'term' : 'post';
        $groupId = get_metadata($metaType, $objectId, self::META_GROUP_ID, true);

        if (empty($groupId)) {
            return null;
        }

        global $wpdb;
        $table = $metaType === 'term' ? $wpdb->termmeta : $wpdb->postmeta;
        $column = $metaType === 'term' ? 'term_id' : 'post_id';

        $objectIds = $wpdb->get_col($wpdb->prepare(
            "SELECT {$column} FROM {$table} WHERE meta_key = %s AND meta_value = %s",
            self::META_GROUP_ID,
            $groupId
        ));

        $translations = [];
        foreach ($objectIds as $id) {
            $langSlug = $this->languageRepository->getLanguageSlugForObject((int) $id, $type);
            if ($langSlug) {
                $translations[$langSlug] = (int) $id;
            }
        }

        return new TranslationGroupEntity((string) $groupId, $type, $translations);
    }

    /**
     * @throws \RuntimeException
     */
    public function saveGroup(TranslationGroupEntity $group): void
    {
        $metaType = $group->getType() === 'term' ? 'term' : 'post';

        foreach ($group->getTranslations() as $langSlug => $objectId) {
            $result = update_metadata($metaType, $objectId, self::META_GROUP_ID, $group->getGroupId());
            if ($result === false) {
                // If the value is the same, WP returns false. We only throw if there's a real failure,
                // but WP doesn't distinguish well. Checking current value mitigates this.
                $current = get_metadata($metaType, $objectId, self::META_GROUP_ID, true);
                if ($current !== $group->getGroupId()) {
                    throw new \RuntimeException("Failed to save translation group metadata for object ID {$objectId}");
                }
            }
            wp_cache_delete('uml_translations_' . $objectId, self::CACHE_GROUP);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function removeFromGroup(int $objectId, string $type): void
    {
        $metaType = $type === 'term' ? 'term' : 'post';

        $current = get_metadata($metaType, $objectId, self::META_GROUP_ID, true);
        if (!empty($current)) {
            $result = delete_metadata($metaType, $objectId, self::META_GROUP_ID);
            if ($result === false) {
                throw new \RuntimeException("Failed to remove translation group metadata for object ID {$objectId}");
            }
        }

        wp_cache_delete('uml_translations_' . $objectId, self::CACHE_GROUP);
    }
}
