<?php
$rrw_old_in_set_display_errors = ini_set("display_errors", true);
// The following files are in the ww-include folder
require_once "randomTrailPicture.php";
require_once "display_tables_class.php";
require_once "freewheelingeasy-wpdpExtra.php";
require_once "rrw_util_inc.php";
require_once "rrwParam.php";
require_once "theme_update_check.php";
// the following is called directly durning the processing of // https::pictures...
// roys-picture-processing/searchBox.php
// cause downstream tasks to use this trailid i.e freeWheelParam::String("trailid") pickups the _cookie
if (array_key_exists("trailid", $_GET)) {
    $trailid = $_GET["trailid"];
    setcookie("trailid", $trailid, time() + (60 * 60 * 24), "/");  // for one day
}
// ***************************** add the copyright message control
function rrw_trail_customize_register($wp_customize)
{
    // Add all your Customize content (i.e. Panels, Sections, Settings & Controls) here...
    $wp_customize->add_setting(
        'rrwtheme-footer-copyright',
        array(
            'default' => 'Copyright 2020 ',
            'transport' => 'refresh',
        )
    );
    $book_design_host = 'Site design, hosting by the book <a class="external"
			href="https://freewheelingeasy.com/"> FreeWheeling Easy NE</a> ';
    $book_host = 'Site hosting by the book <a class="external"
			href="https://freewheelingeasy.com/"> FreeWheeling Easy NE</a> ';
    $artincc = 'ARTinCC and Roy Weil - Site design, hosting by the book <a class="external"
			href="https://freewheelingeasy.com/"> FreeWheeling Easy NE</a> ';
    $roy_design_host = "Site design, hosting by <a class='external'
			href='https://brokenlinks.royweil.com' >Roy Weil Consulting</a>";
    $shawWeil_design_host = "Site design, hosting by <a class='external'
			href='https://freewheelingeasy.com/'> Shaw Weil Associates";
    $copyRights = array(
        "Copyright &copy; 2020, $artincc" => 'artincc',
        "Copyright &copy; 2021, $book_design_host" => 'book_design_host',
        "Copyright &copy; 2026 by $book_host" => 'book_host',
        "Copyright &copy; 2020 by Bicycle Access Council, $book_host" => 'BAC book',
        "Copyright &copy; 2020 by Weil/Ertell Family geology, $book_host" => 'family book',
        "Copyright &copy; 2020, $roy_design_host" => 'roy_design_host',
        "Copyright &copy; 2020 by $shawWeil_design_host" => 'shawWeil_design_host',
        "Copyright &copy; 2020 by Eire to Pittsburgh Trail Alliance
		<a href='https://eriepittsburghtrail.org/get_involved/sponsorssupporters/'> and others</a>, $book_host" => 'Erie Pittsburgh'
    );
    $wp_customize->add_control(
        'rrwtheme-footer-copyright',
        array(
            'label' => esc_html__('Copyright Designation'),
            'description' => esc_html__('in middle of footer'),
            'section' => 'title_tagline',
            'priority' => 10, // Optional. Order priority to load the control. Default: 10
            'type' => 'select',
            'capability' => 'edit_theme_options', // Optional. Default: 'edit_theme_options'
            'choices' => $copyRights
        )
    );
    /*******************************************
    Color scheme
     ********************************************/
    // add the section to contain the settings
    // main color ( site title, h1, h2, h4. h6, widget headings, nav links, footer headings )
    $txtcolors[] = array(
        'slug' => 'freewheelingeasy_menu_footer_background_color',
        'default' => '#f7f4e6',
        'label' => 'Menu, Footer background Color'
    );
    $txtcolors[] = array(
        'slug' => 'freewheelingeasy_menu_footer_text_color',
        'default' => '#000000',
        'label' => 'Menu, Footer text Color'
    );
    // add the settings and controls for each color
    foreach ($txtcolors as $txtcolor) {
        // SETTINGS
        $wp_customize->add_setting(
            $txtcolor['slug'],
            array(
                'default' => $txtcolor['default'],
                'type' => 'option',
                'capability' => 'edit_theme_options'
            )
        );
        // CONTROLS
        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                $txtcolor['slug'],
                array(
                    'label' => $txtcolor['label'],
                    'section' => 'colors',
                    'settings' => $txtcolor['slug']
                )
            )
        );
    }
    /*
    	$wp_customize->add_control(
    		new WP_Customize_Color_Control(
    			$wp_customize,
    			'header/footer bar color',
    			array(
    				'label' => __( 'header/footer bar', 'mytheme' ),
    				'section' => 'colors',
    			)
    		)
    	);
    */
}
add_action('customize_register', 'rrw_trail_customize_register');
//  **************************** requre the rrw utilities plugin to be installed.
register_activation_hook(__FILE__, 'rrw_trail_child_plugin_activate');
function rrw_trail_child_plugin_activate()
{
    // Require parent plugin
    if (!is_plugin_active('rrw-utilities-common/rrw_util_inc.php') and current_user_can('activate_plugins')) {
        // Stop activation redirect and show error
        wp_die('Sorry, but this theme requires the rrw-utilities-common to be installed and active. <br><a href="' . admin_url('plugins.php') . '">&laquo; Return to Plugins</a>');
    }
    //			require top-banner-directory
    $dir = "/Top-Banner-Images/";
    $pathArray = wp_upload_dir();
    $path = $pathArray["basedir"] . $dir;
    if (!is_dir($path)) {
        mkdir($path);
    }
    //	******************************* fix WP File Manger to look at just upload
    $rrw_trail_dirs = array("file-manager/file-manager.php", "wp-file-manager/file_folder_manager.php");
    $rrw_trail_debug = false;
    messWithOtherPoeplesCode($rrw_trail_dirs, false);
    // the below code dose not work on multisites
}
// ************************************  Function to include the child stytle sheet
function rrw_trail_example_enqueue_styles()
{
    wp_enqueue_style('twenty-thirteen-theme', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'rrw_trail_example_enqueue_styles');
// 	*******************************  check for updates to the roy-header theme
require_once "theme_update_check.php";
$MyUpdateChecker = new ThemeUpdateChecker(
    'roys-header',
    'https://pluginserver.royweil.com/roys-header.php'
    //  'https://kernl.us/api/v1/theme-updates/58324bb2bdf1417153c8e59e/'
);
// *******************************  Broken like checker youtube api
add_filter('blc_youtube_api_key', function ($api_key) {
    $a_i_p_ = freewheelAPI::getAPIkey("youtube");
    $a_i_p_ = str_replace("key=", "", $a_i_p_);
    return $a_i_p_;
});
//  *******************************  hide a photo if this is a mobile/phone device
function rrw_if_phone_hide_photo($att = array())
{
    if (array_key_exists("item", $att))
        $msg = $att["item"];
    else
        $msg = "";
    $browser = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($browser, "Mobile") > 0) {
        $desktop = false;
    } else {
        $desktop = true;
    }
    if ($desktop)
        return $msg;
    else
        return "";
}
add_shortcode("ifphonehide", "rrw_if_phone_hide_photo");
// the below code dose not work on multisites
function messWithOtherPoeplesCode($rrw_trail_dirs, $rrw_trail_debug = false)
{
    foreach ($rrw_trail_dirs as $rrw_trail_dir) {
        if ($rrw_trail_debug) echo "look for file $rrw_trail_dir <br />\n";
        $rrw_trail_source = WP_CONTENT_DIR . "/plugins/$rrw_trail_dir";;
        if ($rrw_trail_debug) echo "look for path $rrw_trail_source <br />\n";
        if (file_exists($rrw_trail_source)) {
            if ($rrw_trail_debug) echo "found file $rrw_trail_dir <br />\n";
            $rrw_trail_buffer = file_get_contents($rrw_trail_source);
            if ($rrw_trail_debug) echo 'size of buffer ' . strlen($rrw_trail_buffer) . "<br />\n";
            // we have the commands in $rrw_trail_buffer
            if (!strpos($rrw_trail_buffer, "Code added and path")) { // have we done this
                if ($iiAutoLoad = strpos($rrw_trail_buffer, "autoload.php';")) {
                    $rrw_trail_buffer = substr($rrw_trail_buffer, 0, $iiAutoLoad + 14) . '
				// Code added and path and url modified by roys-header theme
				$pathArray = wp_upload_dir();		// information about the upload directory
				$path = $pathArray["basedir"];		// the actual path
				$url = $pathArray["baseurl"];
				' . substr($rrw_trail_buffer, $iiAutoLoad + 15);
                } else {
                    print("iiAutoLoad equals $iiAutoLoad");
                }
            }
            rrw_trail_replace("manage_options", "upload_files", $rrw_trail_buffer, $rrw_trail_source);
            rrw_trail_replace("=> ABSPATH", '=> $path', $rrw_trail_buffer, $rrw_trail_source);
            rrw_trail_replace("=> site_url(),", '=> $url,', $rrw_trail_buffer, $rrw_trail_source);
            rrw_trail_replace(
                "add_submenu_page(",
                "//  removed by roys-header xdd_submenu_page(",
                $rrw_trail_buffer,
                $rrw_trail_source
            );
        }
    }
}
function rrw_trail_replace($rrw_trail_needle, $rrw_trail_replace, $rrw_trail_haystack, $rrw_trail_file)
{
    $rrw_trail_replace_debug = false;
    if ($rrw_trail_replace_debug)
        echo "looking for $rrw_trail_needle replaceing with $rrw_trail_replace <br />\n";
    if (strpos($rrw_trail_haystack, $rrw_trail_needle)) {
        if ($rrw_trail_replace_debug)
            echo "found $rrw_trail_needle replaceing with $rrw_trail_replace <br />\n";
        $rrw_trail_haystack = str_replace(
            $rrw_trail_needle,
            $rrw_trail_replace,
            $rrw_trail_haystack
        );
        $rrw_trail_result = file_put_contents($rrw_trail_file, $rrw_trail_haystack);
        if ($rrw_trail_replace_debug) {
            if (!$rrw_trail_result) {
                echo "roys-header filed to update the $rrw_trail_file file <br />\n";
            }
        }
    }
}
// *************************** Function to create a policy page
function rrw_trailPolicypage($attrs)
{
    // uses the shortcode [rweil-trail-privacy-policy] to display a Privacy Policy
    // that is generic enough for a trail related business.
    $hideMembership = rrwParam::Boolean("hidemembership", $attrs);
    print "hide membership = $hideMembership ----";
    $content = "<p>Information we collect for ";
    if (!$hideMembership)
        $content .= "membership registrations and";
    $content .= "news updates are limited to ";
    $content .= esc_attr(get_bloginfo('name', 'display'));
    $content .= " officials on a need-to-know basis. This information is used to contact you about trail-related news and activities";
    if (!$hideMembership)
        $content .= ", as well as membership-related items (such as renewal notices)";
    $content .= ". We promise not to abuse your contact information. It will not be sold to a third party.</p>
<p>If you donate through PayPal, we actually do not receive your credit card information. The only thing we receive from PayPal is your donation amount and the contact information attached with it (which becomes part of your membership record). For <strong>PayPal's </strong>Privacy Policy, visit<b> <a class='external' href='https://www.paypal.com/us/webapps/mpp/ua/privacy-full' target='_blank'>https://www.paypal.com/us/cgi-bin/marketingweb?cmd=p/gen/ua/policy_privacy-outside</a></b></p>
<p>We also accept donations made through the United Way. For information on the <b>United Way's </b>Privacy Policy, visit <a class='external' href='https://www.unitedway.org/privacy-policy' target='_blank'>https://www.unitedway.org/privacy-policy</a></p>
<p>We track website usage history via Google Analytics. For <b>Google Analytics'</b> Privary Policy, visit <a class='external' href='https://support.google.com/analytics/answer/6004245?hl=en'>https://support.google.com/analytics/answer/6004245?hl=en</a></p>
";
    return $content;
}
// -------------------------------------  cause it to happen
add_shortcode("rrw-trail-policy-page", 'rrw_trailPolicypage');
function rrw_trail_buildPolicypage()
{
    // do not do this again. it unlnks comments
    //  ---------------------------------------- Delete the past pages
    $pageName = "Privacy Policy";
    $num = post_exists($pageName);
    //	if ( $num != 0 )
    //		return; // page already exists
    //  --------------------------------------- build the pages
    $user_id = get_current_user_id();
    $content = "Information we collect for membership registrations and news updates are limited to SVTC officials on a need-to-know basis. This information is used to contact you about trail-related news and activities, as well as membership-related items (such as renewal notices). We promise not to abuse your contact information. It will not be sold to a third party.
If you donate through PayPal, we actually do not receive your credit card information. The only thing we receive from PayPal is your donation amount and the contact information attached with it (which becomes part of your membership record). For <strong>PayPal's </strong>Privacy Policy, visit<b> <a class='external' href='https://www.paypal.com/us/webapps/mpp/ua/privacy-full' target='_blank'>https://www.paypal.com/us/cgi-bin/marketingweb?cmd=p/gen/ua/policy_privacy-outside</a></b>
We also accept donations made through the United Way. For information on the <b>United Way's </b>Privacy Policy, visit <a class='external' href='https://www.unitedway.org/privacy-policy' target='_blank'>https://www.unitedway.org/privacy-policy</a>
We track website usage history via Google Analytics. For <b>Google Analytics'</b> Privary Policy, visit <a class='external' href='https://support.google.com/analytics/answer/6004245?hl=en'>https://support.google.com/analytics/answer/6004245?hl=en</a>";
    $defaults = array(
        'ID' => $num,
        'post_author' => $user_id,
        'post_content' => "$content",
        'post_title' => "$pageName",
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => "page $pageName",
        'comment_status' => 'open'
    );
    wp_insert_post($defaults);
}
// -------------------------------------  cause it to happen in activation of theme
add_action("after_setup_theme ", 'rrw_trail_buildPolicypage');
// -------------------------------------  switchname used to select different header based on url
function rrw_trail_SetSwitchName()
{
    global $eol;
    $siteUrl = site_url();
    if (strpos($siteUrl, "dinomitedays") !== false)
        $switchName = "dino";
    elseif (strpos($siteUrl, "creative") !== false)
        $switchName = "nudges";
    elseif (strpos($siteUrl, "edit.shaw-weil") !== false)
        $switchName = "edit";
    elseif (strpos($siteUrl, "eriepittsburgh") !== false)
        $switchName = "eriepittsburgh";
    elseif (strpos($siteUrl, "devpicture") !== false)
        $switchName = "pictureDev";
    elseif (strpos($siteUrl, "picture") !== false)
        $switchName = "picture";
    elseif (strpos($siteUrl, "ohio") !== false)
        $switchName = "ohio";
    elseif (strpos($siteUrl, "retrospective") !== false)
        $switchName = "clean";
    elseif (strpos($siteUrl, 'they-working') !== false)
        $switchName = "theyWorking";
    elseif (strpos($siteUrl, "tommarellogc") !== false)
        $switchName = "tommarellogc";
    elseif (strpos($siteUrl, "validate") !== false)
        $switchName = "validate";
    else
        $switchName = "normal";
    // allows testing of switchname in non-production
    $switch_parameter = rrwParam::String("switch");
    if (!empty($switch_parameter))

        $switchName = $switch_parameter;
    return $switchName;
}
/*
    //  ************************************ some found code that might be usefull

    // ******************** Function to change sender name
function rrw_trail_wpb_sender_name( $original_email_from ) {
    return 'Tim Smith';
}
add_filter( 'wp_mail_from_name', 'rrw_trail_wpb_sender_name' );
    //  ******************* Function to change email address
function rrw_trail_wpb_sender_email( $original_email_address ) {
    return 'tim.smith@example.com';
}
add_filter( 'wp_mail_from', 'rrw_trail_wpb_sender_email' );
//******************************  Function to add more icons to the page editor page
add_filter( 'tiny_mce_before_init', 'rrw_trail_myformatTinyMCE' );
function rrw_trail_myformatTinyMCE( $in ) {
    $in[ 'wordpress_adv_hidden' ] = FALSE;
    return $in;
}
*/