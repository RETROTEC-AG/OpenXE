<?php

/*
 * Copyright (c) 2022 OpenXE project
 */

use Xentral\Components\Database\Exception\QueryFailureException;

class upgrade {

    function __construct($app, $intern = false) {
        $this->app = $app;
        if ($intern)
            return;

        $this->app->ActionHandlerInit($this);
        $this->app->ActionHandler("list", "upgrade_overview");        
        $this->app->DefaultActionHandler("list");
        $this->app->ActionHandlerListen($app);
    }

    public function Install() {
        /* Fill out manually later */
    }  
 
    function upgrade_overview() {  
    
        $submit = $this->app->Secure->GetPOST('submit');
        $verbose = $this->app->Secure->GetPOST('details_anzeigen') === '1';
        $db_verbose = $this->app->Secure->GetPOST('db_details_anzeigen') === '1';
        $force = $this->app->Secure->GetPOST('erzwingen') === '1';

        include("../upgrade/upgrade.php");

        $logfile = "../upgrade/data/upgrade.log";
        upgrade_set_out_file_name($logfile);

        $this->app->Tpl->Set('UPGRADE_VISIBLE', "hidden");
        $this->app->Tpl->Set('UPGRADE_DB_VISIBLE', "hidden");

        switch ($submit) {
            case 'check_upgrade':
                $this->app->Tpl->Set('UPGRADE_VISIBLE', "");
                unlink($logfile);
                upgrade_main("../upgrade",$verbose,true,false,true,false,$force);
            break;
            case 'do_upgrade':
                unlink($logfile);
                upgrade_main("../upgrade",$verbose,true,true,true,true,$force);  
            break;    
            case 'check_db':
                $this->app->Tpl->Set('UPGRADE_DB_VISIBLE', "");
                unlink($logfile);
                upgrade_main("../upgrade",$db_verbose,false,false,true,false,$force);  
            break;    
            case 'do_db_upgrade':
                $this->app->Tpl->Set('UPGRADE_DB_VISIBLE', "");
                unlink($logfile);
                upgrade_main("../upgrade",$db_verbose,false,false,true,true,$force);  
            break;    
            case 'refresh':
            break;
        }

        // Read results
        $result = file_get_contents($logfile);             
        $this->app->Tpl->Set('CURRENT', $this->app->erp->Revision());
        $this->app->Tpl->Set('OUTPUT_FROM_CLI',nl2br($result));
        $this->app->Tpl->Parse('PAGE', "upgrade.tpl");
    }   
    

}
