<?php

/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 *
 */
$rrw_old_in_set_display_errors = ini_set( "display_errors", true );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
$rrw_trail_menu_footer_background_color = get_option(
    "freewheelingeasy_menu_footer_background_color", "black" );
$rrw_trail_menu_footer_text_color = get_option(
    "freewheelingeasy_menu_footer_text_color" );
$rrw_trail_footerCopyright = get_option( "rrwtheme-footer-copyright", "white" );


print "

</div>  <!-- #main -->

<!-- page created by footer.php  -->

<!--  --------------------------------------------------------------- footer begins here-->\n";

$switchName = rrw_trail_SetSwitshName();
if ( array_key_exists( "nohead", $_GET ) &&
    ( strcmp( "picture", $switchName ) == 0 || 
      strcmp( "picduredev", $switchName == 0 ) ) ) {
    return;
}

if ( array_key_exists( "nohead", $_GET ) ) {

    $obj_id = get_queried_object_id();

    $current_url = get_permalink( $obj_id );

    print "<!-- message as a result of nohead option -->

    <br/>Extracted " . date( "M d, Y" ) . " from <a href='$current_url' >$current_url</a> &nbsp; &nbsp; &nbsp; &nbsp; 

				$rrw_trail_footerCopyright<br />";

    return;

};


switch ( $switchName ) {
    case "tailonly":
        if ( is_user_logged_in() ) {
            print "<!-- Option is tailonly with a looged in user. therefore on display -->";
            break; // if  user logged in no footer, else fall thru to display
        }
    case "eriepittsburgh":
    case "normal":
    case "picture":
    case "picturedev":
    case "tailonly":
        rrwTrail_footer_default( $rrw_trail_menu_footer_background_color,
            $rrw_trail_menu_footer_text_color );
        break;
    case "dino":
        rrwTrail_footer_dino( $rrw_trail_menu_footer_background_color,
            $rrw_trail_menu_footer_text_color );
    case "clean":
        print "<!-- no footer displayed -->";
        break;
    case "theyworking":
        rrwTrail_footer_theyworking( $rrw_trail_menu_footer_background_color,
            $rrw_trail_menu_footer_text_color );
        break;
    default:
        print "<p>E#304 Unkown switchName of '$switchName' in header.php</p> ";
        break;
}
print "

<!-- #colophon -->

";

wp_footer();

print "

</body>

</html>";


function rrwTrail_footer_Picture( $backgroundcolor, $rrw_trail_menu_footer_text_color ) {


    print "

        <style >

            .site-footer {

            text-align:left;

            }

            .site-footer a {

            color:black;

            }

        </style>

        <footer id='colophon' class='site-footer' >

            <div class='site-info'>

                You are visiting Mary and Roy's Pictures page, the database of pictures taken

                 along the trails of the western Pennsylvania, provided by the authors of 

  <a class='external' style='color:black;'  href='https://freewheelingeasy.com'>FreeWheeling Easy in Western 

                Pennsylvania</a> and other trail enthusiasts. 

                <p>

                Photographs are copyright &copy; by the photographer unless otherwise noted. 

                If no other licensing arrangements are specified above, you may use the photograph for 

                nonprofit purposes without charge, provided you include a credit to the 

                photographer. Download the image by right-clicking on the image and choosing 

                'Save As'. This image will be suitable for use on a web page. Higher-resolution 

                versions of most images are available; send e-mail to mary [dot] shaw [at] 

                    cs.cmu.edu with your request, being sure to include the URL of this page.

                </p>

            </div>  <!-- end site-info -->

            </footer>

            ";

    return;

}


function rrwTrail_footer_default( $rrw_trail_menu_footer_background_color,

    $rrw_trail_menu_footer_text_color ) {

    print '

<footer id="colophon" class="site-footer" >

<div class="site-info ">

    <table  role="presentation" >

        <tr style="background-color:' . $rrw_trail_menu_footer_background_color . ';  ">

            <td> &nbsp;</td>

            ';

    // ************ determine if this site will be displaying the Google translater

    if ( is_plugin_active( "gtranslate/gtranslate.php" ) ) {

        $transHtml = do_shortcode( '[gtranslate]' );

        print "\n<td>\n$transHtml\n &nbsp; </td>\n ";

    } // end of check for google translate


    print " <!-- ========================================== footer after the translate -->\n";


    //  ***************************************************** login - copyright notice

    $url = site_url( "/wp-admin/", "https" );

    $rrw_trail_footerCopyright = get_option( "rrwtheme-footer-copyright", "" );


    if ( empty( $rrw_trail_footerCopyright ) ) {

        update_option( "rrwtheme-footer-copyright",

            "Copyright &copy; 2021 by Shaw-Weil Associates | " .

            "Site design/hosting by the book " .

            "<a class='external' " .

            " href='https://freeWheelingEasy.com/' >FreeWheeling Easy</a> " );

        $rrw_trail_footerCopyright = get_option( "rrwtheme-footer-copyright", "missed" );

    }

    print "

    <td> &nbsp;</td>

    <td>  <a href='$url'><span style='color:$rrw_trail_menu_footer_text_color;' >login</span></a></td>

    <td> &nbsp;</td>

    <td style='text-align:center; color:$rrw_trail_menu_footer_text_color;' >$rrw_trail_footerCopyright</td>";


    // ************************************** privacy policy

    if ( function_exists( "get_privacy_policy_url" ) ) {

        $privacyURL = get_privacy_policy_url();

        if ( !empty( $privacyURL ) ) {

            print "

     <td style='text-align:right' ><a href='$privacyURL' >

	 <span style='color:$rrw_trail_menu_footer_text_color;' >privicy policy</span></a>&nbsp; &nbsp;</td>

     <td> &nbsp;</td>";

        }

    }

    // ************************************** webmaster feedback

    $rrw_trail_url = site_url( "/webmaster-feedback/" );

    print " 

     <td style='text-align:right' ><a href='$rrw_trail_url' >

	 <span style='color:$rrw_trail_menu_footer_text_color;' >feedback</span></a>&nbsp; &nbsp;</td>

     <td> &nbsp;</td>";

    // ************************************** facebook, etc 

    $active_plugins = get_option( 'active_plugins' );

    $activeString = implode( ",", $active_plugins );

    if ( strpos( $activeString, "ultimate-social-media-icons" ) !== false )

        $rrw_facebookicons = do_shortcode( '[DISPLAY_ULTIMATE_SOCIAL_ICONS]' );

    else

        $rrw_facebookicons = "";

    print " 

     <td style='text-align:left' >

	 	$rrw_facebookicons

	 </td>

     <td> &nbsp;</td> 

  </tr> 

  </table>

</div> <!-- end .site-info -->

</footer>";

    return;

}

function rrwTrail_footer_dino( $rrw_trail_menu_footer_background_color,

    $rrw_trail_menu_footer_text_color ) {

    print "<hr> ";

    $siteDire = "/home/pillowan/www-dinomitedays";

    $content = file_get_contents( "$siteDire/wp-content/plugins/dinomitedays/footer_dino.php" );

    print $content;

}


function rrwTrail_footer_theyworking( $rrw_trail_menu_footer_background_color,

    $rrw_trail_menu_footer_text_color ) {


    $imageSource = get_bloginfo( 'stylesheet_directory' ) . "/images";

    print '<hr>

    <table border="0" width="100%" id="table1">

    	<tr>

    		<td valign="bottom">

    		<a href="http://www.pittsburghfoundation.org">

    		<img border="0" ';

    print "src=\"$imageSource/TPFlogo-15.gif\"";

    print ' width="128" height="109" 

    				alt="The Pittsburgh Foundation logo"></a> </td>

    		<td valign="bottom">&nbsp;</td>

    		<td valign="bottom"><p class="footer-p-1">No worthy project with willing 

    		volunteers should founder for lack of tools or materials.</p>

    		<p class="footer-p-2">The <i>Trail Volunteer Fund of The 

    		Pittsburgh Foundation</i> celebrates the efforts of the volunteers who are developing multi-use bicycle 

    		and walking trails by 

    		providing tools and materials they need for projects that develop and 

    		maintain the network of multiuse trails in western Pennsylvania and 

    		surrounding areas.</p>

    		<p class="footer-adm-links" align="center">

    		<a href="aboutus">Contact information</a>&nbsp;&nbsp;|&nbsp;&nbsp;

    		<a href="press">Press room</a>&nbsp;&nbsp;|&nbsp;&nbsp;  

    		<a href="privacy">Privacy policy</a>&nbsp;&nbsp;|&nbsp;&nbsp;

    		<a href="sitemap">Site map</a>

    		</p>

    		</td>

    		<td valign="bottom">&nbsp;</td>

    		<td valign="bottom">

    		<a href="https:/">

    		<img border="0" ';

    print "src=\"$imageSource/TVF7-20in.gif\" ";

    print 'alt="Trail Volunteer Fund Logo" width="212" height="146"></a></td>

    	</tr>

    </table>

    ';

    return;

} // end rrwTrail_footer_theyworking