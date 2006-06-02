<?php
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
?>
<?php
	# Add User definition

	function	add_user($name,$surname,$alias,$passwd,$url,$autologout,$lang,$refresh)
	{
		if(!check_right("User","A",0))
		{
			error("Insufficient permissions");
			return 0;
		}

		if($alias==""){
			error("Incorrect Alias name");
			return 0;
		}

		$sql="select * from users where alias='".zbx_ads($alias)."'";
		$result=DBexecute($sql);
		if(DBnum_rows($result)>0)
		{
			error("User '$alias' already exists");
			return 0;
		}
		
		$passwd=md5($passwd);
		$sql="insert into users (name,surname,alias,passwd,url,autologout,lang,refresh) values ('".zbx_ads($name)."','".zbx_ads($surname)."','".zbx_ads($alias)."','".zbx_ads($passwd)."','".zbx_ads($url)."',$autologout,'".zbx_ads($lang)."',$refresh)";
		return DBexecute($sql);
	}

	# Update User definition

	function	update_user($userid,$name,$surname,$alias,$passwd, $url,$autologout,$lang,$refresh)
	{
		if(!check_right("User","U",$userid))
		{
			error("Insufficient permissions");
			return 0;
		}

		if($alias==""){
			error("incorrect alias name");
			return 0;
		}

		$sql="select * from users where alias='".zbx_ads($alias)."' and userid<>$userid";
		$result=DBexecute($sql);
		if(DBnum_rows($result)>0)
		{
			error("User '$alias' already exists");
			return 0;
		}
		
		if($passwd=="")
		{
			$sql="update users set name='".zbx_ads($name)."',surname='".zbx_ads($surname)."',alias='".zbx_ads($alias)."',url='".zbx_ads($url)."',autologout=$autologout,lang='".zbx_ads($lang)."',refresh=$refresh where userid=$userid";
		}
		else
		{
			$passwd=md5($passwd);
			$sql="update users set name='".zbx_ads($name)."',surname='".zbx_ads($surname)."',alias='".zbx_ads($alias)."',passwd='".zbx_ads($passwd)."',url='".zbx_ads($url)."',autologout=$autologout,lang='".zbx_ads($lang)."',refresh=$refresh where userid=$userid";
		}
		return DBexecute($sql);
	}

	# Update User Profile

	function	update_user_profile($userid,$passwd, $url,$autologout,$lang,$refresh)
	{
		global $USER_DETAILS;

		if($userid!=$USER_DETAILS["userid"])
		{
			error("Insufficient permissions");
			return 0;
		}

		if($passwd=="")
		{
			$sql="update users set url='".zbx_ads($url)."',autologout=$autologout,lang='".zbx_ads($lang)."',refresh=$refresh where userid=$userid";
		}
		else
		{
			$passwd=md5($passwd);
			$sql="update users set passwd='".zbx_ads($passwd)."',url='".zbx_ads($url)."',autologout=$autologout,lang='".zbx_ads($lang)."',refresh=$refresh where userid=$userid";
		}
		return DBexecute($sql);
	}

	# Add permission

	function	add_permission($userid,$right,$permission,$id)
	{
		$sql="insert into rights (userid,name,permission,id) values ($userid,'".zbx_ads($right)."','".zbx_ads($permission)."',$id)";
		return DBexecute($sql);
	}

	function	get_user_by_userid($userid)
	{
		$sql="select * from users where userid=$userid"; 
		$result=DBselect($sql);
		if(DBnum_rows($result) == 1)
		{
			return	DBfetch($result);	
		}
		else
		{
			error("No user with itemid=[$userid]");
		}
		return	$result;
	}

	function	add_user_group($name,$users)
	{
		if(!check_right("Host","A",0))
		{
			error("Insufficient permissions");
			return 0;
		}
		
		if($name==""){
			error("Incorrect group name");
			return 0;
		}

		$sql="select * from usrgrp where name='".zbx_ads($name)."'";
		$result=DBexecute($sql);
		if(DBnum_rows($result)>0)
		{
			error("Group '$name' already exists");
			return 0;
		}

		$sql="insert into usrgrp (name) values ('".zbx_ads($name)."')";
		$result=DBexecute($sql);
		if(!$result)
		{
			return	$result;
		}
		
		$usrgrpid=DBinsert_id($result,"usrgrp","usrgrpid");

		update_user_groups($usrgrpid,$users);

		return $result;
	}

	function	update_user_group($usrgrpid,$name,$users)
	{
		if(!check_right("Host","U",0))
		{
			error("Insufficient permissions");
			return 0;
		}
		
		if($name==""){
			error("Incorrect group name");
			return 0;
		}

		$sql="select * from usrgrp where name='".zbx_ads($name)."' and usrgrpid<>$usrgrpid";
		$result=DBexecute($sql);
		if(DBnum_rows($result)>0)
		{
			error("Group '$name' already exists");
			return 0;
		}

		$sql="update usrgrp set name='".zbx_ads($name)."' where usrgrpid=$usrgrpid";
		$result=DBexecute($sql);
		if(!$result)
		{
			return	$result;
		}
		
		update_user_groups($usrgrpid,$users);

		return $result;
	}

	function	delete_user_group($usrgrpid)
	{
		$sql="delete from users_groups where usrgrpid=$usrgrpid";
		DBexecute($sql);
		$sql="delete from usrgrp where usrgrpid=$usrgrpid";
		return DBexecute($sql);
	}

	function	update_user_groups($usrgrpid,$users)
	{
		$count=count($users);

		$sql="delete from users_groups where usrgrpid=$usrgrpid";
		DBexecute($sql);

		for($i=0;$i<$count;$i++)
		{
			$sql="insert into users_groups (usrgrpid,userid) values ($usrgrpid,".$users[$i].")";
			DBexecute($sql);
		}
	}
?>