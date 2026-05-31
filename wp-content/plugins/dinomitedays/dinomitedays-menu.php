<?php
class Dinomitedays_menu
{
    public static function Display()
    {

        $msg = "";
        ini_set("display_errors", 1);
        error_reporting(E_ALL);

        $msg .= "<h2>Dinomite Days Menu</h2>\n";
        $menu = wp_nav_menu(
            array(
                'theme_location' => 'primary',
                'menu_class' => 'nav-menu menucolor',
                'echo' => false
            )
        );
        $msg .= $menu;

        return $msg;
    }
}
