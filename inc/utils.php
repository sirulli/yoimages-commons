<?php
if (! defined ( 'ABSPATH' )) {
	die ( 'No script kiddies please!' );
}
function yoimg_log($message) {
	if (WP_DEBUG === true) {
		if (is_array ( $message ) || is_object ( $message )) {
			error_log ( print_r ( $message, true ) );
		} else {
			error_log ( $message );
		}
	}
}
function yoimg_register_module($module_id, $module_path, $has_settings = false) {
	global $yoimg_modules;
	if (! isset ( $yoimg_modules )) {
		$yoimg_modules = array ();
	}
	if (! isset ( $yoimg_modules [$module_id] )) {
		$module_loaded = false;
		$module_init_file = $module_path . '/vendor/sirulli/' . $module_id . '/inc/init.php';
		yoimg_log($module_init_file);
		if (file_exists ( $module_init_file )) {
			require_once ($module_init_file);
			$module_loaded = true;
		} else {
			$module_init_file = $module_path . '/inc/init.php';
			yoimg_log($module_init_file);
			if (file_exists ( $module_init_file )) {
				require_once ($module_init_file);
				$module_loaded = true;
			}
		}
		if ($module_loaded) {
			$yoimg_modules [$module_id] = array (
					'has-settings' => $has_settings 
			);
		} else {
			yoimg_log ( 'cannot load module ' . $module_id );
		}
	} else {
		yoimg_log ( 'TODO: show warning because already loaded ' . $module_id );
	}
}