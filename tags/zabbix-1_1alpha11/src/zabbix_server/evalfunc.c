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


#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <netinet/in.h>
#include <netdb.h>

#include <signal.h>

#include <string.h>

#include <time.h>

#include <sys/socket.h>
#include <errno.h>

/* Functions: pow(), round() */
#include <math.h>

#include "common.h"
#include "db.h"
#include "log.h"
#include "zlog.h"
#include "security.h"

#include "evalfunc.h"

/******************************************************************************
 *                                                                            *
 * Function: evaluate_COUNT                                                   *
 *                                                                            *
 * Purpose: evaluate function 'count' for the item                            *
 *                                                                            *
 * Parameters: item - item (performance metric)                               *
 *             parameter - number of seconds                                  *
 *                                                                            *
 * Return value: SUCCEED - evaluated succesfully, result is stored in 'value' *
 *               FAIL - failed to evaluate function                           *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
static int evaluate_COUNT(char *value, DB_ITEM *item, int parameter)
{
	DB_RESULT	*result;

	char		sql[MAX_STRING_LEN];
	int		now;
	int		res = SUCCEED;

	if(item->value_type != ITEM_VALUE_TYPE_FLOAT)
	{
		return	FAIL;
	}

	now=time(NULL);

	snprintf(sql,sizeof(sql)-1,"select count(value) from history where clock>%d and itemid=%d",now-parameter,item->itemid);

	result = DBselect(sql);
	if(DBnum_rows(result) == 0)
	{
		zabbix_log(LOG_LEVEL_DEBUG, "Result for COUNT is empty" );
		res = FAIL;
	}
	else
	{
		strcpy(value,DBget_field(result,0,0));
	}
	DBfree_result(result);

	return res;
}

/******************************************************************************
 *                                                                            *
 * Function: evaluate_SUM                                                     *
 *                                                                            *
 * Purpose: evaluate function 'sum' for the item                              *
 *                                                                            *
 * Parameters: item - item (performance metric)                               *
 *             parameter - number of seconds                                  *
 *                                                                            *
 * Return value: SUCCEED - evaluated succesfully, result is stored in 'value' *
 *               FAIL - failed to evaluate function                           *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
static int evaluate_SUM(char *value,DB_ITEM	*item,int parameter)
{
	DB_RESULT	*result;

	char		sql[MAX_STRING_LEN];
	int		now;
	int		res = SUCCEED;

	if(item->value_type != ITEM_VALUE_TYPE_FLOAT)
	{
		return	FAIL;
	}

	now=time(NULL);

	snprintf(sql,sizeof(sql)-1,"select sum(value) from history where clock>%d and itemid=%d",now-parameter,item->itemid);

	result = DBselect(sql);
	if(DBnum_rows(result) == 0)
	{
		zabbix_log(LOG_LEVEL_DEBUG, "Result for SUM is empty" );
		res = FAIL;
	}
	else
	{
		strcpy(value,DBget_field(result,0,0));
	}
	DBfree_result(result);

	return res;
}

/******************************************************************************
 *                                                                            *
 * Function: evaluate_AVG                                                     *
 *                                                                            *
 * Purpose: evaluate function 'avg' for the item                              *
 *                                                                            *
 * Parameters: item - item (performance metric)                               *
 *             parameter - number of seconds                                  *
 *                                                                            *
 * Return value: SUCCEED - evaluated succesfully, result is stored in 'value' *
 *               FAIL - failed to evaluate function                           *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
static int evaluate_AVG(char *value,DB_ITEM	*item,int parameter)
{
	DB_RESULT	*result;

	char		sql[MAX_STRING_LEN];
	int		now;
	int		res = SUCCEED;

	if(item->value_type != ITEM_VALUE_TYPE_FLOAT)
	{
		return	FAIL;
	}

	now=time(NULL);

	snprintf(sql,sizeof(sql)-1,"select avg(value) from history where clock>%d and itemid=%d",now-parameter,item->itemid);

	result = DBselect(sql);
	if(DBnum_rows(result) == 0)
	{
		zabbix_log(LOG_LEVEL_DEBUG, "Result for AVG is empty" );
		res = FAIL;
	}
	else
	{
		strcpy(value,DBget_field(result,0,0));
		del_zeroes(value);
	}
	DBfree_result(result);

	return res;
}

/******************************************************************************
 *                                                                            *
 * Function: evaluate_MIN                                                     *
 *                                                                            *
 * Purpose: evaluate function 'min' for the item                              *
 *                                                                            *
 * Parameters: item - item (performance metric)                               *
 *             parameter - number of seconds                                  *
 *                                                                            *
 * Return value: SUCCEED - evaluated succesfully, result is stored in 'value' *
 *               FAIL - failed to evaluate function                           *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
static int evaluate_MIN(char *value,DB_ITEM	*item,int parameter)
{
	DB_RESULT	*result;

	char		sql[MAX_STRING_LEN];
	int		now;
	int		res = SUCCEED;

	if(item->value_type != ITEM_VALUE_TYPE_FLOAT)
	{
		return	FAIL;
	}

	now=time(NULL);

	snprintf(sql,sizeof(sql)-1,"select min(value) from history where clock>%d and itemid=%d",now-parameter,item->itemid);

	result = DBselect(sql);
	if(DBnum_rows(result) == 0)
	{
		zabbix_log(LOG_LEVEL_DEBUG, "Result for MIN is empty" );
		res = FAIL;
	}
	else
	{
		strcpy(value,DBget_field(result,0,0));
		del_zeroes(value);
	}
	DBfree_result(result);

	return res;
}

/******************************************************************************
 *                                                                            *
 * Function: evaluate_MAX                                                     *
 *                                                                            *
 * Purpose: evaluate function 'max' for the item                              *
 *                                                                            *
 * Parameters: item - item (performance metric)                               *
 *             parameter - number of seconds                                  *
 *                                                                            *
 * Return value: SUCCEED - evaluated succesfully, result is stored in 'value' *
 *               FAIL - failed to evaluate function                           *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
static int evaluate_MAX(char *value,DB_ITEM *item,int parameter)
{
	DB_RESULT	*result;

	char		sql[MAX_STRING_LEN];
	int		now;
	int		res = SUCCEED;

	if(item->value_type != ITEM_VALUE_TYPE_FLOAT)
	{
		return	FAIL;
	}

	now=time(NULL);

	snprintf(sql,sizeof(sql)-1,"select max(value) from history where clock>%d and itemid=%d",now-parameter,item->itemid);

	result = DBselect(sql);
	if(DBnum_rows(result) == 0)
	{
		zabbix_log(LOG_LEVEL_DEBUG, "Result for MAX is empty" );
		res = FAIL;
	}
	else
	{
		strcpy(value,DBget_field(result,0,0));
		del_zeroes(value);
	}
	DBfree_result(result);

	return res;
}

/******************************************************************************
 *                                                                            *
 * Function: evaluate_DELTA                                                   *
 *                                                                            *
 * Purpose: evaluate function 'delat' for the item                            *
 *                                                                            *
 * Parameters: item - item (performance metric)                               *
 *             parameter - number of seconds                                  *
 *                                                                            *
 * Return value: SUCCEED - evaluated succesfully, result is stored in 'value' *
 *               FAIL - failed to evaluate function                           *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
static int evaluate_DELTA(char *value,DB_ITEM *item,int parameter)
{
	DB_RESULT	*result;

	char		sql[MAX_STRING_LEN];
	int		now;
	int		res = SUCCEED;

	if(item->value_type != ITEM_VALUE_TYPE_FLOAT)
	{
		return	FAIL;
	}

	now=time(NULL);

	snprintf(sql,sizeof(sql)-1,"select max(value)-min(value) from history where clock>%d and itemid=%d",now-parameter,item->itemid);

	result = DBselect(sql);
	if(DBnum_rows(result) == 0)
	{
		zabbix_log(LOG_LEVEL_DEBUG, "Result for DELTA is empty" );
		res = FAIL;
	}
	else
	{
		strcpy(value,DBget_field(result,0,0));
		del_zeroes(value);
	}
	DBfree_result(result);

	return res;
}

/******************************************************************************
 *                                                                            *
 * Function: evaluate_FUNCTION                                                *
 *                                                                            *
 * Purpose: evaluate function                                                 *
 *                                                                            *
 * Parameters: item - item to calculate function for                          *
 *             function - function (for example, 'max')                       *
 *             parameter - parameter of the function)                         *
 *             flag - if EVALUATE_FUNCTION_SUFFIX, then include units and     *
 *                    suffix (K,M,G) into result value (for example, 15GB)    *
 *                                                                            *
 * Return value: SUCCEED - evaluated succesfully, value contains its value    *
 *               FAIL - evaluation failed                                     *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
int evaluate_FUNCTION(char *value,DB_ITEM *item,char *function,char *parameter, int flag)
{
	int	ret  = SUCCEED;
	time_t  now;
	struct  tm      *tm;
	
	float	value_float;
	float	value_float_abs;
	char	suffix[MAX_STRING_LEN];

	int	day;

	zabbix_log( LOG_LEVEL_DEBUG, "In evaluate_FUNCTION() Function [%s] flag [%d]",function,flag);

	if(strcmp(function,"last")==0)
	{
		if(item->lastvalue_null==1)
		{
			ret = FAIL;
		}
		else
		{
			if(item->value_type==ITEM_VALUE_TYPE_FLOAT)
			{
				zabbix_log( LOG_LEVEL_DEBUG, "In evaluate_FUNCTION() 1");
				snprintf(value,MAX_STRING_LEN-1,"%f",item->lastvalue);
				del_zeroes(value);
				zabbix_log( LOG_LEVEL_DEBUG, "In evaluate_FUNCTION() 2 value [%s]", value);
			}
			else
			{
/*				*value=strdup(item->lastvalue_str);*/
				zabbix_log( LOG_LEVEL_DEBUG, "In evaluate_FUNCTION() 3 [%s] [%s]",value,item->lastvalue_str);
				strcpy(value,item->lastvalue_str);
				zabbix_log( LOG_LEVEL_DEBUG, "In evaluate_FUNCTION() 4");
			}
		}
	}
	else if(strcmp(function,"prev")==0)
	{
		if(item->prevvalue_null==1)
		{
			ret = FAIL;
		}
		else
		{
			if(item->value_type==ITEM_VALUE_TYPE_FLOAT)
			{
				snprintf(value,MAX_STRING_LEN-1,"%f",item->prevvalue);
				del_zeroes(value);
			}
			else
			{
				strcpy(value,item->prevvalue_str);
			}
		}
	}
	else if(strcmp(function,"min")==0)
	{
		ret = evaluate_MIN(value,item,atoi(parameter));
	}
	else if(strcmp(function,"max")==0)
	{
		ret = evaluate_MAX(value,item,atoi(parameter));
	}
	else if(strcmp(function,"avg")==0)
	{
		ret = evaluate_AVG(value,item,atoi(parameter));
	}
	else if(strcmp(function,"sum")==0)
	{
		ret = evaluate_SUM(value,item,atoi(parameter));
	}
	else if(strcmp(function,"count")==0)
	{
		ret = evaluate_COUNT(value,item,atoi(parameter));
	}
	else if(strcmp(function,"delta")==0)
	{
		ret = evaluate_DELTA(value,item,atoi(parameter));
	}
	else if(strcmp(function,"nodata")==0)
	{
		strcpy(value,"0");
	}
	else if(strcmp(function,"date")==0)
	{
		now=time(NULL);
                tm=localtime(&now);
                snprintf(value,MAX_STRING_LEN-1,"%.4d%.2d%.2d",tm->tm_year+1900,tm->tm_mon+1,tm->tm_mday);
	}
	else if(strcmp(function,"dayofweek")==0)
	{
		now=time(NULL);
                tm=localtime(&now);
		/* The number of days since Sunday, in the range 0 to 6. */
		day=tm->tm_wday;
		if(0 == day)	day=7;
                snprintf(value,MAX_STRING_LEN-1,"%d", day);
	}
	else if(strcmp(function,"time")==0)
	{
		now=time(NULL);
                tm=localtime(&now);
                snprintf(value,MAX_STRING_LEN-1,"%.2d%.2d%.2d",tm->tm_hour,tm->tm_min,tm->tm_sec);
	}
	else if(strcmp(function,"abschange")==0)
	{
		if((item->lastvalue_null==1)||(item->prevvalue_null==1))
		{
			ret = FAIL;
		}
		else
		{
			if(item->value_type==ITEM_VALUE_TYPE_FLOAT)
			{
				snprintf(value,MAX_STRING_LEN-1,"%f",(float)abs(item->lastvalue-item->prevvalue));
				del_zeroes(value);
			}
			else
			{
				if(strcmp(item->lastvalue_str, item->prevvalue_str) == 0)
				{
					strcpy(value,"0");
				}
				else
				{
					strcpy(value,"1");
				}
			}
		}
	}
	else if(strcmp(function,"change")==0)
	{
		if((item->lastvalue_null==1)||(item->prevvalue_null==1))
		{
			ret = FAIL;
		}
		else
		{
			if(item->value_type==ITEM_VALUE_TYPE_FLOAT)
			{
				snprintf(value,MAX_STRING_LEN-1,"%f",item->lastvalue-item->prevvalue);
				del_zeroes(value);
			}
			else
			{
				if(strcmp(item->lastvalue_str, item->prevvalue_str) == 0)
				{
					strcpy(value,"0");
				}
				else
				{
					strcpy(value,"1");
				}
			}
		}
	}
	else if(strcmp(function,"diff")==0)
	{
		if((item->lastvalue_null==1)||(item->prevvalue_null==1))
		{
			ret = FAIL;
		}
		else
		{
			if(item->value_type==ITEM_VALUE_TYPE_FLOAT)
			{
				if(cmp_double(item->lastvalue, item->prevvalue) == 0)
				{
					strcpy(value,"0");
				}
				else
				{
					strcpy(value,"1");
				}
			}
			else
			{
				if(strcmp(item->lastvalue_str, item->prevvalue_str) == 0)
				{
					strcpy(value,"0");
				}
				else
				{
					strcpy(value,"1");
				}
			}
		}
	}
	else if(strcmp(function,"str")==0)
	{
		if(item->value_type==ITEM_VALUE_TYPE_STR)
		{
			if(strstr(item->lastvalue_str, parameter) == NULL)
			{
				strcpy(value,"0");
			}
			else
			{
				strcpy(value,"1");
			}

		}
		else
		{
			ret = FAIL;
		}
	}
	else if(strcmp(function,"now")==0)
	{
		now=time(NULL);
                snprintf(value,MAX_STRING_LEN-1,"%d",(int)now);
	}
	else
	{
		zabbix_log( LOG_LEVEL_WARNING, "Unsupported function:%s",function);
		zabbix_syslog("Unsupported function:%s",function);
		ret = FAIL;
	}

	zabbix_log( LOG_LEVEL_DEBUG, "In evaluate_FUNCTION() pre-7");
	zabbix_log( LOG_LEVEL_DEBUG, "In evaluate_FUNCTION() 7 Formula [%s]", item->formula);
	zabbix_log( LOG_LEVEL_DEBUG, "In evaluate_FUNCTION() 7 Value [%s]", value);
	zabbix_log( LOG_LEVEL_DEBUG, "In evaluate_FUNCTION() 7 Units [%s]", item->units);
	zabbix_log( LOG_LEVEL_DEBUG, "In evaluate_FUNCTION() 7 Value [%s] Units [%s] Formula [%s]", value, item->units, item->formula);

	/* Add suffix: 1000000 -> 1 MB */
	if( (EVALUATE_FUNCTION_SUFFIX == flag) && (ITEM_VALUE_TYPE_FLOAT == item->value_type) &&
		(SUCCEED == ret) && strlen(item->units)>0)
	{
		value_float=atof(value);
		/* Custom multiplier? */
/*
		if(item->multiplier == 1)
		{
			value_float=value_float*atof(item->formula);
		}*/

		value_float_abs=abs(value_float);

		if(value_float_abs<1024)
		{
			strscpy(suffix,"");
		}
		else if(value_float_abs<1024*1024)
		{
			strscpy(suffix,"K");
			value_float=value_float/1024;
		}
		else if(value_float_abs<1024*1024*1024)
		{
			strscpy(suffix,"M");
			value_float=value_float/(1024*1024);
		}
		else
		{
			strscpy(suffix,"G");
			value_float=value_float/(1024*1024*1024);
		}
		zabbix_log( LOG_LEVEL_DEBUG, "Value [%s] [%f] Suffix [%s] Units [%s]",value,value_float,suffix,item->units);
//		if(cmp_double((double)round(value_float), value_float) == 0)
		if(cmp_double((int)(value_float+0.5), value_float) == 0)
		{
                	snprintf(value, MAX_STRING_LEN-1, "%.0f %s%s", value_float, suffix, item->units);
		}
		else
		{
                	snprintf(value, MAX_STRING_LEN-1, "%.2f %s%s", value_float, suffix, item->units);
		}
	}

	zabbix_log( LOG_LEVEL_DEBUG, "End of evaluate_FUNCTION. Result [%s]",value);
	return ret;
}

/******************************************************************************
 *                                                                            *
 * Function: evaluate_FUNCTION2                                               *
 *                                                                            *
 * Purpose: evaluate function                                                 *
 *                                                                            *
 * Parameters: host - host the key belongs to                                 *
 *             key - item's key (for example, 'max')                          *
 *             function - function (for example, 'max')                       *
 *             parameter - parameter of the function)                         *
 *                                                                            *
 * Return value: SUCCEED - evaluated succesfully, value contains its value    *
 *               FAIL - evaluation failed                                     *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments: Used for evaluation of notification macros                       *
 *                                                                            *
 ******************************************************************************/
int evaluate_FUNCTION2(char *value,char *host,char *key,char *function,char *parameter)
{
	DB_ITEM	item;
	DB_RESULT *result;

        char	sql[MAX_STRING_LEN];
	int	res;

	zabbix_log(LOG_LEVEL_DEBUG, "In get_lastvalue()" );

	snprintf(sql,sizeof(sql)-1,"select i.itemid,i.key_,h.host,h.port,i.delay,i.description,i.nextcheck,i.type,i.snmp_community,i.snmp_oid,h.useip,h.ip,i.history,i.lastvalue,i.prevvalue,i.hostid,h.status,i.value_type,h.network_errors,i.snmp_port,i.delta,i.prevorgvalue,i.lastclock,i.units,i.multiplier,i.snmpv3_securityname,i.snmpv3_securitylevel,i.snmpv3_authpassphrase,i.snmpv3_privpassphrase,i.formula,h.available from items i,hosts h where h.host='%s' and h.hostid=i.hostid and i.key_='%s'", host, key );
	result = DBselect(sql);

	if(DBnum_rows(result) == 0)
	{
        	DBfree_result(result);
		zabbix_log(LOG_LEVEL_WARNING, "Query [%s] returned empty result", sql );
		zabbix_syslog("Query [%s] returned empty result", sql );
		return FAIL;
	}

	DBget_item_from_db(&item,result, 0);

	zabbix_log(LOG_LEVEL_DEBUG, "Itemid:%d", item.itemid );

	zabbix_log(LOG_LEVEL_DEBUG, "Before evaluate_FUNCTION()" );

	res = evaluate_FUNCTION(value,&item,function,parameter, EVALUATE_FUNCTION_SUFFIX);

/* Cannot call DBfree_result until evaluate_FUNC */
	DBfree_result(result);
	return res;
}