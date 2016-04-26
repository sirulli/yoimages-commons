<?php
if (! defined ( 'ABSPATH' )) {
	die ( 'No script kiddies please!' );
}

if ( ! class_exists( 'YoImagesSettingsPage' ) ) {

	class YoImagesSettingsPage {
		
		private $seo_options;
		private $search_options;
		
		public function __construct() {
			add_action ( 'admin_menu', array ( $this, 'add_plugin_page_menu_item' ) );
			add_action ( 'admin_init', array ( $this, 'init_admin_page' ) );
		}
		
		public function add_plugin_page_menu_item() {
			add_options_page( __( 'YoImages settings', YOIMG_DOMAIN ), 'YoImages', 'manage_options', 'yoimg-settings', array( $this, 'create_admin_page' ) );
		}
		
		public function create_admin_page() {
			if ( !current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			global $yoimg_modules;
			global $yoimg_plugins_url;
			$this->seo_options = get_option( 'yoimg_seo_settings' );
			$this->search_options = get_option( 'yoimg_search_settings' );
			$settings = apply_filters( 'yoimg_settings', array() );
			?>
			<div class="wrap" id="yoimg-settings-wrapper">
				<h2><?php _e( 'YoImages settings', YOIMG_DOMAIN ); ?></h2>
				<?php
				if( isset( $_GET[ 'tab' ] ) ) {
					$active_tab = $_GET[ 'tab' ];
				} else {
					foreach ( $yoimg_modules as $key=>$value ) {
						if ( $value['has-settings'] ) {
							$active_tab = $key;
							break;
						}
					}
					if ( ! isset( $active_tab ) ) {
						$active_tab = $settings[0]['option']['page'];
					}
				}
				?>
				<h2 class="nav-tab-wrapper">
					<?php
					foreach ( $settings as $setting ) {
						$option_page = $setting['option']['page'];
					?>
						<a href="?page=yoimg-settings&tab=<?php echo $option_page; ?>" class="nav-tab <?php echo $active_tab == $option_page ? 'nav-tab-active' : ''; ?>"><?php echo $setting['option']['title']; ?></a>
					<?php
					}
					?>
					<a href="?page=yoimg-settings&tab=yoimages-seo" class="nav-tab <?php echo $active_tab == 'yoimages-seo' ? 'nav-tab-active' : ''; ?>"><?php  _e( 'SEO for images', YOIMG_DOMAIN ); ?></a>
					<a href="?page=yoimg-settings&tab=yoimages-search" class="nav-tab <?php echo $active_tab == 'yoimages-search' ? 'nav-tab-active' : ''; ?>"><?php  _e( 'Free stock images search', YOIMG_DOMAIN ); ?></a>
				</h2>
				<?php
				if ( isset( $yoimg_modules[$active_tab] ) && $yoimg_modules[$active_tab]['has-settings'] ) {
				?>
					<form method="post" action="options.php">
					<?php
						settings_fields( $active_tab . '-group' );
						do_settings_sections( $active_tab );
						submit_button(); 
					?>
					</form>
				<?php
				} elseif ( isset( $yoimg_modules[$active_tab] ) ) {
				?>
					<div class="message error">
						<p><?php _e( 'You are trying to access a YoImages\' module that has no settings page', YOIMG_DOMAIN ); ?></p>
					</div>
				<?php
				} elseif ( isset ( $yoimg_plugins_url[$active_tab] ) ) {
				?>
					<div class="message update-nag">
						<p><?php _e( 'This YoImages\' module is not active or installed, please activate it in the plugins administration page or install it from here:', YOIMG_DOMAIN ); ?> <a href="<?php echo $yoimg_plugins_url[$active_tab]; ?>"><?php echo $yoimg_plugins_url[$active_tab]; ?></a></p>
					</div>
				<?php
				} else {
				?>
					<div class="message error">
						<p><?php _e( 'Unknown module', YOIMG_DOMAIN ); ?></p>
					</div>
				<?php
				}
				?>
			</div>
			<?php
		}
	
		public function init_admin_page() {
			$settings = apply_filters( 'yoimg_settings', array() );
			foreach ( $settings as $setting ) {
				$option_page = $setting['option']['page'];
				register_setting( $setting['option']['option_group'], $setting['option']['option_name'], $setting['option']['sanitize_callback'] );
				foreach ( $setting['option']['sections'] as $section ) {
					$section_id = $section['id'];
					add_settings_section( $section_id, $section['title'], $section['callback'], $option_page );
					foreach ( $section['fields'] as $field ) {
						add_settings_field( $field['id'], $field['title'], $field['callback'], $option_page, $section_id );
					}
				}
			}
			
			register_setting( 'yoimages-seo-group', 'yoimg_seo_settings', array( $this, 'sanitize_seo' ) );
			register_setting( 'yoimages-search-group', 'yoimg_search_settings', array( $this, 'sanitize_search' ) );
			
			add_settings_section( 'yoimg_imgseo_options_section', __( 'SEO for images', YOIMG_DOMAIN ), array( $this, 'print_imgseo_options_section_info' ), 'yoimages-seo' );
			add_settings_field( 'imgseo_change_image_title', __( 'Change image title', YOIMG_DOMAIN ), array( $this, 'imgseo_change_image_title_callback' ), 'yoimages-seo', 'yoimg_imgseo_options_section' );
			add_settings_field( 'imgseo_image_title_expression', __( 'Image title expression', YOIMG_DOMAIN), array( $this, 'imgseo_image_title_expression_callback' ), 'yoimages-seo', 'yoimg_imgseo_options_section' );
			add_settings_field( 'imgseo_change_image_alt', __( 'Change image alt attribute', YOIMG_DOMAIN ), array( $this, 'imgseo_change_image_alt_callback' ), 'yoimages-seo', 'yoimg_imgseo_options_section' );
			add_settings_field( 'imgseo_image_alt_expression', __( 'Image alt expression', YOIMG_DOMAIN), array( $this, 'imgseo_image_alt_expression_callback' ), 'yoimages-seo', 'yoimg_imgseo_options_section' );
			add_settings_field( 'imgseo_change_image_filename', __( 'Change image file name', YOIMG_DOMAIN ), array( $this, 'imgseo_change_image_filename_callback' ), 'yoimages-seo', 'yoimg_imgseo_options_section' );
			add_settings_field( 'imgseo_image_filename_expression', __( 'Image file name expression', YOIMG_DOMAIN), array( $this, 'imgseo_image_filename_expression_callback' ), 'yoimages-seo', 'yoimg_imgseo_options_section' );
			
			add_settings_section( 'yoimg_search_options_section', __( 'Free stock images search', YOIMG_DOMAIN ), array( $this, 'print_search_options_section_info' ), 'yoimages-search' );
			add_settings_field( 'search_is_active', __( 'Enable', YOIMG_DOMAIN ), array( $this, 'search_is_active_callback' ), 'yoimages-search', 'yoimg_search_options_section' );
			
		}
	
		public function print_imgseo_options_section_info() {
			print __('Enter your images SEO settings here below', YOIMG_DOMAIN );
			printf( '<p>' .
				__( 'Supported expressions:', YOIMG_DOMAIN ) . ' ' . implode( ', ', apply_filters( 'yoimg_supported_expressions', array() ) )
				. '</p>'
			);
		}
		
		public function print_search_options_section_info() {
			global $yoimg_search_providers;
			print __('Free stock images search settings.<br/>Please note that searches are performed in english therefore use english search terms.', YOIMG_DOMAIN );
			if ( isset( $yoimg_search_providers ) && ! empty( $yoimg_search_providers ) && is_array( $yoimg_search_providers ) ) {
				print '<br /><br />';
				print __('Images sources:', YOIMG_DOMAIN );
				print '<ul>';
				foreach ( $yoimg_search_providers as $yoimg_search_provider ) {
					print '<li><a href="' . $yoimg_search_provider['url'] . '" target="_blank">' . $yoimg_search_provider['name'] . '</a>,';
					print __('see T&C for more info.', YOIMG_DOMAIN );
					print '</li>';
				}
				print '</ul>';
			}
		}
		
		public function imgseo_change_image_title_callback() {
			printf(
				'<input type="checkbox" id="imgseo_change_image_title" name="yoimg_seo_settings[imgseo_change_image_title]" value="TRUE" %s />
				<p class="description">' . __( 'If checked title will be replaced with the expression here below', YOIMG_DOMAIN ) . '</p>',
				$this->seo_options['imgseo_change_image_title'] ? 'checked="checked"' : ( YOIMG_DEFAULT_IMGSEO_CHANGE_IMAGE_TITLE && ! isset( $this->seo_options['imgseo_change_image_title'] ) ? 'checked="checked"' : '' )
			);
		}
	
		public function imgseo_image_title_expression_callback() {
			printf(
			'<input type="text" id="imgseo_image_title_expression" name="yoimg_seo_settings[imgseo_image_title_expression]" value="%s" class="imgseo_change_image_title-dep" />
				<p class="description">' . __( 'expression used to replace the title, accepted values are:', YOIMG_DOMAIN ) . ' ' . implode( ', ', apply_filters( 'yoimg_supported_expressions', array() ) ) . '</p>',
					! empty( $this->seo_options['imgseo_image_title_expression'] ) ? esc_attr( $this->seo_options['imgseo_image_title_expression'] ) : YOIMG_IMGSEO_IMAGE_TITLE_EXPRESSION
			);
		}
	
		public function imgseo_change_image_alt_callback() {
			printf(
				'<input type="checkbox" id="imgseo_change_image_alt" name="yoimg_seo_settings[imgseo_change_image_alt]" value="TRUE" %s />
				<p class="description">' . __( 'If checked alt will be replaced with the expression here below', YOIMG_DOMAIN ) . '</p>',
				$this->seo_options['imgseo_change_image_alt'] ? 'checked="checked"' : ( YOIMG_DEFAULT_IMGSEO_CHANGE_IMAGE_ALT && ! isset( $this->seo_options['imgseo_change_image_alt'] ) ? 'checked="checked"' : '' )
			);
		}
	
		public function imgseo_image_alt_expression_callback() {
			printf(
			'<input type="text" id="imgseo_image_alt_expression" name="yoimg_seo_settings[imgseo_image_alt_expression]" value="%s" class="imgseo_change_image_alt-dep" />
				<p class="description">' . __( 'expression used to replace the alt, accepted values are:', YOIMG_DOMAIN ) . ' ' . implode( ', ', apply_filters( 'yoimg_supported_expressions', array() ) ) . '</p>',
					! empty( $this->seo_options['imgseo_image_alt_expression'] ) ? esc_attr( $this->seo_options['imgseo_image_alt_expression'] ) : YOIMG_IMGSEO_IMAGE_ALT_EXPRESSION
			);
		}
	
		public function imgseo_change_image_filename_callback() {
			printf(
				'<input type="checkbox" id="imgseo_change_image_filename" name="yoimg_seo_settings[imgseo_change_image_filename]" value="TRUE" %s />
				<p class="description">' . __( 'If checked the filename will be replaced with the expression here below', YOIMG_DOMAIN ) . '</p>',
				$this->seo_options['imgseo_change_image_filename'] ? 'checked="checked"' : ( YOIMG_DEFAULT_IMGSEO_CHANGE_IMAGE_FILENAME && ! isset( $this->seo_options['imgseo_change_image_filename'] ) ? 'checked="checked"' : '' )
			);
		}
	
		public function imgseo_image_filename_expression_callback() {
			printf(
			'<input type="text" id="imgseo_image_filename_expression" name="yoimg_seo_settings[imgseo_image_filename_expression]" value="%s" class="imgseo_change_image_filename-dep" />
				<p class="description">' . __( 'expression used to replace the filename, accepted values are:', YOIMG_DOMAIN ) . ' ' . implode( ', ', apply_filters( 'yoimg_supported_expressions', array() ) ) . '</p>',
					! empty( $this->seo_options['imgseo_image_filename_expression'] ) ? esc_attr( $this->seo_options['imgseo_image_filename_expression'] ) : YOIMG_IMGSEO_IMAGE_FILENAME_EXPRESSION
			);
		}
	
		public function search_is_active_callback() {
			printf(
			'<input type="checkbox" id="search_is_active" name="yoimg_search_settings[search_is_active]" value="TRUE" %s />
				<p class="description">' . __( 'If checked free stock images search is active', YOIMG_DOMAIN ) . '</p>',
						$this->search_options['search_is_active'] ? 'checked="checked"' : ( YOIMG_DEFAULT_SEARCH_ENABLED && ! isset( $this->search_options['search_is_active'] ) ? 'checked="checked"' : '' )
			);
		}
	
		public function sanitize_seo( $input ) {
			$new_input = array();
			if( isset( $input['imgseo_change_image_title'] ) && ( $input['imgseo_change_image_title'] === 'TRUE' || $input['imgseo_change_image_title'] === TRUE ) ) {
				$new_input['imgseo_change_image_title'] = TRUE;
			} else {
				$new_input['imgseo_change_image_title'] = FALSE;
			}
			if( isset( $input['imgseo_change_image_alt'] ) && ( $input['imgseo_change_image_alt'] === 'TRUE' || $input['imgseo_change_image_alt'] === TRUE ) ) {
				$new_input['imgseo_change_image_alt'] = TRUE;
			} else {
				$new_input['imgseo_change_image_alt'] = FALSE;
			}
			if( isset( $input['imgseo_change_image_filename'] ) && ( $input['imgseo_change_image_filename'] === 'TRUE' || $input['imgseo_change_image_filename'] === TRUE ) ) {
				$new_input['imgseo_change_image_filename'] = TRUE;
			} else {
				$new_input['imgseo_change_image_filename'] = FALSE;
			}
			if( isset( $input['imgseo_image_title_expression'] ) && ! empty( $input['imgseo_image_title_expression'] ) ) {
				$new_input['imgseo_image_title_expression'] = sanitize_text_field( $input['imgseo_image_title_expression'] );
			} else {
				add_settings_error( 'yoimg_crop_options_group', 'imgseo_image_title_expression', __( 'title expression is not valid, using default:', YOIMG_DOMAIN ) . ' ' . YOIMG_DEFAULT_IMGSEO_IMAGE_TITLE_EXPRESSION, 'error' );
				$new_input['imgseo_image_title_expression'] = YOIMG_DEFAULT_IMGSEO_IMAGE_TITLE_EXPRESSION;
			}
			if( isset( $input['imgseo_image_alt_expression'] ) && ! empty( $input['imgseo_image_alt_expression'] ) ) {
				$new_input['imgseo_image_alt_expression'] = sanitize_text_field( $input['imgseo_image_alt_expression'] );
			} else {
				add_settings_error( 'yoimg_crop_options_group', 'imgseo_image_alt_expression', __( 'alt expression is not valid, using default:', YOIMG_DOMAIN ) . ' ' . YOIMG_DEFAULT_IMGSEO_IMAGE_ALT_EXPRESSION, 'error' );
				$new_input['imgseo_image_alt_expression'] = YOIMG_DEFAULT_IMGSEO_IMAGE_ALT_EXPRESSION;
			}
			if( isset( $input['imgseo_image_filename_expression'] ) && ! empty( $input['imgseo_image_filename_expression'] ) ) {
				$new_input['imgseo_image_filename_expression'] = sanitize_text_field( $input['imgseo_image_filename_expression'] );
			} else {
				add_settings_error( 'yoimg_crop_options_group', 'imgseo_image_filename_expression', __( 'filename expression is not valid, using default:', YOIMG_DOMAIN ) . ' ' . YOIMG_DEFAULT_IMGSEO_IMAGE_FILENAME_EXPRESSION, 'error' );
				$new_input['imgseo_image_filename_expression'] = YOIMG_DEFAULT_IMGSEO_IMAGE_FILENAME_EXPRESSION;
			}
			return $new_input;
		}
	
		public function sanitize_search( $input ) {
			$new_input = array();
			if( isset( $input['search_is_active'] ) && ( $input['search_is_active'] === 'TRUE' || $input['search_is_active'] === TRUE ) ) {
				$new_input['search_is_active'] = TRUE;
			} else {
				$new_input['search_is_active'] = FALSE;
			}
			return $new_input;
		}
		
	}
	
	new YoImagesSettingsPage();

}
