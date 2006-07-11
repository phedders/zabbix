#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <netinet/in.h>
#include <netdb.h>

#include <signal.h>

#include <time.h>

#include "common.h"
#include "db.h"
#include "debug.h"
#include "functions.h"

void	signal_handler( int sig )
{
	if( SIGALRM == sig )
	{
		signal( SIGALRM, signal_handler );
 
		dbg_write( dbg_syswarn, "Timeout while executing operation." );
	}
 
	if( SIGQUIT == sig || SIGINT == sig || SIGTERM == sig )
	{
		dbg_write( dbg_fatal, "\nGot QUIT or INT or TERM signal. Exiting..." );
		exit( FAIL );
	}
 
	return;
}

void	daemon_init(void)
{
	int	i;
	pid_t	pid;

	if( (pid = fork()) != 0 )
	{
		exit( 0 );
	}
	setsid();

	signal( SIGHUP, SIG_IGN );

	if( (pid = fork()) !=0 )
	{
		exit( 0 );
	}

	chdir("/");

	umask(0);

	for(i=0;i<MAXFD;i++)
	{
		close(i);
	}
}

int	get_value(double *Result,char *Key,char *Host,int Port)
{
	int	s;
	int	i;
	char	c[1024];
	char	*e;
	void	*sigfunc;

	struct hostent *hp;

	struct sockaddr_in myaddr_in;
	struct sockaddr_in servaddr_in;

	dbg_write( dbg_proginfo, "%10s%25s\t", Host, Key );

	servaddr_in.sin_family=AF_INET;
	hp=gethostbyname(Host);

	if(hp==NULL)
	{
		dbg_write( dbg_syswarn, "Problem with gethostbyname" );
		return	FAIL;
	}

	servaddr_in.sin_addr.s_addr=((struct in_addr *)(hp->h_addr))->s_addr;

	servaddr_in.sin_port=htons(Port);

	s=socket(AF_INET,SOCK_STREAM,0);
	if(s==0)
	{
		dbg_write( dbg_syswarn, "Problem with socket" );
		return	FAIL;
	}
 
	myaddr_in.sin_family = AF_INET;
	myaddr_in.sin_port=0;
	myaddr_in.sin_addr.s_addr=INADDR_ANY;

	sigfunc = signal( SIGALRM, signal_handler );

	alarm(SUCKER_TIMEOUT);

	if( connect(s,(struct sockaddr *)&servaddr_in,sizeof(struct sockaddr_in)) == -1 )
	{
		dbg_write( dbg_syswarn, "Problem with connect" );
		close(s);
		return	FAIL;
	}
	alarm(0);
	signal( SIGALRM, sigfunc );

	sprintf(c,"%s\n",Key);
	if( sendto(s,c,strlen(c),0,(struct sockaddr *)&servaddr_in,sizeof(struct sockaddr_in)) == -1 )
	{
		dbg_write( dbg_syswarn, "Problem with sendto" );
		close(s);
		return	FAIL;
	} 
	i=sizeof(struct sockaddr_in);

	sigfunc = signal( SIGALRM, signal_handler );
	alarm(SUCKER_TIMEOUT);

	i=recvfrom(s,c,1023,0,(struct sockaddr *)&servaddr_in,&i);
	if(s==-1)
	{
		dbg_write( dbg_syswarn, "Problem with recvfrom" );
		close(s);
		return	FAIL;
	}
	alarm(0);
	signal( SIGALRM, sigfunc );
 
	if( close(s)!=0 )
	{
		dbg_write( dbg_syswarn, "Problem with close" );
		
	}
	c[i-1]=0;

	dbg_write( dbg_proginfo, "Got string:%10s", c );
	*Result=strtod(c,&e);

	if( (*Result==0) && (c==e) )
	{
		return	FAIL;
	}
	if( *Result<0 )
	{
		if( *Result == NOTSUPPORTED)
		{
			return SUCCEED;
		}
		else
		{
			return	FAIL;
		}
	}
	return SUCCEED;
}

int get_minnextcheck(void)
{
	char		c[1024];

	DB_RESULT	*result;
	DB_ROW		row;

	int		res;

	sprintf(c,"select min(nextcheck) from items i,hosts h where i.status=0 and h.status=0 and h.hostid=i.hostid");
	DBexecute(c);

	result = DBget_result();
	if(result==NULL)
	{
		dbg_write( dbg_proginfo, "No items to update for minnextcheck.");
		DBfree_result(result);
		return FAIL; 
	}
	if(DBnum_rows(result)==0)
	{
		dbg_write( dbg_proginfo, "No items to update for minnextcheck.");
		DBfree_result(result);
		return	FAIL;
	}

	row = DBfetch_row(result);
	if( row[0] == NULL )
	{
		DBfree_result(result);
		return	FAIL;
	}

	res=atoi(row[0]);
	DBfree_result(result);

	return	res;
}

int get_values(void)
{
	double		Value;
	char		c[1024];
	ITEM		Item;
 
	DB_RESULT	*result;
	DB_ROW		row;

	sprintf(c,"select i.itemid,i.key_,h.host,h.port,i.delay,i.description,i.history,i.lastdelete from items i,hosts h where i.nextcheck<=unix_timestamp() and i.status=0 and h.status=0 and h.hostid=i.hostid order by i.nextcheck");
	DBexecute(c);

	result = DBget_result();
	if(result==NULL)
	{
		dbg_write( dbg_syswarn, "No items to update.");
		DBfree_result(result);
		return SUCCEED; 
	}
	while ( (row = DBfetch_row(result)) != NULL )
	{
		Item.ItemId=atoi(row[0]);
		Item.Key=row[1];
		Item.Host=row[2];
		Item.Port=atoi(row[3]);
		Item.Delay=atoi(row[4]);
		Item.Description=row[5];
		Item.History=atoi(row[6]);
		Item.LastDelete=atoi(row[7]);
		Item.ShortName=row[8];

		if( get_value(&Value,Item.Key,Item.Host,Item.Port) == SUCCEED )
		{
			if( Value == NOTSUPPORTED)
			{
				sprintf(c,"update items set status=3 where itemid=%d",Item.ItemId);
				DBexecute(c);
			}
			else
			{
				sprintf(c,"insert into history (itemid,clock,value) values (%d,unix_timestamp(),%g)",Item.ItemId,Value);
				DBexecute(c);

				sprintf(c,"update items set NextCheck=unix_timestamp()+%d,PrevValue=LastValue,LastValue=%f,LastClock=unix_timestamp() where ItemId=%d",Item.Delay,Value,Item.ItemId);
				DBexecute(c);

				if( updateFunctions( Item.ItemId ) == FAIL)
				{
					dbg_write( dbg_syswarn, "Updating simple functions failed" );
				}
			}
		}
		else
		{
			dbg_write( dbg_syswarn, "Wrong value from host [HOST:%s KEY:%s VALUE:%f]", Item.Host, Item.Key, Value );
			dbg_write( dbg_syswarn, "The value is not stored in database.");
		}

		if(Item.LastDelete+3600<time(NULL))
		{
			sprintf	(c,"delete from history where ItemId=%d and Clock<unix_timestamp()-%d",Item.ItemId,Item.History);
			DBexecute(c);
	
			sprintf(c,"update items set LastDelete=unix_timestamp() where ItemId=%d",Item.ItemId);
			DBexecute(c);
		}
	}
	DBfree_result(result);
	return SUCCEED;
}

int main_loop()
{
	time_t now;

	int	nextcheck,sleeptime;

	for(;;)
	{
		now=time(NULL);
		get_values();

		dbg_write( dbg_proginfo, "Spent %d seconds while updating values", time(NULL)-now );

		nextcheck=get_minnextcheck();
		dbg_write( dbg_proginfo, "Nextcheck:%d Time:%d", nextcheck,time(NULL) );

		if( FAIL == nextcheck)
		{
			sleeptime=SUCKER_DELAY;
		}
		else
		{
			sleeptime=nextcheck-time(NULL);
			if(sleeptime<0)
			{
				sleeptime=0;
			}
		}
		if(sleeptime>0)
		{
			dbg_write( dbg_proginfo, "Sleeping for %d seconds", sleeptime );
			dbg_flush();
			sleep( sleeptime );
		}
		else
		{
			dbg_write( dbg_proginfo, "No sleeping" );
			dbg_flush();
		}
	}
}

int main(int argc, char **argv)
{
	daemon_init();

	signal( SIGINT,  signal_handler );
	signal( SIGQUIT, signal_handler );
	signal( SIGTERM, signal_handler );

	dbg_init( dbg_syswarn, "/var/log/zabbix_sucker.log" );
//	dbg_init( dbg_proginfo, "/var/log/zabbi_sucker.log" );

	DBconnect();

	main_loop();

	return SUCCEED;
}