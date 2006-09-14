echo Killing servers
killall zabbix_server >/dev/null 2>/dev/null
sleep 1
killall -9 zabbix_server >/dev/null 2>/dev/null
echo Removing log files
rm ~zabbix/logs/node*

for i in 1 2 3 4 5 6 7; do
	echo "drop database node$i"|mysql -uroot
	echo "create database node$i"|mysql -uroot
	cat ../mysql/schema.sql|mysql -uroot node$i
	cat data.sql|sed -e "s/{10010}/{100100$i}/g"|mysql -uroot node$i
#	cat data_small.sql|sed -e "s/{10010}/{100100$i}/g"|mysql -uroot node$i
done
cat nodes.sql|mysql -uroot

for i in 1 2 3 4 5 6 7; do
	echo "update config set configid=100*configid+$i"|mysql -uroot node$i
	echo "update media_type set mediatypeid=100*mediatypeid+$i"|mysql -uroot node$i
	echo "update users set userid=100*userid+$i"|mysql -uroot node$i
	echo "update usrgrp set usrgrpid=100*usrgrpid+$i"|mysql -uroot node$i
	echo "update rights set rightid=100*rightid+$i"|mysql -uroot node$i
	echo "update rights set userid=100*userid+$i"|mysql -uroot node$i
	echo "update hosts set hostid=100*hostid+$i"|mysql -uroot node$i
	echo "update groups set groupid=100*groupid+$i"|mysql -uroot node$i
	echo "update hosts_groups set hostgroupid=100*hostgroupid+$i"|mysql -uroot node$i
	echo "update hosts_groups set hostid=100*hostid+$i"|mysql -uroot node$i
	echo "update hosts_groups set groupid=100*groupid+$i"|mysql -uroot node$i
	echo "update items set itemid=100*itemid+$i"|mysql -uroot node$i
	echo "update items set hostid=100*hostid+$i"|mysql -uroot node$i
	echo "update functions set functionid=100*functionid+$i"|mysql -uroot node$i
	echo "update functions set itemid=100*itemid+$i"|mysql -uroot node$i
	echo "update functions set triggerid=100*triggerid+$i"|mysql -uroot node$i
	echo "update triggers set triggerid=100*triggerid+$i"|mysql -uroot node$i
	echo "update actions set actionid=100*actionid+$i"|mysql -uroot node$i
	echo "update actions set userid=100*userid+$i"|mysql -uroot node$i
	echo "update media set mediaid=100*mediaid+$i"|mysql -uroot node$i
	echo "update media set userid=100*userid+$i"|mysql -uroot node$i
	echo "update media set mediatypeid=100*mediatypeid+$i"|mysql -uroot node$i
done


for i in 1 2 3 4 5 6 7; do
	~zabbix/distributed/bin/zabbix_server -c /etc/zabbix/node$i.conf
done