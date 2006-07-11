#ifndef ZABBIX_COMMON_H
#define ZABBIX_COMMON_H
 
#define PERF

#define	SUCCEED		0
#define	FAIL		(-1)
#define	NOTSUPPORTED	(-2)
#define	NETWORK_ERROR	(-3)
#define	TIMEOUT_ERROR	(-4)

#define	MAXFD	64
#define MAX_STRING_LEN	1024

/* Item types */
#define ITEM_TYPE_ZABBIX	0
#define ITEM_TYPE_SNMP		1
#define ITEM_TYPE_TRAPPER	2

/* Trigger statuses */
#define TRIGGER_STATUS_FALSE	0
#define TRIGGER_STATUS_TRUE	1
#define TRIGGER_STATUS_DISABLED	2
#define TRIGGER_STATUS_UNKNOWN	3
#define TRIGGER_STATUS_NOTSUPPORTED	4

#define	AGENTD_FORKS	5

#define AGENT_MAX_USER_COMMANDS	512

#define	TRAPPERD_FORKS	5

#define	SUCKER_FORKS	11
#define	SUCKER_DELAY	60

#define	SUCKER_TIMEOUT	5
/* Delay on network failure*/
#define DELAY_ON_NETWORK_FAILURE 60

#define	AGENT_TIMEOUT	3

#define	SENDER_TIMEOUT	5
#define	TRAPPER_TIMEOUT	5

#endif