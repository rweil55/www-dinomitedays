<?php
class dinomitedays_information_block
{


    public static function doUpdate(array $attributes)
    {
        global $wpdbExtra;
        global $eol, $errorBeg, $errorEnd;
        $debug = rrwParam::isDebugMode("debug", false);
        $msg = "";

        try {
            if (rrwUtil::notAllowedToEdit("design_html", "all", false)) {
                $msg .= "$errorBeg E#1342 not allowed to edit $errorEnd";
                return $msg;
            }
            $msg .= dinomitedays_upload::buildDinoSelectionForm($dinoInput);

            $sqlDino = "select keyId from " . $wpdbExtra->dinosaurs . " where fileName='$dinoInput' or dinoName='$dinoInput'";
            $dinoKeyId = $wpdbExtra->get_var($sqlDino);
            if (empty($dinoKeyId)) {
                $msg .= dinomitedays_upload::buildDinoSelectionForm($attributes);
                $msg .= $errorBeg . "Error: Dinosaur '$dinoInput' not found." . $errorEnd . $eol;
                return $msg;
            }
            $tab = new rrwDisplayTable();;
            $msg .= $tab->tablename($wpdbExtra->dinosaurs);
            $msg .= $tab->keyName("keyId");
            $msg .= $tab->keyvalue($dinoKeyId);
            $msg .= $tab->DropDownSelf("Status", "Status", 60);
            $msg .= $tab->columns("Sponsor", "Sponsor", 60);
            $msg .= $tab->columns("Charity", "Charity", 60);
            $msg .= $tab->columns("Author ", "Author", 60);
            $msg .= $tab->DropDownSelf("action lot", "action_lot");
            $msg .= $tab->columns("Action Winner/Owner Name", "ActionWinner", 60);
            $msg .= $tab->columns("Fossil Location", "mapLoc", 60);
            $msg .= $tab->columns("Latitude", "Latitude", 60);
            $msg .= $tab->columns("Longitude", "Longitude", 60);
            $msg .= $tab->columns("Auction Price", "ActionPrice", 60);
            $msg .= $tab->columns("Material", "Material", 60);
            $msg .= $tab->columns("Last Seen", "mapDate", 60);
            $msg .= $tab->columns("City State", "cityState", 60);
            $msg .= $tab->columns("Theme", "Theme", 60);
            $msg .= $tab->columnRead("filename", "fileName", 200, 1, "please ignore");
            $msg .= $tab->columnRead("key", "keyId", 200, 1, "please ignore");

            $msg .= $tab->DoAction("");
        } catch (Exception $e) {
            $msg .= $errorBeg . "Error: " . $e->getMessage() . $errorEnd . $eol;
        }
        return $msg;
    } // doUpdate
} // class
