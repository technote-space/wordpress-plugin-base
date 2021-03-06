<?php
/**
 * Technote Views Common Mail
 *
 * @version 2.10.0
 * @author technote-space
 * @since 2.9.0
 * @since 2.10.0 Changed: trivial change
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space/
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Interfaces\Presenter $instance */
/** @var string $subject */
/** @var string $body */
$instance->add_style_view( 'common/style/mail' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ja" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="shift_jis">
    <meta name="viewport" content="target-densitydpi=device-dpi,width=device-width,maximum-scale=1.0,user-scalable=yes">
    <!--    <meta name="viewport" content="width=device-width"/>-->
    <meta http-equiv="Content-Language" content="ja">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <title><?php $instance->h( $subject ); ?></title>
	<?php $instance->app->minify->output_css( true ); ?>
</head>
<!-- BODY -->
<body bgcolor="#FFFFFF">
<table class="body-wrap">
    <tr>
        <td class="container" bgcolor="#FFFFFF">
            <div class="content">
                <table>
                    <tr>
                        <td>
							<?php $instance->h( $body, false, true, false ); ?>
                        </td>
                    </tr>
                </table>
            </div><!-- /content -->
        </td>
    </tr>
</table><!-- /BODY -->
</body>