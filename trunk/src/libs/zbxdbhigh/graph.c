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


#include <stdlib.h>
#include <stdio.h>

#include <string.h>
#include <strings.h>

#include "db.h"
#include "log.h"
#include "zlog.h"
#include "common.h"

int	DBadd_graph(char *name, int width, int height, int yaxistype, double yaxismin, double yaxismax)
{
	char	sql[MAX_STRING_LEN];
	int	graphid;
	char	name_esc[GRAPH_NAME_LEN_MAX];

	DBescape_string(name,name_esc,GRAPH_NAME_LEN_MAX);

	snprintf(sql, sizeof(sql)-1,"insert into graphs (name,width,height,yaxistype,yaxismin,yaxismax) values ('%s',%d,%d,%d,%f,%f)", name_esc, width, height, yaxistype, yaxismin, yaxismax);
	if(FAIL == DBexecute(sql))
	{
		return FAIL;
	}

	graphid=DBinsert_id();

	if(graphid==0)
	{
		return FAIL;
	}

	return graphid;
}

int	DBadd_item_to_graph(int graphid,int itemid, char *color,int drawtype, int sortorder)
{
	char	sql[MAX_STRING_LEN];
	int	gitemid;
	char	color_esc[GRAPH_ITEM_COLOR_LEN_MAX];

	DBescape_string(color,color_esc,GRAPH_ITEM_COLOR_LEN_MAX);

	snprintf(sql, sizeof(sql)-1,"insert into graphs_items (graphid,itemid,drawtype,sortorder,color) values (%d,%d,%d,%d,'%s')", graphid, itemid, drawtype, sortorder, color_esc);
	if(FAIL == DBexecute(sql))
	{
		return FAIL;
	}

	gitemid=DBinsert_id();

	if(gitemid==0)
	{
		return FAIL;
	}

	return gitemid;
}

int	DBget_graph_item_by_gitemid(int gitemid, DB_GRAPH_ITEM *graph_item)
{
	DB_RESULT	*result;
	char	sql[MAX_STRING_LEN];
	int	ret = SUCCEED;

	zabbix_log( LOG_LEVEL_WARNING, "In DBget_graph_item_by_gitemid(%d)", gitemid);

	snprintf(sql,sizeof(sql)-1,"select gitemid, graphid, itemid, drawtype, sortorder, color from graphs_items where gitemid=%d", gitemid);
	result=DBselect(sql);

	if(DBnum_rows(result)==0)
	{
		ret = FAIL;
	}
	else
	{
		graph_item->gitemid=atoi(DBget_field(result,0,0));
		graph_item->graphid=atoi(DBget_field(result,0,1));
		graph_item->itemid=atoi(DBget_field(result,0,2));
		graph_item->drawtype=atoi(DBget_field(result,0,3));
		graph_item->sortorder=atoi(DBget_field(result,0,4));
		strscpy(graph_item->color,DBget_field(result,0,5));
	}

	DBfree_result(result);

	return ret;
}

int	DBget_graph_by_graphid(int graphid, DB_GRAPH *graph)
{
	DB_RESULT	*result;
	char	sql[MAX_STRING_LEN];
	int	ret = SUCCEED;

	zabbix_log( LOG_LEVEL_WARNING, "In DBget_graph_by_graphid(%d)", graphid);

	snprintf(sql,sizeof(sql)-1,"select graphid,name,width,height,yaxistype,yaxismin,yaxismax from graphs where graphid=%d", graphid);
	result=DBselect(sql);

	if(DBnum_rows(result)==0)
	{
		ret = FAIL;
	}
	else
	{
		graph->graphid=atoi(DBget_field(result,0,0));
		strscpy(graph->name,DBget_field(result,0,1));
		graph->width=atoi(DBget_field(result,0,2));
		graph->height=atoi(DBget_field(result,0,3));
		graph->yaxistype=atoi(DBget_field(result,0,4));
		graph->yaxismin=atof(DBget_field(result,0,5));
		graph->yaxismax=atof(DBget_field(result,0,6));
	}

	DBfree_result(result);

	return ret;
}

int	DBadd_graph_item_to_linked_hosts(int gitemid,int hostid)
{
	DB_HOST	host;
	DB_ITEM	item;
	DB_GRAPH_ITEM	graph_item;
	DB_GRAPH	graph;
	DB_RESULT	*result,*result2;
	char	sql[MAX_STRING_LEN];
	char	name_esc[GRAPH_NAME_LEN_MAX];
	int	i,j;
	int	graphid;
	int	itemid;

	zabbix_log( LOG_LEVEL_WARNING, "In DBadd_graph_item_to_linked_hosts(%d,%d)", gitemid, hostid);

	if(DBget_graph_item_by_gitemid(gitemid, &graph_item)==FAIL)
	{
		return FAIL;
	}

	if(DBget_graph_by_graphid(graph_item.graphid, &graph)==FAIL)
	{
		return FAIL;
	}

	if(DBget_item_by_itemid(graph_item.itemid, &item)==FAIL)
	{
		return FAIL;
	}

	if(hostid==0)
	{
		snprintf(sql,sizeof(sql)-1,"select hostid,templateid,graphs from hosts_templates where templateid=%d", item.hostid);
	}
	else
	{
		snprintf(sql,sizeof(sql)-1,"select hostid,templateid,graphs from hosts_templates where hostid=%d and templateid=%d", hostid, item.hostid);
	}

	zabbix_log( LOG_LEVEL_WARNING, "\tSQL [%s]", sql);

	result=DBselect(sql);
	for(i=0;i<DBnum_rows(result);i++)
	{
		if( (atoi(DBget_field(result,i,2))&1) == 0)	continue;

		snprintf(sql,sizeof(sql)-1,"select i.itemid from items i where i.key_='%s' and i.hostid=%d", item.key, atoi(DBget_field(result,i,0)));
		zabbix_log( LOG_LEVEL_WARNING, "\t\tSQL [%s]", sql);

		result2=DBselect(sql);
		if(DBnum_rows(result2)==0)
		{
			DBfree_result(result2);
			continue;
		}

		itemid=atoi(DBget_field(result2,0,0));
		DBfree_result(result2);

		DBescape_string(graph.name,name_esc,GRAPH_NAME_LEN_MAX);

		if(DBget_host_by_hostid(atoi(DBget_field(result,i,0)), &host) == FAIL)	continue;

		snprintf(sql,sizeof(sql)-1,"select distinct g.graphid from graphs g,graphs_items gi,items i where i.itemid=gi.itemid and i.hostid=%d and g.graphid=gi.graphid and g.name='%s'", atoi(DBget_field(result,i,0)), name_esc);
		result2=DBselect(sql);

		for(j=0;j<DBnum_rows(result2);j++)
		{
			DBadd_item_to_graph(atoi(DBget_field(result2,j,0)),itemid,graph_item.color,graph_item.drawtype,graph_item.sortorder);
		}
		if(DBnum_rows(result2)==0)
		{
			graphid=DBadd_graph(graph.name,graph.width,graph.height,graph.yaxistype,graph.yaxismin,graph.yaxismax);
			if(graphid!=FAIL)
			{
				DBadd_item_to_graph(graphid,itemid,graph_item.color,graph_item.drawtype,graph_item.sortorder);
			}
		}
		DBfree_result(result2);
	}
	DBfree_result(result);

	return SUCCEED;
}
