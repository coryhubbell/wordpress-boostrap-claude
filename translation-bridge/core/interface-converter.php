<?php
/**
 * Converter Interface
 *
 * Contract for all framework-specific converters that transform universal
 * component models into framework-specific markup.
 *
 * @package WordPress_Bootstrap_Claude
 * @subpackage Translation_Bridge
 * @since 3.0.0
 */

namespace WPBC\TranslationBridge\Core;

use WPBC\TranslationBridge\Models\WPBC_Component;

/**
 * Interface WPBC_Converter_Interface
 *
 * Defines methods that all framework converters must implement.
 */
interface WPBC_Converter_Interface {

	/**
	 * Convert universal component(s) to framework-specific format
	 *
	 * @param WPBC_Component|WPBC_Component[] $component Component(s) to convert.
	 * @return string|array Framework-specific output (HTML, JSON, shortcodes, etc.).
	 */
	public function convert( $component );

	/**
	 * Get framework name this converter outputs
	 *
	 * @return string Framework name (bootstrap, divi, elementor, avada, bricks).
	 */
	public function get_framework(): string;

	/**
	 * Get supported component types this converter can handle
	 *
	 * @return array<string> Array of supported component type names.
	 */
	public function get_supported_types(): array;

	/**
	 * Check if component type is supported
	 *
	 * @param string $type Component type.
	 * @return bool True if supported, false otherwise.
	 */
	public function supports_type( string $type ): bool;

	/**
	 * Convert single component
	 *
	 * @param WPBC_Component $component Component to convert.
	 * @return string|array Framework-specific output for single component.
	 */
	public function convert_component( WPBC_Component $component );

	/**
	 * Get fallback conversion for unsupported component types
	 *
	 * @param WPBC_Component $component Unsupported component.
	 * @return string|array Fallback output.
	 */
	public function get_fallback( WPBC_Component $component );
}
