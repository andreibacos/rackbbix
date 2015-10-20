# rackbbix

 This script adds objects from racktables to zabbix when:
 - The custom "Add to zabbix" property is set to yes
 - The object has one or more IPs set, if there are more than 1 IP it will use the first one, it`s also possible to use a specific IP if you set it's name to one of "management", "mngmt" or "mgmt" in racktables
