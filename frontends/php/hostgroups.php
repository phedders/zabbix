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
	require_once('include/hosts.inc.php');

	$page['title'] = 'S_HOST_GROUPS';
	$page['file'] = 'hostgroups.php';
	// $page['hist_arg'] = array('groupid','hostid');
	// $page['scripts'] = array('menu_scripts.js','calendar.js');

include_once('include/page_header.php');

	$available_groups = get_accessible_groups_by_user($USER_DETAILS,PERM_READ_WRITE);
	$available_hosts = get_accessible_hosts_by_user($USER_DETAILS,PERM_READ_WRITE);
	// $available_groups1 = CHostGroup::get(array('editable' => 1));
	// $available_hosts1 = CHost::get(array('editable' => 1, 'templated_hosts' => 1));
// SDI('<pre>'.print_r(array_diff($available_groups, $available_groups1), true).'</pre>');
// SDI('<pre>'.print_r($available_groups, true).'</pre>');
// SDI('<pre>'.print_r($available_hosts, true).'</pre>');
	if(isset($_REQUEST['groupid']) && ($_REQUEST['groupid']>0) && !isset($available_groups[$_REQUEST['groupid']])){
		access_deny();
	}
	if(isset($_REQUEST['hostid']) && ($_REQUEST['hostid']>0) && !isset($available_hosts[$_REQUEST['hostid']])) {
		access_deny();
	}
?>
<?php
//		VAR			TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
	$fields=array(
/* ARRAYS */
		'hosts'=>				array(T_ZBX_INT, O_OPT,	P_SYS,		DB_ID, 	NULL),
		'groups'=>				array(T_ZBX_INT, O_OPT,	P_SYS,		DB_ID, 	NULL),
		'hostids'=>				array(T_ZBX_INT, O_OPT,	P_SYS,		DB_ID, 	NULL),
		'groupids'=>			array(T_ZBX_INT, O_OPT,	P_SYS,		DB_ID, 	NULL),
/* group */
		'groupid'=>				array(T_ZBX_INT, O_OPT,	P_SYS,		DB_ID,		'(isset({form})&&({form}=="update"))'),
		'gname'=>				array(T_ZBX_STR, O_OPT,	NULL,		NOT_EMPTY,	'isset({save})'),
/* actions */
		'activate'=>			array(T_ZBX_STR, O_OPT, P_SYS|P_ACT, NULL, 	NULL),
		'disable'=>				array(T_ZBX_STR, O_OPT, P_SYS|P_ACT, NULL, 	NULL),

		'add_to_group'=>		array(T_ZBX_INT, O_OPT, P_SYS|P_ACT, DB_ID, NULL),
		'delete_from_group'=>	array(T_ZBX_INT, O_OPT, P_SYS|P_ACT, DB_ID, NULL),

		'save'=>				array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'clone'=>				array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'full_clone'=>			array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'delete'=>				array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'cancel'=>				array(T_ZBX_STR, O_OPT, P_SYS,			NULL,	NULL),
/* host linkage form */
		'twb_groupid'=> 	array(T_ZBX_INT, O_OPT,	NULL,	DB_ID,	NULL),
/* other */
		'form'=>				array(T_ZBX_STR, O_OPT, P_SYS,			NULL,	NULL),
		'form_refresh'=>		array(T_ZBX_STR, O_OPT, NULL,			NULL,	NULL)
	);
	check_fields($fields);
validate_sort_and_sortorder('h.host',ZBX_SORT_UP);
//	update_profile('web.hosts.config',$_REQUEST['config'], PROFILE_TYPE_INT);
?>
<?php


/*** <--- ACTIONS ---> ***/
	if(inarr_isset(array('add_to_group','hostid'))){
//		if(!uint_in_array($_REQUEST['add_to_group'], get_accessible_groups_by_user($USER_DETAILS,PERM_READ_WRITE,PERM_RES_IDS_ARRAY))){
		if(!isset($available_groups[$_REQUEST['add_to_group']])){
			access_deny();
		}

		DBstart();
			$result = add_host_to_group($_REQUEST['hostid'], $_REQUEST['add_to_group']);
		$result = DBend($result);

		show_messages($result,S_HOST_UPDATED,S_CANNOT_UPDATE_HOST);
	}
	else if(inarr_isset(array('delete_from_group','hostid'))){
//		if(!uint_in_array($_REQUEST['delete_from_group'], get_accessible_groups_by_user($USER_DETAILS,PERM_READ_WRITE,PERM_RES_IDS_ARRAY))){
		if(!isset($available_groups[$_REQUEST['delete_from_group']])){
			access_deny();
		}

		DBstart();
			$result = delete_host_from_group($_REQUEST['hostid'], $_REQUEST['delete_from_group']);
		$result = DBend($result);

		show_messages($result, S_HOST_UPDATED, S_CANNOT_UPDATE_HOST);
	}
	else if(isset($_REQUEST['clone']) && isset($_REQUEST['groupid'])){
		unset($_REQUEST['groupid']);
		$_REQUEST['form'] = 'clone';
	}
	else if(isset($_REQUEST['save'])){
		$hosts = get_request('hosts', array());
		$hosts = array_intersect($available_hosts, $hosts);
		if(isset($_REQUEST['groupid'])){
			DBstart();

			$result = update_host_group($_REQUEST['groupid'], $_REQUEST['gname'], $hosts);
			$result = DBend($result);

/*			$action 	= AUDIT_ACTION_UPDATE;*/
			$msg_ok		= S_GROUP_UPDATED;
			$msg_fail	= S_CANNOT_UPDATE_GROUP;
			$groupid = $_REQUEST['groupid'];
		}
		else {
			if(!count(get_accessible_nodes_by_user($USER_DETAILS,PERM_READ_WRITE,PERM_RES_IDS_ARRAY)))
				access_deny();

			DBstart();
				$groupid = add_host_group($_REQUEST['gname'], $hosts);
			$result = DBend($groupid);

/*			$action 	= AUDIT_ACTION_ADD;*/
			$msg_ok		= S_GROUP_ADDED;
			$msg_fail	= S_CANNOT_ADD_GROUP;
		}
		show_messages($result, $msg_ok, $msg_fail);
		if($result){
/*			add_audit($action,AUDIT_RESOURCE_HOST_GROUP,S_HOST_GROUP.' ['.$_REQUEST['gname'].'] ['.$groupid.']');*/
			unset($_REQUEST['form']);
		}
		unset($_REQUEST['save']);
	}
	else if(isset($_REQUEST['delete'])){
		if(isset($_REQUEST['groupid'])){
			$result = false;
/*			if($group = get_hostgroup_by_groupid($_REQUEST['groupid'])){*/
				DBstart();
				$result = delete_host_group($_REQUEST['groupid']);
				$result = DBend($result);
/*			} */

/*			if($result){
				add_audit(AUDIT_ACTION_DELETE,AUDIT_RESOURCE_HOST_GROUP,S_HOST_GROUP.' ['.$group['name'].' ] ['.$group['groupid'].']');
			}*/

			unset($_REQUEST['form']);

			show_messages($result, S_GROUP_DELETED, S_CANNOT_DELETE_GROUP);
			unset($_REQUEST['groupid']);
		}
		else {
/* group operations */
			$result = true;

			$groups = get_request('groups',array());
			$db_groups=DBselect('select groupid, name from groups where '.DBin_node('groupid'));

			DBstart();
			while($db_group=DBfetch($db_groups)){
				if(!uint_in_array($db_group['groupid'],$groups)) continue;

/*				if(!$group = get_hostgroup_by_groupid($db_group['groupid'])) continue;*/
				$result &= delete_host_group($db_group['groupid']);

/*				if($result){
					add_audit(AUDIT_ACTION_DELETE,AUDIT_RESOURCE_HOST_GROUP,
					S_HOST_GROUP.' ['.$group['name'].' ] ['.$group['groupid'].']');
				}*/
			}
			$result = DBend($result);
			show_messages(true, S_GROUP_DELETED, S_CANNOT_DELETE_GROUP);
		}
		unset($_REQUEST['delete']);
	}
	else if(isset($_REQUEST['activate']) || isset($_REQUEST['disable'])){
		$result = true;
		$status = isset($_REQUEST['activate']) ? HOST_STATUS_MONITORED : HOST_STATUS_NOT_MONITORED;
		$groups = get_request('groups',array());

		$db_hosts=DBselect('select h.hostid, hg.groupid '.
			' from hosts_groups hg, hosts h'.
			' where h.hostid=hg.hostid '.
				' and h.status in ('.HOST_STATUS_MONITORED.','.HOST_STATUS_NOT_MONITORED.')'.
				' and '.DBin_node('h.hostid'));

		DBstart();
		while($db_host=DBfetch($db_hosts)){
			if(!uint_in_array($db_host['groupid'],$groups)) continue;
			$host=get_host_by_hostid($db_host['hostid']);

			$result &= update_host_status($db_host['hostid'],$status);
/*			add_audit(AUDIT_ACTION_UPDATE,AUDIT_RESOURCE_HOST,
				'Old status ['.$host['status'].'] '.'New status ['.$status.']');*/
		}
		$result = DBend($result);
		show_messages($result, S_HOST_STATUS_UPDATED, S_CANNOT_UPDATE_HOST);

		unset($_REQUEST['activate']);
	}
/*** ---> ACTIONS <--- ***/

	// $available_hosts = get_accessible_hosts_by_user($USER_DETAILS,PERM_READ_WRITE,null,null,AVAILABLE_NOCACHE); /* update available_hosts after ACTIONS */

	// $params = array();
	// $options = array('only_current_node');
	// if(isset($_REQUEST['form']) || isset($_REQUEST['massupdate'])){
		// array_push($options, 'do_not_select_if_empty');
	// }
	// foreach($options as $option) $params[$option] = 1;
	
	// $PAGE_GROUPS = get_viewed_groups(PERM_READ_WRITE, $params);
	// $PAGE_HOSTS = get_viewed_hosts(PERM_READ_WRITE, $PAGE_GROUPS['groupids'], $params);
	// validate_group($PAGE_GROUPS, $PAGE_HOSTS, $PAGE_HOSTS, false);
	
	// $available_groups = $PAGE_GROUPS['groupids'];
	// $available_hosts = $PAGE_HOSTS['hostids'];

	$frmForm = new CForm();
	$frmForm->setMethod('get');

	if(!isset($_REQUEST['form'])){
		$frmForm->addItem(new CButton('form', S_CREATE_GROUP));
	}
	show_table_header(S_CONFIGURATION_OF_GROUPS, $frmForm);

	if(isset($_REQUEST['form'])){
		echo SBR;
		global $USER_DETAILS;
		
		$groupid = get_request('groupid', 0);
		$hosts = get_request('hosts', array(0));
		
		$frm_title = S_HOST_GROUP;
		if($groupid > 0){
			$group = get_hostgroup_by_groupid($_REQUEST['groupid']);
			$name = $group['name'];
			$frm_title .= ' ['.$group['name'].']';
		}
		else{
			$name = '';
		}
		
		$frmHostG = new CFormTable($frm_title, 'hostgroups.php');
		$frmHostG->setHelp('web.hosts.group.php');
		$frmHostG->addRow(S_GROUP_NAME, new CTextBox('gname', $name, 48));

		if($groupid > 0){
			$frmHostG->addVar('groupid',$_REQUEST['groupid']);
			// if first time select all hosts for group from db
			if(!isset($_REQUEST['form_refresh'])){
				$params = array('groupids' => $groupid, 
								'editable' => 1,
								'order' => 'host',
								'templated_hosts' => 1);
				$db_hosts = CHost::get($params);
				foreach($db_hosts as $hostid => $db_host){
					$hosts[$db_host['hostid']] = $db_host['hostid'];
				}
			}
		}

		// select all possible groups
		$params = array('not_proxy_host' => 1,
						'order' => 'name',
						'editable' => 1);
		$db_groups = CHostGroup::get($params);
		$selected_grp = get_request('twb_groupid', 0);
		if($selected_grp == 0){
			$gr = reset($db_groups);
			$selected_grp = $gr['groupid'];
		}
		$cmbGroups = new CComboBox('twb_groupid', $selected_grp, 'submit()');
		foreach($db_groups as $groupid => $row){
			$cmbGroups->addItem($row['groupid'], $row['name']);
		}

		$cmbHosts = new CTweenBox($frmHostG, 'hosts', $hosts, 25);
		
		// get hosts from selected twb_groupid combo
		$params = array('groupids'=>$selected_grp,
						'templated_hosts'=>1,
						'order'=>'host',
						'editable' => 1);
		$db_hosts = CHost::get($params);
		foreach($db_hosts as $hostid => $db_host){
			if(!isset($hosts[$hostid])) // add all except selected hosts
			$cmbHosts->addItem($db_host['hostid'], get_node_name_by_elid($db_host['hostid']).$db_host['host']);
		}

		// select selected hosts and add them
		$params = array('hostids' => $hosts,
						'templated_hosts' =>1 ,
						'order' => 'host',
						'editable' => 1);
		$db_hosts = CHost::get($params);
		foreach($db_hosts as $hostid => $db_host){
			$cmbHosts->addItem($db_host['hostid'], get_node_name_by_elid($db_host['hostid']).$db_host['host']);
		}

		$frmHostG->addRow(S_HOSTS,$cmbHosts->Get(S_HOSTS.SPACE.S_IN,array(S_OTHER.SPACE.S_HOSTS.SPACE.'|'.SPACE.S_GROUP.SPACE,$cmbGroups)));

		$frmHostG->addItemToBottomRow(new CButton('save',S_SAVE));
		if($groupid>0){
			$frmHostG->addItemToBottomRow(SPACE);
			$frmHostG->addItemToBottomRow(new CButton('clone',S_CLONE));
			$frmHostG->addItemToBottomRow(SPACE);

			$dltButton = new CButtonDelete('Delete selected group?', url_param('form').url_param('config').url_param('groupid'));
			$dlt_groups = getDeletableHostGroups($_REQUEST['groupid']);

			if(empty($dlt_groups)) $dltButton->addOption('disabled','disabled');

			$frmHostG->addItemToBottomRow($dltButton);
		}
		$frmHostG->addItemToBottomRow(SPACE);
		$frmHostG->addItemToBottomRow(new CButtonCancel(url_param('config')));
		$frmHostG->show();
	}
	else{
		$config = select_config();
		
		$numrows = new CSpan(null, 'info');
		$numrows->addOption('name', 'numrows');
		$header = get_table_header(array(
						S_HOST_GROUPS_BIG,
						new CSpan(SPACE.SPACE.'|'.SPACE.SPACE, 'divider'),
						S_FOUND.': ',
						$numrows
		));
		show_table_header($header);

		$form = new CForm('hostgroups.php');
		$form->setName('groups');

		$table = new CTableInfo(S_NO_HOST_GROUPS_DEFINED);
		$table->setHeader(array(
				array(	
					new CCheckBox('all_groups', NULL, "CheckAll('".$form->GetName()."','all_groups');"),
					SPACE,
					make_sorting_link(S_NAME,'g.name')),
				' # ',
				S_MEMBERS));

		$dlt_groups = getDeletableHostGroups();
		
		$groups = CHostGroup::get(array('order'=> 'name', 'editable' => 1));
		$groupids = array_keys($groups);
		$hosts = CHost::get(array('groupids' => $groupids, 'extenduotput' => 1, 'templated_hosts' => 1));

		foreach($groups as $groupid => $group){
			$groups[$groupid]['hosts'] = array();
		}
		foreach($hosts as $hostid => $host){
			foreach($host['groups'] as $groupid){
				$groups[$groupid]['hosts'][$hostid] = $host;
			}
		}
		
//order_result($groups, 'group', ZBX_SORT_UP);
		
		foreach($groups as $groupid => $group){
			$i = 0;
			$hosts_output = array();
			
			foreach($group['hosts'] as $hostid => $host){
				$i++;
				if($i > $config['max_in_table']){
					array_push($hosts_output, ', ', new CLink('...', 'hosts.php?config=0&hostid=0&groupid='.$groupid));
					break;
				}
				$link = 'hosts.php?form=update&config=0&hostid='.$hostid;
				switch($host['status']){
					case HOST_STATUS_MONITORED:
						$style = null;
						break;
					case HOST_STATUS_TEMPLATE:
						$style = 'unknown';
						break;
					default:
						$style = 'on';
				}
				array_push($hosts_output, (empty($hosts_output) ? '' : ', '), new CLink(new CSpan($host['host'], $style), $link));
			}

			$checkbox_group = new CCheckBox('groups['.$groupid.']', NULL, NULL, $groupid);
			if(!isset($dlt_groups[$groupid])){
				$checkbox_group->addOption('disabled', 'disabled');
			}

			$table->addRow(array(
				array(
					$checkbox_group,
					SPACE,
					new CLink($group['name'], 'hostgroups.php?form=update&groupid='.$groupid, 'action')
				),
				new CLink(count($group['hosts']), 'hosts.php?groupid='.$groupid),
				new CCol((empty($hosts_output) ? '-' : $hosts_output), 'wraptext')
			));
		}
		
		$row_count = $table->getNumRows();
		$table->setFooter(new CCol(array(
			new CButtonQMessage('activate',S_ACTIVATE_SELECTED,S_ACTIVATE_SELECTED_HOST_GROUPS_Q),
			SPACE,
			new CButtonQMessage('disable',S_DISABLE_SELECTED,S_DISABLE_SELECTED_HOST_GROUPS_Q),
			SPACE,
			new CButtonQMessage('delete',S_DELETE_SELECTED,S_DELETE_SELECTED_HOST_GROUPS_Q)
		)));

		$form->addItem($table);
		$form->show();
		
		zbx_add_post_js('insert_in_element("numrows","'.$row_count.'");');
	}

include_once 'include/page_footer.php';
?>