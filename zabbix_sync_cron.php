<?php
$script_mode = TRUE;
include('config.php');
include('inc/init.php');

$racktables_objects = getRacktablesObjects(RACKTABLES_SERVER_TYPE_ID, RACKTABLES_ZABBIX_FLAG_ID, RACKTABLES_ZABBIX_FLAG_VALUE);

foreach ( $racktables_objects as $object ) {
    syncZabbix($object);
}



// Discovery output (disabled until this feature is implemented in zabbix)
//$result = array();
//
//foreach ($switches as $switch) {
//    $tmp = array();
//    $name = $switch['name'];
//    $ip = ip_format(ip4_int2bin($switch['ip']));
//    $tmp['{#NAME}'] = $name;
//    $tmp['{#IP}'] = $ip;
//    array_push($result, $tmp);
//}
//
//#print_r($result);
//#echo '{"data":',json_encode($result).'}';
?>