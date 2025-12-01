<?php
/**
 * Component Attribute Model
 *
 * Represents a single attribute/property of a component with type information
 * and validation capabilities.
 *
 * @package DevelopmentTranslation_Bridge
 * @subpackage Translation_Bridge
 * @since 3.0.0
 */

namespace DEVTB\TranslationBridge\Models;

/**
 * Class DEVTB_Attribute
 *
 * Represents a component attribute with type safety and validation.
 */
class DEVTB_Attribute {

	/**
	 * Attribute name/key
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * Attribute value
	 *
	 * @var mixed
	 */
	public $value;

	/**
	 * Attribute type (text, number, boolean, url, color, etc.)
	 *
	 * @var string
	 */
	public string $type;

	/**
	 * Whether attribute is required
	 *
	 * @var bool
	 */
	public bool $required;

	/**
	 * Default value if not provided
	 *
	 * @var mixed
	 */
	public $default;

	/**
	 * Attribute constructor
	 *
	 * @param string $name Attribute name.
	 * @param mixed  $value Attribute value.
	 * @param string $type Attribute type.
	 * @param bool   $required Whether required.
	 * @param mixed  $default Default value.
	 */
	public function __construct(
		string $name,
		$value = null,
		string $type = 'text',
		bool $required = false,
		$default = null
	) {
		$this->name     = $name;
		$this->value    = $value ?? $default;
		$this->type     = $type;
		$this->required = $required;
		$this->default  = $default;
	}

	/**
	 * Validate attribute value based on type
	 *
	 * @return bool
	 */
	public function is_valid(): bool {
		// Required check
		if ( $this->required && $this->is_empty() ) {
			return false;
		}

		// Type validation
		switch ( $this->type ) {
			case 'text':
			case 'string':
				return is_string( $this->value ) || is_numeric( $this->value );

			case 'number':
			case 'int':
			case 'integer':
				return is_numeric( $this->value );

			case 'boolean':
			case 'bool':
				return is_bool( $this->value ) || in_array( $this->value, [ 'yes', 'no', '1', '0', 1, 0, true, false ], true );

			case 'url':
				return filter_var( $this->value, FILTER_VALIDATE_URL ) !== false;

			case 'email':
				return filter_var( $this->value, FILTER_VALIDATE_EMAIL ) !== false;

			case 'color':
				return $this->is_valid_color();

			case 'array':
				return is_array( $this->value );

			case 'object':
				return is_object( $this->value ) || is_array( $this->value );

			default:
				return true;
		}
	}

	/**
	 * Check if value is empty
	 *
	 * @return bool
	 */
	public function is_empty(): bool {
		return empty( $this->value ) && $this->value !== '0' && $this->value !== 0 && $this->value !== false;
	}

	/**
	 * Validate color value (hex, rgb, rgba, color names)
	 *
	 * @return bool
	 */
	private function is_valid_color(): bool {
		if ( ! is_string( $this->value ) ) {
			return false;
		}

		// Hex color
		if ( preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $this->value ) ) {
			return true;
		}

		// RGB/RGBA
		if ( preg_match( '/^rgba?\([\d\s,\.]+\)$/', $this->value ) ) {
			return true;
		}

		// Named colors (basic check)
		$color_names = [ 'red', 'blue', 'green', 'yellow', 'black', 'white', 'gray', 'transparent' ];
		if ( in_array( strtolower( $this->value ), $color_names, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get typed value
	 *
	 * @return mixed
	 */
	public function get_typed_value() {
		switch ( $this->type ) {
			case 'number':
			case 'int':
			case 'integer':
				return (int) $this->value;

			case 'float':
			case 'double':
				return (float) $this->value;

			case 'boolean':
			case 'bool':
				return $this->to_boolean();

			case 'array':
				return (array) $this->value;

			case 'string':
			case 'text':
				return (string) $this->value;

			default:
				return $this->value;
		}
	}

	/**
	 * Convert value to boolean
	 *
	 * @return bool
	 */
	private function to_boolean(): bool {
		if ( is_bool( $this->value ) ) {
			return $this->value;
		}

		if ( is_numeric( $this->value ) ) {
			return (bool) $this->value;
		}

		if ( is_string( $this->value ) ) {
			$lower = strtolower( trim( $this->value ) );
			return in_array( $lower, [ 'yes', 'true', '1', 'on' ], true );
		}

		return (bool) $this->value;
	}

	/**
	 * Convert to array
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return [
			'name'     => $this->name,
			'value'    => $this->value,
			'type'     => $this->type,
			'required' => $this->required,
			'default'  => $this->default,
		];
	}

	/**
	 * Create from array
	 *
	 * @param array<string, mixed> $data Attribute data.
	 * @return DEVTB_Attribute
	 */
	public static function from_array( array $data ): DEVTB_Attribute {
		return new self(
			$data['name'] ?? '',
			$data['value'] ?? null,
			$data['type'] ?? 'text',
			$data['required'] ?? false,
			$data['default'] ?? null
		);
	}

	/**
	 * Sanitize attribute value
	 *
	 * @return mixed
	 */
	public function sanitize() {
		switch ( $this->type ) {
			case 'text':
			case 'string':
				return sanitize_text_field( $this->value );

			case 'textarea':
				return sanitize_textarea_field( $this->value );

			case 'email':
				return sanitize_email( $this->value );

			case 'url':
				return esc_url_raw( $this->value );

			case 'color':
				return sanitize_hex_color( $this->value );

			case 'number':
			case 'int':
			case 'integer':
				return absint( $this->value );

			case 'float':
			case 'double':
				return floatval( $this->value );

			case 'boolean':
			case 'bool':
				return $this->to_boolean();

			default:
				return $this->value;
		}
	}
}
