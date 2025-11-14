<?php
/**
 * Parser Interface
 *
 * Contract for all framework-specific parsers that convert framework markup
 * into universal component models.
 *
 * @package WordPress_Bootstrap_Claude
 * @subpackage Translation_Bridge
 * @since 3.0.0
 */

namespace WPBC\TranslationBridge\Core;

use WPBC\TranslationBridge\Models\WPBC_Component;

/**
 * Interface WPBC_Parser_Interface
 *
 * Defines methods that all framework parsers must implement.
 */
interface WPBC_Parser_Interface {

	/**
	 * Parse framework-specific content into universal components
	 *
	 * @param string|array $content Framework-specific content (HTML, JSON, shortcodes, etc.).
	 * @return WPBC_Component[] Array of parsed components.
	 */
	public function parse( $content ): array;

	/**
	 * Get framework name this parser handles
	 *
	 * @return string Framework name (bootstrap, divi, elementor, avada, bricks).
	 */
	public function get_framework(): string;

	/**
	 * Validate that content is valid for this framework
	 *
	 * @param string|array $content Content to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public function is_valid_content( $content ): bool;

	/**
	 * Get supported component types for this framework
	 *
	 * @return array<string> Array of supported component type names.
	 */
	public function get_supported_types(): array;

	/**
	 * Parse single component/element
	 *
	 * @param mixed $element Single framework element to parse.
	 * @return WPBC_Component|null Parsed component or null if invalid.
	 */
	public function parse_element( $element ): ?WPBC_Component;
}
