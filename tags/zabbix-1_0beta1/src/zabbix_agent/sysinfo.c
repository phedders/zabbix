#include "config.h"

#include "errno.h"

/* For bcopy */
#ifdef HAVE_STRING_H
	#include <string.h>
#endif
#ifdef HAVE_STRINGS_H
	#include <strings.h>
#endif
#ifdef HAVE_STDIO_H
	#include <stdio.h>
#endif
#ifdef HAVE_STDLIB_H
	#include <stdlib.h>
#endif
#ifdef HAVE_UNISTD_H
	#include <unistd.h>
#endif
#ifdef HAVE_SYS_STAT_H
	#include <sys/stat.h>
#endif
#ifdef HAVE_SYS_TYPES_H
	#include <sys/types.h>
#endif
#ifdef HAVE_FCNTL_H
	#include <fcntl.h>
#endif
#ifdef HAVE_DIRENT_H
	#include <dirent.h>
#endif
/* Linux */
#ifdef HAVE_SYS_VFS_H
	#include <sys/vfs.h>
#endif
#ifdef HAVE_SYS_SYSINFO_H
	#include <sys/sysinfo.h>
#endif
/* Solaris */
#ifdef HAVE_SYS_STATVFS_H
	#include <sys/statvfs.h>
#endif
#ifdef HAVE_SYS_LOADAVG_H
	#include <sys/loadavg.h>
#endif
#ifdef HAVE_SYS_SOCKET_H
	#include <sys/socket.h>
#endif
#ifdef HAVE_NETINET_IN_H
	#include <netinet/in.h>
#endif
#ifdef HAVE_ARPA_INET_H
	#include <arpa/inet.h>
#endif
/* OpenBSD */
#ifdef HAVE_SYS_PARAM_H
	#include <sys/param.h>
#endif

#ifdef HAVE_SYS_MOUNT_H
	#include <sys/mount.h>
#endif

/* HP-UX */
#ifdef HAVE_SYS_PSTAT_H
	#include <sys/pstat.h>
#endif

#ifdef HAVE_NETDB_H
	#include <netdb.h>
#endif

/* Solaris */
#ifdef HAVE_SYS_SWAP_H
	#include <sys/swap.h>
#endif

/* FreeBSD */
#ifdef HAVE_SYS_SYSCTL_H
	#include <sys/sysctl.h>
#endif
/* FreeBSD */
#ifdef HAVE_VM_VM_PARAM_H
	#include <vm/vm_param.h>
#endif
/* FreeBSD */
#ifdef HAVE_SYS_VMMETER_H
	#include <sys/vmmeter.h>
#endif

#include "common.h"
#include "sysinfo.h"


COMMAND	commands[512]=
	{
	{"proc_cnt[*]"			,PROCCNT, "inetd"},

	{"memory[total]"		,TOTALMEM, 0},
	{"memory[shared]"		,SHAREDMEM, 0},
	{"memory[buffers]"		,BUFFERSMEM, 0},
	{"memory[cached]"		,CACHEDMEM, 0},
	{"memory[free]"			,FREEMEM, 0},

	{"diskfree[*]"			,DF, "/"},

	{"inodefree[*]"			,INODE, "/"},

	{"cksum[*]"			,CKSUM, "/etc/services"},

	{"filesize[*]"			,FILESIZE, "/var/log/syslog"},

	{"swap[free]"			,SWAPFREE, 0},
	{"swap[total]"			,SWAPTOTAL, 0},

/****************************************
  	All these perameters require more than 1 second to retrieve.

  	{"swap[in]"			,EXECUTE, "vmstat -n 1 2|tail -1|cut -b37-40"},
	{"swap[out]"			,EXECUTE, "vmstat -n 1 2|tail -1|cut -b41-44"},

	{"system[interrupts]"		,EXECUTE, "vmstat -n 1 2|tail -1|cut -b57-61"},
	{"system[switches]"		,EXECUTE, "vmstat -n 1 2|tail -1|cut -b62-67"},
***************************************/

	{"io[disk_io]"			,DISK_IO,  0},
	{"io[disk_rio]"			,DISK_RIO, 0},
	{"io[disk_wio]"			,DISK_WIO, 0},
	{"io[disk_rblk]"		,DISK_RBLK, 0},
	{"io[disk_wblk]"		,DISK_WBLK, 0},

	{"system[procload]"		,PROCLOAD, 0},
	{"system[procload5]"		,PROCLOAD5, 0},
	{"system[procload15]"		,PROCLOAD15, 0},
	{"system[proccount]"		,PROCCOUNT, 0},
#ifdef HAVE_PROC_LOADAVG
	{"system[procrunning]"		,EXECUTE, "cat /proc/loadavg|cut -f1 -d'/'|cut -f4 -d' '"},
#endif
	{"system[uptime]"		,UPTIME, 0},
	{"system[users]"		,EXECUTE, "who|wc -l"},

	{"ping"				,PING, 0},
/*	{"tcp_count"			,EXECUTE, "netstat -tn|grep EST|wc -l"}, */

	{"net[listen_23]"		,TCP_LISTEN, "0017"},
	{"net[listen_80]"		,TCP_LISTEN, "0050"},

	{"check_port[*]"		,CHECK_PORT, "80"},

	{"check_service[ssh]"		,CHECK_SERVICE_SSH, 0},
	{"check_service[smtp]"		,CHECK_SERVICE_SMTP, 0},
	{"check_service[ftp]"		,CHECK_SERVICE_FTP, 0},
	{"check_service[pop]"		,CHECK_SERVICE_POP, 0},
	{"check_service[nntp]"		,CHECK_SERVICE_NNTP, 0},
	{"check_service[imap]"		,CHECK_SERVICE_IMAP, 0},
	{0				,0}
	};

void	add_user_parameter(char *key,char *command)
{
	int i;

	for(i=0;;i++)
	{
		if( commands[i].key == 0)
		{
			commands[i].key=(char *)malloc(strlen(key)+1);
			strcpy(commands[i].key,key);

			commands[i].function=&EXECUTE;

			commands[i].parameter=(char *)malloc(strlen(command)+1);
			strcpy(commands[i].parameter,command);

			commands[i+1].key = 0;
			
			break;
		}
	}


}

void	test_parameters(void)
{
	int	i;
	float	result;
	float	(*function)();
	char	*parameter = NULL;
	char	*key = NULL;

	i=0;
	while(0 != commands[i].function)
	{
		key=commands[i].key;
		function=commands[i].function;
		parameter=commands[i].parameter;

		result = function(parameter);
		if( result == FAIL )
		{
			printf("\tUNSUPPORTED Key: %s\n",key);
		}
		else
		{
			printf("SUPPORTED Key: %s [%f]\n",key,result);
		}
		fflush(stdout);

		i++;
	}
}

float	process(char *command)
{
	char	*p;
	float	result;
	int	i;
	char	*n,*l,*r;
	float	(*function)();
	char	*parameter = NULL;
	char	key[1024];
	char	param[1024];
	char	cmd[1024];

	for( p=command+strlen(command)-1; p>command && ( *p=='\r' || *p =='\n' || *p == ' ' ); --p );
	p[1]=0;
	
	for(i=0;;i++)
	{
		if( commands[i].key == 0)
		{
			function=0;
			break;
		}

		strcpy(key, commands[i].key);

		if( (n = strstr(key,"[*]")) != NULL)
		{
			n[0]=0;

			l=strstr(command,"[");	
			r=strstr(command,"]");

			if( (l==NULL)||(r==NULL) )
			{
				continue;
			}

			strncpy( param,l+1, r-l-1);
			param[r-l-1]=0;

			strncpy( cmd, command, l-command);
			cmd[l-command]=0;

			if( strcmp(key, cmd) == 0)
			{
				function=commands[i].function;
				parameter=param;
				break;
			}
		}
		else
		{
			if( strcmp(key,command) == 0)
			{
				function=commands[i].function;
				parameter=commands[i].parameter;
				break;
			}	
		}
	}

	if( function !=0 )
	{
		result = function(parameter);
		if( result == FAIL )
		{
			result = NOTSUPPORTED;
		}
	}
	else
	{
		result=NOTSUPPORTED;
	}

	return	result;
}

/* Code for cksum is based on code from cksum.c */

static u_long crctab[] = {
	0x0,
	0x04c11db7, 0x09823b6e, 0x0d4326d9, 0x130476dc, 0x17c56b6b,
	0x1a864db2, 0x1e475005, 0x2608edb8, 0x22c9f00f, 0x2f8ad6d6,
	0x2b4bcb61, 0x350c9b64, 0x31cd86d3, 0x3c8ea00a, 0x384fbdbd,
	0x4c11db70, 0x48d0c6c7, 0x4593e01e, 0x4152fda9, 0x5f15adac,
	0x5bd4b01b, 0x569796c2, 0x52568b75, 0x6a1936c8, 0x6ed82b7f,
	0x639b0da6, 0x675a1011, 0x791d4014, 0x7ddc5da3, 0x709f7b7a,
	0x745e66cd, 0x9823b6e0, 0x9ce2ab57, 0x91a18d8e, 0x95609039,
	0x8b27c03c, 0x8fe6dd8b, 0x82a5fb52, 0x8664e6e5, 0xbe2b5b58,
	0xbaea46ef, 0xb7a96036, 0xb3687d81, 0xad2f2d84, 0xa9ee3033,
	0xa4ad16ea, 0xa06c0b5d, 0xd4326d90, 0xd0f37027, 0xddb056fe,
	0xd9714b49, 0xc7361b4c, 0xc3f706fb, 0xceb42022, 0xca753d95,
	0xf23a8028, 0xf6fb9d9f, 0xfbb8bb46, 0xff79a6f1, 0xe13ef6f4,
	0xe5ffeb43, 0xe8bccd9a, 0xec7dd02d, 0x34867077, 0x30476dc0,
	0x3d044b19, 0x39c556ae, 0x278206ab, 0x23431b1c, 0x2e003dc5,
	0x2ac12072, 0x128e9dcf, 0x164f8078, 0x1b0ca6a1, 0x1fcdbb16,
	0x018aeb13, 0x054bf6a4, 0x0808d07d, 0x0cc9cdca, 0x7897ab07,
	0x7c56b6b0, 0x71159069, 0x75d48dde, 0x6b93dddb, 0x6f52c06c,
	0x6211e6b5, 0x66d0fb02, 0x5e9f46bf, 0x5a5e5b08, 0x571d7dd1,
	0x53dc6066, 0x4d9b3063, 0x495a2dd4, 0x44190b0d, 0x40d816ba,
	0xaca5c697, 0xa864db20, 0xa527fdf9, 0xa1e6e04e, 0xbfa1b04b,
	0xbb60adfc, 0xb6238b25, 0xb2e29692, 0x8aad2b2f, 0x8e6c3698,
	0x832f1041, 0x87ee0df6, 0x99a95df3, 0x9d684044, 0x902b669d,
	0x94ea7b2a, 0xe0b41de7, 0xe4750050, 0xe9362689, 0xedf73b3e,
	0xf3b06b3b, 0xf771768c, 0xfa325055, 0xfef34de2, 0xc6bcf05f,
	0xc27dede8, 0xcf3ecb31, 0xcbffd686, 0xd5b88683, 0xd1799b34,
	0xdc3abded, 0xd8fba05a, 0x690ce0ee, 0x6dcdfd59, 0x608edb80,
	0x644fc637, 0x7a089632, 0x7ec98b85, 0x738aad5c, 0x774bb0eb,
	0x4f040d56, 0x4bc510e1, 0x46863638, 0x42472b8f, 0x5c007b8a,
	0x58c1663d, 0x558240e4, 0x51435d53, 0x251d3b9e, 0x21dc2629,
	0x2c9f00f0, 0x285e1d47, 0x36194d42, 0x32d850f5, 0x3f9b762c,
	0x3b5a6b9b, 0x0315d626, 0x07d4cb91, 0x0a97ed48, 0x0e56f0ff,
	0x1011a0fa, 0x14d0bd4d, 0x19939b94, 0x1d528623, 0xf12f560e,
	0xf5ee4bb9, 0xf8ad6d60, 0xfc6c70d7, 0xe22b20d2, 0xe6ea3d65,
	0xeba91bbc, 0xef68060b, 0xd727bbb6, 0xd3e6a601, 0xdea580d8,
	0xda649d6f, 0xc423cd6a, 0xc0e2d0dd, 0xcda1f604, 0xc960ebb3,
	0xbd3e8d7e, 0xb9ff90c9, 0xb4bcb610, 0xb07daba7, 0xae3afba2,
	0xaafbe615, 0xa7b8c0cc, 0xa379dd7b, 0x9b3660c6, 0x9ff77d71,
	0x92b45ba8, 0x9675461f, 0x8832161a, 0x8cf30bad, 0x81b02d74,
	0x857130c3, 0x5d8a9099, 0x594b8d2e, 0x5408abf7, 0x50c9b640,
	0x4e8ee645, 0x4a4ffbf2, 0x470cdd2b, 0x43cdc09c, 0x7b827d21,
	0x7f436096, 0x7200464f, 0x76c15bf8, 0x68860bfd, 0x6c47164a,
	0x61043093, 0x65c52d24, 0x119b4be9, 0x155a565e, 0x18197087,
	0x1cd86d30, 0x029f3d35, 0x065e2082, 0x0b1d065b, 0x0fdc1bec,
	0x3793a651, 0x3352bbe6, 0x3e119d3f, 0x3ad08088, 0x2497d08d,
	0x2056cd3a, 0x2d15ebe3, 0x29d4f654, 0xc5a92679, 0xc1683bce,
	0xcc2b1d17, 0xc8ea00a0, 0xd6ad50a5, 0xd26c4d12, 0xdf2f6bcb,
	0xdbee767c, 0xe3a1cbc1, 0xe760d676, 0xea23f0af, 0xeee2ed18,
	0xf0a5bd1d, 0xf464a0aa, 0xf9278673, 0xfde69bc4, 0x89b8fd09,
	0x8d79e0be, 0x803ac667, 0x84fbdbd0, 0x9abc8bd5, 0x9e7d9662,
	0x933eb0bb, 0x97ffad0c, 0xafb010b1, 0xab710d06, 0xa6322bdf,
	0xa2f33668, 0xbcb4666d, 0xb8757bda, 0xb5365d03, 0xb1f740b4
};

/*
 * Compute a POSIX 1003.2 checksum.  These routines have been broken out so
 * that other programs can use them.  The first routine, crc(), takes a file
 * descriptor to read from and locations to store the crc and the number of
 * bytes read.  The second routine, crc_buf(), takes a buffer and a length,
 * and a location to store the crc.  Both routines return 0 on success and 1
 * on failure.  Errno is set on failure.
 */

float   CKSUM(const char * filename)
{
	register u_char *p;
	register int nr;
	register u_long crc, len;
	u_char buf[16 * 1024];
	u_long cval, clen;
	int	fd;

	fd=open(filename,O_RDONLY);
	if(fd == -1)
	{
		return	FAIL;
	}

#define	COMPUTE(var, ch)	(var) = (var) << 8 ^ crctab[(var) >> 24 ^ (ch)]

	crc = len = 0;
	while ((nr = read(fd, buf, sizeof(buf))) > 0)
	{
		for( len += nr, p = buf; nr--; ++p)
		{
			COMPUTE(crc, *p);
		}
	}
	close(fd);
	
	if (nr < 0)
	{
		return	FAIL;
	}

	clen = len;

	/* Include the length of the file. */
	for (; len != 0; len >>= 8) {
		COMPUTE(crc, len & 0xff);
	}

	cval = ~crc;
	return	(float)cval;
}

int
crc_buf2(p, clen, cval)
	register u_char *p;
	u_long clen;
	u_long *cval;
{
	register u_long crc, len;

	crc = 0;
	for (len = clen; len--; ++p)
		COMPUTE(crc, *p);

	/* Include the length of the file. */
	for (len = clen; len != 0; len >>= 8)
		COMPUTE(crc, len & 0xff);

	*cval = ~crc;
	return (0);
}

















/* Solaris. The code is stolen from www.deja.com */
#ifndef HAVE_SYSINFO_FREESWAP
#ifdef HAVE_SYS_SWAP_H
void get_swapinfo(int *total, int *fr)
{
    register int cnt, i;
    register int t, f;
    struct swaptable *swt;
    struct swapent *ste;
    static char path[256];

    /* get total number of swap entries */
    cnt = swapctl(SC_GETNSWP, 0);

    /* allocate enough space to hold count + n swapents */
    swt = (struct swaptable *)malloc(sizeof(int) +
             cnt * sizeof(struct swapent));
    if (swt == NULL)
    {
  *total = 0;
  *fr = 0;
  return;
    }
    swt->swt_n = cnt;

    /* fill in ste_path pointers: we don't care about the paths, so we
point
       them all to the same buffer */
    ste = &(swt->swt_ent[0]);
    i = cnt;
    while (--i >= 0)
    {
  ste++->ste_path = path;
    }

    /* grab all swap info */
    swapctl(SC_LIST, swt);

    /* walk thru the structs and sum up the fields */
    t = f = 0;
    ste = &(swt->swt_ent[0]);
    i = cnt;
    while (--i >= 0)
    {
  /* dont count slots being deleted */
  if (!(ste->ste_flags & ST_INDEL) &&
      !(ste->ste_flags & ST_DOINGDEL))
  {
      t += ste->ste_pages;
      f += ste->ste_free;
  } ste++;
    }

    /* fill in the results */
    *total = t;
    *fr = f;
    free(swt);
}
#endif
#endif

float   FILESIZE(const char * filename)
{
	struct stat	buf;

	if(stat(filename,&buf) == 0)
	{
		return	buf.st_size;
	}

	return	FAIL;
}

float	PROCCNT(const char * procname)
{
#ifdef	HAVE_PROC_1_STATUS
	DIR	*dir;
	struct	dirent *entries;
	struct	stat buf;
	char	filename[512];
	char	line[512];
	char	name1[256],name2[256];

	FILE	*f;

	int	proccount=0;

	dir=opendir("/proc");
	while((entries=readdir(dir))!=NULL)
	{
		strcpy(filename,"/proc/");	
		strcat(filename,entries->d_name);
		strcat(filename,"/status");

		if(stat(filename,&buf)==0)
		{
			fflush(stdout);
			f=fopen(filename,"r");
			if(f==NULL)
			{
				continue;
			}
			fgets(line,512,f);
			fclose(f);

			if(sscanf(line,"%s\t%s\n",name1,name2)==2)
                        {
                                if(strcmp(name1,"Name:") == 0)
                                {
                                        if(strcmp(procname,name2)==0)
                                        {
                                                proccount++;
                                        }
                                }
                                else
                                {
                                        closedir(dir);
                                        return  FAIL;
                                }
                        }
                        else
                        {
                                closedir(dir);
                                return  FAIL;
                        }
		}
	}
	closedir(dir);
	return	(float)proccount;
#else
	return	FAIL;
#endif
}

float	INODE(const char * mountPoint)
{
#ifdef HAVE_SYS_STATVFS_H
	struct statvfs   s;

	if ( statvfs( (char *)mountPoint, &s) != 0 )
	{
		return  FAIL;
	}

	return  s.f_favail;
#else
	struct statfs   s;
	long            blocks_used;
	long            blocks_percent_used;

	if ( statfs( (char *)mountPoint, &s) != 0 ) 
	{
		return	FAIL;
	}
        
	if ( s.f_blocks > 0 ) {
		blocks_used = s.f_blocks - s.f_bfree;
		blocks_percent_used = (long)
		(blocks_used * 100.0 / (blocks_used + s.f_bavail) + 0.5);

//		printf(
//		"%7.0f %7.0f  %7.0f  %5ld%%   %s\n"
//		,s.f_blocks * (s.f_bsize / 1024.0)
//		,(s.f_blocks - s.f_bfree)  * (s.f_bsize / 1024.0)
//		,s.f_bavail * (s.f_bsize / 1024.0)
//		,blocks_percent_used
//		,mountPoint);
		return s.f_ffree;

	}

	return	FAIL;
#endif
}

float	DF(const char * mountPoint)
{
#ifdef HAVE_SYS_STATVFS_H
	struct statvfs   s;

	if ( statvfs( (char *)mountPoint, &s) != 0 )
	{
		return  FAIL;
	}

	return  s.f_bsize*s.f_bavail;
#else
	struct statfs   s;
	long            blocks_used;
	long            blocks_percent_used;

	if ( statfs( (char *)mountPoint, &s) != 0 )
	{
		return	FAIL;
	}
        
	if ( s.f_blocks > 0 ) {
		blocks_used = s.f_blocks - s.f_bfree;
		blocks_percent_used = (long)
		(blocks_used * 100.0 / (blocks_used + s.f_bavail) + 0.5);

//		printf(
//		"%7.0f %7.0f  %7.0f  %5ld%%   %s\n"
//		,s.f_blocks * (s.f_bsize / 1024.0)
//		,(s.f_blocks - s.f_bfree)  * (s.f_bsize / 1024.0)
//		,s.f_bavail * (s.f_bsize / 1024.0)
//		,blocks_percent_used
//		,mountPoint);
		return s.f_bavail * (s.f_bsize / 1024.0);

	}

	return	FAIL;
#endif
}

float	TCP_LISTEN(const char *porthex)
{
#ifdef HAVE_PROC
	FILE	*f;
	char	c[1024];

	char	pattern[1024]="0050 00000000:0000 0A";

	strcpy(pattern,porthex);
	strcat(pattern," 00000000:0000 0A");

	f=fopen("/proc/net/tcp","r");
	if(NULL == f)
	{
		return	FAIL;
	}

	while (NULL!=fgets(c,1024,f))
	{
		if(NULL != strstr(c,pattern))
		{
			fclose(f);
			return 1;
		}
	}
	fclose(f);

	return	0;
#else
	return	FAIL;
#endif
}

#ifdef	HAVE_PROC
float	getPROC(char *file,int lineno,int fieldno)
{
	FILE	*f;
	char	*t;
	char	c[1024];
	float	result;
	int	i;

	f=fopen(file,"r");
	if(NULL == f)
	{
		return	FAIL;
	}
	for(i=1;i<=lineno;i++)
	{	
		fgets(c,1024,f);
	}
	t=(char *)strtok(c," ");
	for(i=2;i<=fieldno;i++)
	{
		t=(char *)strtok(NULL," ");
	}
	fclose(f);

	sscanf(t, "%f", &result );

	return	result;
}
#endif

float	CACHEDMEM(void)
{
#ifdef HAVE_PROC
	return getPROC("/proc/meminfo",8,2);
#else
	return FAIL;
#endif
}

float	BUFFERSMEM(void)
{
#ifdef HAVE_SYSINFO_BUFFERRAM
	struct sysinfo info;

	if( 0 == sysinfo(&info))
	{
		return	(float)info.bufferram;
	}
	else
	{
		return FAIL;
	}
#else
	return	FAIL;
#endif
}

float	SHAREDMEM(void)
{
#ifdef HAVE_SYSINFO_SHAREDRAM
	struct sysinfo info;

	if( 0 == sysinfo(&info))
	{
		return	(float)info.sharedram;
	}
	else
	{
		return FAIL;
	}
#else
#ifdef HAVE_SYS_VMMETER_H
	int mib[2],len;
	struct vmtotal v;

	len=sizeof(struct vmtotal);
	mib[0]=CTL_VM;
	mib[1]=VM_METER;

	sysctl(mib,2,&v,&len,NULL,0);

	return (float)(v.t_armshr<<2);
#else
	return	FAIL;
#endif
#endif
}

float	TOTALMEM(void)
{
#ifdef HAVE_SYS_PSTAT_H
	struct	pst_static pst;
	long	page;

	if(pstat_getstatic(&pst, sizeof(pst), (size_t)1, 0) == -1)
	{
		return FAIL;
	}
	else
	{
		/* Get page size */	
		page = pst.page_size;
		/* Total physical memory in bytes */	
		return page*pst.physical_memory;
	}
#else
#ifdef HAVE_SYSINFO_TOTALRAM
	struct sysinfo info;

	if( 0 == sysinfo(&info))
	{
		return	(float)info.totalram;
	}
	else
	{
		return FAIL;
	}
#else
#ifdef HAVE_SYS_VMMETER_H
	int mib[2],len;
	struct vmtotal v;

	len=sizeof(struct vmtotal);
	mib[0]=CTL_VM;
	mib[1]=VM_METER;

	sysctl(mib,2,&v,&len,NULL,0);

	return (float)(v.t_rm<<2);
#else
	return	FAIL;
#endif
#endif
#endif
}

float	FREEMEM(void)
{
#ifdef HAVE_SYS_PSTAT_H
	struct	pst_static pst;
	struct	pst_dynamic dyn;
	long	page;

	if(pstat_getstatic(&pst, sizeof(pst), (size_t)1, 0) == -1)
	{
		return FAIL;
	}
	else
	{
		/* Get page size */	
		page = pst.page_size;
//		return pst.physical_memory;

		if (pstat_getdynamic(&dyn, sizeof(dyn), 1, 0) == -1)
		{
			return FAIL;
		}
		else
		{
//cout<<"total virtual memory allocated is " << dyn.psd_vm << "
//pages, " << dyn.psd_vm * page << " bytes" << endl;
//cout<<"active virtual memory is " << dyn.psd_avm <<" pages, " <<
//dyn.psd_avm * page << " bytes" << endl;
//cout<<"total real memory is " << dyn.psd_rm << " pages, " <<
//dyn.psd_rm * page << " bytes" << endl;
//cout<<"active real memory is " << dyn.psd_arm << " pages, " <<
//dyn.psd_arm * page << " bytes" << endl;
//cout<<"free memory is " << dyn.psd_free << " pages, " <<
		/* Free memory in bytes */	
			return dyn.psd_free * page;
		}
	}
#else
#ifdef HAVE_SYSINFO_FREERAM
	struct sysinfo info;

	if( 0 == sysinfo(&info))
	{
		return	(float)info.freeram;
	}
	else
	{
		return FAIL;
	}
#else
#ifdef HAVE_SYS_VMMETER_H
	int mib[2],len;
	struct vmtotal v;

	len=sizeof(struct vmtotal);
	mib[0]=CTL_VM;
	mib[1]=VM_METER;

	sysctl(mib,2,&v,&len,NULL,0);

	return (float)(v.t_free<<2);
#else
	return	FAIL;
#endif
#endif
#endif
}

float	UPTIME(void)
{
#ifdef HAVE_SYSINFO_UPTIME
	struct sysinfo info;

	if( 0 == sysinfo(&info))
	{
		return	(float)info.uptime;
	}
	else
	{
		return FAIL;
	}
#else
	return	FAIL;
#endif
}

float	PING(void)
{
	return	1;
}

float	PROCLOAD(void)
{
#ifdef HAVE_GETLOADAVG
	double	load[3];

	if(getloadavg(load, 3))
	{
		return load[0];	
	}
	else
	{
		return FAIL;	
	}
#else
#ifdef	HAVE_SYS_PSTAT_H
	struct	pst_dynamic dyn;

	if (pstat_getdynamic(&dyn, sizeof(dyn), 1, 0) == -1)
	{
		return FAIL;
	}
	else
	{
		return dyn.psd_avg_1_min;
	}
#else
#ifdef HAVE_PROC_LOADAVG
	return	getPROC("/proc/loadavg",1,1);
#else
	return	FAIL;
#endif
#endif
#endif
}

float	PROCLOAD5(void)
{
#ifdef HAVE_GETLOADAVG
	double	load[3];

	if(getloadavg(load, 3))
	{
		return load[1];	
	}
	else
	{
		return FAIL;	
	}
#else
#ifdef	HAVE_SYS_PSTAT_H
	struct	pst_dynamic dyn;

	if (pstat_getdynamic(&dyn, sizeof(dyn), 1, 0) == -1)
	{
		return FAIL;
	}
	else
	{
		return dyn.psd_avg_5_min;
	}
#else
#ifdef	HAVE_PROC_LOADAVG
	return	getPROC("/proc/loadavg",1,2);
#else
	return	FAIL;
#endif
#endif
#endif
}

float	PROCLOAD15(void)
{
#ifdef HAVE_GETLOADAVG
	double	load[3];

	if(getloadavg(load, 3))
	{
		return load[2];	
	}
	else
	{
		return FAIL;	
	}
#else
#ifdef	HAVE_SYS_PSTAT_H
	struct	pst_dynamic dyn;

	if (pstat_getdynamic(&dyn, sizeof(dyn), 1, 0) == -1)
	{
		return FAIL;
	}
	else
	{
		return dyn.psd_avg_5_min;
	}
#else
#ifdef	HAVE_PROC_LOADAVG
	return	getPROC("/proc/loadavg",1,3);
#else
	return	FAIL;
#endif
#endif
#endif
}

float	SWAPFREE(void)
{
#ifdef HAVE_SYSINFO_FREESWAP
	struct sysinfo info;

	if( 0 == sysinfo(&info))
	{
		return	(float)info.freeswap;
	}
	else
	{
		return FAIL;
	}
/* Solaris */
#else
#ifdef HAVE_SYS_SWAP_H
	int swaptotal,swapfree;

	get_swapinfo(&swaptotal,&swapfree);

	return	(float)swapfree;
#else
	return	FAIL;
#endif
#endif
}

float	PROCCOUNT(void)
{
#ifdef HAVE_SYSINFO_PROCS
	struct sysinfo info;

	if( 0 == sysinfo(&info))
	{
		return	info.procs;
	}
	else
	{
		return FAIL;
	}
#else
	return	FAIL;
#endif
}

float	SWAPTOTAL(void)
{
#ifdef HAVE_SYSINFO_TOTALSWAP
	struct sysinfo info;

	if( 0 == sysinfo(&info))
	{
		return	(float)info.totalswap;
	}
	else
	{
		return FAIL;
	}
/* Solaris */
#else
#ifdef HAVE_SYS_SWAP_H
	int swaptotal,swapfree;

	get_swapinfo(&swaptotal,&swapfree);

	return	(float)swaptotal;
#else
	return	FAIL;
#endif
#endif
}

float	DISK_IO(void)
{
#ifdef	HAVE_PROC
	return	getPROC("/proc/stat",2,2);
#else
	return	FAIL;
#endif
}

float	DISK_RIO(void)
{
#ifdef	HAVE_PROC
	return	getPROC("/proc/stat",3,2);
#else
	return	FAIL;
#endif
}

float	DISK_WIO(void)
{
#ifdef	HAVE_PROC
	return	getPROC("/proc/stat",4,2);
#else
	return	FAIL;
#endif
}

float	DISK_RBLK(void)
{
#ifdef	HAVE_PROC
	return	getPROC("/proc/stat",5,2);
#else
	return	FAIL;
#endif
}

float	DISK_WBLK(void)
{
#ifdef	HAVE_PROC
	return	getPROC("/proc/stat",6,2);
#else
	return	FAIL;
#endif
}

float	EXECUTE(char *command)
{
	FILE	*f;
	float	result;
	char	c[1024];

	f=popen( command,"r");
	if(f==0)
	{
		switch (errno)
		{
			case	EINTR:
				return TIMEOUT_ERROR;
			default:
				return FAIL;
		}
	}

	if(NULL == fgets(c,1024,f))
	{
		pclose(f);
		switch (errno)
		{
			case	EINTR:
				return TIMEOUT_ERROR;
			default:
				return FAIL;
		}
	}

	if(pclose(f) != 0)
	{
		switch (errno)
		{
			case	EINTR:
				return TIMEOUT_ERROR;
			default:
				return FAIL;
		}
	}

	sscanf(c, "%f", &result );

	return	result;
}

float	tcp_expect(char	*hostname, short port, char *expect,char *sendtoclose)
{
	char	*haddr;
	char	c[1024];
	
	int	s;
	struct	sockaddr_in addr;
	int	addrlen;


	struct hostent *host;

	host = gethostbyname(hostname);
	if(host == NULL)
	{
		return	0;
	}

	haddr=host->h_addr;


	addrlen = sizeof(addr);
	memset(&addr, 0, addrlen);
	addr.sin_port = htons(port);
	addr.sin_family = AF_INET;
	bcopy(haddr, (void *) &addr.sin_addr.s_addr, 4);

	s = socket(AF_INET, SOCK_STREAM, 0);
	if (s == -1)
	{
		close(s);
		return	0;
	}

	if (connect(s, (struct sockaddr *) &addr, addrlen) == -1)
	{
		close(s);
		return	0;
	}

	if( expect == NULL)
	{
		close(s);
		return	1;
	}

	memset(&c, 0, 1024);
	recv(s, c, 1024, 0);
	if ( strncmp(c, expect, strlen(expect)) == 0 )
	{
		send(s,sendtoclose,strlen(sendtoclose),0);
		close(s);
		return	1;
	}
	else
	{
		send(s,sendtoclose,strlen(sendtoclose),0);
		close(s);
		return	0;
	}
}

float	CHECK_SERVICE_SSH(void)
{
	return	tcp_expect("127.0.0.1",22,"SSH","0\n");
}

float	CHECK_SERVICE_SMTP(void)
{
	return	tcp_expect("127.0.0.1",25,"220","");
}

float	CHECK_SERVICE_FTP(void)
{
	return	tcp_expect("127.0.0.1",21,"220","");
}

float	CHECK_SERVICE_POP(void)
{
	return	tcp_expect("127.0.0.1",110,"+OK","");
}

float	CHECK_SERVICE_NNTP(void)
{
	return	tcp_expect("127.0.0.1",119,"220","");
}

float	CHECK_SERVICE_IMAP(void)
{
	return	tcp_expect("127.0.0.1",143,"* OK","a1 LOGOUT\n");
}

float	CHECK_PORT(char *port)
{
	int i;

	i=atoi(port);
	return	tcp_expect("127.0.0.1",i,NULL,"");
}