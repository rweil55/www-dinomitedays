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
        $table->columns("map location description", "Maploc", 69);
        $table->columns("last seen", "Mapdate", 20);
        $table->columns("Latitude", "Latitude", 20);
        $table->columns("Longitude", "Longitude", 20);

        if (empty($action)) {
            $msg .= $table->listdata();
            return $msg;
        }
        $msg .= $table->DoAction();
        return $msg;
    }
}
