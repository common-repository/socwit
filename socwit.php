<?php
/*
Plugin Name: SocWit
Plugin URI: https://socwit.com
Description: Socwit plugin
Version: 0.5
Author: Mkechinov
Author URI: https://mkechinov.ru
*/
define('LD_CONTENT_URL', get_option('siteurl') . '/wp-content');
define('LD_PLUGIN_URL', LD_CONTENT_URL . '/plugins/socwit');
define('LD_HOST', 'socwit.com');
define('LD_URL', 'https://' . LD_HOST);
define('LD_ID', 'socwit');
define('LD_URL_SETTING', get_option('siteurl') . '/wp-admin/edit-comments.php?page=' . LD_ID);

register_deactivation_hook(__FILE__, 'livdis_delete');

add_filter('the_content', 'append_livdis_script', 50);
add_action('admin_head', 'livdis_admin_head');

add_action('admin_menu', 'livids_add_pages');
add_action('admin_notices', 'livdis_messages');
add_filter("plugin_action_links_" . plugin_basename(__FILE__), 'plugin_add_settings_link');

function plugin_add_settings_link($links)
{
    $settings_link = '<a href="' . LD_URL_SETTING . '">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}

function append_livdis_script($s)
{

    if (get_option('livdis_uid')) {
        $uid = get_option('livdis_uid');
        $host = LD_URL;
        $script = <<<HTML
<div id="ld_comments_box"></div>
<script src="{$host}/js/api/comments.js"></script>
<script>LDApi({app_id: {$uid}});</script>
HTML;

        if (is_singular()) {
            return "<div class=\"livedisable\">{$s}</div>" . $script;
        } else {
            return $s;
        }
    } else {
        return $s;
    }
}

/**
 * Include styles and files in the admin
 */
function livdis_admin_head()
{
    $page = (isset($_GET['page']) ? $_GET['page'] : null);
    if ($page == LD_ID) {
        ?>
        <!--
        <link rel='stylesheet' href='<?= LD_PLUGIN_URL ?>/css/socwit.css' type='text/css'/>
        -->
        <style>
            .wrap, .wrap-auth {
                background: #daa30a;
            }

            .wrap-auth {
            }

            .ld-header {
                background: url(header_background.png) top right no-repeat #ebebeb;
                height: 228px;
                position: relative;
            }

            .ld-logo {
                display: block;
                width: 318px;
                height: 72px;
                background: url(logo.png) no-repeat;
                margin-left: 55px;
            }

            .ld-logo-bottom {
                background: url(logo_bottom.png) no-repeat;
                width: 430px;
                height: 87px;
                margin: 25px 0 0 55px;
            }

            .ld-content {
                padding: 35px;
            }

            #auth_frame {
                display: block;
                margin: 0 auto;
                width: 1px;
                height: 0;
                background: #daa30a;
                overflow: hidden;
            }

            .livdis_notificator {
                background: #daa20e;
                top: 49%;
                left: 50%;
                position: absolute;
                z-index: 10000;
                color: #1b1b1b !important;
                font-weight: bold;
                font-family: 'Tahoma', 'Arial';
                font-size: 18px;
                max-width: 400px;
                min-width: 100px;
                padding: 15px 45px 15px 15px;
                -webkit-box-shadow: 0 5px 5px 0 #777;
                -moz-box-shadow: 0 5px 5px 0 #777;
                box-shadow: 0 5px 5px 0 #777;
            }
        </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
        <script>jQueryLD = jQuery.noConflict(true);
            LD_HOST = "<?= LD_HOST ?>";</script>

        <!--
        <script src="<?= LD_PLUGIN_URL ?>/socwit.js"></script>
        -->

        <script type="text/javascript">

            /**
             * User: P.Nixx
             * Date: 07.11.12
             * Time: 17:20
             */
            (function () {
                var $ = jQueryLD;
                $(window).bind("message", function (e) {
                    e = e.originalEvent;
                    if (e.origin.match(new RegExp("^https:\/\/([^\.]+\.)?" + LD_HOST))) {
                        console.log(e);
                        var data = e.data,
                            param = data.match(/^ld_iframe_login:([^:]+):(.*)$/);

                        if (param && param[1]) {
                            switch (param[1]) {
                                case "css":
                                    var css = $.parseJSON(param[2]);
                                    $('#auth_frame').css(css);
                                    break;

                                case "auth":
                                    $('#auth_frame').remove();
                                    break;

                                case "host":
                                    e.source.postMessage(document.location.host, e.origin);
                                    break;

                                case "app_id":
                                    $('#auth_frame').remove();
                                    $('#livdis_uid').val(param[2]);
                                    $('#form_app').submit();
                                    break;
                            }
                        }

                        param = data.match(/^ld_iframe:([^:]+):(.*)$/);
                        if (param && param[1]) {
                            switch (param[1]) {
                                case "notificator":
                                    notificator(param[2]);
                                    break;
                            }
                        }
                    }
                });

                function notificator(message) {
                    $('.livdis_notificator').remove();

                    // Формируем HTML
                    var html = $('<div class="livdis_notificator"></div>').html('<div class="mw-close"></div>').append(message);

                    // Добавляем нотификатор и отображаем его
                    $('body').append(html);

                    html.css({
                        "margin-left": -html.width() / 2
                    });
                    $(html).fadeIn();

                    // Устанавливаем таймер на 3 секунды, после чего нотификатор должен быть уничтожен
                    var time = setInterval(function () {
                        $(html).remove();
                        clearInterval(time);
                    }, 3000);

                    html.click(function () {
                        clearInterval(time);
                        $(this).remove();
                    });
                }
            })();


        </script>

        <?php
    }
}

/**
 * Notice of setting the widget
 */
function livdis_messages()
{

    $page = (isset($_GET['page']) ? $_GET['page'] : null);
    if (!get_option('livdis_uid') && $page != LD_ID) {
        echo '<div class="updated"><p><b>' . __('You must <a href="edit-comments.php?page=' . LD_ID . '">configure the plugin</a> to enable Socwit.',
                LD_ID) . '</b></p></div>';
    }
}

/**
 * Action when uninstall plugin
 */
function livdis_delete()
{
    delete_option('livdis_id');
    delete_option('livdis_uid');
}

/**
 * Include manage file
 */
function livdis_options_page()
{
    if ($_POST['livdis_form_counter_sub'] == 'Y') {
        if (isset($_POST['livdis_uid'])) {
            update_option('livdis_uid', $_POST['livdis_uid']);
        } else {
            delete_option('livdis_uid');
        }
        echo '<div class="updated"><p><strong>' . __('Options saved', LD_ID) . '</strong></p></div>';
    }
    include_once(dirname(__FILE__) . '/manage.php');
}


/**
 * Insert menu in the Comments section
 */
function livids_add_pages()
{
    add_comments_page('Socwit Plugin Comments', 'Socwit', 'read', LD_ID, 'livdis_options_page');
}
