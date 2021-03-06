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
	require_once('include/config.inc.php');
	require_once('include/nodes.inc.php');

	$page['title'] = "S_NODES";
	$page['file'] = 'nodes.php';

include_once('include/page_header.php');

	$_REQUEST['config'] = get_request('config','nodes.php');

?>
<?php
	$fields=array(
//		VAR			TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
		'config'=>			array(T_ZBX_STR, O_OPT, P_SYS,	NULL,	NULL),
		
// media form
		'nodeid'=>			array(T_ZBX_INT, O_NO,	null,	DB_ID,			'(isset({form})&&({form}=="update"))'),

		'new_nodeid'=>		array(T_ZBX_INT, O_OPT,	null,	DB_ID,			'isset({save})'),
		'name'=>			array(T_ZBX_STR, O_OPT,	null,	NOT_EMPTY,		'isset({save})'),
		'timezone'=>		array(T_ZBX_INT, O_OPT,	null,	BETWEEN(-12,+13),	'isset({save})'),
		'ip'=>				array(T_ZBX_IP,	 O_OPT,	null,	null,			'isset({save})'),
		'node_type'=>		array(T_ZBX_INT, O_OPT,	null,
			IN(ZBX_NODE_REMOTE.','.ZBX_NODE_MASTER.','.ZBX_NODE_LOCAL),		'isset({save})&&!isset({nodeid})'),
		'port'=>			array(T_ZBX_INT, O_OPT,	null,	BETWEEN(1,65535),	'isset({save})'),
		'slave_history'=>	array(T_ZBX_INT, O_OPT,	null,	BETWEEN(0,65535),	'isset({save})'),
		'slave_trends'=>	array(T_ZBX_INT, O_OPT,	null,	BETWEEN(0,65535),	'isset({save})'),

/* actions */
		'save'=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'delete'=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'cancel'=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
/* other */
		'form'=>			array(T_ZBX_STR, O_OPT, P_SYS,	NULL,	NULL),
		'form_refresh'=>	array(T_ZBX_INT, O_OPT,	NULL,	NULL,	NULL)
	);

	check_fields($fields);
	validate_sort_and_sortorder();

	$available_nodes = get_accessible_nodes_by_user($USER_DETAILS,PERM_READ_LIST);

	if (0 == count($available_nodes) ){
		access_deny();
	}

?>
<?php
	if(isset($_REQUEST['save'])){
		$result = false;
		if(isset($_REQUEST['nodeid'])){
/* update */
			$audit_action = AUDIT_ACTION_UPDATE;
			DBstart();
			$result = update_node($_REQUEST['nodeid'],$_REQUEST['new_nodeid'],
				$_REQUEST['name'], $_REQUEST['timezone'], $_REQUEST['ip'], $_REQUEST['port'],
				$_REQUEST['slave_history'], $_REQUEST['slave_trends']);
			$result = DBend($result);
			$nodeid = $_REQUEST['nodeid'];
			show_messages($result, S_NODE_UPDATED, S_CANNOT_UPDATE_NODE);
		}
		else{
/* add */
			$audit_action = AUDIT_ACTION_ADD;

			DBstart();
			$nodeid = add_node($_REQUEST['new_nodeid'],
				$_REQUEST['name'], $_REQUEST['timezone'], $_REQUEST['ip'], $_REQUEST['port'],
				$_REQUEST['slave_history'], $_REQUEST['slave_trends'], $_REQUEST['node_type']);
			$result = DBend($nodeid);
			show_messages($result, S_NODE_ADDED, S_CANNOT_ADD_NODE);
		}
		add_audit_if($result,$audit_action,AUDIT_RESOURCE_NODE,'Node ['.$_REQUEST['name'].'] id ['.$nodeid.']');
		if($result){
			unset($_REQUEST['form']);
		}
	}
	else if(isset($_REQUEST['delete'])){
		$node_data = get_node_by_nodeid($_REQUEST['nodeid']);

		DBstart();
		$result = delete_node($_REQUEST['nodeid']);
		$result = DBend($result);
		show_messages($result, S_NODE_DELETED, S_CANNOT_DELETE_NODE);

		add_audit_if($result,AUDIT_ACTION_DELETE,AUDIT_RESOURCE_NODE,'Node ['.$node_data['name'].'] id ['.$node_data['nodeid'].']');
		if($result){
			unset($_REQUEST['form'],$node_data);
		}
	}
?>
<?php
	$available_nodes = get_accessible_nodes_by_user($USER_DETAILS,PERM_READ_LIST);

	$frmForm = new CForm();
	$frmForm->setMethod('get');
	
// Config
	$cmbConf = new CComboBox('config','nodes.php','javascript: submit()');
	$cmbConf->setAttribute('onchange','javascript: redirect(this.options[this.selectedIndex].value);');	
		$cmbConf->addItem('nodes.php',S_NODES);
		$cmbConf->addItem('proxies.php',S_PROXIES);
		
	$frmForm->addItem($cmbConf);
	
	if(!isset($_REQUEST['form'])){
		$frmForm->addItem(new CButton('form',S_NEW_NODE));
	}
	
	show_table_header(S_CONFIGURATION_OF_NODES, $frmForm);
	
	if(isset($_REQUEST['form'])){
		global $ZBX_CURMASTERID;

		$frm_title = S_NODE;

		if(isset($_REQUEST['nodeid'])){
			$node_data = get_node_by_nodeid($_REQUEST['nodeid']);

			$node_type = detect_node_type($node_data);

			$masterid	= $node_data['masterid'];

			$frm_title = S_NODE.' "'.$node_data['name'].'"';
		}

		$frmNode= new CFormTable($frm_title);
		$frmNode->SetHelp('node.php');

		if(isset($_REQUEST['nodeid'])){
			$frmNode->addVar('nodeid', $_REQUEST['nodeid']);
		}

		if(isset($_REQUEST['nodeid']) && (!isset($_REQUEST['form_refresh']) || isset($_REQUEST['register']))){
			$new_nodeid	= $node_data['nodeid'];
			$name		= $node_data['name'];
			$timezone	= $node_data['timezone'];
			$ip		= $node_data['ip'];
			$port		= $node_data['port'];
			$slave_history	= $node_data['slave_history'];
			$slave_trends	= $node_data['slave_trends'];
		}
		else{
			$new_nodeid	= get_request('new_nodeid',0);
			$name 		= get_request('name','');
			$timezone 	= get_request('timezone', 0);
			$ip		= get_request('ip','127.0.0.1');
			$port		= get_request('port',10051);
			$slave_history	= get_request('slave_history',90);
			$slave_trends	= get_request('slave_trends',365);
			$node_type	= get_request('node_type', ZBX_NODE_REMOTE);

			$masterid	= get_request('masterid', get_current_nodeid(false));
		}

		$master_node = DBfetch(DBselect('SELECT name FROM nodes WHERE nodeid='.$masterid));

		$frmNode->addRow(S_NAME, new CTextBox('name', $name, 40));

		$frmNode->addRow(S_ID, new CNumericBox('new_nodeid', $new_nodeid, 10));

		if(!isset($_REQUEST['nodeid'])){
			$cmbNodeType = new CComboBox('node_type', $node_type, 'submit()');
			$cmbNodeType->addItem(ZBX_NODE_REMOTE, S_REMOTE);
			if($ZBX_CURMASTERID == 0)
			{
				$cmbNodeType->addItem(ZBX_NODE_MASTER, S_MASTER);
			}
		}
		else{
			$cmbNodeType = new CTextBox('node_type_name', node_type2str($node_type), null, 'yes');
		}
		$frmNode->addRow(S_TYPE, 	$cmbNodeType);

		if($node_type == ZBX_NODE_REMOTE){
			$frmNode->addRow(S_MASTER_NODE, new CTextBox('master_name',	$master_node['name'], 40, 'yes'));
		}

		$cmbTimeZone = new CComboBox('timezone', $timezone);
		for($i = -12; $i <= 13; $i++){
			$cmbTimeZone->addItem($i, 'GMT'.sprintf('%+03d:00', $i));
		}
		$frmNode->addRow(S_TIME_ZONE, $cmbTimeZone);
		$frmNode->addRow(S_IP, new CTextBox('ip', $ip, 15));
		$frmNode->addRow(S_PORT, new CNumericBox('port', $port,5));
		$frmNode->addRow(S_DO_NOT_KEEP_HISTORY_OLDER_THAN, new CNumericBox('slave_history', $slave_history,6));
		$frmNode->addRow(S_DO_NOT_KEEP_TRENDS_OLDER_THAN, new CNumericBox('slave_trends', $slave_trends,6));


		$frmNode->addItemToBottomRow(new CButton('save',S_SAVE));
		if(isset($_REQUEST['nodeid']) && $node_type != ZBX_NODE_LOCAL){
			$frmNode->addItemToBottomRow(SPACE);
			$frmNode->addItemToBottomRow(new CButtonDelete('Delete selected node?',
				url_param('form').url_param('nodeid')));
		}
		$frmNode->addItemToBottomRow(SPACE);
		$frmNode->addItemToBottomRow(new CButtonCancel(url_param('config')));
		$frmNode->Show();
	}
	else{
		show_table_header(S_NODES_BIG);

		$table=new CTableInfo(S_NO_NODES_DEFINED);
		$table->SetHeader(array(
			make_sorting_link(S_ID,'n.nodeid'),
			make_sorting_link(S_NAME,'n.name'),
			make_sorting_link(S_TYPE,'n.nodetype'),
			make_sorting_link(S_TIME_ZONE,'n.timezone'),
			make_sorting_link(S_IP.':'.S_PORT,'n.ip')
		));

		$sql = 'SELECT n.* '.
				' FROM nodes n'.
				' WHERE '.DBcondition('n.nodeid',$available_nodes).
				order_by('n.nodeid,n.name,n.nodetype,n.timezone,n.ip','n.masterid');

		$db_nodes = DBselect($sql);
		while($row=DBfetch($db_nodes)){

			$node_type = detect_node_type($row);
			$node_type_name = node_type2str($node_type);

			$table->AddRow(array(
				$row['nodeid'],
				array(
					get_node_path($row['masterid']),
					new CLink(
						($row['nodetype'] ? new CSpan($row['name'], 'bold') : $row['name']),
						'?&form=update&nodeid='.$row['nodeid'],'action')),
				$node_type == ZBX_NODE_LOCAL ? new CSpan($node_type_name, 'bold') : $node_type_name,
				new CSpan('GMT'.sprintf('%+03d:00', $row['timezone']),	$row['nodetype'] ? 'bold' : null),
				new CSpan($row['ip'].':'.$row['port'], 			$row['nodetype'] ? 'bold' : null)
				));
		}
		$table->Show();
	}

include_once 'include/page_footer.php';

?>
