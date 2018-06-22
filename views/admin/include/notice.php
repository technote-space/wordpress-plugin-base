<?php
/**
 * Technote Views Admin Include Notice
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Traits\Presenter $instance */
/** @var array $errors */
/** @var array $messages */
?>
<?php if ( ! empty( $errors ) ): ?>
    <div class="error <?php $instance->id(); ?>-admin-message">
        <ul>
			<?php foreach ( $errors as list( $m, $escape ) ): ?>
                <li><p><?php $instance->h( $m, true, true, $escape ); ?></p></li>
			<?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php if ( ! empty( $messages ) ): ?>
    <div class="updated <?php $instance->id(); ?>-admin-message">
        <ul>
			<?php foreach ( $messages as list( $m, $escape ) ): ?>
                <li><p><?php $instance->h( $m, true, true, $escape ); ?></p></li>
			<?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>