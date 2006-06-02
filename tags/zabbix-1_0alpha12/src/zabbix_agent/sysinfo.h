#ifndef MON_SYSINFO_H
#define MON_SYSINFO_H
 
float	process(char *command);

void	test_parameters(void);

float   CKSUM(const char * filename);
float	INODE(const char * mountPoint);
float	FILESIZE(const char * filename);
float	DF(const char * mountPoint);
float	getPROC(char *file,int lineno,int fieldno);
float	FREEMEM(void);
float	TOTALMEM(void);
float	SHAREDMEM(void);
float	BUFFERSMEM(void);
float	CACHEDMEM(void);
float	DISK_IO(void);
float	DISK_RIO(void);
float	DISK_WIO(void);
float	DISK_RBLK(void);
float	DISK_WBLK(void);
float	PING(void);
float	PROCCNT(const char *procname);
float	PROCCOUNT(void);
float	PROCLOAD(void);
float	PROCLOAD5(void);
float	PROCLOAD15(void);
float	SWAPFREE(void);
float	SWAPTOTAL(void);
float	TCP_LISTEN(const char *porthex);
float	UPTIME(void);
float	EXECUTE(char *command);

float	CHECK_SERVICE_SSH(void);
float	CHECK_SERVICE_SMTP(void);
float	CHECK_SERVICE_FTP(void);
float	CHECK_SERVICE_POP(void);
float	CHECK_SERVICE_NNTP(void);
float	CHECK_SERVICE_IMAP(void);

float	CHECK_PORT(char *port);

#define COMMAND struct command_type
COMMAND
{
	char	*key;
	void	*function;
	char	*parameter;
};


#endif