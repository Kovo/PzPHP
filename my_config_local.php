<?php
	global $PZPHP_CONFIG_ARRAY;

	$PZPHP_CONFIG_ARRAY['ROOT_URL'] = '';
	$PZPHP_CONFIG_ARRAY['ROOT_URI'] = '';
	$PZPHP_CONFIG_ARRAY['COOKIE_URL'] = '';
	$PZPHP_CONFIG_ARRAY['CSS_URL'] = $PZPHP_CONFIG_ARRAY['ROOT_URL'].'Resources/static/css/';
	$PZPHP_CONFIG_ARRAY['JS_URL'] = $PZPHP_CONFIG_ARRAY['ROOT_URL'].'Resources/static/js/';
	$PZPHP_CONFIG_ARRAY['IMG_URL'] = $PZPHP_CONFIG_ARRAY['ROOT_URL'].'Resources/static/images/';
	$PZPHP_CONFIG_ARRAY['FONT_URL'] = $PZPHP_CONFIG_ARRAY['ROOT_URL'].'Resources/static/fonts/';

	$PZPHP_CONFIG_ARRAY['LANGS']['en_us'] = array(
		'short' => 'en',
		'default' => true,
		'nid' => 1,
	);

	$PZPHP_CONFIG_ARRAY['DB_USER'] = '';
	$PZPHP_CONFIG_ARRAY['DB_PASSWORD'] = '';
	$PZPHP_CONFIG_ARRAY['DB_NAME'] = '';
	$PZPHP_CONFIG_ARRAY['DB_HOST'] = '';
	$PZPHP_CONFIG_ARRAY['DB_PORT'] = 3306;
