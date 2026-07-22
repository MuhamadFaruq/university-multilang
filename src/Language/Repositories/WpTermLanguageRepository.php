<?php

declare(strict_types=1);

namespace UniversityMultilang\Language\Repositories;

use UniversityMultilang\Language\Contracts\LanguageRepositoryInterface;
use UniversityMultilang\Language\Domain\LanguageEntity;

class WpTermLanguageRepository implements LanguageRepositoryInterface
{
    public const TAXONOMY = 'language';
    public const CACHE_GROUP = 'uml_language_cache';
    public const CACHE_KEY_ALL_LANGS = 'uml_all_languages';

    /**
     * @return LanguageEntity[]
     */
    public function findAll(): array
    {
        $languages = wp_cache_get(self::CACHE_KEY_ALL_LANGS, self::CACHE_GROUP);

        if (false === $languages) {
            $terms = get_terms([
                'taxonomy'   => self::TAXONOMY,
                'hide_empty' => false,
                'orderby'    => 'term_id',
                'order'      => 'ASC',
            ]);

            $languages = [];
            if (!is_wp_error($terms) && is_array($terms)) {
                foreach ($terms as $term) {
                    $locale = (string) get_term_meta($term->term_id, 'locale', true);
                    $languages[] = new LanguageEntity(
                        (int) $term->term_id,
                        $term->name,
                        $term->slug,
                        $locale
                    );
                }
            }

            wp_cache_set(self::CACHE_KEY_ALL_LANGS, $languages, self::CACHE_GROUP);
        }

        return $languages;
    }

    public function findBySlug(string $slug): ?LanguageEntity
    {
        $languages = $this->findAll();
        foreach ($languages as $lang) {
            if ($lang->getSlug() === $slug) {
                return $lang;
            }
        }
        return null;
    }

    public function findById(int $id): ?LanguageEntity
    {
        $languages = $this->findAll();
        foreach ($languages as $lang) {
            if ($lang->getId() === $id) {
                return $lang;
            }
        }
        return null;
    }

    /**
     * @throws \RuntimeException
     */
    public function save(LanguageEntity $language): LanguageEntity
    {
        $termId = $language->getId();

        if ($termId === 0) {
            // Insert
            $slug = $language->getSlug();

            if (empty($slug)) {
                throw new \RuntimeException("Failed to insert language: Slug cannot be empty.");
            }

            if (term_exists($slug, self::TAXONOMY) !== null) {
                throw new \RuntimeException("Failed to insert language: A term with the slug provided already exists.");
            }

            $result = wp_insert_term($language->getName(), self::TAXONOMY, [
                'slug' => $slug,
            ]);

            if (is_wp_error($result)) {
                throw new \RuntimeException("Failed to insert language: " . $result->get_error_message());
            }
            $termId = (int) $result['term_id'];
        } else {
            // Update
            $result = wp_update_term($termId, self::TAXONOMY, [
                'name' => $language->getName(),
                'slug' => $language->getSlug(),
            ]);

            if (is_wp_error($result)) {
                throw new \RuntimeException("Failed to update language: " . $result->get_error_message());
            }
        }

        if ($termId > 0 && !empty($language->getLocale())) {
            update_term_meta($termId, 'locale', $language->getLocale());
        }

        wp_cache_delete(self::CACHE_KEY_ALL_LANGS, self::CACHE_GROUP);

        return new LanguageEntity(
            $termId,
            $language->getName(),
            $language->getSlug(),
            $language->getLocale()
        );
    }

    /**
     * @throws \RuntimeException
     */
    public function delete(int $id): bool
    {
        $result = wp_delete_term($id, self::TAXONOMY);

        if (is_wp_error($result)) {
            throw new \RuntimeException("Failed to delete language: " . $result->get_error_message());
        }

        if ($result !== false) {
            wp_cache_delete(self::CACHE_KEY_ALL_LANGS, self::CACHE_GROUP);
            return true;
        }

        return false;
    }

    public function getLanguageSlugForObject(int $objectId, string $type): ?string
    {
        if ($type === 'post') {
            $terms = wp_get_object_terms($objectId, self::TAXONOMY);
            if (!empty($terms) && !is_wp_error($terms)) {
                return $terms[0]->slug;
            }
        } else {
            return get_term_meta($objectId, 'language', true) ?: null;
        }

        return null;
    }

    /**
     * @throws \RuntimeException
     */
    public function setLanguageForObject(int $objectId, string $type, string $languageSlug): void
    {
        if ($type === 'post') {
            $result = wp_set_object_terms($objectId, $languageSlug, self::TAXONOMY, false);
            if (is_wp_error($result)) {
                throw new \RuntimeException("Failed to set language '{$languageSlug}' for post ID {$objectId}: " . $result->get_error_message());
            }
        } else {
            $result = update_term_meta($objectId, 'language', $languageSlug);
            if ($result === false) {
                $current = get_term_meta($objectId, 'language', true);
                if ($current !== $languageSlug) {
                    throw new \RuntimeException("Failed to set language '{$languageSlug}' for term ID {$objectId}");
                }
            }
        }
    }
}
