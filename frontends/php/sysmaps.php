<?php
/*
** ZABBIX
** Copyright (C) 2000-2009 SIA Zabbix
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
	require_once 'include/config.inc.php';
	require_once 'include/maps.inc.php';
	require_once 'include/forms.inc.php';

	$page['title'] = 'S_NETWORK_MAPS';
	$page['file'] = 'sysmaps.php';
	$page['hist_arg'] = array();

include_once 'include/page_header.php';

?>
<?php
//		VAR			TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
	$fields=array(
		'maps'=>		array(T_ZBX_INT, O_OPT,	P_SYS,	DB_ID, NULL),
		'sysmapid'=>		array(T_ZBX_INT, O_OPT,	 P_SYS,	DB_ID,NULL),
		'name'=>		array(T_ZBX_STR, O_OPT,	 NULL,	NOT_EMPTY,		'isset({save})'),
		'width'=>		array(T_ZBX_INT, O_OPT,	 NULL,	BETWEEN(0,65535),	'isset({save})'),
		'height'=>		array(T_ZBX_INT, O_OPT,	 NULL,	BETWEEN(0,65535),	'isset({save})'),
		'backgroundid'=>	array(T_ZBX_INT, O_OPT,	 NULL,	DB_ID,			'isset({save})'),
		'label_type'=>		array(T_ZBX_INT, O_OPT,	 NULL,	BETWEEN(0,4),		'isset({save})'),
		'label_location'=>	array(T_ZBX_INT, O_OPT,	 NULL,	BETWEEN(0,3),		'isset({save})'),
/* Actions */
		'save'=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'delete'=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'cancel'=>		array(T_ZBX_STR, O_OPT, P_SYS, NULL,	NULL),
		'go'=>			array(T_ZBX_STR, O_OPT, P_SYS|P_ACT, NULL, NULL),
/* Form */
		'form'=>		array(T_ZBX_STR, O_OPT, P_SYS,	NULL,	NULL),
		'form_refresh'=>	array(T_ZBX_INT, O_OPT,	NULL,	NULL,	NULL)

	);
	check_fields($fields);
	validate_sort_and_sortorder('sm.name',ZBX_SORT_UP);

	if(isset($_REQUEST['sysmapid'])){
		if(!sysmap_accessible($_REQUEST['sysmapid'],PERM_READ_WRITE))
			access_deny();

		$sysmap = DBfetch(DBselect('select * from sysmaps where sysmapid='.$_REQUEST['sysmapid']));
	}
?>
<?php

	$_REQUEST['go'] = get_request('go', 'none');

	if(isset($_REQUEST["save"])){
		if(isset($_REQUEST["sysmapid"])){
			// TODO check permission by new value.
			DBstart();
			update_sysmap($_REQUEST["sysmapid"],$_REQUEST["name"],$_REQUEST["width"],
				$_REQUEST["height"],$_REQUEST["backgroundid"],$_REQUEST["label_type"],
				$_REQUEST["label_location"]);
			$result = DBend();

			add_audit_if($result,AUDIT_ACTION_UPDATE,AUDIT_RESOURCE_MAP,'Name ['.$_REQUEST['name'].']');
			show_messages($result,"Network map updated","Cannot update network map");
		}
		else {
			if(!count(get_accessible_nodes_by_user($USER_DETAILS,PERM_READ_WRITE,PERM_RES_IDS_ARRAY)))
				access_deny();

			DBstart();
			add_sysmap($_REQUEST["name"],$_REQUEST["width"],$_REQUEST["height"],
				$_REQUEST["backgroundid"],$_REQUEST["label_type"],$_REQUEST["label_location"]);
			$result = DBend();

			add_audit_if($result,AUDIT_ACTION_ADD,AUDIT_RESOURCE_MAP,'Name ['.$_REQUEST['name'].']');
			show_messages($result,"Network map added","Cannot add network map");
		}
		if($result){
			unset($_REQUEST["form"]);
		}
	}
	else if(isset($_REQUEST["delete"])&&isset($_REQUEST["sysmapid"])){
		DBstart();
		delete_sysmap($_REQUEST["sysmapid"]);
		$result = DBend();

		add_audit_if($result,AUDIT_ACTION_DELETE,AUDIT_RESOURCE_MAP,'Name ['.$sysmap['name'].']');
		show_messages($result, S_MAP_DELETED, S_CANNOT_DELETE_MAP);
		if($result){
			unset($_REQUEST["form"]);
		}
	}
	else if($_REQUEST['go'] == 'delete'){
		$result = true;
		$maps = get_request('maps', array());
		
		DBstart();
		foreach($maps as $mapid){
			$result &= delete_sysmap($mapid);
			if(!$result) break;
		}
		$result = DBend($result);
		
		if($result){
			unset($_REQUEST["form"]);
		}
		show_messages($result, S_MAP_DELETED, S_CANNOT_DELETE_MAP);
	}
?>
<?php
	$form = new CForm();
	$form->SetMethod('get');

	$form->AddItem(new CButton("form",S_CREATE_MAP));
	show_table_header(S_CONFIGURATION_OF_NETWORK_MAPS, $form);
	echo SBR;
?>
<?php
	if(isset($_REQUEST["form"])){
		insert_map_form();
	}
	else{
	
		$form = new CForm();
		$form->setName('frm_maps');
		
		$numrows = new CSpan(null,'info');
		$numrows->setAttribute('name','numrows');
		$header = get_table_header(array(
			S_MAPS_BIG,
			new CSpan(SPACE.SPACE.'|'.SPACE.SPACE, 'divider'),
			S_FOUND.': ',$numrows));
			
		show_table_header($header);

		$table = new CTableInfo(S_NO_MAPS_DEFINED);
		$table->SetHeader(array(
			new CCheckBox('all_maps',NULL,"checkAll('".$form->getName()."','all_maps','maps');"),
			make_sorting_link(S_NAME,'sm.name'),
			make_sorting_link(S_WIDTH,'sm.width'),
			make_sorting_link(S_HEIGHT,'sm.height'),
			S_MAP));

		$result = DBselect('SELECT sm.sysmapid,sm.name,sm.width,sm.height '.
						' FROM sysmaps sm'.
						' WHERE '.DBin_node('sm.sysmapid').
						order_by('sm.name,sm.width,sm.height','sm.sysmapid'));

		while($row=DBfetch($result)){
			if(!sysmap_accessible($row["sysmapid"],PERM_READ_WRITE)) continue;

			$table->AddRow(array(
				new CCheckBox('maps['.$row['sysmapid'].']', NULL, NULL, $row['sysmapid']),
				new CLink($row["name"], "sysmaps.php?form=update".
					"&sysmapid=".$row["sysmapid"]."#form",'action'),
				$row["width"],
				$row["height"],
				new CLink(S_EDIT,"sysmap.php?sysmapid=".$row["sysmapid"])
				));
		}
		
//----- GO ------
		$goBox = new CComboBox('go');
		$goBox->addItem('delete', S_DELETE_SELECTED);

// goButton name is necessary!!!
		$goButton = new CButton('goButton',S_GO.' (0)');
		$goButton->setAttribute('id','goButton');
		zbx_add_post_js('chkbxRange.pageGoName = "maps";');

		$table->setFooter(new CCol(array($goBox, $goButton)));
		
		$form->addItem($table);
		$form->show();
		
		zbx_add_post_js('insert_in_element("numrows","'.$table->getNumRows().'");');
	}		


include_once "include/page_footer.php";

?>