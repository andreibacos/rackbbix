<?php
use ZabbixApi\ZabbixApi;

function getZabbixAPI() {
    try
    {
        $api = new ZabbixApi(ZABBIX_API_URL, ZABBIX_USER, ZABBIX_PASS);
    } catch (Exception $e) {
        echo "Failed to connect to Zabbix API ".$e->getMessage();
        exit;
    }
    return $api;
}

function getAttrId ($attr_name){
    $result = usePreparedSelectBlade('SELECT id FROM Attribute WHERE name = ?' , array ($attr_name));
    return $result->fetch (PDO::FETCH_COLUMN,0);
}

function getRacktablesObjects($type_id, $attr_id, $attr_value) {
    $r = usePreparedSelectBlade('select o.id, o.name, o.objtype_id, o.label, i.ip, i.type, i.name as ifname,
        av.attr_id as zabbix_flag_id, av.uint_value as zabbix_flag_value, av2.attr_id as sw_type_id,
        av2.uint_value as sw_type_value from Object AS o
            INNER JOIN IPv4Allocation as i ON (o.id = i.object_id AND o.objtype_id = ?)
            INNER JOIN AttributeValue av on (av.object_id = o.id AND av.attr_id = ? AND av.uint_value = ?)
			LEFT JOIN AttributeValue av2 on (av2.object_id = o.id AND av2.attr_id = ?)', array($type_id, $attr_id, $attr_value, RACKTABLES_SOFTWARE_TYPE_ID));

    $items = $r->fetchAll (PDO::FETCH_ASSOC);
    $result = array();

    foreach ($items as $item) {
        $ip = ip_format(ip4_int2bin($item['ip']));
        if ( ! isset($result[$item['id']]) ) {
            if (!empty($item['ifname'])) {
                $item['ip'] = array($item['ifname'] => $ip);
            } else {
                $item['ip'] = array($ip);
            }
            $result[$item['id']] = $item;
        } else if (is_array($result[$item['id']]['ip'])) {
            if (!empty($item['ifname'])) {
                $result[$item['id']]['ip'][$item['ifname']] = $ip;
            } else {
                $result[$item['id']]['ip'][] = $ip;
            }
        }
    }

    return $result;
}

function zabbixSyncHost($name, $groups, $templates, $interfaces) {

    $api = getZabbixAPI();
    $host = $api->hostGet(array('search' => array('name' => $name)));

    if (empty($host)) {
        echo 'Adding host: '.$name."\n";
        $api->hostCreate(array('host' => $name,
            'interfaces' => $interfaces,
            'groups' => $groups,
            'templates' => $templates
        ));

    } else {
        #TODO: Do a check if templates should be changed
        $hostid = $host[0]->hostid;
        foreach ($interfaces as $interface) {

            $ifaces = $api->hostinterfaceGet(array('hostids' => $hostid, 'filter' => array('type' => $interface['type'])));
            if (empty($ifaces)) {
                $params = $interface;
                $params['hostid'] = $hostid;
                $api->hostinterfaceCreate($params);
                echo "Host: $name adding IP: " . $interface['ip'] . "\n";
            } else {
                $weregood = false;
                foreach ($ifaces as $iface) {
                    if ($iface->ip != $interface['ip']) {
                        if (!$weregood) {
                            echo "Host: $name changing IP: " . $iface->ip . " into " . $interface['ip'] . "\n";
                            $api->hostinterfaceUpdate(array('interfaceid' => $iface->interfaceid, 'ip' => $interface['ip']));
                            $weregood = true;
                        } else {
                            $api->hostinterfaceDelete(array($iface->interfaceid));
                            echo "Host: $name deleting IP: " . $iface->ip . "\n";
                        }
                    } else {
                        $weregood = true;
                    }
                }
            }
        }
    }
}

function syncZabbix($object) {

    $ips = $object['ip'];
    if ( isset($ips['management']) ) {
        $ip = $ips['management'];
    } else if ( isset($ips['mgmt']) ) {
        $ip = $ips['mgmt'];
    } else if ( isset($ips['mngmt']) ) {
        $ip = $ips['mngmt'];
    } else {
        $ip = $ips[0];
    }

    switch($object['objtype_id']) {
        case RACKTABLES_SWITCH_TYPE_ID:
            $interfaces = array(array('type' => ZABBIX_INTERFACE_TYPE_SNMP, 'ip' => $ip, 'main' => 1, 'useip' => 1, 'dns' => '', 'port' => 161));
            $groups = array(array('groupid' => ZABBIX_HOST_GROUP_SWITCH));
            $templates = array(array('templateid' => 10060), array('templateid' => 10065));
            break;
        case RACKTABLES_SERVER_TYPE_ID:
            $interfaces = array(array('type' => ZABBIX_INTERFACE_TYPE_AGENT, 'ip' => $ip, 'main' => 1, 'useip' => 1, 'dns' => '', 'port' => 10050));
            switch($object['sw_type_value']) {
                case RACKTABLES_SOFTWARE_TYPE_WINDOWS:
                    $groups = array(array('groupid' => ZABBIX_HOST_GROUP_WINDOWS));
                    $templates = array(array('templateid' => ZABBIX_TEMPLATE_WINDOWS), array('templateid' => ZABBIX_TEMPLATE_PING));
                break;
                default:
                    $groups = array(array('groupid' => ZABBIX_HOST_GROUP_LINUX));
                    $templates = array(array('templateid' => ZABBIX_TEMPLATE_LINUX), array('templateid' => ZABBIX_TEMPLATE_PING));
            }
            break;
        default:
            $groups = array(array());
            $templates = array(array());
            $interfaces = array(array());
    }
    
    zabbixSyncHost($object['name'], $groups, $templates, $interfaces);
}

?>