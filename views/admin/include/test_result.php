<?php
/**
 * Technote Views Admin Test
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
/** @var \Technote\Controllers\Admin\Base $instance */
/** @var array $args */
/** @var \PHPUnit_Framework_TestResult $result */
/** @var \Technote\Tests\Base $class */
$instance->add_style_view( 'admin/style/table' );
?>

<h2><?php $instance->h( $class->class_name ); ?></h2>
<table class="widefat striped">
    <tr>
        <td><?php $instance->h( 'Count', true ); ?></td>
        <td><?php $instance->h( $result->count() ); ?></td>
    </tr>
    <tr>
        <td><?php $instance->h( 'Elapsed Time', true ); ?></td>
        <td><?php $instance->h( $result->time() ); ?></td>
    </tr>
    <tr>
        <td><?php $instance->h( 'Passed', true ); ?></td>
        <td>
            <ul>
				<?php foreach ( $result->passed() as $test => $item ): ?>
                    <li><?php $instance->h( $test ); ?></li>
				<?php endforeach; ?>
            </ul>
        </td>
    </tr>
    <tr>
        <td><?php $instance->h( 'Warning', true ); ?></td>
        <td>
			<?php if ( $result->warningCount() > 0 ): ?>
                <ul>
					<?php foreach ( $result->warnings() as $item ): ?>
                        <li><?php $instance->h( $item->toString() ); ?></li>
					<?php endforeach; ?>
                </ul>
			<?php endif; ?>
        </td>
    </tr>
    <tr>
        <td><?php $instance->h( 'Errors', true ); ?></td>
        <td>
			<?php if ( $result->errorCount() > 0 ): ?>
                <ul>
					<?php foreach ( $result->errors() as $item ): ?>
                        <li><?php $instance->h( $item->toString() ); ?></li>
					<?php endforeach; ?>
                </ul>
			<?php endif; ?>
        </td>
    </tr>
    <tr>
        <td><?php $instance->h( 'Failure', true ); ?></td>
        <td>
			<?php if ( $result->failureCount() > 0 ): ?>
                <ul>
					<?php foreach ( $result->failures() as $item ): ?>
                        <li><?php $instance->h( $item->toString() ); ?></li>
					<?php endforeach; ?>
                </ul>
			<?php endif; ?>
        </td>
    </tr>
    <tr>
        <td><?php $instance->h( 'Risky', true ); ?></td>
        <td>
			<?php if ( $result->riskyCount() > 0 ): ?>
                <ul>
					<?php foreach ( $result->risky() as $item ): ?>
                        <li><?php $instance->h( $item->toString() ); ?></li>
					<?php endforeach; ?>
                </ul>
			<?php endif; ?>
        </td>
    </tr>
    <tr>
        <td><?php $instance->h( 'Not Implemented', true ); ?></td>
        <td>
			<?php if ( $result->notImplementedCount() > 0 ): ?>
                <ul>
					<?php foreach ( $result->notImplemented() as $item ): ?>
                        <li><?php $instance->h( $item->toString() ); ?></li>
					<?php endforeach; ?>
                </ul>
			<?php endif; ?>
        </td>
    </tr>
</table>
