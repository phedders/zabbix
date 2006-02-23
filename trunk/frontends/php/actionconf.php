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
	$page["title"]="S_ACTIONS";
	$page["file"]="actionconf.php";
	show_header($page["title"],0,0);
	insert_confirm_javascript();
?>

<?php
	$_REQUEST["actiontype"] = get_request("actiontype",0);

	update_profile("web.menu.config.last",$page["file"]);
//	if(($_REQUEST["triggerid"]!=0)&&!check_right_on_trigger("U",$_REQUEST["triggerid"]))
//	{
//		show_table_header("<font color=\"AA0000\">".S_NO_PERMISSIONS."</font>");
//		show_page_footer();
//		exit;
//	}
?>
<?php
//		VAR			TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
	$fields=array(
		"actiontype"=>	array(T_ZBX_INT, O_OPT,	 NULL,	IN("0,1"),	NULL),

		"actionid"=>	array(T_ZBX_INT, O_OPT,  P_SYS,	DB_ID,		NULL),
		"source"=>	array(T_ZBX_INT, O_OPT,	 NULL,	IN("0"),	'isset({save})'),
		"recipient"=>	array(T_ZBX_INT, O_OPT,	 NULL,	IN("0,1"), 	'isset({save})'),
		"userid"=>	array(T_ZBX_INT, O_OPT,	 NULL,	DB_ID, 		'isset({save})'),
		"delay"=>	array(T_ZBX_INT, O_OPT,	 NULL,	BETWEEN(0,65535),'isset({save})'),
		"subject"=>	array(T_ZBX_STR, O_OPT,  NULL,	NOT_EMPTY,	'isset({save})'),
		"message"=>	array(T_ZBX_STR, O_OPT,  NULL,	NOT_EMPTY,	'isset({save})'),
		"repeat"=>	array(T_ZBX_INT, O_OPT,	 NULL,	IN("0,1"), 	'isset({save})'),
		"status"=>	array(T_ZBX_INT, O_OPT,	 NULL,	IN("0,1"), 	'isset({save})'),

		"maxrepeats"=>	array(T_ZBX_INT, O_OPT,	 NULL,	BETWEEN(0,65535),'{repeat}==1&&isset({save})'),
		"repeatdelay"=>	array(T_ZBX_INT, O_OPT,	 NULL,	BETWEEN(0,65535),'{repeat}==1&&isset({save})'),

		"conditions"=>	array(NULL, O_OPT, NULL, NULL, NULL),
//		"conditions[i][type]"=>		array(T_ZBX_INT, O_OPT,  NULL,	NULL,	NULL),
//		"conditions[i][operator]"=>	array(T_ZBX_INT, O_OPT,  NULL,	NULL,	NULL),
//		"conditions[i][value]"=>	array(NULL, 	 O_OPT,  NULL,	NULL,	NULL),

		"rem_condition"=> array(NULL, O_OPT, NULL, NULL, NULL),
//		"rem_condition[i][type]"=>	array(T_ZBX_INT, O_OPT,  NULL,	NULL, NULL);	
//		"rem_condition[i][operator]"=>	array(T_ZBX_INT, O_OPT,  NULL,	NULL, NULL);
//		"rem_condition[i][value]"=>	array(NULL, 	 O_OPT,  NULL,	NULL, NULL);

		"g_actionid"=>	array(T_ZBX_INT, O_OPT,  NULL,	DB_ID,		NULL),

		"new_condition_type"=>		array(T_ZBX_INT, O_OPT,  NULL,	NULL,	'isset({add_condition})'),
		"new_condition_operator"=>	array(T_ZBX_INT, O_OPT,  NULL,	NULL,	'isset({add_condition})'),
		"new_condition_value"=>		array(NULL, 	 O_OPT,  NULL,	NULL,	'isset({add_condition})'),

/* actions */
		"group_delete"=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"group_enable"=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"group_disable"=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"add_condition"=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"del_condition"=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"save"=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"delete"=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"cancel"=>		array(T_ZBX_STR, O_OPT, P_SYS,	NULL,	NULL),
/* other */
		"form"=>		array(T_ZBX_STR, O_OPT, P_SYS,	NULL,	NULL),
		"form_refresh"=>	array(T_ZBX_INT, O_OPT,	NULL,	NULL,	NULL)
	);

	check_fields($fields);
?>

<?php
	if(isset($_REQUEST["save"]))
	{
		if($_REQUEST["repeat"]==0)
		{
			$_REQUEST["maxrepeats"]=0;
			$_REQUEST["repeatdelay"]=600;
		}

		if(isset($_REQUEST["actionid"]))
		{
			$actionid=$_REQUEST["actionid"];
			$result = update_action($actionid, $_REQUEST['userid'], $_REQUEST["delay"],
				$_REQUEST["subject"], $_REQUEST["message"],$_REQUEST["recipient"],
				$_REQUEST["maxrepeats"],$_REQUEST["repeatdelay"],$_REQUEST["status"]);

			show_messages($result,S_ACTION_UPDATED,S_CANNOT_UPDATE_ACTION);
		} else {
			$actionid=add_action($_REQUEST['userid'], $_REQUEST["delay"], 
				$_REQUEST["subject"],$_REQUEST["message"],$_REQUEST["recipient"],
				$_REQUEST["maxrepeats"],$_REQUEST["repeatdelay"],$_REQUEST["status"]);
			$result=$actionid;

			show_messages($result,S_ACTION_ADDED,S_CANNOT_ADD_ACTION);
		}

//			add_action_to_linked_hosts($actionid);

		if($result) // result - OK
		{

			DBexecute("delete from conditions where actionid=$actionid");
			if(isset($_REQUEST["conditions"])) foreach($_REQUEST["conditions"] as $val)
				add_action_condition($actionid,$val["type"],$val["operator"],$val["value"]);

			if($_REQUEST["recipient"] == RECIPIENT_TYPE_USER)
			{
				$user=get_user_by_userid($_REQUEST["userid"]);
				add_audit(AUDIT_ACTION_ADD,AUDIT_RESOURCE_ACTION,
					"User [".$user["alias"]."] subject [".$_REQUEST["subject"]."]");
			}
			else
			{
				$group=get_usergroup_by_groupid($_REQUEST["userid"]);
				add_audit(AUDIT_ACTION_ADD,AUDIT_RESOURCE_ACTION,
					"User [".$group["name"]."] subject [".$_REQUEST["subject"]."]");
			}
			unset($_REQUEST["form"]);
		}
	}
	elseif(isset($_REQUEST["delete"])&&isset($_REQUEST["actionid"]))
	{
//			delete_action_from_templates($_REQUEST["actionid"]);

		$result=delete_action($_REQUEST["actionid"]);
		show_messages($result,S_ACTION_DELETED,S_CANNOT_DELETE_ACTION);
		if($result)
		{
			add_audit(AUDIT_ACTION_DELETE,AUDIT_RESOURCE_ACTION,
				"Subject [".$_REQUEST["subject"]."]");
			unset($_REQUEST["form"]);
			unset($_REQUEST["actionid"]);
		}
	}
	elseif(isset($_REQUEST["add_condition"]))
	{
		$new_condition = array(
			"type"=>	$_REQUEST["new_condition_type"], 
			"operator"=>	$_REQUEST["new_condition_operator"],
			"value"=>	$_REQUEST["new_condition_value"]);

		$_REQUEST["conditions"] = get_request("conditions",array());
		if(!in_array($new_condition,$_REQUEST["conditions"]))
			array_push($_REQUEST["conditions"],$new_condition);
	}
	elseif(isset($_REQUEST["del_condition"])&&isset($_REQUEST["rem_condition"]))
	{
		$_REQUEST["conditions"] = get_request("conditions",array());
		foreach($_REQUEST["rem_condition"] as $val){
			unset($_REQUEST["conditions"][$val]);
		}
	}
/* GROUP ACTIONS */
	elseif(isset($_REQUEST["group_enable"])&&isset($_REQUEST["g_actionid"]))
	{
		$result=DBselect("select distinct actionid from actions");
		while($row=DBfetch($result))
		{
			if(!in_array($row["actionid"], $_REQUEST["g_actionid"]))	continue;
			$res=update_action_status($row["actionid"],0);
		}
		if(isset($res))
			show_messages(true, S_STATUS_UPDATED, S_CANNOT_UPDATE_STATUS);
	}
	elseif(isset($_REQUEST["group_disable"])&&isset($_REQUEST["g_actionid"]))
	{
		$result=DBselect("select distinct actionid from actions");
		while($row=DBfetch($result))
		{
			if(!in_array($row["actionid"], $_REQUEST["g_actionid"]))	continue;
			$res=update_action_status($row["actionid"],1);
		}
		if(isset($res))
			show_messages(true, S_STATUS_UPDATED, S_CANNOT_UPDATE_STATUS);
	}
	elseif(isset($_REQUEST["group_delete"])&&isset($_REQUEST["g_actionid"]))
	{
		$result=DBselect("select distinct actionid from actions");
		while($row=DBfetch($result))
		{
			if(!in_array($row["actionid"], $_REQUEST["g_actionid"])) continue;
			$del_res = delete_action($row["actionid"]);
		}
		if(isset($del_res))
			show_messages(TRUE, S_ACTIONS_DELETED, S_CANNOT_DELETE_ACTIONS);
	}
?>

<?php
	if(isset($_REQUEST["form"]))
	{
		insert_action_form();
	}
	else
	{
/* table header */
		$form = new CForm();

		$cmbType = new CComboBox("actiontype",$_REQUEST["actiontype"],"submit()");
		$cmbType->AddItem(0,S_SEND_MESSAGE);
		$cmbType->AddItem(1,S_REMOTE_COMMAND,NULL,'no');
		$form->AddItem($cmbType);

		$form->AddItem(SPACE."|".SPACE);
		$form->AddItem(new CButton("form",S_CREATE_ACTION));

		show_header2(S_ACTIONS, $form);

/* table */
		$form = new CForm();
		$form->SetName('actions');

		$tblActions = new CTableInfo(S_NO_ACTIONS_DEFINED);
		$tblActions->SetHeader(array(
			array(	new CCheckBox("all_items",NULL,NULL,
					"CheckAll('".$form->GetName()."','all_items');"),
				S_SOURCE
			),
			S_CONDITIONS,S_SEND_MESSAGE_TO,
			S_DELAY,S_SUBJECT,S_REPEATS,S_STATUS));

		if(isset($_REQUEST["actiontype"])&&($_REQUEST["actiontype"]==1))
		{
			$sql="select * from actions where actiontype=1 order by actiontype, source";
		}
		else
		{
			$sql="select * from actions where actiontype=0 order by actiontype, source";
		}
		$result=DBselect($sql);
		while($row=DBfetch($result))
		{
			$conditions="";
			$result2=DBselect("select * from conditions where actionid=".$row["actionid"].
				" order by conditiontype");
			while($condition=DBfetch($result2))
			{
				$conditions=$conditions.get_condition_desc($condition["conditiontype"],
					$condition["operator"],$condition["value"]).BR;
			}
	
			if($row["recipient"] == RECIPIENT_TYPE_USER)
			{
				$user=get_user_by_userid($row["userid"]);
				$recipient=$user["alias"];
			}
			else
			{
				$groupd=get_usergroup_by_groupid($row["userid"]);
				$recipient=$groupd["name"];
			}

			if($row["status"] == ACTION_STATUS_DISABLED)
			{
				$status= new CLink(S_DISABLED,
					"actionconf.php?group_enable=1&g_actionid%5B%5D=".$row["actionid"],
					'disabled');
			}
			else if($row["status"] == ACTION_STATUS_ENABLED)
			{
				$status= new CLink(S_ENABLED,
					"actionconf.php?group_disable=1&g_actionid%5B%5D=".$row["actionid"],
					'enabled');
			}

			$tblActions->AddRow(array(
				array(
					new CCheckBox(
						"g_actionid[]",	/* name */
						NULL,			/* checked */
						NULL,			/* caption */
						NULL,			/* action */
						$row["actionid"]),	/* value */
					SPACE,
					new CLink(
						get_source_description($row["source"]),
						"actionconf.php?form=update&actionid=".$row['actionid'],'action'),
					),
				$conditions,
				$recipient,
				htmlspecialchars($row["delay"]),
				htmlspecialchars($row["subject"]),
				$row["maxrepeats"] == 0 ? S_NO_REPEATS : $row["maxrepeats"],
				$status
				));	
		}
		$footerButtons = array();
		array_push($footerButtons, new CButton('group_enable','enable selected',
			"return Confirm('Enable selected actions?');"));
		array_push($footerButtons, SPACE);
		array_push($footerButtons, new CButton('group_disable','disable selected',
			"return Confirm('Disable selected actions?');"));
		array_push($footerButtons, SPACE);
		array_push($footerButtons, new CButton('group_delete','delete selected',
			"return Confirm('Delete selected action?');"));
		$tblActions->SetFooter(new CCol($footerButtons),'table_footer');

		$form->AddItem($tblActions);
		$form->Show();
	}

	show_page_footer();
?>
