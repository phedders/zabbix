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

	$page["title"] = "S_CONFIGURATION_OF_ZABBIX";
	$page["file"] = "config.php";

	show_header($page["title"],0,0);
	insert_confirm_javascript();
?>

<?php
        if(!check_anyright("Configuration of Zabbix","U"))
        {
                show_table_header("<font color=\"AA0000\">".S_NO_PERMISSIONS."</font
>");
                show_page_footer();
                exit;
        }
?>

<?php
	update_profile("web.menu.config.last",$page["file"]);
?>

<?php
	$fields=array(
//		VAR			TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION

		"config"=>		array(T_ZBX_INT, O_OPT,	NULL,	IN("0,1,3,4,5,6"),	NULL),

// other form
		"alert_history"=>	array(T_ZBX_INT, O_NO,	NULL,	BETWEEN(0,65535),
						'in_array({config},array(0,5))&&({save}=="Save")'),
		"alarm_history"=>	array(T_ZBX_INT, O_NO,	NULL,	BETWEEN(0,65535),
						'in_array({config},array(0,5))&&({save}=="Save")'),
		"refresh_unsupported"=>	array(T_ZBX_INT, O_NO,	NULL,	BETWEEN(0,65535),
						'in_array({config},array(0,5))&&({save}=="Save")'),

// media form
		"mediatypeid"=>		array(T_ZBX_INT, O_NO,	P_SYS,	BETWEEN(0,65535),
						'{config}==1&&{form}=="update"'),
		"type"=>		array(T_ZBX_INT, O_OPT,	NULL,	IN("0,1"),
						'({config}==1)&&(isset({save}))'),
		"description"=>		array(T_ZBX_STR, O_OPT,	NULL,	NOT_EMPTY,
						'({config}==1)&&(isset({save}))'),
		"smtp_server"=>		array(T_ZBX_STR, O_OPT,	NULL,	NOT_EMPTY,
						'({config}==1)&&({type}==0)'),
		"smtp_helo"=>		array(T_ZBX_STR, O_OPT,	NULL,	NOT_EMPTY,
						'({config}==1)&&({type}==0)'),
		"smtp_email"=>		array(T_ZBX_STR, O_OPT,	NULL,	NOT_EMPTY,
						'({config}==1)&&({type}==0)'),
		"exec_path"=>		array(T_ZBX_STR, O_OPT,	NULL,	NOT_EMPTY,
						'({config}==1)&&({type}==1)&&isset({save})'),

// image form
		"imageid"=>		array(T_ZBX_INT, O_NO,	P_SYS,	BETWEEN(0,65535),
						'{config}==3&&{form}=="update"'),
		"MAX_FILE_SIZE"=>	array(T_ZBX_INT, O_NO,	NULL,	BETWEEN(0,2097152),
						'{config}==3&&isset({save})'),
		"name"=>		array(T_ZBX_STR, O_NO,	NULL,	NOT_EMPTY,
						'{config}==3&&isset({save})'),
		"imagetype"=>		array(T_ZBX_INT, O_OPT,	NULL,	IN("1,2"),
						'({config}==3)&&(isset({save}))'),
//value mapping
		"valuemapid"=>		array(T_ZBX_INT, O_NO,	P_SYS,	DB_ID,	'{config}==6&&{form}=="update"'),
		"mapname"=>		array(T_ZBX_STR, O_OPT,	NULL,	NOT_EMPTY, '{config}==6&&isset({save})'),
		"valuemap"=>		array(T_ZBX_STR, O_OPT, NULL,	NULL, 	NULL),
		"rem_value"=>		array(T_ZBX_INT, O_OPT, NULL,	BETWEEN(0,65535), NULL),
		"add_value"=>		array(T_ZBX_STR, O_OPT, NULL,	NOT_EMPTY, 'isset({add_map})'),
		"add_newvalue"=>	array(T_ZBX_STR, O_OPT, NULL,	NOT_EMPTY, 'isset({add_map})'),

// autoregistration form
		"autoregid"=>		array(T_ZBX_INT, O_NO,	P_SYS,	BETWEEN(0,65535),
						'{config}==4&&{form}=="update"'),
		"pattern"=>		array(T_ZBX_STR, O_OPT,	NULL,	NOT_EMPTY,
						'{config}==4&&isset({save})'),
		"hostid"=>		array(T_ZBX_INT, O_NO,	NULL,	BETWEEN(1,65535),
						'{config}==4&&isset({save})'),
		"priority"=>		array(T_ZBX_INT, O_NO,	NULL,	BETWEEN(0,65535),
						'{config}==4&&isset({save})'),
/* actions */
		"add_map"=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"del_map"=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"save"=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"delete"=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		"cancel"=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
/* other */
		"form"=>		array(T_ZBX_STR, O_OPT, P_SYS,	NULL,	NULL),
		"form_refresh"=>	array(T_ZBX_INT, O_OPT,	NULL,	NULL,	NULL)
	);
?>

<?php
	$_REQUEST["config"]=get_request("config",get_profile("web.config.config",0));

	check_fields($fields);

	update_profile("web.config.config",$_REQUEST["config"]);

	$result = 0;
	if($_REQUEST["config"]==1)
	{



/* MEDIATYPE ACTIONS */
		if(isset($_REQUEST["save"]))
		{
			if(isset($_REQUEST["mediatypeid"]))
			{
	/* UPDATE */
				$action = AUDIT_ACTION_UPDATE;
				$result=update_mediatype($_REQUEST["mediatypeid"],
					$_REQUEST["type"],$_REQUEST["description"],$_REQUEST["smtp_server"],
					$_REQUEST["smtp_helo"],$_REQUEST["smtp_email"],$_REQUEST["exec_path"]);

				show_messages($result, S_MEDIA_TYPE_UPDATED, S_MEDIA_TYPE_WAS_NOT_UPDATED);
			}
			else
			{
	/* ADD */
				$action = AUDIT_ACTION_ADD;
				$result=add_mediatype(
					$_REQUEST["type"],$_REQUEST["description"],$_REQUEST["smtp_server"],
					$_REQUEST["smtp_helo"],$_REQUEST["smtp_email"],$_REQUEST["exec_path"]);

				show_messages($result, S_ADDED_NEW_MEDIA_TYPE, S_NEW_MEDIA_TYPE_WAS_NOT_ADDED);
			}
			if($result)
			{
				add_audit($action,AUDIT_RESOURCE_MEDIA_TYPE,
					"Media type [".$_REQUEST["description"]."]");

				unset($_REQUEST["form"]);
			}
		} elseif(isset($_REQUEST["delete"])&&isset($_REQUEST["mediatypeid"])) {
	/* DELETE */
			$mediatype=get_mediatype_by_mediatypeid($_REQUEST["mediatypeid"]);
			$result=delete_mediatype($_REQUEST["mediatypeid"]);
			show_messages($result, S_MEDIA_TYPE_DELETED, S_MEDIA_TYPE_WAS_NOT_DELETED);
			if($result)
			{
				add_audit(AUDIT_ACTION_DELETE,AUDIT_RESOURCE_MEDIA_TYPE,
					"Media type [".$mediatype["description"]."]");

				unset($_REQUEST["form"]);
			}
		}
	}
	elseif($_REQUEST["config"]==3)
	{



/* IMAGES ACTIONS */
		if(isset($_REQUEST["save"]))
		{
			if(isset($_REQUEST["imageid"]))
			{
	/* UPDATE */
				$result=update_image($_REQUEST["imageid"],$_REQUEST["name"],
					$_REQUEST["imagetype"],$_FILES);

				$msg_ok = S_IMAGE_UPDATED;
				$msg_fail = S_CANNOT_UPDATE_IMAGE;
				$audit_action = "Image updated";
				unset($_REQUEST["imageid"]);
			} else {
	/* ADD */
				$result=add_image($_REQUEST["name"],$_REQUEST["imagetype"],$_FILES);

				$msg_ok = S_IMAGE_ADDED;
				$msg_fail = S_CANNOT_ADD_IMAGE;
				$audit_action = "Image added";
			}
			show_messages($result, $msg_ok, $msg_fail);
			if($result)
			{
				add_audit(AUDIT_ACTION_UPDATE,AUDIT_RESOURCE_ZABBIX_CONFIG,$audit_action);
				unset($_REQUEST["form"]);
			}
		} elseif(isset($_REQUEST["delete"])&&isset($_REQUEST["imageid"])) {
	/* DELETE */
			$result=delete_image($_REQUEST["imageid"]);
			show_messages($result, S_IMAGE_DELETED, S_CANNOT_DELETE_IMAGE);
			if($result)
			{
				add_audit(AUDIT_ACTION_UPDATE,AUDIT_RESOURCE_ZABBIX_CONFIG,"Image deleted");
				unset($_REQUEST["form"]);
			}
			unset($_REQUEST["imageid"]);
		}
	}
	elseif($_REQUEST["config"]==4)
	{



/* AUTOREG ACTIONS */
		if(isset($_REQUEST["save"]))
		{
			if(isset($_REQUEST["autoregid"]))
			{
	/* UPDATE */
				$result=update_autoregistration($_REQUEST["autoregid"],
					$_REQUEST["pattern"],$_REQUEST["priority"],$_REQUEST["hostid"]);

				$msg_ok = S_AUTOREGISTRATION_UPDATED;
				$msg_fail = S_AUTOREGISTRATION_WAS_NOT_UPDATED;
				$audit_action = AUDIT_ACTION_UPDATE;
			} else {
	/* ADD */
				$result=add_autoregistration(
					$_REQUEST["pattern"],$_REQUEST["priority"],$_REQUEST["hostid"]);

				$msg_ok = S_AUTOREGISTRATION_ADDED;
				$msg_fail = S_CANNOT_ADD_AUTOREGISTRATION;
				$audit_action = AUDIT_ACTION_ADD;
			}

			if($result)
			{
				add_audit($audit_action, AUDIT_RESOURCE_AUTOREGISTRATION,
					"Autoregistration [".$_REQUEST["pattern"]."]");

				unset($_REQUEST["form"]);
			}
			show_messages($result, $msg_ok, $msg_fail);

		} elseif(isset($_REQUEST["delete"])&&isset($_REQUEST["autoregid"])) {
	/* DELETE */
			$result=delete_autoregistration($_REQUEST["autoregid"]);
			if($result)
			{
				add_audit(AUDIT_ACTION_DELETE,AUDIT_RESOURCE_AUTOREGISTRATION,
					"Autoregistration [".$_REQUEST["pattern"]."]");
				unset($_REQUEST["form"]);
			}
			show_messages($result, S_AUTOREGISTRATION_DELETED, S_AUTOREGISTRATION_WAS_NOT_DELETED);
		}
	}
	elseif(isset($_REQUEST["save"])&&in_array($_REQUEST["config"],array(0,5)))
	{



/* OTHER ACTIONS */
		$result=update_config($_REQUEST["alarm_history"],$_REQUEST["alert_history"],
			$_REQUEST["refresh_unsupported"]);

		show_messages($result, S_CONFIGURATION_UPDATED, S_CONFIGURATION_WAS_NOT_UPDATED);
		if($result)
		{
			add_audit(AUDIT_ACTION_UPDATE,AUDIT_RESOURCE_ZABBIX_CONFIG,
				"Alarm history [".$_REQUEST["alarm_history"]."]".
				" alert history [".$_REQUEST["alert_history"]."]".
				" refresh unsupported items [".$_REQUEST["refresh_unsupported"]."]");
		}
	}
	elseif($_REQUEST["config"]==6)
	{
		$_REQUEST["valuemap"] = get_request("valuemap",array());
		if(isset($_REQUEST["add_map"]))
		{
			$added = 0;
			$cnt = count($_REQUEST["valuemap"]);
			for($i=0; $i < $cnt; $i++)
			{
				if($_REQUEST["valuemap"][$i]["value"] != $_REQUEST["add_value"])	continue;
				$_REQUEST["valuemap"][$i]["newvalue"] = $_REQUEST["add_newvalue"];
				$added = 1;
				break;
			}
			if($added == 0)
			{
				array_push($_REQUEST["valuemap"],array(
					"value"		=> $_REQUEST["add_value"],
					"newvalue"	=> $_REQUEST["add_newvalue"]));
			}
		}
		elseif(isset($_REQUEST["del_map"])&&isset($_REQUEST["rem_value"]))
		{
			$_REQUEST["valuemap"] = get_request("valuemap",array());
			foreach($_REQUEST["rem_value"] as $val)
				unset($_REQUEST["valuemap"][$val]);
		}
		elseif(isset($_REQUEST["save"]))
		{
			$mapping = get_request("valuemap",array());
			if(isset($_REQUEST["valuemapid"]))
			{
				$result = update_valuemap($_REQUEST["valuemapid"],$_REQUEST["mapname"], $mapping);
				$msg_ok = S_VALUE_MAP_UPDATED;
				$msg_fail = S_CANNNOT_UPDATE_VALUE_MAP;
			}
			else
			{
				$result = add_valuemap($_REQUEST["mapname"], $mapping);
				$msg_ok = S_VALUE_MAP_ADDED;
				$msg_fail = S_CANNNOT_ADD_VALUE_MAP;
			}
			if($result)
			{
				unset($_REQUEST["form"]);
			}
			show_messages($result,$msg_ok, $msg_fail);
		}
		elseif(isset($_REQUEST["delete"]) && isset($_REQUEST["valuemapid"]))
		{
			$result = delete_valuemap($_REQUEST["valuemapid"]);
			if($result)
			{
				unset($_REQUEST["form"]);
			}
			show_messages($result, S_VALUE_MAP_DELETED, S_CANNNOT_DELETE_VALUE_MAP);
		}
	}

?>

<?php

	$form = new CForm("config.php");
	$cmbConfig = new CCombobox("config",$_REQUEST["config"],"submit()");
	$cmbConfig->AddItem(0,S_HOUSEKEEPER);
	$cmbConfig->AddItem(1,S_MEDIA_TYPES);
//	$cmbConfig->AddItem(2,S_ESCALATION_RULES);
	$cmbConfig->AddItem(3,S_IMAGES);
	$cmbConfig->AddItem(4,S_AUTOREGISTRATION);
	$cmbConfig->AddItem(6,S_VALUE_MAPPING);
	$cmbConfig->AddItem(5,S_OTHER);
	$form->AddItem($cmbConfig);
	switch($_REQUEST["config"])
	{
	case 1:
		$form->AddItem(SPACE."|".SPACE);
		$form->AddItem(new CButton("form",S_CREATE_MEDIA_TYPE));
		break;
	case 3:
		$form->AddItem(SPACE."|".SPACE);
		$form->AddItem(new CButton("form",S_CREATE_IMAGE));
		break;
	case 4:
		$form->AddItem(SPACE."|".SPACE);
		$form->AddItem(new CButton("form",S_CREATE_RULE));
		break;
	case 6:
		$form->AddItem(SPACE."|".SPACE);
		$form->AddItem(new CButton("form",S_CREATE_VALUE_MAP));
		break;
	}
	show_header2(S_CONFIGURATION_OF_ZABBIX_BIG, $form);
	echo BR;
?>

<?php
	if($_REQUEST["config"]==0)
	{
		insert_housekeeper_form();
	}
	elseif($_REQUEST["config"]==5)
	{
		insert_other_parameters_form();
	}
	elseif($_REQUEST["config"]==1)
	{
		if(isset($_REQUEST["form"]))
		{
			insert_media_type_form();
		}
		else
		{
			show_table_header(S_MEDIA_TYPES_BIG);

			$table=new CTableInfo(S_NO_MEDIA_TYPES_DEFINED);
			$table->setHeader(array(S_DESCRIPTION,S_TYPE));

			$result=DBselect("select mt.mediatypeid,mt.type,mt.description,mt.smtp_server,".
				"mt.smtp_helo,mt.smtp_email,mt.exec_path from media_type mt order by mt.type");
			while($row=DBfetch($result))
			{
				$description=new CLink($row["description"],"config.php?&form=update".
					url_param("config")."&mediatypeid=".$row["mediatypeid"],'action');

				if($row["type"]==0)		$type=S_EMAIL;
				else if($row["type"]==1)	$type=S_SCRIPT;
				else				$type=S_UNKNOWN;

				$table->addRow(array(
//					$row["mediatypeid"],
					$description,
					$type));
			}
			$table->show();
		}
	}
/*
// Disabled
	elseif($_REQUEST["config"]==2)
	{
		echo "<br>";
		show_table_header(S_ESCALATION_RULES_BIG);

		table_begin();
		table_header(array(S_ID,S_DESCRIPTION_SMALL,S_DEFAULT,S_ACTIONS));

		$result=DBselect("select * from escalations order by name");
		$col=0;
		while($row=DBfetch($result))
		{
			$yes=iif($row["dflt"]==1,array("value"=>S_YES,"class"=>"on"),array("value"=>S_NO,"class"=>"off"));

			$actions="<a href=\"config.php?config=2&register=change&escalationid=".$row["escalationid"]."\">".S_CHANGE."</a>";
			table_row(array(
				$row["escalationid"],
				$row["name"],
				$yes,
				$actions),$col++);
		}
		if(DBnum_rows($result)==0)
		{
				echo "<TR BGCOLOR=#EEEEEE>";
				echo "<TD COLSPAN=4 ALIGN=CENTER>".S_NO_ESCALATION_RULES_DEFINED."</TD>";
				echo "<TR>";
		}
		table_end();

		insert_escalation_form($_REQUEST["escalationid"]);

		if(isset($_REQUEST["escalationid"]))
		{
			echo "<br>";
			show_table_header(S_ESCALATION_RULES);

			table_begin();
			table_header(array(S_LEVEL,S_PERIOD,S_DELAY_BEFORE_ACTION,S_DO,S_ACTIONS));

			$result=DBselect("select * from escalation_rules order by level");
			$col=0;
			while($row=DBfetch($result))
			{
				$actions="<a href=\"config.php?config=2&register=change&escalationid=".$_REQUEST["escalationid"]."&escalationruleid=".$row["escalationruleid"]."\">".S_CHANGE."</a>";
				$actiontypes=array("Do nothing","Execute action","Increase severity","Increase administrative hierarcy");

				table_row(array(
					$row["level"],
					$row["period"],
					$row["delay"],
					$actiontypes[$row["actiontype"]],
					$actions),$col++);
			}
			if(DBnum_rows($result)==0)
			{
					echo "<TR BGCOLOR=#EEEEEE>";
					echo "<TD COLSPAN=5 ALIGN=CENTER>".S_NO_ESCALATION_DETAILS."</TD>";
					echo "<TR>";
			}
			table_end();

			insert_escalation_rule_form($_REQUEST["escalationid"],$_REQUEST["escalationruleid"]);
		}
	}
*/
	elseif($_REQUEST["config"]==3)
	{
		if(isset($_REQUEST["form"]))
		{
			insert_image_form();
		}
		else
		{
			show_table_header(S_IMAGES_BIG);

			$table=new CTableInfo(S_NO_IMAGES_DEFINED);
			$table->setHeader(array(S_ID,S_NAME,S_TYPE,S_IMAGE));
	
			$result=DBselect("select imageid,imagetype,name,image from images order by name");
			while($row=DBfetch($result))
			{
				if($row["imagetype"]==1)	$imagetype=S_ICON;
				else if($row["imagetype"]==2)	$imagetype=S_BACKGROUND;
				else				$imagetype=S_UNKNOWN;

				$name=new CLink($row["name"],"config.php?form=update".url_param("config").
					"&imageid=".$row["imageid"],'action');

				$table->addRow(array(
					$row["imageid"],
					$name,
					$imagetype,
					$actions=new CLink(
						new CImg("image.php?imageid=".$row["imageid"],"no image",NULL,"24"),
						"image.php?imageid=".$row["imageid"])
					));
			}
			$table->show();
		}
	}
	elseif($_REQUEST["config"]==4)
	{
		if(isset($_REQUEST["form"]))
		{
			insert_autoregistration_form();
		}
		else
		{
			show_table_header(S_AUTOREGISTRATION_RULES_BIG);

			$table=new CTableInfo(S_NO_AUTOREGISTRATION_RULES_DEFINED);
			$table->setHeader(array(S_ID,S_PRIORITY,S_PATTERN,S_HOST));

			$result=DBselect("select * from autoreg order by priority");
			while($row=DBfetch($result))
			{
				if($row["hostid"]==0)
				{
					$name=SPACE;
				}
				else
				{
					$host=get_host_by_hostid($row["hostid"]);
					$name=$host["host"];
				}
				$pattern=new CLink($row["pattern"],
					"config.php?form=update".url_param("config")."&autoregid=".$row["id"],
					'action');

				$table->addRow(array(
					$row["id"],
					$row["priority"],
					$pattern,
					$name));
			}
			$table->show();
		}
	}
	elseif($_REQUEST["config"]==6)
	{
		if(isset($_REQUEST["form"]))
		{
			insert_value_mapping_form();
		}
		else
		{
			show_table_header(S_VALUE_MAPPING_BIG);
			$table = new CTableInfo();
			$table->SetHeader(array(S_NAME, S_VALUE_MAP));

			$db_valuemaps = DBselect("select * from valuemaps");
			while($db_valuemap = DBfetch($db_valuemaps))
			{
				$mappings_row = array();
				$db_maps = DBselect("select * from mappings".
					" where valuemapid=".$db_valuemap["valuemapid"]);
				while($db_map = DBfetch($db_maps))
				{
					array_push($mappings_row, 
						$db_map["value"],
						SPACE.RARR.SPACE,
						$db_map["newvalue"],
						BR);
				}
				$table->AddRow(array(
					new CLink($db_valuemap["name"],"config.php?form=update&".
						"valuemapid=".$db_valuemap["valuemapid"].url_param("config"),
						"action"),
					$mappings_row));
			}
			
			$table->Show();
		}
	}
?>

<?php
	show_page_footer();
?>