<?php
include 'access_data.php';
$dbstring=$pwdconfiguration['web_inventory_dbstring'];

$location_types=array('City','Building','Room','Rack','Crate','Slot','Cabinet','Module','Box');
$model_types=array('NIM Crate','NIM Module','VME Crate','VME Module','CAMAC Crate','CAMAC Module','Rack','HV Crate','HV Module','Fantray','Power Supply','Board','Computer');
$maintenance_states=array('Working','Broken','Problems','Notice');

?>


