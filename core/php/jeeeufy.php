<?php

try {
    require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

    if (!jeedom::apiAccess(init('apikey'), 'eufy')) {
        echo __('Vous n\'etes pas autorisé à effectuer cette action', __FILE__);
        die();
    }
    if (init('test') != '') {
        echo 'OK';
        die();
    }
    $result = json_decode(file_get_contents("php://input"), true);
    echo 'results : ' . json_encode($result);
    if (!is_array($result)) {
        die();
    }

    if(isset($result['type'])){
        echo "received results " . $result['type'];
        if ($result['type'] == 'stations') {
            echo 'Stations received from daemon';
            log::add('eufy', 'info', 'Stations received from daemon');
            eufy::syncDevices($result['stations'],'Station');
        }

        if ($result['type'] == 'devices') {
            echo 'Devices received from daemon';
            log::add('eufy', 'info', 'Devices received from daemon');
            eufy::syncDevices($result['devices'],'Camera');
        } 
        if ($result['type'] == 'event') {
            log::add('eufy', 'debug', 'Event received from daemon: serialNumber: '. $result['serialNumber'] . ', property: ' . $result['property'] . ', value: ' . $result['value']);          
            
            if(!isset($result['subtype']))
                eufy::updateDeviceInfo($result['serialNumber'], $result['property'], $result['value']);
            else{
                if($result['subtype'] == 'properties')
                eufy::updateDeviceInfoForProperties($result['serialNumber'], $result['property'], $result['value']);
            }
        }
    }
} 
catch (Exception $e) {
    log::add('eufy', 'error', displayException($e));
}
