/* 
** ZABBIX
** Copyright (C) 2000-2005 SIA Zabbix
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
**/
/*
#define	ZABBIX_TEST
*/

#include "common.h"

#include "cfg.h"
#include "pid.h"
#include "db.h"
#include "dbcache.h"
#include "log.h"
#include "zlog.h"
#include "zbxgetopt.h"
#include "mutexs.h"

#ifdef ZABBIX_TEST
#include "zbxjson.h"
#endif

#include "functions.h"
#include "expression.h"
#include "sysinfo.h"

#include "daemon.h"

#include "dbsyncer/dbsyncer.h"
#include "discoverer/discoverer.h"
#include "httppoller/httppoller.h"
#include "housekeeper/housekeeper.h"
#include "pinger/pinger.h"
#include "poller/poller.h"
#include "poller/checks_snmp.h"
#include "trapper/trapper.h"
#include "proxyconfig/proxyconfig.h"

/*
#define       LISTENQ 1024
*/
char *progname = NULL;
char title_message[] = "ZABBIX Proxy (daemon)";
char usage_message[] = "[-hV] [-c <file>]";

#ifndef HAVE_GETOPT_LONG
char *help_message[] = {
        "Options:",
        "  -c <file>       Specify configuration file",
        "  -h              give this help",
        "  -V              display version number",
        0 /* end of text */
};
#else
char *help_message[] = {
        "Options:",
        "  -c --config <file>       Specify configuration file",
        "  -h --help                give this help",
        "  -V --version             display version number",
        0 /* end of text */
};
#endif

/* COMMAND LINE OPTIONS */

/* long options */

static struct zbx_option longopts[] =
{
	{"config",	1,	0,	'c'},
	{"help",	0,	0,	'h'},
	{"version",	0,	0,	'V'},

#if defined (_WINDOWS)

	{"install",	0,	0,	'i'},
	{"uninstall",	0,	0,	'd'},

	{"start",	0,	0,	's'},
	{"stop",	0,	0,	'x'},

#endif /* _WINDOWS */

	{0,0,0,0}
};

/* short options */

static char	shortopts[] = 
	"c:n:hV"
#if defined (_WINDOWS)
	"idsx"
#endif /* _WINDOWS */
	;

/* end of COMMAND LINE OPTIONS*/

pid_t	*threads=NULL;

int	CONFIG_DBSYNCER_FORKS		= 0;//1;
int	CONFIG_DISCOVERER_FORKS		= 1;
int	CONFIG_HOUSEKEEPER_FORKS	= 1;
/*int	CONFIG_NODEWATCHER_FORKS	= 1;*/
int	CONFIG_PINGER_FORKS		= 1;
int	CONFIG_POLLER_FORKS		= 5;
int	CONFIG_HTTPPOLLER_FORKS		= 5;
int	CONFIG_TRAPPERD_FORKS		= 5;
int	CONFIG_UNREACHABLE_POLLER_FORKS	= 1;

int	CONFIG_LISTEN_PORT		= 10051;
char	*CONFIG_LISTEN_IP		= NULL;
int	CONFIG_TRAPPER_TIMEOUT		= TRAPPER_TIMEOUT;
/**/
/*int	CONFIG_NOTIMEWAIT		=0;*/
int	CONFIG_HOUSEKEEPING_FREQUENCY	= 1;
int	CONFIG_SENDER_FREQUENCY		= 30;
int	CONFIG_DBSYNCER_FREQUENCY	= 5;
int	CONFIG_PINGER_FREQUENCY		= 60;
/*int	CONFIG_DISABLE_PINGER		= 0;*/
int	CONFIG_DISABLE_HOUSEKEEPING	= 0;
int	CONFIG_UNREACHABLE_PERIOD	= 45;
int	CONFIG_UNREACHABLE_DELAY	= 15;
int	CONFIG_UNAVAILABLE_DELAY	= 60;
int	CONFIG_LOG_LEVEL		= LOG_LEVEL_WARNING;
char	*CONFIG_ALERT_SCRIPTS_PATH	= NULL;
char	*CONFIG_EXTERNALSCRIPTS		= NULL;
char	*CONFIG_FPING_LOCATION		= NULL;
char	*CONFIG_DBHOST			= NULL;
char	*CONFIG_DBNAME			= NULL;
char	*CONFIG_DBUSER			= NULL;
char	*CONFIG_DBPASSWORD		= NULL;
char	*CONFIG_DBSOCKET		= NULL;
int	CONFIG_DBPORT			= 0;
int	CONFIG_ENABLE_REMOTE_COMMANDS	= 0;

char	*CONFIG_SERVER			= NULL;
int	CONFIG_SERVER_PORT		= 10050;
char	*CONFIG_HOSTNAME		= NULL;
int	CONFIG_NODEID			= 0;
int	CONFIG_MASTER_NODEID		= 0;
int	CONFIG_NODE_NOHISTORY		= 0;

/* Global variable to control if we should write warnings to log[] */
int	CONFIG_ENABLE_LOG		= 1;

/* From table config */
int	CONFIG_REFRESH_UNSUPPORTED	= 0;

/* Zabbix server sturtup time */
int     CONFIG_SERVER_STARTUP_TIME      = 0;

/* Mutex for node syncs */
ZBX_MUTEX	node_sync_access;

/******************************************************************************
 *                                                                            *
 * Function: init_config                                                      *
 *                                                                            *
 * Purpose: parse config file and update configuration parameters             *
 *                                                                            *
 * Parameters:                                                                *
 *                                                                            *
 * Return value:                                                              *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments: will terminate process if parsing fails                          *
 *                                                                            *
 ******************************************************************************/
void	init_config(void)
{
	static struct cfg_line cfg[]=
	{
/*		 PARAMETER	,VAR	,FUNC,	TYPE(0i,1s),MANDATORY,MIN,MAX	*/
		{"Server",&CONFIG_SERVER,0,TYPE_STRING,PARM_MAND,0,0},
		{"ServerPort",&CONFIG_SERVER_PORT,0,TYPE_INT,PARM_MAND,1024,32768},
		{"Hostname",&CONFIG_HOSTNAME,0,TYPE_STRING,PARM_MAND,0,0},

		{"StartDiscoverers",&CONFIG_DISCOVERER_FORKS,0,TYPE_INT,PARM_OPT,0,255},
		{"StartHTTPPollers",&CONFIG_HTTPPOLLER_FORKS,0,TYPE_INT,PARM_OPT,0,255},
		{"StartPingers",&CONFIG_PINGER_FORKS,0,TYPE_INT,PARM_OPT,0,255},
		{"StartPollers",&CONFIG_POLLER_FORKS,0,TYPE_INT,PARM_OPT,0,255},
		{"StartPollersUnreachable",&CONFIG_UNREACHABLE_POLLER_FORKS,0,TYPE_INT,PARM_OPT,0,255},
		{"StartTrappers",&CONFIG_TRAPPERD_FORKS,0,TYPE_INT,PARM_OPT,0,255},
		{"HousekeepingFrequency",&CONFIG_HOUSEKEEPING_FREQUENCY,0,TYPE_INT,PARM_OPT,1,24},
		{"SenderFrequency",&CONFIG_SENDER_FREQUENCY,0,TYPE_INT,PARM_OPT,5,3600},
		{"PingerFrequency",&CONFIG_PINGER_FREQUENCY,0,TYPE_INT,PARM_OPT,1,3600},
		{"FpingLocation",&CONFIG_FPING_LOCATION,0,TYPE_STRING,PARM_OPT,0,0},
		{"Timeout",&CONFIG_TIMEOUT,0,TYPE_INT,PARM_OPT,1,30},
		{"TrapperTimeout",&CONFIG_TRAPPER_TIMEOUT,0,TYPE_INT,PARM_OPT,1,30},
		{"UnreachablePeriod",&CONFIG_UNREACHABLE_PERIOD,0,TYPE_INT,PARM_OPT,1,3600},
		{"UnreachableDelay",&CONFIG_UNREACHABLE_DELAY,0,TYPE_INT,PARM_OPT,1,3600},
		{"UnavailableDelay",&CONFIG_UNAVAILABLE_DELAY,0,TYPE_INT,PARM_OPT,1,3600},
		{"ListenIP",&CONFIG_LISTEN_IP,0,TYPE_STRING,PARM_OPT,0,0},
		{"ListenPort",&CONFIG_LISTEN_PORT,0,TYPE_INT,PARM_OPT,1024,32768},
/*		{"NoTimeWait",&CONFIG_NOTIMEWAIT,0,TYPE_INT,PARM_OPT,0,1},*/
/*		{"DisablePinger",&CONFIG_DISABLE_PINGER,0,TYPE_INT,PARM_OPT,0,1},*/
		{"DisableHousekeeping",&CONFIG_DISABLE_HOUSEKEEPING,0,TYPE_INT,PARM_OPT,0,1},
		{"DebugLevel",&CONFIG_LOG_LEVEL,0,TYPE_INT,PARM_OPT,0,4},
		{"PidFile",&APP_PID_FILE,0,TYPE_STRING,PARM_OPT,0,0},
		{"LogFile",&CONFIG_LOG_FILE,0,TYPE_STRING,PARM_OPT,0,0},
		{"LogFileSize",&CONFIG_LOG_FILE_SIZE,0,TYPE_INT,PARM_OPT,0,1024},
		{"AlertScriptsPath",&CONFIG_ALERT_SCRIPTS_PATH,0,TYPE_STRING,PARM_OPT,0,0},
		{"ExternalScripts",&CONFIG_EXTERNALSCRIPTS,0,TYPE_STRING,PARM_OPT,0,0},
		{"DBHost",&CONFIG_DBHOST,0,TYPE_STRING,PARM_OPT,0,0},
		{"DBName",&CONFIG_DBNAME,0,TYPE_STRING,PARM_MAND,0,0},
		{"DBUser",&CONFIG_DBUSER,0,TYPE_STRING,PARM_OPT,0,0},
		{"DBPassword",&CONFIG_DBPASSWORD,0,TYPE_STRING,PARM_OPT,0,0},
		{"DBSocket",&CONFIG_DBSOCKET,0,TYPE_STRING,PARM_OPT,0,0},
		{"DBPort",&CONFIG_DBPORT,0,TYPE_INT,PARM_OPT,1024,65535},
		{"NodeID",&CONFIG_NODEID,0,TYPE_INT,PARM_OPT,0,65535},
		{0}
	};

	CONFIG_SERVER_STARTUP_TIME = time(NULL);


	parse_cfg_file(CONFIG_FILE,cfg);

	if(CONFIG_DBNAME == NULL)
	{
		zabbix_log( LOG_LEVEL_CRIT, "DBName not in config file");
		exit(1);
	}
	if(APP_PID_FILE == NULL)
	{
		APP_PID_FILE=strdup("/tmp/zabbix_server.pid");
	}
	if(CONFIG_ALERT_SCRIPTS_PATH == NULL)
	{
		CONFIG_ALERT_SCRIPTS_PATH=strdup("/home/zabbix/bin");
	}
	if(CONFIG_FPING_LOCATION == NULL)
	{
		CONFIG_FPING_LOCATION=strdup("/usr/sbin/fping");
	}
	if(CONFIG_EXTERNALSCRIPTS == NULL)
	{
		CONFIG_EXTERNALSCRIPTS=strdup("/etc/zabbix/externalscripts");
	}
#ifndef	HAVE_LIBCURL
	CONFIG_HTTPPOLLER_FORKS = 0;
#endif
}

#ifdef ZABBIX_TEST
int	test()
{
printf("test()\n");

	struct zbx_json	j;
	zbx_uint64_t	id = 100100000000001;
	int		i;
	int		s = time(NULL);
	zbx_uint64_t	useip = 1, port = 10050;

	zbx_json_init(&j, 128*1024);

	zbx_json_addstring(&j, "type", "ZBX_PROXY_CONFIG");
	zbx_json_addarray(&j, "hosts");
		
	for (i = 0; i < 1000000; i++) {
		zbx_json_addobject(&j, NULL);
		zbx_json_adduint64(&j, "hostid", &id);
		zbx_json_addstring(&j, "host", "zbxw01");
		zbx_json_addstring(&j, "dns", NULL);
		zbx_json_adduint64(&j, "useip", &useip);
		zbx_json_addstring(&j, "ip", "127.0.0.1");
		zbx_json_adduint64(&j, "port", &port);
		zbx_json_close(&j);

		id++;
	}
printf("[sizeof:%4d] [size:%4d] [offset:%4d] [status:%d] [time:%d]\n", j.buffer_allocated, j.buffer_size, j.buffer_offset, j.status, time(NULL) - s);
/*fprintf(stderr, j.buffer);*/

	zbx_json_free(&j);
}
#endif




/******************************************************************************
 *                                                                            *
 * Function: main                                                             *
 *                                                                            *
 * Purpose: executes server processes                                         *
 *                                                                            *
 * Parameters:                                                                *
 *                                                                            *
 * Return value:                                                              *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
int main(int argc, char **argv)
{
	char    ch      = '\0';

	progname = argv[0];

	/* Parse the command-line. */
	while ((ch = (char)zbx_getopt_long(argc, argv, shortopts, longopts,NULL)) != (char)EOF)
	switch (ch) {
		case 'c':
			CONFIG_FILE = strdup(zbx_optarg);
			break;
		case 'h':
			help();
			exit(-1);
			break;
		case 'V':
			version();
			exit(-1);
			break;
		default:
			usage();
			exit(-1);
			break;
        }

	if(CONFIG_FILE == NULL)
	{
		CONFIG_FILE=strdup("/etc/zabbix/zabbix_proxy.conf");
	}

	/* Required for simple checks */
	init_metrics();

	init_config();

	if(CONFIG_DBSYNCER_FORKS!=0)
	{
		init_database_cache();
	}

#ifdef ZABBIX_TEST
/*	struct sigaction  phan;

	phan.sa_handler = test_child_signal_handler;
	sigemptyset(&phan.sa_mask);
	phan.sa_flags = 0;

	sigaction(SIGINT,       &phan, NULL);
	sigaction(SIGQUIT,      &phan, NULL);
	sigaction(SIGTERM,      &phan, NULL);
	sigaction(SIGPIPE,      &phan, NULL);
	sigaction(SIGCHLD,      &phan, NULL);
*/
	test();

	zbx_on_exit();
	return 0;
#endif /* ZABBIX_TEST */
	
	return daemon_start(CONFIG_ALLOW_ROOT);
}

int MAIN_ZABBIX_ENTRY(void)
{
        DB_RESULT       result;
        DB_ROW          row;

	int	i;
	pid_t	pid;

	zbx_sock_t	listen_sock;

	int		server_num = 0;

	if(CONFIG_LOG_FILE == NULL)
	{
		zabbix_open_log(LOG_TYPE_SYSLOG,CONFIG_LOG_LEVEL,NULL);
	}
	else
	{
		zabbix_open_log(LOG_TYPE_FILE,CONFIG_LOG_LEVEL,CONFIG_LOG_FILE);
	}

#ifdef  HAVE_SNMP
#	define SNMP_FEATURE_STATUS "YES"
#else
#	define SNMP_FEATURE_STATUS " NO"
#endif
#ifdef  HAVE_LIBCURL
#	define LIBCURL_FEATURE_STATUS "YES"
#else
#	define LIBCURL_FEATURE_STATUS " NO"
#endif
#ifdef  HAVE_ODBC
#	define ODBC_FEATURE_STATUS "YES"
#else
#	define ODBC_FEATURE_STATUS " NO"
#endif
#ifdef  HAVE_IPV6
#       define IPV6_FEATURE_STATUS "YES"
#else
#       define IPV6_FEATURE_STATUS " NO"
#endif

/*	zabbix_log( LOG_LEVEL_WARNING, "INFO [%s]", ZBX_SQL_MOD(a,%d)); */
	zabbix_log( LOG_LEVEL_WARNING, "Starting zabbix_proxy. ZABBIX %s.", ZABBIX_VERSION);

	zabbix_log( LOG_LEVEL_WARNING, "**** Enabled features ****");
	zabbix_log( LOG_LEVEL_WARNING, "SNMP monitoring:       " SNMP_FEATURE_STATUS	);
	zabbix_log( LOG_LEVEL_WARNING, "WEB monitoring:        " LIBCURL_FEATURE_STATUS	);
	zabbix_log( LOG_LEVEL_WARNING, "ODBC:                  " ODBC_FEATURE_STATUS	);
	zabbix_log( LOG_LEVEL_WARNING, "IPv6 support:          " IPV6_FEATURE_STATUS	);
	zabbix_log( LOG_LEVEL_WARNING, "**************************");

	DBconnect(ZBX_DB_CONNECT_EXIT);

	result = DBselect("select refresh_unsupported from config where " ZBX_COND_NODEID,
		LOCAL_NODE("configid"));
	row = DBfetch(result);

	if( (row != NULL) && DBis_null(row[0]) != SUCCEED)
	{
		CONFIG_REFRESH_UNSUPPORTED = atoi(row[0]);
	}
	DBfree_result(result);

	result = DBselect("select masterid from nodes where nodeid=%d",
		CONFIG_NODEID);
	row = DBfetch(result);

	if( (row != NULL) && DBis_null(row[0]) != SUCCEED)
	{
		CONFIG_MASTER_NODEID = atoi(row[0]);
	}
	DBfree_result(result);

/* Need to set trigger status to UNKNOWN since last run */
/* DBconnect() already made in init_config() */
/*	DBconnect();*/
	DBupdate_triggers_status_after_restart();
	DBclose();

/* To make sure that we can connect to the database before forking new processes */
/*	DBconnect(ZBX_DB_CONNECT_EXIT);*/
/* Do not close database. It is required for database cache */
/*	DBclose();*/

	if (ZBX_MUTEX_ERROR == zbx_mutex_create_force(&node_sync_access, ZBX_MUTEX_NODE_SYNC)) {
		zbx_error("Unable to create mutex for node syncs");
		exit(FAIL);
	}

	threads = calloc(1+CONFIG_POLLER_FORKS+CONFIG_TRAPPERD_FORKS+CONFIG_PINGER_FORKS
		+CONFIG_HOUSEKEEPER_FORKS+CONFIG_UNREACHABLE_POLLER_FORKS
		+/*CONFIG_NODEWATCHER_FORKS+*/CONFIG_HTTPPOLLER_FORKS+CONFIG_DISCOVERER_FORKS,
		sizeof(pid_t));

	if(CONFIG_TRAPPERD_FORKS > 0)
	{
		if( FAIL == zbx_tcp_listen(&listen_sock, CONFIG_LISTEN_IP, (unsigned short)CONFIG_LISTEN_PORT) )
		{
			zabbix_log(LOG_LEVEL_CRIT, "Listener failed with error: %s.", zbx_tcp_strerror());
			exit(1);
		}
	}

	for(	i=1;
		i<=CONFIG_POLLER_FORKS+CONFIG_TRAPPERD_FORKS+CONFIG_PINGER_FORKS+CONFIG_HOUSEKEEPER_FORKS+CONFIG_UNREACHABLE_POLLER_FORKS+/*CONFIG_NODEWATCHER_FORKS+*/CONFIG_HTTPPOLLER_FORKS+CONFIG_DISCOVERER_FORKS+CONFIG_DBSYNCER_FORKS; 
		i++)
	{
		if((pid = zbx_fork()) == 0)
		{
			server_num = i;
			break; 
		}
		else
		{
			threads[i]=pid;
		}
	}

/*	zabbix_log( LOG_LEVEL_WARNING, "zabbix_server #%d started",server_num); */
	/* Main process */
	if(server_num == 0)
	{
		init_main_process();
		main_proxyconfig_loop(server_num);
/*		zabbix_log( LOG_LEVEL_WARNING, "server #%d started [Watchdog]",
			server_num);
		main_watchdog_loop();*/
		for(;;)	zbx_sleep(3600);
	}


	if (server_num <= CONFIG_POLLER_FORKS) {
#ifdef HAVE_SNMP
		init_snmp("zabbix_server");
#endif /* HAVE_SNMP */
		zabbix_log(LOG_LEVEL_WARNING, "server #%d started [Poller. SNMP:%s]",
			server_num,
			SNMP_FEATURE_STATUS);
		main_poller_loop(ZBX_POLLER_TYPE_NORMAL, server_num);
	} else if (server_num <= CONFIG_POLLER_FORKS + CONFIG_TRAPPERD_FORKS) {
/* Run trapper processes then do housekeeping */
		child_trapper_main(server_num, &listen_sock);

/*		threads[i] = child_trapper_make(i, listenfd, addrlen); */
/*		child_trapper_make(server_num, listenfd, addrlen); */
	} else if(server_num <= CONFIG_POLLER_FORKS + CONFIG_TRAPPERD_FORKS + CONFIG_PINGER_FORKS)
	{
		zabbix_log( LOG_LEVEL_WARNING, "server #%d started [ICMP pinger]",
			server_num);
		main_pinger_loop(server_num-(CONFIG_POLLER_FORKS + CONFIG_TRAPPERD_FORKS));
	} else if(server_num <= CONFIG_POLLER_FORKS + CONFIG_TRAPPERD_FORKS + CONFIG_PINGER_FORKS
			+CONFIG_HOUSEKEEPER_FORKS)
	{
		zabbix_log( LOG_LEVEL_WARNING, "server #%d started [Housekeeper]",
			server_num);
		main_housekeeper_loop();
	} else if(server_num <= CONFIG_POLLER_FORKS + CONFIG_TRAPPERD_FORKS + CONFIG_PINGER_FORKS
			+CONFIG_HOUSEKEEPER_FORKS + CONFIG_UNREACHABLE_POLLER_FORKS)
	{
#ifdef HAVE_SNMP
		init_snmp("zabbix_server");
#endif /* HAVE_SNMP */
		zabbix_log( LOG_LEVEL_WARNING, "server #%d started [Poller for unreachable hosts. SNMP:%s]",
			server_num,
			SNMP_FEATURE_STATUS);
		main_poller_loop(ZBX_POLLER_TYPE_UNREACHABLE,
				server_num - (CONFIG_POLLER_FORKS + CONFIG_TRAPPERD_FORKS + CONFIG_PINGER_FORKS + CONFIG_HOUSEKEEPER_FORKS));
/*	} else if(server_num <= CONFIG_POLLER_FORKS + CONFIG_TRAPPERD_FORKS + CONFIG_PINGER_FORKS
			+ CONFIG_HOUSEKEEPER_FORKS + CONFIG_UNREACHABLE_POLLER_FORKS
			+ CONFIG_NODEWATCHER_FORKS)
	{
zabbix_log( LOG_LEVEL_WARNING, "server #%d started [Node watcher. Node ID:%d]",
				server_num,
				CONFIG_NODEID);
		main_nodewatcher_loop();*/
	} else if(server_num <= CONFIG_POLLER_FORKS + CONFIG_TRAPPERD_FORKS + CONFIG_PINGER_FORKS
			+ CONFIG_HOUSEKEEPER_FORKS + CONFIG_UNREACHABLE_POLLER_FORKS
			+ /*CONFIG_NODEWATCHER_FORKS + */CONFIG_HTTPPOLLER_FORKS)
	{
		zabbix_log( LOG_LEVEL_WARNING, "server #%d started [HTTP Poller]",
				server_num);
		main_httppoller_loop(server_num - CONFIG_POLLER_FORKS - CONFIG_TRAPPERD_FORKS -CONFIG_PINGER_FORKS
				- CONFIG_HOUSEKEEPER_FORKS
				- CONFIG_UNREACHABLE_POLLER_FORKS/* - CONFIG_NODEWATCHER_FORKS*/);
	} else if(server_num <= CONFIG_POLLER_FORKS + CONFIG_TRAPPERD_FORKS + CONFIG_PINGER_FORKS
			+ CONFIG_HOUSEKEEPER_FORKS + CONFIG_UNREACHABLE_POLLER_FORKS
			/*+ CONFIG_NODEWATCHER_FORKS */+ CONFIG_HTTPPOLLER_FORKS + CONFIG_DISCOVERER_FORKS)
	{
#ifdef HAVE_SNMP
		init_snmp("zabbix_server");
#endif /* HAVE_SNMP */
		zabbix_log( LOG_LEVEL_WARNING, "server #%d started [Discoverer. SNMP:%s]",
			server_num,
			SNMP_FEATURE_STATUS);
		main_discoverer_loop(server_num - CONFIG_POLLER_FORKS - CONFIG_TRAPPERD_FORKS -CONFIG_PINGER_FORKS
				- CONFIG_HOUSEKEEPER_FORKS
				- CONFIG_UNREACHABLE_POLLER_FORKS - /*CONFIG_NODEWATCHER_FORKS - */CONFIG_HTTPPOLLER_FORKS);
	} else if(server_num <= CONFIG_POLLER_FORKS + CONFIG_TRAPPERD_FORKS + CONFIG_PINGER_FORKS
			+ CONFIG_HOUSEKEEPER_FORKS + CONFIG_UNREACHABLE_POLLER_FORKS
			+ /*CONFIG_NODEWATCHER_FORKS + */CONFIG_HTTPPOLLER_FORKS + CONFIG_DISCOVERER_FORKS + CONFIG_DBSYNCER_FORKS)
	{
		zabbix_log( LOG_LEVEL_WARNING, "server #%d started [DB Syncer]",
				server_num);
		main_dbsyncer_loop();
	}

	return SUCCEED;
}

void	zbx_on_exit()
{
#if !defined(_WINDOWS)
	
	int i = 0;

	if(threads != NULL)
	{
		for(i = 1; i <= CONFIG_POLLER_FORKS+CONFIG_TRAPPERD_FORKS+CONFIG_PINGER_FORKS+CONFIG_HOUSEKEEPER_FORKS+CONFIG_UNREACHABLE_POLLER_FORKS+/*CONFIG_NODEWATCHER_FORKS+*/CONFIG_HTTPPOLLER_FORKS+CONFIG_DISCOVERER_FORKS+CONFIG_DBSYNCER_FORKS; i++)
		{
			if(threads[i]) {
				kill(threads[i],SIGTERM);
				threads[i] = (ZBX_THREAD_HANDLE)NULL;
			}
		}
	}
	
#endif /* not _WINDOWS */

#ifdef USE_PID_FILE

	daemon_stop();

#endif /* USE_PID_FILE */

	free_metrics();

	zbx_sleep(2); /* wait for all threads closing */

	DBconnect(ZBX_DB_CONNECT_EXIT);
	
	if(CONFIG_DBSYNCER_FORKS!=0)
	{
		free_database_cache();
	}
	DBclose();
	zbx_mutex_destroy(&node_sync_access);
	zabbix_close_log();
	
#ifdef  HAVE_SQLITE3
	php_sem_remove(&sqlite_access);
#endif /* HAVE_SQLITE3 */

	zabbix_log(LOG_LEVEL_INFORMATION, "ZABBIX Server stopped");

	exit(SUCCEED);
}
