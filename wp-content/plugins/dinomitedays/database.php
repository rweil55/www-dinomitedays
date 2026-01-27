<?php

class dinomitedays_database
{

    public  static function displayDatabase($attr)
    {
        $action = rrwUtil::fetchparameterString("action");
        $msg = "";

        $table = new  rrwDisplayTable();
        $table->tablename('wpprrj_00rrwdinos');
        $table->sortdefault('Name');
        $table->keyname('keyid');
        $table->columnread("dinasourer name", "Name", 30, 1);
        $table->columnread("keyid", "keyid", 10, 1);
        $table->columns("map location description", "mapLoc", 69);
        $table->columns("last seen", "mapDate", 20);
        $table->columns("Latitude", "Latitude", 20);
        $table->columns("Longitude", "Longitude", 20);

        if (empty($action)) {
            $msg .= $table->listdata();
            return $msg;
        }
        $msg .= $table->DoAction();
        return $msg;
    }
    public static function updateStreetViewField($attributes)
    {
        global $wpdb;
        global $eol;
        $msg = "";
        $table_name = $wpdb->prefix . '00rrwdinos';

        $dinoFileName = rrwParam::String('dinoFileName', $attributes);
        if (empty($dinoFileName)) {
            return "Invalid dinoId value. '$dinoFileName' $eol ";
        }
        $streetViewField = rrwParam::String('streetViewField', $attributes);
        $sql = "update $table_name set streetViewList = '$streetViewField' where fileName = '$dinoFileName'";
        $msg .= "$sql $eol";
        $wpdb->query($sql);

        return "$msg Panoramic field updated.";
    }
}
