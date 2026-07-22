<?php

declare(strict_types=1);

namespace UniversityMultilang\Language\Domain;

class LanguageEntity
{
    public function __construct(
        private int $id,
        private string $name,
        private string $slug,
        private string $locale
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
