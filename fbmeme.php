<?php
/*
Plugin Name: FBMeme Button
Plugin URI: http://bijayrungta.com/fbmeme-plugin-for-wordpress-to-show-count-of-facebook-shares-and-to-share
Description: Adds the FBMeme button into your posts and RSS feed.
Version: 1.0.1
Author: Bijay Rungta AKA @rungss Spreading Knowledge..
Author URI: http://bijayrungta.com
*/

// As Recommended by Sunny(mypaaji.com) was here. to use wp_enqueue script instead of adding js via script tag :)
wp_enqueue_script("fbShare", "http://static.ak.fbcdn.net/connect.php/js/FB.Share");

function fm_options() {
    add_options_page('FBMeme Settings', 'FBMeme', 8,
        basename(__FILE__), 'fm_options_page');
}

/* Code added by Eric Canon - http://linkup.com */
function fm_generate_button() {
    global $post;
    echo "\n<!-- Here I am inside fm_generate_button -->";
    $button = '';
    $url = '';
    if (get_post_status($post->ID) == 'publish') {
        $url = get_permalink();
    }

    $style = get_option('fm_style');
    $button .= <<<EOF
<div id="fbmeme_button" style="{$style}">
<a name="fb_share" type="box_count" share_url='$url'>Share</a>
</div>
EOF;
    return $button;
}

function fm_update($content) {
    global $post;
//    echo "\n<!-- Here I am inside fm_update -->";

    // add the manual option, code added by kovshenin
    if (get_option('fm_where') == 'manual') {
        return $content;
    }

    if (get_option('fm_display_page') == null && is_page()) {
        return $content;
    }

    if (get_option('fm_display_feed') == null && is_feed()) {
        return $content;
    }

    $button = fm_generate_button();
    // Before and After code added by http://www.jimyaghi.com
    if (get_option('fm_where') == 'beforeandafter') {
        return $button . $content . $button;
    } else if (get_option('fm_where') == 'before') {
        return $button . $content;
    } else {
        return $content . $button;
    }
}

// Manual output
function fbmeme() {
    if (get_option('fm_where') == 'manual') {
        return fm_generate_button();
    } else {
        return false;
    }
}

// Remove the filter excerpts
// Code added by Soccer Dad
function fm_remove_filter($content) {
    remove_action('the_content', 'fm_update');
    return $content;
}

function fm_ping($post_id) {
    // do we have curl
//    if ((get_option('fm_ping') != 'off') && function_exists('curl_init')) {
//        $url = get_permalink($post_id);
//        // create a new cURL resource
//        $ch = curl_init();
//
//        // set URL and other appropriate options
//        curl_setopt($ch, CURLOPT_URL, 'http://api.tweetmeme.com/ping.php?url='
//            . urlencode($url));
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
//        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//
//        // grab URL and pass it to the browser
//        curl_exec($ch);
//
//        // close cURL resource, and free up system resources
//        curl_close($ch);
//    }
}

function fm_options_page() {
?>
  <div class="wrap">
    <div class="icon32" id="icon-options-general"><br/></div>
    <h2>Settings for FBmeme Integration</h2>
    <p>This plugin will install the FBmeme widget for each of your blog posts in both the content of your posts and the RSS feed.
      It can be easily styled in your blog posts and is referenced by the id <code>fbmeme_button</code>.
    </p>
    <form method="post" action="options.php">
    <?php
        // New way of setting the fields, for WP 2.7 and newer
        if(function_exists('settings_fields')) {
            settings_fields('fm-options');
        } else {
            wp_nonce_field('update-options');
            ?>
      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="page_options"
          value="fm_ping,fm_where,fm_style,fm_version,fm_display_page,fm_display_feed,fm_source" />
            <?php
        }
    ?>
      <table class="form-table">
        <tr>
          <th scope="row">
            Position
          </th>
          <td>
            <p>
              <input type="radio" value="before" <?php
            if (get_option('fm_where') == 'before') echo 'checked="checked"';
            ?> id="fm_where_before" name="fm_where" group="fm_where"/>
                        <label for="fm_where_before">Before the content of your post</label>
            </p>
            <p>
                <input type="radio" value="after" <?php
                if (get_option('fm_where') == 'after') echo 'checked="checked"';
                ?> name="fm_where" id="fm_where_after" group="fm_where" />
                <label for="fm_where_after">After the content of your post</label>
            </p>
            <p>
                <input type="radio" value="beforeandafter" <?php
                if (get_option('fm_where') == 'beforeandafter') echo 'checked="checked"';
                ?> name="fm_where" id="fm_where_both" group="fm_where"/>
                <label for="fm_where_both">Before AND After the content of your post</label>
            </p>
            <p>
                <input type="radio" value="manual" <?php
                if (get_option('fm_where') == 'manual') echo 'checked="checked"';
                ?> name="fm_where" id="fm_where_manual" group="fm_where"/>
                <label for="fm_where_manual">Manually call the retweet button</label> <br/>
                <span class="setting-description">You can manually call the <code>fbmeme()</code> function. E.g.
                <code>if (function_exists('fbmeme')) echo fbmeme();</code>.
                <br/> N.B. this will disable the display of the button in your feed and pages.</span>
            </p>
          </td>
        </tr>
        <tr>
            <th scope="row">
                Display
            </th>
            <td>
                <p>
                    <input type="checkbox" value="1" <?php
                        if (get_option('fm_display_page') == '1') echo 'checked="checked"';
                        ?> name="fm_display_page" id="fm_display_page" group="fm_display"/>
                    <label for="fm_display_page">Display the button on pages</label>
                </p>
                <p>
                    <input type="checkbox" value="1" <?php
                    if (get_option('fm_display_feed') == '1') echo 'checked="checked"';
                    ?> name="fm_display_feed" id="fm_display_feed" group="fm_display" />
                    <label for="fm_display_feed">Display the button on your feed</label>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="fm_style">Styling</label></th>
            <td>
                <input type="text" value="<?php echo htmlspecialchars(get_option('fm_style')); ?>" name="fm_style" id="fm_style" />
                <span class="setting-description">Add style to the div that surrounds the button E.g. <code>float: left; margin-right: 10px;</code></span>
            </td>
        </tr>
      </table>
      <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
      </p>
    </form>
  </div>
<?php
}

// On access of the admin page, register these variables (required for WP 2.7 & newer)
function fm_init() {
    if (function_exists('register_setting')) {
        register_setting('fm-options', 'fm_display_feed');
        register_setting('fm-options', 'fm_display_page');
        // register_setting('fm-options', 'fm_source', 'fm_sanitize_username');
        register_setting('fm-options', 'fm_style');
        register_setting('fm-options', 'fm_version');
        register_setting('fm-options', 'fm_where');
        // register_setting('fm-options', 'fm_ping');
    }
}

function fm_sanitize_username($username) {
    return preg_replace('/[^A-Za-z0-9_]/','',$username);
}

// Only all the admin options if the user is an admin
if(is_admin()) {
    add_action('admin_menu', 'fm_options');
    add_action('admin_init', 'fm_init');
}

//Set the default options when the plugin is activated
function fm_activate() {
    add_option('fm_where');
    add_option('fm_source');
    add_option('fm_style', 'float: left; margin-right: 10px;');
    add_option('fm_version', 'large');
    add_option('fm_display_page', '1');
    add_option('fm_display_feed', '1');
    // add_option('fm_ping', 'on');
}

add_filter('the_content', 'fm_update');
add_filter('get_the_excerpt', 'fm_remove_filter', 9);

// add_action('publish_post', 'fm_ping', 9);

register_activation_hook( __FILE__, 'fm_activate' );