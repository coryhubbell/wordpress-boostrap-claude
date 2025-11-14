<?php
/**
 * Universal Component Model
 *
 * Intermediate representation for components across all frameworks.
 * This allows translation between any framework by converting to/from this universal format.
 *
 * @package WordPress_Bootstrap_Claude
 * @subpackage Translation_Bridge
 * @since 3.0.0
 */

namespace WPBC\TranslationBridge\Models;

/**
 * Class WPBC_Component
 *
 * Universal component model that represents elements from any framework
 * (Bootstrap, DIVI, Elementor, Avada, Bricks) in a standardized format.
 */
class WPBC_Component {

	/**
	 * Unique component identifier
	 *
	 * @var string
	 */
	public string $id;

	/**
	 * Component type (button, card, container, heading, etc.)
	 *
	 * @var string
	 */
	public string $type;

	/**
	 * Component category (layout, content, media, interactive, etc.)
	 *
	 * @var string
	 */
	public string $category;

	/**
	 * Universal attributes
	 *
	 * @var array<string, mixed>
	 */
	public array $attributes;

	/**
	 * CSS styles and properties
	 *
	 * @var array<string, mixed>
	 */
	public array $styles;

	/**
	 * Child components (nested elements)
	 *
	 * @var WPBC_Component[]
	 */
	public array $children;

	/**
	 * Inner content (text, HTML, etc.)
	 *
	 * @var string
	 */
	public string $content;

	/**
	 * Framework-specific metadata
	 *
	 * @var array<string, mixed>
	 */
	public array $metadata;

	/**
	 * Component constructor
	 *
	 * @param array<string, mixed> $data Component data.
	 */
	public function __construct( array $data = [] ) {
		$this->id         = $data['id'] ?? $this->generate_id();
		$this->type       = $data['type'] ?? 'unknown';
		$this->category   = $data['category'] ?? 'general';
		$this->attributes = $data['attributes'] ?? [];
		$this->styles     = $data['styles'] ?? [];
		$this->children   = $data['children'] ?? [];
		$this->content    = $data['content'] ?? '';
		$this->metadata   = $data['metadata'] ?? [];
	}

	/**
	 * Generate unique component ID
	 *
	 * @return string
	 */
	private function generate_id(): string {
		return 'wpbc_' . uniqid( '', true );
	}

	/**
	 * Add child component
	 *
	 * @param WPBC_Component $component Child component to add.
	 * @return void
	 */
	public function add_child( WPBC_Component $component ): void {
		$this->children[] = $component;
	}

	/**
	 * Get attribute value
	 *
	 * @param string $key Attribute key.
	 * @param mixed  $default Default value if not found.
	 * @return mixed
	 */
	public function get_attribute( string $key, $default = null ) {
		return $this->attributes[ $key ] ?? $default;
	}

	/**
	 * Set attribute value
	 *
	 * @param string $key Attribute key.
	 * @param mixed  $value Attribute value.
	 * @return void
	 */
	public function set_attribute( string $key, $value ): void {
		$this->attributes[ $key ] = $value;
	}

	/**
	 * Get style value
	 *
	 * @param string $key Style property key.
	 * @param mixed  $default Default value if not found.
	 * @return mixed
	 */
	public function get_style( string $key, $default = null ) {
		return $this->styles[ $key ] ?? $default;
	}

	/**
	 * Set style value
	 *
	 * @param string $key Style property key.
	 * @param mixed  $value Style value.
	 * @return void
	 */
	public function set_style( string $key, $value ): void {
		$this->styles[ $key ] = $value;
	}

	/**
	 * Get metadata value
	 *
	 * @param string $key Metadata key.
	 * @param mixed  $default Default value if not found.
	 * @return mixed
	 */
	public function get_metadata( string $key, $default = null ) {
		return $this->metadata[ $key ] ?? $default;
	}

	/**
	 * Set metadata value
	 *
	 * @param string $key Metadata key.
	 * @param mixed  $value Metadata value.
	 * @return void
	 */
	public function set_metadata( string $key, $value ): void {
		$this->metadata[ $key ] = $value;
	}

	/**
	 * Check if component has children
	 *
	 * @return bool
	 */
	public function has_children(): bool {
		return ! empty( $this->children );
	}

	/**
	 * Get all children
	 *
	 * @return WPBC_Component[]
	 */
	public function get_children(): array {
		return $this->children;
	}

	/**
	 * Convert component to array
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return [
			'id'         => $this->id,
			'type'       => $this->type,
			'category'   => $this->category,
			'attributes' => $this->attributes,
			'styles'     => $this->styles,
			'children'   => array_map( fn( $child ) => $child->to_array(), $this->children ),
			'content'    => $this->content,
			'metadata'   => $this->metadata,
		];
	}

	/**
	 * Create component from array
	 *
	 * @param array<string, mixed> $data Component data.
	 * @return WPBC_Component
	 */
	public static function from_array( array $data ): WPBC_Component {
		// Convert children arrays back to components
		if ( isset( $data['children'] ) && is_array( $data['children'] ) ) {
			$data['children'] = array_map(
				fn( $child_data ) => self::from_array( $child_data ),
				$data['children']
			);
		}

		return new self( $data );
	}

	/**
	 * Convert to JSON
	 *
	 * @return string
	 */
	public function to_json(): string {
		return json_encode( $this->to_array(), JSON_PRETTY_PRINT );
	}

	/**
	 * Create component from JSON
	 *
	 * @param string $json JSON string.
	 * @return WPBC_Component|null
	 */
	public static function from_json( string $json ): ?WPBC_Component {
		$data = json_decode( $json, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return null;
		}

		return self::from_array( $data );
	}

	/**
	 * Clone component with new ID
	 *
	 * @return WPBC_Component
	 */
	public function duplicate(): WPBC_Component {
		$clone     = clone $this;
		$clone->id = $this->generate_id();

		// Clone children recursively
		$clone->children = array_map(
			fn( $child ) => $child->duplicate(),
			$this->children
		);

		return $clone;
	}

	/**
	 * Get source framework from metadata
	 *
	 * @return string|null
	 */
	public function get_source_framework(): ?string {
		return $this->get_metadata( 'source_framework' );
	}

	/**
	 * Check if component is from specific framework
	 *
	 * @param string $framework Framework name (bootstrap, divi, elementor, avada, bricks).
	 * @return bool
	 */
	public function is_from_framework( string $framework ): bool {
		return $this->get_source_framework() === strtolower( $framework );
	}

	/**
	 * Validate component structure
	 *
	 * @return bool
	 */
	public function is_valid(): bool {
		// Must have valid type
		if ( empty( $this->type ) || $this->type === 'unknown' ) {
			return false;
		}

		// Must have valid category
		if ( empty( $this->category ) ) {
			return false;
		}

		// All children must be valid
		foreach ( $this->children as $child ) {
			if ( ! $child->is_valid() ) {
				return false;
			}
		}

		return true;
	}
}
