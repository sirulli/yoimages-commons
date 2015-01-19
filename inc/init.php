<?php
if (! defined ( 'ABSPATH' )) {
	die ( 'No script kiddies please!' );
}

if ( ! defined( 'YOIMG_COMMONS_PATH' ) ) {

	define ( 'YOIMG_COMMONS_PATH', dirname ( __FILE__ ) );
	define ( 'YOIMG_SUPPORTED_LOCALES', 'en_US it_IT de_DE nl_NL' ); 
	
	require_once (YOIMG_COMMONS_PATH . '/utils.php');
	
	if (is_admin ()) {
	
		define ( 'YOIMG_COMMONS_URL', plugins_url ( plugin_basename ( YOIMG_COMMONS_PATH ) ) );
		
		global $yoimg_plugins_url;
		$yoimg_plugins_url = array (
			'yoimages-crop' => 'https://github.com/sirulli/yoimages-crop',
			'yoimages-seo' => 'https://github.com/sirulli/yoimages-seo'
		);
		
		define ( 'YOIMG_DOMAIN', 'yoimg' );
		define ( 'YOIMG_LANG_REL_PATH', plugin_basename ( YOIMG_COMMONS_PATH . '/languages/' ) );
		load_plugin_textdomain ( YOIMG_DOMAIN, FALSE, YOIMG_LANG_REL_PATH );
		
		require_once (YOIMG_COMMONS_PATH . '/settings.php');
	}
	if (! function_exists( 'yoimg_settings_load_styles_and_scripts' ) ) {
		function yoimg_settings_load_styles_and_scripts($hook) {
			if (isset ( $_GET ['page'] ) && $_GET ['page'] === 'yoimg-settings') {
				wp_enqueue_script ( 'yoimg-settings-js', YOIMG_COMMONS_URL . '/js/yoimg-settings.js', array (
						'jquery' 
				), false, true );
			}
		}
		add_action ( 'admin_enqueue_scripts', 'yoimg_settings_load_styles_and_scripts' );
	}

}