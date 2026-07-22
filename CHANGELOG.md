# Changelog

All notable changes to the **University Multilang** plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-07-22

### Added
- **Language Switcher Feature** (`Sprint 6.1`): Public PHP template tag `uml_language_switcher()`, shortcode `[uml_language_switcher]`, and native WordPress Widget (`LanguageSwitcherWidget`).
- **Translation Management UI** (`Sprint 6.2`): Admin post list table language columns (`edit.php`), status badges (`Publish`/`Draft`), `[Unlink]` action, and `wp_ajax_uml_link_existing_post` AJAX endpoint.
- **Language Routing Engine** (`Sprint 6.3`): Path-based language prefix routing (`/en/`, `/id/`), `RoutingGuardService` (REST API, Admin, Previews, SEO files), `RoutingContextService` (Single Source of Truth), and `term_link` / `post_type_archive_link` filter hooks.
- **Settings Page & Configuration Abstraction** (`Sprint 6.4`): `SettingsRepositoryInterface` & `WpSettingsRepository`, `SettingsService`, and admin options tab for General, Routing, Detection, and Developer/SEO settings.
- **Automatic Translation Provider Interface** (`Sprint 6.5`): Extensible provider hierarchy (`ContentTranslatorInterface`, `NullTranslator`, `GoogleTranslateProvider`, `DeepLTranslateProvider`), and `TranslationProviderFactory`.
- **Elementor Integration** (`Sprint 6.6`): `ElementorJsonWalker` recursive JSON tree parser, `ElementorDataService` metadata cloner, `ElementorTemplateManager` Pro Theme Builder location filter, and `LanguageSwitcherWidget` Elementor controls.
- **Release Candidate Hardening** (`Sprint 6.7`): `uninstall.php` secure data cleaner, official WordPress.org `readme.txt`, `Activator` & `Deactivator` with `flush_rewrite_rules()`, and textdomain loading on `plugins_loaded`.
