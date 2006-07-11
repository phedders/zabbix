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
	include "include/config.inc.php";
	include "include/forms.inc.php";
	$page["title"] = "S_HOSTS";
	$page["file"] = "hosts.php";
	show_header($page["title"]);
	insert_confirm_javascript();
?>
<?php
	if(!check_anyright("Host","U"))
	{
		show_table_header("<font color=\"AA0000\">".S_NO_PERMISSIONS."</font>");
		show_page_footer();
		exit;
	}

	$_REQUEST["config"] = get_request("config",get_profile("web.hosts.config",0));

	update_profile("web.hosts.config",$_REQUEST["config"]);
	update_profile("web.menu.config.last",$page["file"]);
?>
<?php
//		VAR			TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
	$fields=array(
		"config"=>	array(T_ZBX_INT, O_OPT,	NULL,	IN("0,1,2"),	NULL),

		"hosts"=>	array(T_ZBX_INT, O_OPT,	P_SYS,	DB_ID, NULL),
		"groups"=>	array(T_ZBX_INT, O_OPT,	P_SYS,	DB_ID, NULL),
/* host */
		"hostid"=>	array(T_ZBX_INT, O_OPT,  P_SYS,  DB_ID,		'{config}==0&&{form}=="update"'),
		"host"=>	array(T_ZBX_STR, O_OPT,  NULL,   NOT_EMPTY,	'{config}==0&&isset({save})'),
		"useip"=>	array(T_ZBX_STR, O_OPT, NULL,   NULL,	NULL),
		"ip"=>		array(T_ZBX_STR, O_OPT, NULL,   NULL,			'isset({useip})'),
		"port"=>	array(T_ZBX_INT, O_OPT,  NULL,   BETWEEN(0,65535),'{config}==0&&isset({save})'),
		"status"=>	array(T_ZBX_INT, O_OPT,  NULL,   IN("0,1,3"),	'{config}==0&&isset({save})'),

		"newgroup"=>		array(T_ZBX_STR, O_OPT, NULL,   NULL,	NULL),
		"host_templateid"=>	array(T_ZBX_INT, O_OPT,	NULL,	DB_ID,	NULL),

		"useprofile"=>	array(T_ZBX_STR, O_OPT, NULL,   NULL,	NULL),
		"devicetype"=>	array(T_ZBX_STR, O_OPT, NULL,   NULL,	'isset({useprofile})'),
		"name"=>	array(T_ZBX_STR, O_OPT, NULL,   NULL,	'isset({useprofile})'),
		"os"=>		array(T_ZBX_STR, O_OPT, NULL,   NULL,	'isset({useprofile})'),
		"serialno"=>	array(T_ZBX_STR, O_OPT, NULL,   NULL,	'isset({useprofile})'),
		"tag"=>		array(T_ZBX_STR, O_OPT, NULL,   NULL,	'isset({useprofile})'),
		"macaddress"=>	array(T_ZBX_STR, O_OPT, NULL,   NULL,	'isset({useprofile})'),
		"hardware"=>	array(T_ZBX_STR, O_OPT, NULL,   NULL,	'isset({useprofile})'),
		"software"=>	array(T_ZBX_STR, O_OPT, NULL,   NULL,	'isset({useprofile})'),
		"contact"=>	array(T_ZBX_STR, O_OPT, NULL,   NULL,	'isset({useprofile})'),
		"location"=>	array(T_ZBX_STR, O_OPT, NULL,   NULL,	'isset({useprofile})'),
		"notes"=>	array(T_ZBX_STR, O_OPT, NULL,   NULL,	'isset({useprofile})'),
/* group */
		"groupid"=>	array(T_ZBX_INT, O_OPT,	P_SYS,	DB_ID,		'{config}==1&&{form}=="update"'),
		"gname"=>	array(T_ZBX_STR, O_NO,	NULL,	NOT_EMPTY,	'{config}==1&&isset({save})'),
/* linkages */
		"hosttemplateid"=>	array(T_ZBX_INT, O_OPT, P_SYS,  DB_ID,	'{config}==3&&{form}=="update"'),
		"templateid"=>	array(T_ZBX_INT, O_OPT, P_SYS,  DB_ID,	NULL),
		"items"=>	array(T_ZBX_INT, O_OPT,	P_SYS,	IN("1,2,4"), NULL),
		"triggers"=>	array(T_ZBX_INT, O_OPT,	P_SYS,	IN("1,2,4"), NULL),
		"graphs"=>	array(T_ZBX_INT, O_OPT,	P_SYS,	IN("1,2,4"), NULL),

/* actions */
		"activate"=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT, NULL, NULL),	
		"disable"=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT, NULL, NULL),	

//		"register"=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"save"=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"delete"=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"cancel"=>	array(T_ZBX_STR, O_OPT, P_SYS,	NULL,	NULL),
/* other */
		"form"=>	array(T_ZBX_STR, O_OPT, P_SYS,	NULL,	NULL),
		"form_refresh"=>array(T_ZBX_STR, O_OPT, NULL,	NULL,	NULL)
	);

	check_fields($fields);
?>
<?php

/************ ACTIONS FOR HOSTS ****************/
/* SAVE HOST */
	if($_REQUEST["config"]==0&&isset($_REQUEST["save"]))
	{
		$useip = get_request("useip","no");

		$groups=get_request("groups",array());

		if(isset($_REQUEST["hostid"]))
		{
			$result=update_host(
				$_REQUEST["hostid"],$_REQUEST["host"],$_REQUEST["port"],$_REQUEST["status"],
				$useip,$_REQUEST["ip"],$_REQUEST["newgroup"],$groups);

			$msg_ok 	= S_HOST_UPDATED;
			$msg_fail 	= S_CANNOT_UPDATE_HOST;
			$audit_action 	= AUDIT_ACTION_UPDATE;

			$hostid=$_REQUEST["hostid"];
		} else {
			$result=add_host(
				$_REQUEST["host"],$_REQUEST["port"],$_REQUEST["status"],$useip,
				$_REQUEST["ip"],$_REQUEST["host_templateid"],$_REQUEST["newgroup"],$groups);

			$msg_ok 	= S_HOST_ADDED;
			$msg_fail 	= S_CANNOT_ADD_HOST;
			$audit_action 	= AUDIT_ACTION_ADD;

			$db_hosts=DBexecute("select hostid from hosts where host='".$_REQUEST["host"]."'");
			if(DBnum_rows($db_hosts)==0){
				$result=FALSE;
				$hostid=0;
			} else {
				$db_host = DBfetch($db_hosts);
				$hostid=$db_host["hostid"];
			}
		}

		if($result){
			$useprofile = get_request("useprofile","no");
			$db_profiles = DBselect("select * from hosts_profiles where hostid=".$hostid);
			if($useprofile=="yes"){
				if(DBnum_rows($db_profiles)==0)
				{
					$result=add_host_profile($hostid,
						$_REQUEST["devicetype"],$_REQUEST["name"],$_REQUEST["os"],
						$_REQUEST["serialno"],$_REQUEST["tag"],$_REQUEST["macaddress"],
						$_REQUEST["hardware"],$_REQUEST["software"],$_REQUEST["contact"],
						$_REQUEST["location"],$_REQUEST["notes"]);

//					show_messages($result, S_PROFILE_ADDED, S_CANNOT_ADD_PROFILE);
				} else {
					$result=update_host_profile($hostid,
						$_REQUEST["devicetype"],$_REQUEST["name"],$_REQUEST["os"],
						$_REQUEST["serialno"],$_REQUEST["tag"],$_REQUEST["macaddress"],
						$_REQUEST["hardware"],$_REQUEST["software"],$_REQUEST["contact"],
						$_REQUEST["location"],$_REQUEST["notes"]);

//					show_messages($result, S_PROFILE_UPDATED, S_CANNOT_UPDATE_PROFILE);
				}
			} elseif (DBnum_rows($db_profiles)>0){
				$result=delete_host_profile($hostid);
//				show_messages($result, S_PROFILE_DELETED, S_CANNOT_DELETE_PROFILE);
			}
		}

		show_messages($result, $msg_ok, $msg_fail);
		if($result){
			add_audit($audit_action,AUDIT_RESOURCE_HOST,
				"Host [".zbx_ads($_REQUEST["host"])."] IP [".$_REQUEST["ip"]."] ".
				"Status [".$_REQUEST["status"]."]");

			unset($_REQUEST["form"]);
			unset($_REQUEST["hostid"]);
		}
		unset($_REQUEST["save"]);
	}

/* DELETE HOST */ 
	elseif(($_REQUEST["config"]==0)&&isset($_REQUEST["delete"])){
		if(isset($_REQUEST["hostid"])){
			$host=get_host_by_hostid($_REQUEST["hostid"]);
			$result=delete_host($_REQUEST["hostid"]);

			show_messages($result, S_HOST_DELETED, S_CANNOT_DELETE_HOST);
			if($result)
			{
				add_audit(AUDIT_ACTION_DELETE,AUDIT_RESOURCE_HOST,
				"Host [".zbx_ads($host["host"])."]");

				unset($_REQUEST["form"]);
				unset($_REQUEST["hostid"]);
			}
		} else {
/* group operations */
			$result = 0;
			$hosts = get_request("hosts",array());
			$db_hosts=DBselect("select hostid from hosts");
			while($db_host=DBfetch($db_hosts))
			{
				if(!in_array($db_host["hostid"],$hosts)) continue;
				if(!delete_host($db_host["hostid"]))	continue;
				$result = 1;

				$host=get_host_by_hostid($db_host["hostid"]);
				add_audit(AUDIT_ACTION_DELETE,AUDIT_RESOURCE_HOST,
					"Host [".zbx_ads($host["host"])."]");
			}
			show_messages($result, S_HOST_DELETED, NULL);
		}
		unset($_REQUEST["delete"]);
	}
/* ACTIVATE / DISABLE HOSTS */
	elseif($_REQUEST["config"]==0&&(isset($_REQUEST["activate"])||isset($_REQUEST["disable"]))){
		$result = 0;
		$status = isset($_REQUEST["activate"]) ? HOST_STATUS_MONITORED : HOST_STATUS_NOT_MONITORED;
		$hosts = get_request("hosts",array());

		$db_hosts=DBselect("select hostid from hosts");
		while($db_host=DBfetch($db_hosts))
		{
			if(!in_array($db_host["hostid"],$hosts)) continue;
			$host=get_host_by_hostid($db_host["hostid"]);
			$res=update_host_status($db_host["hostid"],$status);

			$result = 1;
			add_audit(AUDIT_ACTION_UPDATE,AUDIT_RESOURCE_HOST,
				"Old status [".$host["status"]."] "."New status [".$status."]");
		}
		show_messages($result, S_HOST_STATUS_UPDATED, NULL);
		unset($_REQUEST["activate"]);
	}

	elseif($_REQUEST["config"]==0&&isset($_REQUEST["chstatus"])&&isset($_REQUEST["hostid"]))
	{
		$host=get_host_by_hostid($_REQUEST["hostid"]);
		$result=update_host_status($_REQUEST["hostid"],$_REQUEST["chstatus"]);
		show_messages($result,S_HOST_STATUS_UPDATED,S_CANNOT_UPDATE_HOST_STATUS);
		if($result)
		{
			add_audit(AUDIT_ACTION_UPDATE,AUDIT_RESOURCE_HOST,
				"Old status [".$host["status"]."] New status [".$_REQUEST["chstatus"]."]");
		}
		unset($_REQUEST["chstatus"]);
		unset($_REQUEST["hostid"]);
	}

/****** ACTIONS FOR GROUPS **********/
	if($_REQUEST["config"]==1&&isset($_REQUEST["save"]))
	{
		$hosts = get_request("hosts",array());
		if(isset($_REQUEST["groupid"]))
		{
			$result = update_host_group($_REQUEST["groupid"], $_REQUEST["gname"], $hosts);
			$msg_ok		= S_GROUP_UPDATED;
			$msg_fail	= S_CANNOT_UPDATE_GROUP;
		} else {
			$result = add_host_group($_REQUEST["gname"], $hosts);
			$msg_ok		= S_GROUP_ADDED;
			$msg_fail	= S_CANNOT_ADD_GROUP;
		}
		show_messages($result, $msg_ok, $msg_fail);
		if($result){
			unset($_REQUEST["form"]);
		}
		unset($_REQUEST["save"]);
	}
	if($_REQUEST["config"]==1&&isset($_REQUEST["delete"]))
	{
		if(isset($_REQUEST["groupid"])){
			$result=delete_host_group($_REQUEST["groupid"]);
			if($result){
//				$group = get_group_by_groupid($_REQUEST["groupid"]);
//				add_audit(AUDIT_ACTION_DELETE,AUDIT_RESOURCE_GROUP,
//					"Group [".$group["name"]."]");

				unset($_REQUEST["form"]);
			}
			show_messages($result, S_GROUP_DELETED, S_CANNOT_DELETE_GROUP);
			unset($_REQUEST["groupid"]);
		} else {
/* group operations */
			$result = 0;
			$groups = get_request("groups",array());

			$db_groups=DBselect("select groupid, name from groups");
			while($db_group=DBfetch($db_groups))
			{
				if(!in_array($db_group["groupid"],$groups)) continue;
				if(!delete_host_group($db_group["groupid"])) continue

				$result = 1;

//				add_audit(AUDIT_ACTION_DELETE,AUDIT_RESOURCE_GROUP,
//					"Group [".$db_group["name"]."]");

			}
			show_messages($result, S_GROUP_DELETED, NULL);
		}
		unset($_REQUEST["delete"]);
	}

	if($_REQUEST["config"]==1&&(isset($_REQUEST["activate"])||isset($_REQUEST["disable"]))){
		$result = 0;
		$status = isset($_REQUEST["activate"]) ? HOST_STATUS_MONITORED : HOST_STATUS_NOT_MONITORED;
		$groups = get_request("groups",array());

		$db_hosts=DBselect("select h.hostid, hg.groupid from hosts_groups hg, hosts h".
			" where h.hostid=hg.hostid and h.status<>".HOST_STATUS_DELETED);
		while($db_host=DBfetch($db_hosts))
		{
			if(!in_array($db_host["groupid"],$groups)) continue;
			$host=get_host_by_hostid($db_host["hostid"]);
			if(!update_host_status($db_host["hostid"],$status))	continue;

			$result = 1;
			add_audit(AUDIT_ACTION_UPDATE,AUDIT_RESOURCE_HOST,
				"Old status [".$host["status"]."] "."New status [".$status."]");
		}
		show_messages($result, S_HOST_STATUS_UPDATED, NULL);
		unset($_REQUEST["activate"]);
	}
/****** ACTIONS FOR LINKS **********/
	elseif($_REQUEST["config"]==2&&isset($_REQUEST["save"]))
	{
		$items = 0;
		$arr_items = get_request("items",array());
		foreach($arr_items as $el) $items |= $el;

		$triggers=0;
		$arr_triggers= get_request("triggers",array());
		foreach($arr_triggers as $el) $triggers |= $el;

		$graphs=0;
		$arr_graphs = get_request("graphs",array());
		foreach($arr_graphs as $el) $graphs |= $el;

		if(isset($_REQUEST["hosttemplateid"]))
		{	
			$result=update_template_linkage($_REQUEST["hosttemplateid"],
				$_REQUEST["hostid"],$_REQUEST["templateid"],$items,$triggers,$graphs);
			show_messages($result, S_TEMPLATE_LINKAGE_UPDATED, S_CANNOT_UPDATE_TEMPLATE_LINKAGE);
		}
		else
		{	
			$result=add_template_linkage(
				$_REQUEST["hostid"],$_REQUEST["templateid"],$items,$triggers,$graphs);

			show_messages($result, S_TEMPLATE_LINKAGE_ADDED, S_CANNOT_ADD_TEMPLATE_LINKAGE);
		}
		if($result){
			unset($_REQUEST["form"]);
		}
	}
	elseif($_REQUEST["config"]==2&&isset($_REQUEST["delete"])&&isset($_REQUEST["hosttemplateid"]))
	{
		$result=delete_template_linkage($_REQUEST["hosttemplateid"]);
		show_messages($result, S_TEMPLATE_LINKAGE_DELETED, S_CANNOT_DELETE_TEMPLATE_LINKAGE);
		unset($_REQUEST["hosttemplateid"]);
		if($result){
			unset($_REQUEST["form"]);
		}
	}	

/************* OLD ACTIONS ***************/
/*
	elseif(isset($_REQUEST["register"]))
	{
		if($_REQUEST["register"]=="add items from template")
		{
			if(isset($_REQUEST["host_templateid"])&&($_REQUEST["host_templateid"]!=0))
			{
				$result=sync_items_with_template_host($_REQUEST["hostid"],$_REQUEST["host_templateid"]);
				show_messages(TRUE,S_ITEMS_ADDED,S_CANNOT_ADD_ITEMS);
			}
			else
			{
				show_messages(FALSE,"",S_SELECT_HOST_TEMPLATE_FIRST);
			}
		}	
	}
*/
?>

<?php
	$frmForm = new CForm();

	$cmbConf = new CComboBox("config",$_REQUEST["config"],"submit()");
	$cmbConf->AddItem(0,S_HOSTS);
	$cmbConf->AddItem(1,S_HOST_GROUPS);
	$cmbConf->AddItem(2,S_HOSTS_TEMPLATES_LINKAGE);

	switch($_REQUEST["config"]){
		case 0:
			$frmForm->AddVar("groupid",get_request("groupid",0));
			$btn = new CButton("form",S_CREATE_HOST);
			break;
		case 1: 
			$btn = new CButton("form",S_CREATE_GROUP);
			break;
		case 2: 
			$frmForm->AddVar("groupid",get_request("groupid",0));
			$frmForm->AddVar("hostid",get_request("hostid",0));
			$btn = new CButton("form",S_CREATE_LINKAGE);
			break;
	}

	$frmForm->AddItem($cmbConf);
	if(isset($btn)){
		$frmForm->AddItem(SPACE."|".SPACE);
		$frmForm->AddItem($btn);
	}
	show_header2(S_CONFIGURATION_OF_HOSTS_AND_HOST_GROUPS, $frmForm);
	echo BR;
?>


<?php
	if($_REQUEST["config"]==2)
	{
		if(isset($_REQUEST["groupid"])&&($_REQUEST["groupid"]==0))
		{
			unset($_REQUEST["groupid"]);
		}
		if(isset($_REQUEST["hostid"])&&($_REQUEST["hostid"]==0))
		{
			unset($_REQUEST["hostid"]);
		}

/* filter panel */
		$form = new CForm();
		$form->AddVar("config",$_REQUEST["config"]);

		$_REQUEST["groupid"] = get_request("groupid",0);
		$cmbGroup = new CComboBox("groupid",$_REQUEST["groupid"],"submit();");
		$cmbGroup->AddItem(0,S_ALL_SMALL);
		$result=DBselect("select groupid,name from groups order by name");
		while($row=DBfetch($result))
		{
	// Check if at least one host with read permission exists for this group
			$result2=DBselect("select h.hostid,h.host from hosts h,hosts_groups hg".
				" where hg.groupid=".$row["groupid"]." and hg.hostid=h.hostid and".
				" h.status<>".HOST_STATUS_DELETED." group by h.hostid,h.host order by h.host");
			while($row2=DBfetch($result2))
			{
				if(!check_right("Host","U",$row2["hostid"]))	continue;
				$cmbGroup->AddItem($row["groupid"],$row["name"]);
				break;
			}
		}
		$form->AddItem(S_GROUP.SPACE);
		$form->AddItem($cmbGroup);

		if(isset($_REQUEST["groupid"]) && $_REQUEST["groupid"]>0)
		{
			$sql="select h.hostid,h.host from hosts h,hosts_groups hg".
				" where hg.groupid=".$_REQUEST["groupid"]." and hg.hostid=h.hostid and".
				" h.status<>".HOST_STATUS_DELETED." group by h.hostid,h.host order by h.host";
		}
		else
		{
			$sql="select h.hostid,h.host from hosts h where h.status<>".HOST_STATUS_DELETED.
				" group by h.hostid,h.host order by h.host";
		}

		$result=DBselect($sql);

		$_REQUEST["hostid"] = get_request("hostid",0);
		$cmbHosts = new CComboBox("hostid",$_REQUEST["hostid"],"submit();");

		$correct_hostid='no';
		$first_hostid = -1;
		while($row=DBfetch($result))
		{
			if(!check_right("Host","U",$row["hostid"]))	continue;
			$cmbHosts->AddItem($row["hostid"],$row["host"]);

			if($_REQUEST["hostid"]!=0){
				if($_REQUEST["hostid"]==$row["hostid"])
					$correct_hostid = 'ok';
			}
			if($first_hostid <= 0)
				$first_hostid = $row["hostid"];
		}
		if($correct_hostid!='ok')
			$_REQUEST["hostid"] = $first_hostid;

		$form->AddItem(SPACE.S_HOST.SPACE);
		$form->AddItem($cmbHosts);
		
		show_header2(S_CONFIGURATION_OF_ITEMS_BIG, $form);

/* table */
		echo BR;
		if(isset($_REQUEST["form"]))
		{
			insert_template_form();
		} else {
			$table = new CTableInfo(S_NO_LINKAGES_DEFINED);
			$table->setHeader(array(S_HOST,S_TEMPLATE,S_ITEMS,S_TRIGGERS,S_GRAPHS));

			$result=DBselect("select * from hosts_templates where hostid=".$_REQUEST["hostid"]);
			while($row=DBfetch($result))
			{
				$host=get_host_by_hostid($row["hostid"]);
				$template=get_host_by_hostid($row["templateid"]);

				$table->addRow(array(
					new CLink(
						$host["host"],
						"hosts.php?form=update".url_param("config").
						"&hostid=".$row["hostid"].
						"&hosttemplateid=".$row["hosttemplateid"],
						'action'),
					new CLink(
						$template["host"],
						"hosts.php?form=update".url_param("config").
						"&hostid=".$row["hostid"].
						"&hosttemplateid=".$row["hosttemplateid"],
						'action'),
					get_template_permission_str($row["items"]),
					get_template_permission_str($row["triggers"]),
					get_template_permission_str($row["graphs"])
					));
			}
			$table->show();
		}
	}
?>
<?php
	if($_REQUEST["config"]==1)
	{
		if(isset($_REQUEST["form"]))
		{
			insert_hostgroups_form(get_request("groupid",NULL));
		} else {
			show_table_header(S_HOST_GROUPS_BIG);

			$form = new CForm('hosts.php');
			$form->SetName('groups');
			$form->AddVar("config",get_request("config",0));

			$table = new CTableInfo(S_NO_HOST_GROUPS_DEFINED);

			$table->setHeader(array(
				array(	new CCheckBox("all_groups",NULL,NULL,
						"CheckAll('".$form->GetName()."','all_groups');"),
					S_NAME),
				S_MEMBERS));

			$db_groups=DBselect("select groupid,name from groups order by name");
			while($db_group=DBfetch($db_groups))
			{
				$db_hosts = DBselect("select distinct h.host, h.status".
					" from hosts h, hosts_groups hg".
					" where h.hostid=hg.hostid and hg.groupid=".$db_group["groupid"].
					" and h.status not in (".HOST_STATUS_DELETED.") order by host");

				$hosts = array("");
				if($db_host=DBfetch($db_hosts)){
					$style = $db_host["status"]==HOST_STATUS_MONITORED ? NULL: "on";
					array_push($hosts,new CSpan($db_host["host"],$style));
				}
				while($db_host=DBfetch($db_hosts)){
					$style = $db_host["status"]==HOST_STATUS_MONITORED ? NULL: "on";
					array_push($hosts,", ",new CSpan($db_host["host"],$style));
				}

				$table->AddRow(array(
					array(
						new CCheckBox("groups[]",NULL,NULL,NULL,$db_group["groupid"]),
						new CLink(
							$db_group["name"],
							"hosts.php?form=update&groupid=".$db_group["groupid"].
							url_param("config"),'action')
					),
					$hosts
					));
			}
			$footerButtons = array();
			array_push($footerButtons, new CButton('activate','Activate selected',
				"return Confirm('".S_ACTIVATE_SELECTED_HOSTS_Q."');"));
			array_push($footerButtons, SPACE);
			array_push($footerButtons, new CButton('disable','Disable selected',
				"return Confirm('".S_DISABLE_SELECTED_HOSTS_Q."');"));
			array_push($footerButtons, SPACE);
			array_push($footerButtons, new CButton('delete','Delete selected',
				"return Confirm('".S_DELETE_SELECTED_GROUPS_Q."');"));
			$table->SetFooter(new CCol($footerButtons),'table_footer');

			$form->AddItem($table);
			$form->Show();
		}
	}
?>
<?php
	if($_REQUEST["config"]==0)
	{
		if(isset($_REQUEST["form"]))
		{
			insert_host_form();
		} else {
			$cmbGroups = new CComboBox("groupid",get_request("groupid",0),"submit()");
			$cmbGroups->AddItem(0,S_ALL_SMALL);
			$result=DBselect("select groupid,name from groups order by name");
			while($row=DBfetch($result))
			{
// Check if at least one host with read permission exists for this group
				$result2=DBselect("select h.hostid,h.host from hosts h,items i,hosts_groups hg".
					" where h.hostid=i.hostid and hg.groupid=".$row["groupid"].
					" and hg.hostid=h.hostid and h.status not in (".HOST_STATUS_DELETED.")".
					" group by h.hostid,h.host order by h.host");

				$right='no';
				while($row2=DBfetch($result2))
				{
					if(!check_right("Host","R",$row2["hostid"]))	continue;
					$right='yes'; break;
				}
				if($right=='no')	continue;
				$cmbGroups->AddItem($row["groupid"],$row["name"]);
			}
			$frmForm = new CForm("hosts.php");
			$frmForm->AddVar("config",$_REQUEST["config"]);
			$frmForm->AddItem(S_GROUP.SPACE);
			$frmForm->AddItem($cmbGroups);
			show_header2(S_HOSTS_BIG, $frmForm);

	/* table HOSTS */
			if(isset($_REQUEST["groupid"]) && $_REQUEST["groupid"]==0) unset($_REQUEST["groupid"]);

			$form = new CForm('hosts.php');
			$form->SetName('hosts');
			$form->AddVar("config",get_request("config",0));

			$table = new CTableInfo(S_NO_HOSTS_DEFINED);
			$table->setHeader(array(
				array(	new CCheckBox("all_hosts",NULL,NULL,
						"CheckAll('".$form->GetName()."','all_hosts');"),
					S_HOST),
				S_IP,S_PORT,S_STATUS,S_AVAILABILITY,S_ERROR,S_SHOW));
		
			$sql="select h.* from";
			if(isset($_REQUEST["groupid"]))
			{
				$sql .= " hosts h,hosts_groups hg where";
				$sql .= " hg.groupid=".$_REQUEST["groupid"]." and hg.hostid=h.hostid and";
			} else  $sql .= " hosts h where";
			$sql .= " h.status<>".HOST_STATUS_DELETED." order by h.host";
			$result=DBselect($sql);
		
			while($row=DBfetch($result))
			{
		        	if(!check_right("Host","R",$row["hostid"]))
				{
					continue;
				}

				$host=new CCol(array(
					new CCheckBox("hosts[]",NULL,NULL,NULL,$row["hostid"]),
					new CLink($row["host"],"hosts.php?form=update&hostid=".
						$row["hostid"].url_param("groupid").url_param("config"), 'action')
					));
		
				$ip=$row["useip"]==1 ? $row["ip"] : "-";

				if($row["status"] == HOST_STATUS_MONITORED){
					$text = S_MONITORED;
					if(check_right("Host","U",$row["hostid"]))
					{
						$text=new CLink($text,"hosts.php?hosts%5B%5D=".$row["hostid"].
							"&disable=1".url_param("config"),
							"off");
					}
					$status=new CCol($text,"off");
				} else if($row["status"] == HOST_STATUS_NOT_MONITORED) {
					$text = S_NOT_MONITORED;
					if(check_right("Host","U",$row["hostid"]))
					{
						$text=new CLink($text,"hosts.php?hosts%5B%5D=".$row["hostid"].
							"&activate=1".url_param("config"),
							"on");
					}
					$status=new CCol($text,"on");
				} else if($row["status"] == HOST_STATUS_TEMPLATE)
					$status=new CCol(S_TEMPLATE,"unknown");
				else if($row["status"] == HOST_STATUS_DELETED)
					$status=new CCol(S_DELETED,"unknown");
				else
					$status=S_UNKNOWN;
		
				if($row["available"] == HOST_AVAILABLE_TRUE)	
					$available=new CCol(S_AVAILABLE,"off");
				else if($row["available"] == HOST_AVAILABLE_FALSE)
					$available=new CCol(S_NOT_AVAILABLE,"on");
				else if($row["available"] == HOST_AVAILABLE_UNKNOWN)
					$available=new CCol(S_UNKNOWN,"unknown");
		
				if($row["error"] == "")	$error=new CCol("&nbsp;","off");
				else			$error=new CCol($row["error"],"on");

				if(check_right("Host","U",$row["hostid"])) {
					$items=new CLink(S_ITEMS,"items.php?hostid=".$row["hostid"]);
				} else {
					$items=S_CHANGE;
				}

				$table->addRow(array(
					$host,
					$ip,
					$row["port"],
					$status,
					$available,
					$error,
					$items));
			}

			$footerButtons = array();
			array_push($footerButtons, new CButton('activate','Activate selected',
				"return Confirm('".S_ACTIVATE_SELECTED_HOSTS_Q."');"));
			array_push($footerButtons, SPACE);
			array_push($footerButtons, new CButton('disable','Disable selected',
				"return Confirm('".S_DISABLE_SELECTED_HOSTS_Q."');"));
			array_push($footerButtons, SPACE);
			array_push($footerButtons, new CButton('delete','Delete selected',
				"return Confirm('".S_DELETE_SELECTED_HOSTS_Q."');"));
			$table->SetFooter(new CCol($footerButtons),'table_footer');

			$form->AddItem($table);
			$form->Show();

		}
	}
?>
<?php
	show_page_footer();
?>