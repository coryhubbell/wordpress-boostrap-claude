<?php
/**
 * WPBakery Page Builder Parser
 *
 * Intelligent WPBakery (Visual Composer) shortcode parser featuring:
 * - Hierarchical structure parsing (Row > Column > Element)
 * - 50+ element type support
 * - Nested shortcode handling
 * - Parameter extraction and normalization
 * - Responsive settings parsing
 * - Animation and styling support
 *
 * @package WordPress_Bootstrap_Claude
 * @subpackage Translation_Bridge
 * @since 3.0.0
 */

namespace WPBC\TranslationBridge\Parsers;

use WPBC\TranslationBridge\Core\WPBC_Parser_Interface;
use WPBC\TranslationBridge\Models\WPBC_Component;
use WPBC\TranslationBridge\Utils\WPBC_Shortcode_Helper;
use WPBC\TranslationBridge\Utils\WPBC_CSS_Helper;

/**
 * Class WPBC_WPBakery_Parser
 *
 * Parse WPBakery Page Builder shortcodes into universal components.
 */
class WPBC_WPBakery_Parser implements WPBC_Parser_Interface {

	/**
	 * Supported WPBakery element types (50+ elements)
	 *
	 * @var array<string>
	 */
	private array $supported_types = [
		// Layout
		'vc_row',
		'vc_row_inner',
		'vc_column',
		'vc_column_inner',
		'vc_section',
		'vc_empty_space',
		'vc_separator',

		// Content
		'vc_column_text',
		'vc_text_separator',
		'vc_message',
		'vc_custom_heading',
		'vc_cta',
		'vc_cta_button',
		'vc_cta_button2',

		// Media
		'vc_single_image',
		'vc_gallery',
		'vc_images_carousel',
		'vc_masonry_grid',
		'vc_media_grid',
		'vc_masonry_media_grid',
		'vc_video',
		'vc_gmaps',
		'vc_googleplus',

		// Interactive
		'vc_btn',
		'vc_button',
		'vc_button2',
		'vc_accordion',
		'vc_accordion_tab',
		'vc_tta_accordion',
		'vc_tta_section',
		'vc_tabs',
		'vc_tab',
		'vc_tta_tabs',
		'vc_tour',
		'vc_toggle',
		'vc_tta_tour',
		'vc_tta_pageable',

		// Tables & Lists
		'vc_basic_grid',
		'vc_posts_grid',
		'vc_posts_slider',
		'vc_carousel',

		// Social
		'vc_facebook',
		'vc_tweetmeme',
		'vc_googleplus',
		'vc_pinterest',
		'vc_toggle',

		// Widgets
		'vc_widget_sidebar',
		'vc_wp_archives',
		'vc_wp_calendar',
		'vc_wp_categories',
		'vc_wp_custommenu',
		'vc_wp_links',
		'vc_wp_meta',
		'vc_wp_pages',
		'vc_wp_posts',
		'vc_wp_recentcomments',
		'vc_wp_rss',
		'vc_wp_search',
		'vc_wp_tagcloud',
		'vc_wp_text',

		// Advanced
		'vc_pie',
		'vc_line_chart',
		'vc_round_chart',
		'vc_progress_bar',
		'vc_icon',
		'vc_zigzag',
		'vc_flickr',
		'vc_raw_html',
		'vc_raw_js',

		// WooCommerce
		'product',
		'products',
		'product_page',
		'product_category',
		'product_categories',
		'add_to_cart',
		'add_to_cart_url',
	];

	/**
	 * Parse WPBakery shortcode content into universal components
	 *
	 * @param string|array $content WPBakery shortcode content.
	 * @return WPBC_Component[] Array of parsed components.
	 */
	public function parse( $content ): array {
		if ( is_array( $content ) ) {
			$content = implode( "\n", $content );
		}

		if ( ! is_string( $content ) || empty( $content ) ) {
			return [];
		}

		$components = [];

		// Parse shortcodes using regex
		$pattern = get_shortcode_regex( [ 'vc_row', 'vc_section' ] );

		if ( preg_match_all( '/' . $pattern . '/s', $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$tag     = $match[2];
				$attrs   = shortcode_parse_atts( $match[3] );
				$inner   = $match[5] ?? '';

				if ( $tag === 'vc_row' || $tag === 'vc_row_inner' ) {
					$row = $this->parse_row( $attrs, $inner );
					if ( $row ) {
						$components[] = $row;
					}
				} elseif ( $tag === 'vc_section' ) {
					$section = $this->parse_section( $attrs, $inner );
					if ( $section ) {
						$components[] = $section;
					}
				}
			}
		}

		// If no rows found, try parsing as direct elements
		if ( empty( $components ) ) {
			$element = $this->parse_direct_element( $content );
			if ( $element ) {
				$components[] = $element;
			}
		}

		return $components;
	}

	/**
	 * Parse WPBakery row
	 *
	 * @param array  $attrs Row attributes.
	 * @param string $inner Inner content.
	 * @return WPBC_Component|null Parsed row component.
	 */
	private function parse_row( array $attrs, string $inner ): ?WPBC_Component {
		$attributes = $this->normalize_attributes( $attrs );

		$row = new WPBC_Component([
			'type'       => 'row',
			'category'   => 'layout',
			'attributes' => $attributes,
			'metadata'   => [
				'source_framework'     => 'wpbakery',
				'original_type'        => 'vc_row',
				'wpbakery_attributes'  => $attrs,
			],
		]);

		// Parse columns
		$column_pattern = get_shortcode_regex( [ 'vc_column', 'vc_column_inner' ] );

		if ( preg_match_all( '/' . $column_pattern . '/s', $inner, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$col_attrs = shortcode_parse_atts( $match[3] );
				$col_inner = $match[5] ?? '';

				$column = $this->parse_column( $col_attrs, $col_inner );
				if ( $column ) {
					$row->add_child( $column );
				}
			}
		}

		return $row;
	}

	/**
	 * Parse WPBakery section
	 *
	 * @param array  $attrs Section attributes.
	 * @param string $inner Inner content.
	 * @return WPBC_Component|null Parsed section component.
	 */
	private function parse_section( array $attrs, string $inner ): ?WPBC_Component {
		$attributes = $this->normalize_attributes( $attrs );

		$section = new WPBC_Component([
			'type'       => 'section',
			'category'   => 'layout',
			'attributes' => $attributes,
			'metadata'   => [
				'source_framework'     => 'wpbakery',
				'original_type'        => 'vc_section',
				'wpbakery_attributes'  => $attrs,
			],
		]);

		// Parse rows within section
		$row_pattern = get_shortcode_regex( [ 'vc_row', 'vc_row_inner' ] );

		if ( preg_match_all( '/' . $row_pattern . '/s', $inner, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$row_attrs = shortcode_parse_atts( $match[3] );
				$row_inner = $match[5] ?? '';

				$row = $this->parse_row( $row_attrs, $row_inner );
				if ( $row ) {
					$section->add_child( $row );
				}
			}
		}

		return $section;
	}

	/**
	 * Parse WPBakery column
	 *
	 * @param array  $attrs Column attributes.
	 * @param string $inner Inner content.
	 * @return WPBC_Component|null Parsed column component.
	 */
	private function parse_column( array $attrs, string $inner ): ?WPBC_Component {
		$attributes = $this->normalize_attributes( $attrs );

		// Extract column width (1/1, 1/2, 1/3, etc.)
		$width = $attrs['width'] ?? '1/1';
		$attributes['width'] = $this->convert_vc_width( $width );

		$column = new WPBC_Component([
			'type'       => 'column',
			'category'   => 'layout',
			'attributes' => $attributes,
			'metadata'   => [
				'source_framework'     => 'wpbakery',
				'original_type'        => 'vc_column',
				'column_width'         => $width,
				'wpbakery_attributes'  => $attrs,
			],
		]);

		// Parse elements within column
		// Get all shortcodes
		preg_match_all( '/' . get_shortcode_regex() . '/s', $inner, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {
			$tag       = $match[2];
			$el_attrs  = shortcode_parse_atts( $match[3] );
			$el_inner  = $match[5] ?? '';

			// Skip column and row tags (already handled)
			if ( in_array( $tag, [ 'vc_column', 'vc_column_inner', 'vc_row', 'vc_row_inner' ], true ) ) {
				continue;
			}

			$element = $this->parse_element_by_tag( $tag, $el_attrs, $el_inner );
			if ( $element ) {
				$column->add_child( $element );
			}
		}

		return $column;
	}

	/**
	 * Parse WPBakery element by tag
	 *
	 * @param string $tag       Element tag.
	 * @param array  $attrs     Element attributes.
	 * @param string $content   Element content.
	 * @return WPBC_Component|null Parsed element component.
	 */
	private function parse_element_by_tag( string $tag, array $attrs, string $content ): ?WPBC_Component {
		// Map WPBakery element type to universal type
		$universal_type = $this->map_element_type( $tag );

		if ( $universal_type === 'unknown' ) {
			return null;
		}

		$attributes = $this->normalize_attributes( $attrs );

		// Process content (remove nested shortcodes if needed)
		$processed_content = do_shortcode( $content );

		$component = new WPBC_Component([
			'type'       => $universal_type,
			'category'   => $this->get_category( $universal_type ),
			'attributes' => $attributes,
			'content'    => $processed_content,
			'metadata'   => [
				'source_framework'     => 'wpbakery',
				'original_type'        => $tag,
				'wpbakery_attributes'  => $attrs,
			],
		]);

		// Handle special nested structures (tabs, accordion, etc.)
		if ( in_array( $tag, [ 'vc_tabs', 'vc_tour', 'vc_accordion', 'vc_tta_tabs', 'vc_tta_accordion', 'vc_tta_tour' ], true ) ) {
			$this->parse_nested_elements( $component, $content, $tag );
		}

		return $component;
	}

	/**
	 * Parse nested elements (tabs, accordion sections, etc.)
	 *
	 * @param WPBC_Component $parent  Parent component.
	 * @param string         $content Content with nested shortcodes.
	 * @param string         $parent_tag Parent shortcode tag.
	 */
	private function parse_nested_elements( WPBC_Component $parent, string $content, string $parent_tag ): void {
		// Determine child tag based on parent
		$child_tags = [
			'vc_tabs'          => 'vc_tab',
			'vc_tour'          => 'vc_tab',
			'vc_accordion'     => 'vc_accordion_tab',
			'vc_tta_tabs'      => 'vc_tta_section',
			'vc_tta_accordion' => 'vc_tta_section',
			'vc_tta_tour'      => 'vc_tta_section',
		];

		$child_tag = $child_tags[ $parent_tag ] ?? null;
		if ( ! $child_tag ) {
			return;
		}

		$pattern = get_shortcode_regex( [ $child_tag ] );

		if ( preg_match_all( '/' . $pattern . '/s', $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$attrs      = shortcode_parse_atts( $match[3] );
				$inner      = $match[5] ?? '';

				$child = $this->parse_element_by_tag( $child_tag, $attrs, $inner );
				if ( $child ) {
					$parent->add_child( $child );
				}
			}
		}
	}

	/**
	 * Parse direct element (content without row/column structure)
	 *
	 * @param string $content Shortcode content.
	 * @return WPBC_Component|null Parsed component.
	 */
	private function parse_direct_element( string $content ): ?WPBC_Component {
		preg_match( '/' . get_shortcode_regex() . '/s', $content, $match );

		if ( empty( $match ) ) {
			return null;
		}

		$tag     = $match[2];
		$attrs   = shortcode_parse_atts( $match[3] );
		$inner   = $match[5] ?? '';

		return $this->parse_element_by_tag( $tag, $attrs, $inner );
	}

	/**
	 * Map WPBakery element type to universal component type
	 *
	 * @param string $vc_type WPBakery element type.
	 * @return string Universal component type.
	 */
	private function map_element_type( string $vc_type ): string {
		$type_map = [
			// Content
			'vc_column_text'       => 'text',
			'vc_custom_heading'    => 'heading',
			'vc_text_separator'    => 'divider',
			'vc_separator'         => 'divider',
			'vc_message'           => 'alert',
			'vc_cta'               => 'cta',
			'vc_cta_button'        => 'button',
			'vc_cta_button2'       => 'button',
			'vc_empty_space'       => 'spacer',

			// Media
			'vc_single_image'      => 'image',
			'vc_gallery'           => 'gallery',
			'vc_images_carousel'   => 'slider',
			'vc_masonry_grid'      => 'gallery',
			'vc_media_grid'        => 'gallery',
			'vc_masonry_media_grid' => 'gallery',
			'vc_video'             => 'video',
			'vc_gmaps'             => 'map',

			// Interactive
			'vc_btn'               => 'button',
			'vc_button'            => 'button',
			'vc_button2'           => 'button',
			'vc_accordion'         => 'accordion',
			'vc_accordion_tab'     => 'accordion-item',
			'vc_tta_accordion'     => 'accordion',
			'vc_tta_section'       => 'tab',
			'vc_tabs'              => 'tabs',
			'vc_tab'               => 'tab',
			'vc_tta_tabs'          => 'tabs',
			'vc_tour'              => 'tabs',
			'vc_tta_tour'          => 'tabs',
			'vc_toggle'            => 'accordion',

			// Lists & Grids
			'vc_basic_grid'        => 'grid',
			'vc_posts_grid'        => 'post-grid',
			'vc_posts_slider'      => 'slider',
			'vc_carousel'          => 'slider',

			// Social
			'vc_facebook'          => 'social-icon',
			'vc_tweetmeme'         => 'social-icon',
			'vc_googleplus'        => 'social-icon',
			'vc_pinterest'         => 'social-icon',

			// Charts & Progress
			'vc_pie'               => 'chart',
			'vc_line_chart'        => 'chart',
			'vc_round_chart'       => 'chart',
			'vc_progress_bar'      => 'progress',

			// Other
			'vc_icon'              => 'icon',
			'vc_zigzag'            => 'divider',
			'vc_flickr'            => 'gallery',
			'vc_raw_html'          => 'html',
			'vc_raw_js'            => 'code',

			// Widgets
			'vc_widget_sidebar'    => 'widget',
			'vc_wp_search'         => 'search',
			'vc_wp_text'           => 'text',
			'vc_wp_posts'          => 'blog',
			'vc_wp_categories'     => 'list',
			'vc_wp_tagcloud'       => 'tag-cloud',

			// WooCommerce
			'product'              => 'product',
			'products'             => 'product-grid',
			'product_page'         => 'product',
			'product_category'     => 'product-grid',
			'product_categories'   => 'product-grid',
			'add_to_cart'          => 'button',
			'add_to_cart_url'      => 'button',
		];

		return $type_map[ $vc_type ] ?? 'unknown';
	}

	/**
	 * Normalize WPBakery attributes to universal format
	 *
	 * @param array $vc_attrs WPBakery attributes.
	 * @return array Normalized attributes.
	 */
	private function normalize_attributes( array $vc_attrs ): array {
		$normalized = [];

		// Common attribute mappings
		$attr_map = [
			// Button
			'link'                  => 'url',
			'target'                => 'target',
			'size'                  => 'size',
			'color'                 => 'variant',
			'icon_left'             => 'icon',
			'icon_right'            => 'icon_right',
			'button_block'          => 'full_width',

			// Image
			'image'                 => 'image_url',
			'img_size'              => 'image_size',
			'alignment'             => 'alignment',
			'onclick'               => 'on_click',
			'img_link'              => 'link_url',
			'img_link_target'       => 'link_target',

			// Heading
			'text'                  => 'heading',
			'font_size'             => 'size',
			'font_container'        => 'font_container',
			'use_theme_fonts'       => 'use_theme_fonts',

			// Colors & Styling
			'css'                   => 'custom_css',
			'el_class'              => 'css_class',
			'el_id'                 => 'element_id',
			'css_animation'         => 'animation',

			// Layout
			'width'                 => 'width',
			'offset'                => 'offset',
			'content_placement'     => 'content_placement',
			'gap'                   => 'gap',
			'equal_height'          => 'equal_height',
			'full_width'            => 'full_width',
			'full_height'           => 'full_height',

			// Video
			'el_width'              => 'width',
			'el_aspect'             => 'aspect_ratio',

			// Accordion/Tabs
			'title'                 => 'title',
			'tab_id'                => 'tab_id',
			'active'                => 'active',

			// Grid
			'grid_id'               => 'grid_id',
			'posts_per_page'        => 'posts_per_page',
			'max_items'             => 'max_items',
			'item'                  => 'item_template',
		];

		// Map attributes
		foreach ( $vc_attrs as $key => $value ) {
			$universal_key = $attr_map[ $key ] ?? $key;
			$normalized[ $universal_key ] = $value;
		}

		// Convert boolean values
		foreach ( $normalized as $key => $value ) {
			if ( in_array( strtolower( (string) $value ), [ 'yes', 'true', '1' ], true ) ) {
				$normalized[ $key ] = true;
			} elseif ( in_array( strtolower( (string) $value ), [ 'no', 'false', '0' ], true ) ) {
				$normalized[ $key ] = false;
			}
		}

		return $normalized;
	}

	/**
	 * Convert WPBakery column width to percentage
	 *
	 * @param string $width WPBakery column width (1/1, 1/2, etc.).
	 * @return string Width as percentage.
	 */
	private function convert_vc_width( string $width ): string {
		$width_map = [
			'1/1'   => '100%',
			'1/2'   => '50%',
			'1/3'   => '33.33%',
			'2/3'   => '66.66%',
			'1/4'   => '25%',
			'3/4'   => '75%',
			'1/5'   => '20%',
			'2/5'   => '40%',
			'3/5'   => '60%',
			'4/5'   => '80%',
			'1/6'   => '16.66%',
			'5/6'   => '83.33%',
			'1/12'  => '8.33%',
			'5/12'  => '41.66%',
			'7/12'  => '58.33%',
			'11/12' => '91.66%',
		];

		return $width_map[ $width ] ?? '100%';
	}

	/**
	 * Get component category from type
	 *
	 * @param string $type Component type.
	 * @return string Category name.
	 */
	private function get_category( string $type ): string {
		$categories = [
			'layout'      => [ 'container', 'row', 'column', 'section', 'spacer' ],
			'content'     => [ 'text', 'heading', 'image', 'card', 'alert', 'cta' ],
			'media'       => [ 'video', 'audio', 'gallery', 'slider', 'map' ],
			'interactive' => [ 'button', 'accordion', 'tabs', 'tab', 'accordion-item', 'toggle' ],
			'form'        => [ 'form', 'input', 'search' ],
			'data'        => [ 'counter', 'progress', 'chart', 'grid', 'table' ],
			'social'      => [ 'social-icon', 'social-icons', 'share-buttons' ],
			'blog'        => [ 'blog', 'post-grid', 'post-card' ],
			'ecommerce'   => [ 'product', 'product-grid', 'product-slider' ],
		];

		foreach ( $categories as $category => $types ) {
			if ( in_array( $type, $types, true ) ) {
				return $category;
			}
		}

		return 'general';
	}

	/**
	 * Get framework name
	 *
	 * @return string Framework name.
	 */
	public function get_framework(): string {
		return 'wpbakery';
	}

	/**
	 * Validate WPBakery shortcode content
	 *
	 * @param string|array $content Content to validate.
	 * @return bool True if valid WPBakery content.
	 */
	public function is_valid_content( $content ): bool {
		if ( is_array( $content ) ) {
			$content = implode( "\n", $content );
		}

		if ( ! is_string( $content ) || empty( $content ) ) {
			return false;
		}

		// Check for WPBakery/Visual Composer shortcodes
		return (
			strpos( $content, '[vc_row' ) !== false ||
			strpos( $content, '[vc_column' ) !== false ||
			strpos( $content, '[vc_section' ) !== false ||
			preg_match( '/\[vc_[a-z_]+/', $content )
		);
	}

	/**
	 * Get supported component types
	 *
	 * @return array<string> Array of supported types.
	 */
	public function get_supported_types(): array {
		return $this->supported_types;
	}

	/**
	 * Parse single element
	 *
	 * @param mixed $element WPBakery shortcode or element data.
	 * @return WPBC_Component|null Parsed component or null.
	 */
	public function parse_element( $element ): ?WPBC_Component {
		if ( is_string( $element ) ) {
			$components = $this->parse( $element );
			return $components[0] ?? null;
		}

		if ( is_array( $element ) && isset( $element['tag'] ) ) {
			return $this->parse_element_by_tag(
				$element['tag'],
				$element['attrs'] ?? [],
				$element['content'] ?? ''
			);
		}

		return null;
	}
}
