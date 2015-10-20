<?php


define('RACKTABLES_BASE_DIR','/var/www/html/racktables');
define('ZABBIX_USER', 'admin');
define('ZABBIX_PASS', 'admin');
define('ZABBIX_API_URL', 'http://127.0.0.1/zabbix/api_jsonrpc.php');

set_include_path(RACKTABLES_BASE_DIR);
require_once 'ZabbixApi.class.php';
include('functions.php');

# TODO: Get these automatically instead of hardocding them
define('RACKTABLES_SWITCH_TYPE_ID', 8);
define('RACKTABLES_SERVER_TYPE_ID', 4);
define('RACKTABLES_ZABBIX_FLAG_ID', 10004);
define('RACKTABLES_ZABBIX_FLAG_VALUE', 1501);

define('RACKTABLES_SOFTWARE_TYPE_ID', 4);
define('RACKTABLES_SOFTWARE_TYPE_WINDOWS', 2064);
define('RACKTABLES_SOFTWARE_TYPE_LINUX', "");


define('ZABBIX_HOST_GROUP_SWITCH', 8);
define('ZABBIX_HOST_GROUP_LINUX', 2);
define('ZABBIX_HOST_GROUP_WINDOWS', 9);

define('ZABBIX_INTERFACE_TYPE_AGENT', 1);
define('ZABBIX_INTERFACE_TYPE_SNMP', 2);

define('ZABBIX_TEMPLATE_SNMP_INTERFACES', 10060);
define('ZABBIX_TEMPLATE_SNMP_GENERIC', 10065);

define('ZABBIX_TEMPLATE_PING', 10104);
define('ZABBIX_TEMPLATE_LINUX', 10001);
define('ZABBIX_TEMPLATE_WINDOWS', 10081);
?>
