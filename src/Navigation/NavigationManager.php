<?php

declare(strict_types=1);

namespace UniversityMultilang\Navigation;

use UniversityMultilang\Router\RequestProcessor;

class NavigationManager
{
    public const OPTION_KEY = 'uml_nav_menu_locations';

    private RequestProcessor $requestProcessor;

    public function __construct(RequestProcessor $requestProcessor)
    {
        $this->requestProcessor = $requestProcessor;
    }

    /**
     * Hooked to 'theme_mod_nav_menu_locations'
     * Swaps the menu IDs based on current language.
     * 
     * @param array|false $locations
     * @return array|false
     */
    public function filterNavMenuLocations($locations)
    {
        if (!is_array($locations)) {
            return $locations;
        }

        $currentLang = $this->requestProcessor->getCurrentLanguage();
        if (empty($currentLang)) {
            return $locations;
        }

        $mappings = get_option(self::OPTION_KEY, []);
        if (empty($mappings) || !is_array($mappings)) {
            return $locations;
        }

        foreach ($locations as $location => $defaultMenuId) {
            if (isset($mappings[$location][$currentLang]) && !empty($mappings[$location][$currentLang])) {
                $locations[$location] = (int) $mappings[$location][$currentLang];
            }
        }

        return $locations;
    }

    /**
     * Get saved mappings.
     * 
     * @return array
     */
    public function getMappings(): array
    {
        return get_option(self::OPTION_KEY, []);
    }

    /**
     * Save mappings.
     * 
     * @param array $mappings
     */
    public function saveMappings(array $mappings): void
    {
        update_option(self::OPTION_KEY, $mappings);
    }
}
