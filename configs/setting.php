<?php
/**
 * Technote Configs Setting
 *
 * @version 1.1.21
 * @author technote-space
 * @since 1.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

return [

	'10' => [
		'Performance' => [
			'10' => [
				'minify_js'  => [
					'label'   => 'Whether to minify js which generated by this plugin.',
					'type'    => 'bool',
					'default' => true,
				],
				'minify_css' => [
					'label'   => 'Whether to minify css which generated by this plugin.',
					'type'    => 'bool',
					'default' => true,
				],
			],
		],
	],

	'999' => [
		'Others' => [
			'10' => [
				'admin_menu_position'     => [
					'label'   => 'Admin menu position',
					'type'    => 'int',
					'default' => 100,
					'min'     => 0,
				],
				'check_update'            => [
					'label'   => 'Whether to check develop update.',
					'type'    => 'bool',
					'default' => true,
				],
				'assets_version'          => [
					'label'   => 'Assets Version',
					'type'    => 'string',
					'default' => '',
				],
				'use_admin_ajax'          => [
					'label'   => 'Use admin-ajax.php instead of wp-json.',
					'type'    => 'bool',
					'default' => false,
				],
				'get_nonce_check_referer' => [
					'label'   => 'Whether to check referer when get nonce.',
					'type'    => 'bool',
					'default' => true,
				],
				'check_referer_host'      => [
					'label'   => 'Server host which used to check referer host.',
					'default' => function ( $app ) {
						/** @var \Technote $app */
						return $app->input->server( 'HTTP_HOST', '' );
					},
				],
			],
		],
	],

];