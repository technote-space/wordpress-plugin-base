<?php
/**
 * Technote Views Admin Style Setting
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
$instance->add_style_view( 'admin/style/table' );
?>
<style>
    #<?php $instance->id();?>-main-contents table .<?php $instance->id(); ?>-setting-detail {
        float: right;
    }
</style>