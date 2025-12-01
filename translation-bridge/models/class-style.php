<?php
/**
 * Component Style Model
 *
 * Represents CSS styles and properties for components with conversion
 * and validation capabilities.
 *
 * @package DevelopmentTranslation_Bridge
 * @subpackage Translation_Bridge
 * @since 3.0.0
 */

namespace DEVTB\TranslationBridge\Models;

/**
 * Class DEVTB_Style
 *
 * Manages CSS styles for components with cross-framework compatibility.
 */
class DEVTB_Style {

	/**
	 * CSS property name
	 *
	 * @var string
	 */
	public string $property;

	/**
	 * CSS property value
	 *
	 * @var string|int|float
	 */
	public $value;

	/**
	 * Priority/specificity
	 *
	 * @var int
	 */
	public int $priority;

	/**
	 * Responsive breakpoint (mobile, tablet, desktop, or null for all)
	 *
	 * @var string|null
	 */
	public ?string $breakpoint;

	/**
	 * Style constructor
	 *
	 * @param string               $property CSS property.
	 * @param string|int|float     $value CSS value.
	 * @param int                  $priority Priority level.
	 * @param string|null          $breakpoint Responsive breakpoint.
	 */
	public function __construct(
		string $property,
		$value,
		int $priority = 10,
		?string $breakpoint = null
	) {
		$this->property   = $property;
		$this->value      = $value;
		$this->priority   = $priority;
		$this->breakpoint = $breakpoint;
	}

	/**
	 * Convert to CSS string
	 *
	 * @return string
	 */
	public function to_css(): string {
		return sprintf( '%s: %s;', $this->property, $this->value );
	}

	/**
	 * Convert property name to camelCase (for JavaScript/frameworks)
	 *
	 * @return string
	 */
	public function to_camel_case(): string {
		$parts = explode( '-', $this->property );
		$parts = array_map( 'ucfirst', $parts );
		$camel = implode( '', $parts );
		return lcfirst( $camel );
	}

	/**
	 * Convert property name to kebab-case (standard CSS)
	 *
	 * @return string
	 */
	public function to_kebab_case(): string {
		return strtolower( preg_replace( '/([a-z])([A-Z])/', '$1-$2', $this->property ) );
	}

	/**
	 * Convert to Bricks-style property name (underscore-prefixed camelCase)
	 *
	 * @return string
	 */
	public function to_bricks_property(): string {
		return '_' . $this->to_camel_case();
	}

	/**
	 * Get value with unit
	 *
	 * @param string $unit Unit to append (px, em, rem, %, etc.).
	 * @return string
	 */
	public function get_value_with_unit( string $unit = '' ): string {
		// If value already has a unit or is 0/auto/none, return as is
		if ( preg_match( '/^(0|auto|none|inherit|initial|unset)$/i', (string) $this->value ) ) {
			return (string) $this->value;
		}

		if ( preg_match( '/\d+(px|em|rem|%|vh|vw|pt|cm|mm|in)$/i', (string) $this->value ) ) {
			return (string) $this->value;
		}

		// Append unit if numeric
		if ( is_numeric( $this->value ) && ! empty( $unit ) ) {
			return $this->value . $unit;
		}

		return (string) $this->value;
	}

	/**
	 * Extract numeric value (strip unit)
	 *
	 * @return float|int
	 */
	public function get_numeric_value() {
		if ( is_numeric( $this->value ) ) {
			return $this->value;
		}

		// Extract number from string like "20px"
		if ( preg_match( '/^([\d\.]+)/', (string) $this->value, $matches ) ) {
			return floatval( $matches[1] );
		}

		return 0;
	}

	/**
	 * Extract unit from value
	 *
	 * @return string
	 */
	public function get_unit(): string {
		if ( preg_match( '/\d+(px|em|rem|%|vh|vw|pt|cm|mm|in)$/i', (string) $this->value, $matches ) ) {
			return $matches[1];
		}

		return '';
	}

	/**
	 * Convert value to different unit
	 *
	 * @param string $target_unit Target unit.
	 * @param int    $base_font_size Base font size for em/rem calculations (default 16px).
	 * @return DEVTB_Style
	 */
	public function convert_unit( string $target_unit, int $base_font_size = 16 ): DEVTB_Style {
		$current_unit = $this->get_unit();
		$numeric      = $this->get_numeric_value();

		if ( empty( $current_unit ) || $current_unit === $target_unit ) {
			return $this;
		}

		// Convert to px first
		switch ( $current_unit ) {
			case 'em':
			case 'rem':
				$px_value = $numeric * $base_font_size;
				break;

			case 'pt':
				$px_value = $numeric * 1.3333;
				break;

			case 'cm':
				$px_value = $numeric * 37.795;
				break;

			case 'mm':
				$px_value = $numeric * 3.7795;
				break;

			case 'in':
				$px_value = $numeric * 96;
				break;

			default:
				$px_value = $numeric;
		}

		// Convert from px to target unit
		switch ( $target_unit ) {
			case 'em':
			case 'rem':
				$converted = $px_value / $base_font_size;
				break;

			case 'pt':
				$converted = $px_value / 1.3333;
				break;

			case 'cm':
				$converted = $px_value / 37.795;
				break;

			case 'mm':
				$converted = $px_value / 3.7795;
				break;

			case 'in':
				$converted = $px_value / 96;
				break;

			default:
				$converted = $px_value;
		}

		return new self(
			$this->property,
			round( $converted, 2 ) . $target_unit,
			$this->priority,
			$this->breakpoint
		);
	}

	/**
	 * Check if this is a spacing property
	 *
	 * @return bool
	 */
	public function is_spacing(): bool {
		return in_array(
			$this->property,
			[ 'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left', 'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left' ],
			true
		);
	}

	/**
	 * Check if this is a color property
	 *
	 * @return bool
	 */
	public function is_color(): bool {
		return in_array(
			$this->property,
			[ 'color', 'background-color', 'border-color', 'outline-color' ],
			true
		);
	}

	/**
	 * Check if this is a typography property
	 *
	 * @return bool
	 */
	public function is_typography(): bool {
		return in_array(
			$this->property,
			[ 'font-family', 'font-size', 'font-weight', 'font-style', 'line-height', 'letter-spacing', 'text-align', 'text-decoration', 'text-transform' ],
			true
		);
	}

	/**
	 * Convert to array
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return [
			'property'   => $this->property,
			'value'      => $this->value,
			'priority'   => $this->priority,
			'breakpoint' => $this->breakpoint,
		];
	}

	/**
	 * Create from array
	 *
	 * @param array<string, mixed> $data Style data.
	 * @return DEVTB_Style
	 */
	public static function from_array( array $data ): DEVTB_Style {
		return new self(
			$data['property'] ?? '',
			$data['value'] ?? '',
			$data['priority'] ?? 10,
			$data['breakpoint'] ?? null
		);
	}

	/**
	 * Parse inline CSS string to array of styles
	 *
	 * @param string $css Inline CSS string.
	 * @return DEVTB_Style[]
	 */
	public static function parse_inline_css( string $css ): array {
		$styles = [];
		$rules  = explode( ';', $css );

		foreach ( $rules as $rule ) {
			$rule = trim( $rule );
			if ( empty( $rule ) ) {
				continue;
			}

			if ( strpos( $rule, ':' ) === false ) {
				continue;
			}

			list( $property, $value ) = explode( ':', $rule, 2 );
			$styles[] = new self( trim( $property ), trim( $value ) );
		}

		return $styles;
	}

	/**
	 * Convert array of styles to inline CSS string
	 *
	 * @param DEVTB_Style[] $styles Array of styles.
	 * @return string
	 */
	public static function to_inline_css( array $styles ): string {
		$css = [];

		foreach ( $styles as $style ) {
			if ( $style instanceof self ) {
				$css[] = $style->to_css();
			}
		}

		return implode( ' ', $css );
	}
}
