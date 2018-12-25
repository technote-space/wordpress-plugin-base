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
$attr = $instance->app->utility->array_get( $column, 'attributes', [] );
if ( isset( $column['maxlength'] ) ) {
	$attr['maxlength'] = $column['maxlength'];
}
$attr['placeholder'] = $instance->app->utility->array_get( $column, 'default', '' );
?>
<?php $instance->form( 'input/number', [
	'name'       => $prefix . $name,
	'id'         => $prefix . $name,
	'value'      => $instance->old( $prefix . $name, $data, $name ),
	'attributes' => $attr,
] ); ?>