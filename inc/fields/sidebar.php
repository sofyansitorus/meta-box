<?php
/**
 * The sidebar select field.
 *
 * @package Meta Box
 */

/**
 * Sidebar field class.
 */
class RWMB_Sidebar_Field extends RWMB_Object_Choice_Field {
	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, array(
			'placeholder' => __( 'Select a sidebar', 'meta-box' ),
		) );

		// Get sidebars for field options.
		$field['options'] = self::query( $field );

		$field = parent::normalize( $field );

		return $field;
	}

	/**
	 * Get sidebars for field options.
	 * @param  array $field Field settings.
	 * @return array        Field options array.
	 */
	public static function query( $field ) {
		global $wp_registered_sidebars;
		$options = array();
		foreach ( $wp_registered_sidebars as $sidebar ) {
			$options[$sidebar['id']] = array(
				'value' => $sidebar['id'],
				'label' => $sidebar['name'],
			);
		}
		return $options;
	}

	/**
	 * Get option label.
	 *
	 * @param array  $field Field parameters.
	 * @param string $value Option value.
	 *
	 * @return string
	 */
	public static function get_option_label( $field, $value ) {
		if ( ! is_active_sidebar( $value ) ) {
			return '';
		}
		ob_start();
		dynamic_sidebar( $value );
		return ob_get_clean();
	}
}
