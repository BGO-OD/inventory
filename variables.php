<?php
include 'access_data.php';
$dbstring=$pwdconfiguration['web_inventory_dbstring'];

$location_types=array('City','Building','Room','Rack','Crate','Slot','Cabinet','Module','Box');
$model_types=array('NIM Crate','NIM Module','VME Crate','VME Module','CAMAC Crate','CAMAC Module','Rack','HV Crate','HV Module','Misc Crate','Fantray','Power Supply','Board','Computer','Monitor','DIN-Rail stuff','Detector','Photomultiplier','Various');
$maintenance_states=array('Working','Broken','Problems','Notice');

?>


