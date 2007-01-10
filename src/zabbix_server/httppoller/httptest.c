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

#include "config.h"

#include "cfg.h"
#include "pid.h"
#include "db.h"
#include "log.h"
#include "zlog.h"

#include "common.h"
#include "httptest.h"

size_t WRITEFUNCTION( void *ptr, size_t size, size_t nmemb, void *stream)
{
	size_t s = size*nmemb + 1;
	char *str_dat = calloc(1, s);

	zbx_snprintf(str_dat,s,ptr);
//	ZBX_LIM_PRINT("WRITEFUNCTION", s, str_dat, 65535);
//	zabbix_log(LOG_LEVEL_WARNING, "In WRITEFUNCTION");

	return size*nmemb;
}

size_t HEADERFUNCTION( void *ptr, size_t size, size_t nmemb, void *stream)
{
//	ZBX_LIM_PRINT("HEADERFUNCTION", size*nmemb, ptr, 300);
//	zabbix_log(LOG_LEVEL_WARNING, "In HEADERFUNCTION");

	return size*nmemb;
}


/*
void	process_http_data(int hostid,char *stat,char *httptest_name,char *httpstep_name)
{
#ifdef	HAVE_LIBCURL
	DB_RESULT	result;
	DB_ROW	row;
	char	server_esc[MAX_STRING_LEN];
	char	key_esc[MAX_STRING_LEN];

	zabbix_log(LOG_LEVEL_WARNING, "In process_httptest(httptestid:" ZBX_FS_UI64 ")", httptestid);

	DBescape_string(server, server_esc, MAX_STRING_LEN);
	DBescape_string(key, key_esc, MAX_STRING_LEN);

	result = DBselect("select %s where h.status=%d and h.hostid=i.hostid and h.host='%s' and i.key_='%s' and i.status=%d and i.type in (%d,%d) and" ZBX_COND_NODEID, ZBX_SQL_ITEM_SELECT, HOST_STATUS_MONITORED, server_esc, key_esc, ITEM_STATUS_ACTIVE, ITEM_TYPE_TRAPPER, ITEM_TYPE_ZABBIX_ACTIVE, LOCAL_NODE("h.hostid"));

	row=DBfetch(result);
	DBget_item_from_db(&item,row);

	if(set_result_type(&agent, item.value_type, value) == SUCCEED)
	{
		process_new_value(&item,&agent);
		update_triggers(item.itemid);
	}
	else
	{
		zabbix_log( LOG_LEVEL_WARNING, "Type of received value [%s] is not suitable for [%s@%s]", value, item.key, item.host );
		zabbix_syslog("Type of received value [%s] is not suitable for [%s@%s]", value, item.key, item.host );
	}
 
	DBfree_result(result);
	DBfree_result(result);
#endif
}
*/



/******************************************************************************
 *                                                                            *
 * Function: process_httptest                                                 *
 *                                                                            *
 * Purpose: process single scenario of http test                              *
 *                                                                            *
 * Parameters: httptestid - ID of http test                                   *
 *                                                                            *
 * Return value:                                                              *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments: SUCCEED or FAIL                                                  *
 *                                                                            *
 ******************************************************************************/
int	process_httptest(zbx_uint64_t httptestid)
{
#ifdef HAVE_LIBCURL
	DB_RESULT	result;
	DB_ROW	row;
	int	ret = SUCCEED;
	int	err;

	long	rspcode;
	double	total_time;
	double	speed_download;

	CURL            *easyhandle = NULL;

	zabbix_log(LOG_LEVEL_WARNING, "In process_httptest(httptestid:" ZBX_FS_UI64 ")", httptestid);

	easyhandle = curl_easy_init();
	if(easyhandle == NULL)
	{
		zabbix_log(LOG_LEVEL_ERR, "Cannot init CURL");

		return FAIL;
	}
	if(CURLE_OK != (err = curl_easy_setopt(easyhandle, CURLOPT_COOKIEFILE, "")))
	{
		zabbix_log(LOG_LEVEL_ERR, "Cannot set CURLOPT_COOKIEFILE [%s]", curl_easy_strerror(err));
		return FAIL;
	}
	if(CURLE_OK != (err = curl_easy_setopt(easyhandle, CURLOPT_FOLLOWLOCATION, 1)))
	{
		zabbix_log(LOG_LEVEL_ERR, "Cannot set CURLOPT_FOLLOWLOCATION [%s]", curl_easy_strerror(err));
		return FAIL;
	}
	if(CURLE_OK != (err = curl_easy_setopt(easyhandle,CURLOPT_WRITEFUNCTION ,WRITEFUNCTION)))
	{
		zabbix_log(LOG_LEVEL_ERR, "Error doing curl_easy_perform [%s]", curl_easy_strerror(err));
		return FAIL;
	}
	if(CURLE_OK != (err = curl_easy_setopt(easyhandle,CURLOPT_HEADERFUNCTION ,HEADERFUNCTION)))
	{
		zabbix_log(LOG_LEVEL_ERR, "Error doing curl_easy_perform [%s]", curl_easy_strerror(err));
		return FAIL;
	}

	result = DBselect("select httpstepid,no,name,url,timeout,posts from httpstep where httptestid=" ZBX_FS_UI64 " order by no",
				httptestid);

	while((row=DBfetch(result)))
	{
		zabbix_log(LOG_LEVEL_WARNING, "Processing step %s [%s]", row[1], row[3]);
		if(row[5][0] != 0)
		{
			if(CURLE_OK != (err = curl_easy_setopt(easyhandle, CURLOPT_POSTFIELDS, row[5])))
			{
				zabbix_log(LOG_LEVEL_ERR, "Cannot set POST vars [%s]", curl_easy_strerror(err));
				ret = FAIL;
				break;
			}
		}
		if(CURLE_OK != (err = curl_easy_setopt(easyhandle, CURLOPT_URL, row[3])))
		{
			zabbix_log(LOG_LEVEL_ERR, "Cannot set URL [%s]", curl_easy_strerror(err));
			ret = FAIL;
			break;
		}
		if(CURLE_OK != (err = curl_easy_setopt(easyhandle, CURLOPT_TIMEOUT, atoi(row[4]))))
		{
			zabbix_log(LOG_LEVEL_ERR, "Cannot set URL [%s]", curl_easy_strerror(err));
			ret = FAIL;
			break;
		}
		if(CURLE_OK != (err = curl_easy_perform(easyhandle)))
		{
			zabbix_log(LOG_LEVEL_ERR, "Error doing curl_easy_perform [%s]", curl_easy_strerror(err));
			ret = FAIL;
			break;
		}
		if(CURLE_OK != (err = curl_easy_getinfo(easyhandle,CURLINFO_RESPONSE_CODE ,&rspcode)))
		{
			zabbix_log(LOG_LEVEL_ERR, "Error doing curl_easy_perform [%s]", curl_easy_strerror(err));
			ret = FAIL;
			break;
		}
		if(CURLE_OK != (err = curl_easy_getinfo(easyhandle,CURLINFO_TOTAL_TIME ,&total_time)))
		{
			zabbix_log(LOG_LEVEL_ERR, "Error doing curl_easy_perform [%s]", curl_easy_strerror(err));
			ret = FAIL;
			break;
		}
		if(CURLE_OK != (err = curl_easy_getinfo(easyhandle,CURLINFO_SPEED_DOWNLOAD ,&speed_download)))
		{
			zabbix_log(LOG_LEVEL_ERR, "Error doing curl_easy_perform [%s]", curl_easy_strerror(err));
			ret = FAIL;
			break;
		}
		zabbix_log(LOG_LEVEL_WARNING, "%s: Rspcode [%d] Time [%f] Speed download [%f]",
			 row[2], rspcode, total_time, speed_download);
	}
	DBfree_result(result);

	(void)curl_easy_cleanup(easyhandle);

	return ret;
#endif /* HAVE_LIBCURL */
}

/******************************************************************************
 *                                                                            *
 * Function: process_httptests                                                *
 *                                                                            *
 * Purpose: process httptests                                                 *
 *                                                                            *
 * Parameters: now - current timestamp                                        *
 *                                                                            *
 * Return value:                                                              *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments: always SUCCEED                                                   *
 *                                                                            *
 ******************************************************************************/
void process_httptests(int now)
{
#ifdef HAVE_LIBCURL
	DB_RESULT	result;
	DB_ROW	row;

	zbx_uint64_t httptestid;

	zabbix_log(LOG_LEVEL_DEBUG, "In process_httptests");

	result = DBselect("select t.httptestid from httptest t where t.status=%d and t.nextcheck<=%d and " ZBX_SQL_MOD(t.httptestid,%d) "=%d and " ZBX_COND_NODEID, HTTPTEST_STATUS_MONITORED, now, CONFIG_HTTPPOLLER_FORKS, httppoller_num-1, LOCAL_NODE("t.httptestid"));
	while((row=DBfetch(result)))
	{
		ZBX_STR2UINT64(httptestid, row[0]);
		process_httptest(httptestid);

		DBexecute("update httptest set nextcheck=%d+delay where httptestid=" ZBX_FS_UI64, now, httptestid);
	}
	DBfree_result(result);
#endif /* HAVE_LIBCURL */
}