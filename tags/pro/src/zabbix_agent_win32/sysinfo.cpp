/* 
** ZabbixW32 - Win32 agent for Zabbix
** Copyright (C) 2002 Victor Kirhenshtein
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
**
** $module: sysinfo.cpp
**
**/

#include "zabbixw32.h"
#include <psapi.h>


//
// Static data
//

static DWORD procList[MAX_PROCESSES];
static HMODULE modList[MAX_MODULES];


//
// Get instance for parameters like name[instance]
//

static void GetParameterInstance(char *param,char *instance,int maxSize)
{
   char *ptr1,*ptr2;

   instance[0]=0;    // Default is empty string
   ptr1=strchr(param,'[');
   ptr2=strchr(ptr1,']');
   if ((ptr1==NULL)||(ptr2==NULL))
      return;

   ptr1++;
   memcpy(instance,ptr1,min(ptr2-ptr1,maxSize-1));
   instance[min(ptr2-ptr1,maxSize-1)]=0;
}


//
// Handler for parameters which always returns numeric constant (like ping)
//

static float H_NumericConstant(char *cmd,char *arg)
{
   return (float)((long)arg);
}


//
// Handler for parameters which always returns string constant (like version[zabbix_agent])
//

static char *H_StringConstant(char *cmd,char *arg)
{
   return strdup(arg ? arg : "(null)\n");
}


//
// Handler for parameters which returns numeric value from specific variable
//

static float H_NumericPtr(char *cmd,char *arg)
{
   return *((float *)arg);
}


//
// Handler for cpu_load[]
//

static float H_ProcUtil(char *cmd,char *arg)
{
   char proc[16];
   int procnum;

   if ((DWORD)arg & 0x80)  // bit 7 is set if we are called for specific instance
   {
      GetParameterInstance(cmd,proc,15);
      if (proc[0]==0)
      {
         procnum=1;
      }
      else
      {
         procnum=atoi(proc)+1;
         if ((procnum<1)||(procnum>MAX_CPU))
            return NOTSUPPORTED;
      }
   }
   else
   {
      procnum=0;     // _Total
   }
   switch((DWORD)arg & 0x03)
   {
      case 0:  // procload
         return (float)statProcUtilization[procnum];
      case 1:  // procload5
         return (float)statProcUtilization5[procnum];
      case 2:  // procload15
         return (float)statProcUtilization15[procnum];
   }
   return NOTSUPPORTED;
}


//
// Handler for system[proccount]
//

static float H_ProcCount(char *cmd,char *arg)
{
   DWORD dwSize=0;

   EnumProcesses(procList,sizeof(DWORD)*MAX_PROCESSES,&dwSize);
   return (float)(dwSize/sizeof(DWORD));
}


//
// Handler for proc_cnt[*]
//

static float H_ProcCountSpecific(char *cmd,char *arg)
{
   DWORD dwSize=0;
   int i,counter,procCount;
   char procName[MAX_PATH];
   HANDLE hProcess;

   GetParameterInstance(cmd,procName,MAX_PATH-1);
   EnumProcesses(procList,sizeof(DWORD)*MAX_PROCESSES,&dwSize);
   procCount=dwSize/sizeof(DWORD);
   for(i=0,counter=0;i<procCount;i++)
   {
      hProcess=OpenProcess(PROCESS_QUERY_INFORMATION | PROCESS_VM_READ,FALSE,procList[i]);
      if (hProcess!=NULL)
      {
         if (EnumProcessModules(hProcess,modList,sizeof(HMODULE)*MAX_MODULES,&dwSize))
         {
            if (dwSize>=sizeof(HMODULE))     // At least one module exist
            {
               char baseName[MAX_PATH];

               GetModuleBaseName(hProcess,modList[0],baseName,sizeof(baseName));
               if (!stricmp(baseName,procName))
                  counter++;
            }
         }
         CloseHandle(hProcess);
      }
   }
   return (float)counter;
}


//
// Handler for memory[*] and swap[*] parameters
//

static float H_MemoryInfo(char *cmd,char *arg)
{
   MEMORYSTATUS ms;

   GlobalMemoryStatus(&ms);

   if (!strcmp(cmd,"memory[total]"))
      return (float)ms.dwTotalPhys;
   if (!strcmp(cmd,"memory[free]"))
      return (float)ms.dwAvailPhys;
   if (!strcmp(cmd,"swap[total]"))
      return (float)ms.dwTotalPageFile;
   if (!strcmp(cmd,"swap[free]"))
      return (float)ms.dwAvailPageFile;

   return NOTSUPPORTED;
}


//
// Handler for system[hostname] parameter
//

static char *H_HostName(char *cmd,char *arg)
{
   DWORD dwSize;
   char buffer[MAX_COMPUTERNAME_LENGTH+1];

   dwSize=MAX_COMPUTERNAME_LENGTH+1;
   GetComputerName(buffer,&dwSize);
   return strdup(buffer);
}


//
// Handler for diskfree[*] and disktotal[*] parameters
//

static float H_DiskInfo(char *cmd,char *arg)
{
   char path[MAX_PATH];
   ULARGE_INTEGER freeBytes,totalBytes;
   double kbytes;

   GetParameterInstance(cmd,path,MAX_PATH-1);
   if (!GetDiskFreeSpaceEx(path,&freeBytes,&totalBytes,NULL))
      return NOTSUPPORTED;

   if (!memcmp(cmd,"diskfree[",9))
   {
      kbytes=(double)((__int64)(freeBytes.QuadPart/1024));
      return (float)kbytes;
   }
   else
   {
      kbytes=(double)((__int64)(totalBytes.QuadPart/1024));
      return (float)kbytes;
   }
}


//
// Handler for service_state[*] parameter
//

static float H_ServiceState(char *cmd,char *arg)
{
   SC_HANDLE mgr,service;
   char serviceName[MAX_PATH];
   float result;

   GetParameterInstance(cmd,serviceName,MAX_PATH-1);

   mgr=OpenSCManager(NULL,NULL,GENERIC_READ);
   if (mgr==NULL)
      return 255;    // Unable to retrieve information

   service=OpenService(mgr,serviceName,SERVICE_QUERY_STATUS);
   if (service==NULL)
   {
      result=NOTSUPPORTED;
   }
   else
   {
      SERVICE_STATUS status;

      if (QueryServiceStatus(service,&status))
      {
         int i;
         static DWORD states[7]={ SERVICE_RUNNING,SERVICE_PAUSED,SERVICE_START_PENDING,
                                  SERVICE_PAUSE_PENDING,SERVICE_CONTINUE_PENDING,
                                  SERVICE_STOP_PENDING,SERVICE_STOPPED };

         for(i=0;i<7;i++)
            if (status.dwCurrentState==states[i])
               break;
         result=(float)i;
      }
      else
      {
         result=255;    // Unable to retrieve information
      }

      CloseServiceHandle(service);
   }

   CloseServiceHandle(mgr);
   return result;
}


//
// Handler for perf_counter[*] parameter
//

static float H_PerfCounter(char *cmd,char *arg)
{
   HQUERY query;
   HCOUNTER counter;
   PDH_RAW_COUNTER rawData;
   PDH_FMT_COUNTERVALUE value;
   char counterName[MAX_PATH];

   GetParameterInstance(cmd,counterName,MAX_PATH-1);

   if (PdhOpenQuery(NULL,0,&query)!=ERROR_SUCCESS)
   {
      WriteLog("PdhOpenQuery failed\r\n");
      return FAIL;
   }

   if (PdhAddCounter(query,counterName,0,&counter)!=ERROR_SUCCESS)
   {
      WriteLog("PdhAddCounter(%s) failed\r\n",counterName);
      PdhCloseQuery(query);
      return NOTSUPPORTED;
   }

   if (PdhCollectQueryData(query)!=ERROR_SUCCESS)
   {
      WriteLog("PdhCollectQueryData failed\r\n");
      PdhCloseQuery(query);
      return FAIL;
   }

   PdhGetRawCounterValue(counter,NULL,&rawData);
   PdhCalculateCounterFromRawValue(counter,PDH_FMT_DOUBLE,
                                   &rawData,NULL,&value);

   PdhCloseQuery(query);
   return (float)value.doubleValue;
}


//
// Handler for user counters
//

static float H_UserCounter(char *cmd,char *arg)
{
   USER_COUNTER *counter;
   char *ptr1,*ptr2;

   ptr1=strchr(cmd,'{');
   ptr2=strchr(cmd,'}');
   ptr1++;
   *ptr2=0;
   for(counter=userCounterList;counter!=NULL;counter=counter->next)
      if (!strcmp(counter->name,ptr1))
         return counter->lastValue;

   return NOTSUPPORTED;
}


//
// Parameters and handlers
//

static AGENT_COMMAND commands[]=
{
   { "__usercnt{*}",H_UserCounter,NULL,NULL },
   { "cpu_util",H_ProcUtil,NULL,(char *)0x00 },
   { "cpu_util5",H_ProcUtil,NULL,(char *)0x01 },
   { "cpu_util15",H_ProcUtil,NULL,(char *)0x02 },
   { "cpu_util[*]",H_ProcUtil,NULL,(char *)0x80 },
   { "cpu_util5[*]",H_ProcUtil,NULL,(char *)0x81 },
   { "cpu_util15[*]",H_ProcUtil,NULL,(char *)0x82 },
   { "diskfree[*]",H_DiskInfo,NULL,NULL },
   { "disktotal[*]",H_DiskInfo,NULL,NULL },
   { "memory[*]",H_MemoryInfo,NULL,NULL },
   { "perf_counter[*]",H_PerfCounter,NULL,NULL },
   { "ping",H_NumericConstant,NULL,(char *)1 },
   { "proc_cnt[*]",H_ProcCountSpecific,NULL,NULL },
   { "service_state[*]",H_ServiceState,NULL,NULL },
   { "swap[*]",H_MemoryInfo,NULL,NULL },
   { "system[hostname]",NULL,H_HostName,NULL },
   { "system[proccount]",H_ProcCount,NULL,NULL },
   { "system[procload]",H_NumericPtr,NULL,(char *)&statProcLoad },
   { "system[procload5]",H_NumericPtr,NULL,(char *)&statProcLoad5 },
   { "system[procload15]",H_NumericPtr,NULL,(char *)&statProcLoad15 },
   { "version[zabbix_agent]",NULL,H_StringConstant,AGENT_VERSION },
   { "",NULL,NULL,NULL }
};


//
// Command processing function
//

void ProcessCommand(char *received_cmd,char *result)
{
   int i;
   float fResult=NOTSUPPORTED;
   char *strResult=NULL,cmd[MAX_ZABBIX_CMD_LEN];

   ExpandAlias(received_cmd,cmd);

   // Find match for command
   for(i=0;;i++)
   {
      if (commands[i].name[0]==0)
         break;

      if (MatchString(commands[i].name,cmd))
      {
         if (commands[i].handler_float!=NULL)
         {
            fResult=commands[i].handler_float(cmd,commands[i].arg);
         }
         else
         {
            if (commands[i].handler_string!=NULL)
               strResult=commands[i].handler_string(cmd,commands[i].arg);
         }
         break;
      }
   }

   if (strResult==NULL)
   {
      sprintf(result,"%f",fResult);
   }
   else
   {
      strncpy(result,strResult,MAX_STRING_LEN-1);
      strcat(result,"\n");
      free(strResult);
   }
}