<?php
class dinomitedays_information_block
{


    public static function doUpdate($attributes)
    {
        global $wpdbExtra;
        global $eol, $errorBeg, $errorEnd;
        $debug = rrwParam::isDebugMode("debug", $attributes);
        $msg = "";
        if ($debug) $msg .= rrwUtil::print_r($_POST, true, "post variables");
        $dinoInput = rrwParam::String("dino", $attributes);
        if ($debug)
            $msg .= "dino Input = $dinoInput $eol";
        if (empty($dinoInput)) {
            $dinoInput = rrwParam::String("fileName", $_POST);
        }
        if (empty($dinoInput)) {
            $msg .= dinomitedays_upload::buildDinoSelectionForm($attributes);
            $msg .= $errorBeg . "Error: 'dino' parameter is required." . $errorEnd . $eol;
            return $msg;
        }
        $sqlDino = "select keyid from " . $wpdbExtra->dinosaurs . " where fileName='$dinoInput' or dinoName='$dinoInput'";
        $dinoKeyId = $wpdbExtra->get_var($sqlDino);
        if (empty($dinoKeyId)) {
            $msg .= dinomitedays_upload::buildDinoSelectionForm($attributes);
            $msg .= $errorBeg . "Error: Dinosaur '$dinoInput' not found." . $errorEnd . $eol;
            return $msg;
        }
        $tab = new rrwDisplayTable();;
        $msg .= $tab->tablename($wpdbExtra->dinosaurs);
        $msg .= $tab->keyName("keyid");
        $msg .= $tab->keyvalue($dinoKeyId);
        $msg .= $tab->columns("Status", "Status", 60);
        $msg .= $tab->columns("Sponsor", "Sponsor", 60);
        $msg .= $tab->columns("Charity", "Charity", 60);
        $msg .= $tab->columns("Author ", "Author", 60);
        $msg .= $tab->DropDownSelf("action lot", "action_lot");
        $msg .= $tab->columns("Fossil Location", "mapLoc", 60);
        $msg .= $tab->columns("Auction Price", "ActionPrice", 60);
        $msg .= $tab->columns("Material", "Material", 60);
        $msg .= $tab->columns("Last Seen", "mapDate", 60);
        $msg .= $tab->columns("City State", "cityState", 60);
        $msg .= $tab->columns("Theme", "Theme", 60);
        $msg .= $tab->columnRead("filename", "fileName", 200, 1, "please ignore");
        $msg .= $tab->columnRead("key", "keyid", 200, 1, "please ignore");

        $msg .= $tab->DoAction("");
        return $msg;
    } // doUpdate
} // class
