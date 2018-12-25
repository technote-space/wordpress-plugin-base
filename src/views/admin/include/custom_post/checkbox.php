<?php
/**
 * Technote Views Admin Include Action_links
 *
 * @version 1.1.13
 * @author technote-space
 * @since 1.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Traits\Presenter $instance */
/** @var array $data */
/** @var array $column */
/** @var string $name */
/** @var string $prefix */
$attr    = [];
$default = ! empty( $instance->app->utility->array_get( $column, 'default' ) );
$val     = $instance->old( $prefix . $name, $data, $name, $default, true ) - 0;
if ( ! empty( $val ) ) {
	$attr['checked'] = 'checked';
}
?>
<?php $instance->form( 'input/checkbox', [
	'name'       => $prefix . $name,
	'id'         => $prefix . $name,
	'value'      => 1,
	'label'      => $instance->app->utility->array_get( $column, 'label', $instance->app->utility->array_get( $column, 'comment', $column['name'] ) ),
	'attributes' => $attr,
] ); ?>