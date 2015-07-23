<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build Customizer
 * @param mixed $wp_customize
 * @author Dahz
 * @return mixed
 * @since 1.2.1
 */
class Dahz_Customizer_Builder {

	static $instance;
	public $settings;
	public $controls;

	public function __construct() {

		self::$instance =& $this;

		add_action('customize_register', array( $this, 'regControlType' ), 99);
		add_action('customize_register', array( $this, 'isBuildCustomizer' ), 99 );
	}

	public function isBuildCustomizer( $wp_customize ) {
		$controls = $this->getAllControl();

		// Early exit if controls are not set or if they're empty
		if ( ! isset( $controls ) || empty( $controls ) ) {
			return;
		}
		foreach ( $controls as $control ) {
			$priority       = ( isset( $control['priority'] ) ) ? $control['priority'] : '';
			$default        = ( isset( $control['default'] ) ) ? $control['default'] : '';
			$description    = ( isset( $control['description'] ) ) ? $control['description'] : '';
			$section        = ( isset( $control['section'] ) ) ? esc_attr( $control['section'] ) : '';
			$label					= ( isset( $control['label'] ) ) ? $control['label'] : '';
			$transport      = ( isset( $control['transport'] ) ) ? esc_attr( $control['transport'] ) : 'refresh';
			$input_attrs  	= ( isset( $control['input_attrs'] ) ) ? $control['input_attrs'] : array();
			$choices  			= ( isset( $control['choices'] ) ) ? $control['choices'] : array();
			$setting				= 'df_options['. $control['setting'] .']';
			$id							= sanitize_key( str_replace( '[', '-', str_replace( ']', '', $setting ) ) );
			$sanitize_cb    = self::get_sanitization( $control['type'] );

			$wp_customize->add_setting( $setting, array(
					'default'    => $default,
					'type'       => 'option',
					'capability' => 'edit_theme_options',
					'transport'  => $transport,
					'sanitize_callback' => $sanitize_cb,
				) );

			if( in_array( $control['type'], array( 'text', 'url', 'password', 'email' ) ) ) {
						$wp_customize->add_control( $id, array(
								'priority'          => $priority,
								'section'           => $section,
								'label'             => $label,
								'description'       => $description,
								'settings'          => $setting
							) );
			} else {

				switch ( $control['type'] ) {
					case 'description':
						$control_object = 'DAHZ_TextDescription_Control';
						break;

					case 'sub-title':
						$control_object = 'DAHZ_Subtitle_Control';
						break;

					case 'textarea':
						$control_object = 'DAHZ_Textarea_Control';
						break;

					case 'images_radio':
						$control_object = 'DAHZ_Layout_Picker_Control';
						break;

					case 'slider':
						$control_object = 'DAHZ_RangeSlider_Control';
						break;

					case 'uploader':
						$control_object = 'DAHZ_Media_Uploader_Control';
						break;

					case 'image':
						$control_object = 'WP_Customize_Image_Control';
						break;

					case 'color':
						$control_object =	'WP_Customize_Color_Control';
						break;

					case 'select':
						$mode  		= ( isset( $control['mode'] ) ) ? $control['mode'] : '';
						$dir      = ( isset( $control['direction'] ) ) ? $control['direction'] : '';
						$control_object =	'DAHZ_Selectbox_Dropdown_Control';
						break;

					case 'checkbox':
						$mode  		= ( isset( $control['mode'] ) ) ? $control['mode'] : '';
						$control_object =	'DAHZ_Checkbox_Control';
						break;

					case 'radio':
						$mode  		= ( isset( $control['mode'] ) ) ? $control['mode'] : '';
						$control_object =	'DAHZ_Radiobox_Control';
						break;
				}

						$wp_customize->add_control( new $control_object ( $wp_customize, $id, array(
							'priority'          => $priority,
							'mode'              => $mode,
							'section'           => $section,
							'label'             => $label,
							'description'       => $description,
							'choices'           => $choices,
							'input_attrs'       => $input_attrs,
							'settings'          => $setting
						) ) );
				}
		}

	}

	public function getAllControl() {

		$controls = apply_filters( 'df_customizer_controls', array() );
		return $controls;

	}

	public function regControlType( $wp_customize ) {

		require_once DF_CUSTOMIZER_CONTROL_DIR . 'controls/media/media-uploader-custom-control.php';
		require_once DF_CUSTOMIZER_CONTROL_DIR . 'controls/typography/typography-custom-control.php';
		require_once DF_CUSTOMIZER_CONTROL_DIR . 'controls/text/text-description-custom-control.php';
		require_once DF_CUSTOMIZER_CONTROL_DIR . 'controls/text/text-subtitle-custom-control.php';
		require_once DF_CUSTOMIZER_CONTROL_DIR . 'controls/text/text-slider-custom-control.php';
		require_once DF_CUSTOMIZER_CONTROL_DIR . 'controls/layout/layout-picker-custom-control.php';
		require_once DF_CUSTOMIZER_CONTROL_DIR . 'controls/select/selectbox-dropdown-custom-control.php';
		require_once DF_CUSTOMIZER_CONTROL_DIR . 'controls/text/textarea-custom-control.php';
		require_once DF_CUSTOMIZER_CONTROL_DIR . 'controls/text/checkbox-custom-control.php';
		require_once DF_CUSTOMIZER_CONTROL_DIR . 'controls/text/radiobox-custom-control.php';

		$wp_customize->register_control_type( 'DAHZ_Subtitle_Control' );
		$wp_customize->register_control_type( 'DAHZ_TextDescription_Control' );
		$wp_customize->register_control_type( 'DAHZ_Layout_Picker_Control' );
		$wp_customize->register_control_type( 'DAHZ_Selectbox_Dropdown_Control' );
		$wp_customize->register_control_type( 'DAHZ_Typography_Control' );
	}

	public static function get_sanitization( $control_type ) {

		switch ( $control_type ) {
			case 'checkbox' :
				$sanitize_callback = 'dahz_sanitize_checkbox';
				break;
			case 'select' :
				$sanitize_callback = 'sanitize_text_field';
				break;
			case 'radio' :
				$sanitize_callback = 'sanitize_text_field';
				break;
			case 'color' :
				$sanitize_callback = 'sanitize_hex_color';
				break;
			case 'image' :
				$sanitize_callback = 'esc_url_raw';
				break;
			case 'text' :
				$sanitize_callback = 'esc_attr';
				break;
			case 'textarea' :
				$sanitize_callback = 'dahz_sanitize_textarea';
				break;
			case 'uploader' :
				$sanitize_callback = 'esc_url_raw';
				break;
			case 'slider' :
				$sanitize_callback = 'dahz_sanitize_range';
				break;
			default:
				$sanitize_callback = 'dahz_sanitize_default';
		}

		return $sanitize_callback;

	}

}
new Dahz_Customizer_Builder;
