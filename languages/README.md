# DevelopmentTranslation Bridge - Translation Guide

Thank you for helping translate DevelopmentTranslation Bridge!

## Translation Files

This directory contains translation files for DevelopmentTranslation Bridge:

- **devtb.pot** - Translation template file (all translatable strings)
- **devtb-{locale}.po** - Translation files for specific languages
- **devtb-{locale}.mo** - Compiled translation files (machine-readable)

## How to Translate

### Option 1: Using Poedit (Recommended)

1. Download and install [Poedit](https://poedit.net/)
2. Open `devtb.pot` in Poedit
3. Choose "Create New Translation" from the Catalog menu
4. Select your language
5. Translate all strings
6. Save the file as `devtb-{locale}.po` (e.g., `devtb-es_ES.po` for Spanish)
7. Poedit will automatically generate the `.mo` file

### Option 2: Manual Translation

1. Copy `devtb.pot` to `devtb-{locale}.po`
2. Edit the `.po` file with a text editor
3. Fill in the `msgstr` values for each `msgid`
4. Compile to `.mo` using `msgfmt`:
   ```bash
   msgfmt devtb-{locale}.po -o devtb-{locale}.mo
   ```

## Language Codes

Common locale codes:
- **en_US** - English (US)
- **es_ES** - Spanish (Spain)
- **fr_FR** - French (France)
- **de_DE** - German (Germany)
- **it_IT** - Italian (Italy)
- **pt_BR** - Portuguese (Brazil)
- **ja** - Japanese
- **zh_CN** - Chinese (Simplified)
- **ru_RU** - Russian

Full list: https://make.wordpress.org/polyglots/teams/

## Translation Context

DevelopmentTranslation Bridge is a theme that translates between page builder frameworks:
- Bootstrap, DIVI, Elementor, Avada, Bricks, WPBakery, Beaver Builder, Gutenberg, Oxygen, Claude AI

When translating:
- **Framework names** should generally remain in English
- **Technical terms** (Translation Bridge™, CLI, API) may remain in English or be adapted
- **UI elements** should be translated
- **Error messages** should be translated
- **Documentation text** should be translated

## Testing Your Translation

1. Place your `.po` and `.mo` files in this directory
2. Activate your language in WordPress (Settings > General > Site Language)
3. Visit the DevelopmentTranslation Bridge admin pages
4. Verify all strings display correctly

## Submitting Your Translation

1. Fork the repository
2. Add your translation files to `/languages/`
3. Test your translation
4. Create a pull request

## Need Help?

- **Issues**: https://github.com/coryhubbell/development-translation-bridge/issues
- **Documentation**: See the main README.md

## Translation Statistics

Current translations:
- English (en_US): 100% ✅ (Source language)
- Your translation: 0% - Help us reach 100%!

---

**Text Domain:** `devtb`
**Strings to translate:** ~78
**Last updated:** January 2025
