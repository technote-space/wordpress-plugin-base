<?php
/**
 * Technote Views Admin Include Custom Post Textarea
 *
 * @version 2.9.1
 * @author technote-space
 * @since 2.8.3
 * @since 2.9.1 Improved: enable to overwrite args
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
$attr         = $instance->app->utility->array_get( $column, 'attributes', [] );
$attr['rows'] = $instance->app->utility->array_get( $column, 'rows', 5 );
?>
<?php $instance->form( 'textarea', [
	'name'       => $prefix . $name,
	'id'         => $prefix . $name,
	'value'      => $instance->old( $prefix . $name, $data, $name, $instance->app->utility->array_get( $column, 'default', '' ) ),
	'attributes' => $attr,
], $instance->app->utility->array_get( $column, 'args', [] ) ); ?>
