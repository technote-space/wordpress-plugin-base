<?php
/**
 * Technote Views Include Img
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
/** @var string $endpoint */
/** @var string $namespace */
/** @var string $nonce */
/** @var array $functions */
/** @var string $class */
?>

<script>
    (function ($) {
        class <?php $instance->h( $class );?> {
            constructor() {
                this.endpoint = '<?php $instance->h( $endpoint . $namespace );?>/';
                this.functions = <?php echo json_encode( $functions );?>;
            }

            ajax(func, args) {
                if (this.functions[func]) {
                    const setting = this.functions[func];
                    let url = this.endpoint + setting.endpoint;
                    const method = setting.method.toUpperCase();
                    const config = {
                        method: method,
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php $instance->h( $nonce );?>');
                        }
                    };
                    switch (method) {
                        case 'POST':
                        case 'PUSH':
                            config.data = args;
                            break;
                        default:
                            const query = [];
                            for (const prop in args) {
                                if (args.hasOwnProperty(prop)) {
                                    query.push(prop + '=' + encodeURIComponent(args[prop]));
                                }
                            }
                            if (query.length) {
                                url += '?' + query.join('&');
                            }
                            break;
                    }
                    config.url = url;
                    return $.ajax(config);
                } else {
                    const defer = $.Deferred();
                    setTimeout(function () {
                        defer.reject();
                    }, 1);
                    return defer;
                }
            }
        }

        window.<?php $instance->h( $class );?> = new <?php $instance->h( $class );?> ();
    })(jQuery);
</script>
