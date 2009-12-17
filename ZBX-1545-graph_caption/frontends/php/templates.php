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
require_once('include/forms.inc.php');

$page['title'] = 'S_TEMPLATES';
$page['file'] = 'templates.php';
$page['hist_arg'] = array('groupid');

include_once('include/page_header.php');
?>
<?php
//		VAR						TYPE		OPTIONAL FLAGS			VALIDATION	EXCEPTION
	$fields=array(
		'hosts'				=> array(T_ZBX_INT,	O_OPT,	P_SYS,			DB_ID, 		NULL),
		'groups'			=> array(T_ZBX_INT, O_OPT,	P_SYS,			DB_ID, 		NULL),
		'clear_templates'	=> array(T_ZBX_INT, O_OPT,	P_SYS,		DB_ID, 		NULL),
		'templates'			=> array(T_ZBX_STR, O_OPT,	NULL,			NULL,		NULL),
		'templateid'		=> array(T_ZBX_INT,	O_OPT,	P_SYS,		DB_ID,		'isset({form})&&({form}=="update")'),
		'template_name'		=> array(T_ZBX_STR,	O_OPT,	NOT_EMPTY,	NULL,		'isset({save})'),
		'groupid'			=> array(T_ZBX_INT, O_OPT,	P_SYS,			DB_ID,		NULL),
		'twb_groupid'		=> array(T_ZBX_INT, O_OPT,	P_SYS,		DB_ID,		NULL),
		'newgroup'			=> array(T_ZBX_STR, O_OPT,	NULL,			NULL,		NULL),

		'macros_rem'		=> array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'macros'			=> array(T_ZBX_STR, O_OPT, P_SYS,			NULL,	NULL),
		'macro_new'			=> array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	'isset({macro_add})'),
		'value_new'			=> array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	'isset({macro_add})'),
		'macro_add'			=> array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'macros_del'		=> array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
// actions
		'go'				=> array(T_ZBX_STR, O_OPT,	P_SYS|P_ACT,	NULL,		NULL),
//form
		'unlink'			=> array(T_ZBX_STR, O_OPT,	P_SYS|P_ACT,	NULL,		NULL),
		'unlink_and_clear'	=> array(T_ZBX_STR, O_OPT,	P_SYS|P_ACT,	NULL,		NULL),
		'save'				=> array(T_ZBX_STR, O_OPT,	P_SYS|P_ACT,	NULL,		NULL),
		'clone'				=> array(T_ZBX_STR, O_OPT,	P_SYS|P_ACT,	NULL,		NULL),
		'full_clone'		=> array(T_ZBX_STR, O_OPT,	P_SYS|P_ACT,	NULL,		NULL),
		'delete'			=> array(T_ZBX_STR, O_OPT,	P_SYS|P_ACT,	NULL,		NULL),
		'delete_and_clear'	=> array(T_ZBX_STR, O_OPT,	P_SYS|P_ACT,	NULL,		NULL),
		'cancel'			=> array(T_ZBX_STR, O_OPT,	P_SYS,			NULL,		NULL),
// other
		'form'				=> array(T_ZBX_STR, O_OPT,	P_SYS,			NULL,		NULL),
		'form_refresh'		=> array(T_ZBX_STR, O_OPT,	NULL,			NULL,		NULL)
	);

// OUTER DATA
	check_fields($fields);
	validate_sort_and_sortorder('host', ZBX_SORT_UP);

	$_REQUEST['go'] = get_request('go', 'none');

// PERMISSIONS
	if(get_request('groupid', 0) > 0){
		$groupids = available_groups($_REQUEST['groupid'], 1);
		if(empty($groupids)) access_deny();
	}

	if(get_request('templateid', 0) > 0){
		$hostids = available_hosts($_REQUEST['templateid'], 1);
		if(empty($hostids)) access_deny();
	}
?>
<?php
/**********************************/
/* <<<--- TEMPLATE ACTIONS --->>> */
/**********************************/
// REMOVE MACROS
	if(isset($_REQUEST['macros_del']) && isset($_REQUEST['macros_rem'])){
		$macros_rem = get_request('macros_rem', array());
		foreach($macros_rem as $macro)
			unset($_REQUEST['macros'][$macro]);
	}
// ADD MACRO
	if(isset($_REQUEST['macro_add'])){
		$macro_new = get_request('macro_new');
		$value_new = get_request('value_new', null);

		$currentmacros = array_keys(get_request('macros', array()));

		if(!CUserMacro::validate(zbx_toObject($macro_new, 'macro'))){
			error(S_WRONG_MACRO.' : '.$macro_new);
			show_messages(false, '', S_MACROS);
		}
		else if(zbx_empty($value_new)){
			error(S_EMPTY_MACRO_VALUE);
			show_messages(false, '', S_MACROS);
		}
		else if(str_in_array($macro_new, $currentmacros)){
			error(S_MACRO_EXISTS.' : '.$macro_new);
			show_messages(false, '', S_MACROS);
		}
		else if(strlen($macro_new) > 64){
			error(S_MACRO_TOO_LONG.' : '.$macro_new);
			show_messages(false, '', S_MACROS);
		}
		else if(strlen($value_new) > 255){
			error(S_MACRO_VALUE_TOO_LONG.' : '.$value_new);
			show_messages(false, '', S_MACROS);
		}
		else{
			$_REQUEST['macros'][$macro_new]['macro'] = $macro_new;
			$_REQUEST['macros'][$macro_new]['value'] = $value_new;
			unset($_REQUEST['macro_new']);
			unset($_REQUEST['value_new']);
		}
	}
// unlink, unlink_and_clear
	if((isset($_REQUEST['unlink']) || isset($_REQUEST['unlink_and_clear']))){
		$_REQUEST['clear_templates'] = get_request('clear_templates', array());

		if(isset($_REQUEST['unlink'])){
			$unlink_templates = array_keys($_REQUEST['unlink']);
		}
		else{
			$unlink_templates = array_keys($_REQUEST['unlink_and_clear']);
			$_REQUEST['clear_templates'] = zbx_array_merge($_REQUEST['clear_templates'], $unlink_templates);
		}
		foreach($unlink_templates as $id) unset($_REQUEST['templates'][$id]);
	}
// clone
	else if(isset($_REQUEST['clone']) && isset($_REQUEST['templateid'])){
		unset($_REQUEST['templateid']);
		$_REQUEST['form'] = 'clone';
	}
// full_clone
	else if(isset($_REQUEST['full_clone']) && isset($_REQUEST['templateid'])){
		$_REQUEST['form'] = 'full_clone';
	}
// save
	else if(isset($_REQUEST['save'])){
		$groups = get_request('groups', array());
		$hosts = get_request('hosts', array());
		$templates = get_request('templates', array());
		$templates_clear = get_request('clear_templates', array());
		$templateid = get_request('templateid', 0);
		$newgroup = get_request('newgroup', 0);
		$template_name = get_request('template_name', '');

		$result = true;

		if(!count(get_accessible_nodes_by_user($USER_DETAILS, PERM_READ_WRITE, PERM_RES_IDS_ARRAY)))
			access_deny();


		$clone_templateid = false;
		if($_REQUEST['form'] == 'full_clone'){
			$clone_templateid = $templateid;
			$templateid = null;
		}

		DBstart();

// CREATE NEW GROUP
		$groups = zbx_toObject($groups, 'groupid');
		if(!empty($newgroup)){
			if($newgroup = CHostGroup::create(array('name' => $newgroup))){
				$groups = array_merge($groups, $newgroup);
			}
			else{
				$result = false;
			}
		}
		
		$templates = array_keys($templates);
		$templates = zbx_toObject($templates, 'templateid');		
		$templates_clear = zbx_toObject($templates_clear, 'templateid');
		
// CREATE/UPDATE TEMPLATE WITH GROUPS AND LINKED TEMPLATES {{{
		if($templateid){
			$template = array('templateid' => $templateid);			
			$result = CTemplate::update(array(
				'templateid' => $templateid, 
				'host' => $template_name, 
				'groups' => $groups,
				'templates' => $templates,
				'templates_clear' => $templates_clear
			));
			if(!$result){
				error(CTemplate::resetErrors());
				$result = false;
			}
			
			$msg_ok = S_TEMPLATE_UPDATED;
			$msg_fail = S_CANNOT_UPDATE_TEMPLATE;
		}
		else{
			if($result = CTemplate::create(array('host' => $template_name, 'groups' => $groups, 'templates' => $templates))){
				$template = reset($result);
				$templateid = $template['hostid'];
			}
			else{
				error(CTemplate::resetErrors());
				$result = false;
			}
			$msg_ok = S_TEMPLATE_ADDED;
			$msg_fail = S_CANNOT_ADD_TEMPLATE;
		}
// }}} CREATE/UPDATE TEMPLATE WITH GROUPS AND LINKED TEMPLATES

// FULL_CLONE {

		if(!zbx_empty($templateid) && $templateid && $clone_templateid && ($_REQUEST['form'] == 'full_clone')){
// Host applications
			$sql = 'SELECT * FROM applications WHERE hostid='.$clone_templateid.' AND templateid=0';
			$res = DBselect($sql);
			while($db_app = DBfetch($res)){
				add_application($db_app['name'], $templateid, 0);
			}

// Host items
			$sql = 'SELECT DISTINCT i.itemid, i.description '.
					' FROM items i '.
					' WHERE i.hostid='.$clone_templateid.
						' AND i.templateid=0 '.
					' ORDER BY i.description';
			$res = DBselect($sql);
			while($db_item = DBfetch($res)){
				$result &= (bool) copy_item_to_host($db_item['itemid'], $templateid, true);
			}

// Host triggers
			$available_triggers = get_accessible_triggers(PERM_READ_ONLY, array($clone_templateid), PERM_RES_IDS_ARRAY);

			$sql = 'SELECT DISTINCT t.triggerid, t.description '.
					' FROM triggers t, items i, functions f'.
					' WHERE i.hostid='.$clone_templateid.
						' AND f.itemid=i.itemid '.
						' AND t.triggerid=f.triggerid '.
						' AND '.DBcondition('t.triggerid', $available_triggers).
						' AND t.templateid=0 '.
					' ORDER BY t.description';

			$res = DBselect($sql);
			while($db_trig = DBfetch($res)){
				$result &= (bool) copy_trigger_to_host($db_trig['triggerid'], $templateid, true);
			}

// Host graphs
			$available_graphs = get_accessible_graphs(PERM_READ_ONLY, array($clone_templateid), PERM_RES_IDS_ARRAY);

			$sql = 'SELECT DISTINCT g.graphid, g.name '.
						' FROM graphs g, graphs_items gi,items i '.
						' WHERE '.DBcondition('g.graphid',$available_graphs).
							' AND gi.graphid=g.graphid '.
							' AND g.templateid=0 '.
							' AND i.itemid=gi.itemid '.
							' AND i.hostid='.$clone_templateid.
						' ORDER BY g.name';

			$res = DBselect($sql);
			while($db_graph = DBfetch($res)){
				$result &= (bool) copy_graph_to_host($db_graph['graphid'], $templateid, true);
			}
		}
// }
// LINK/UNLINK HOSTS {
		if($result){
			$hosts = CHost::get(array('hostids' => $hosts, 'editable' => 1, 'templated_hosts' => 1));
			$hosts = zbx_objectValues($hosts, 'hostid');
//-- unlink --
			$linked_hosts = array();
			$db_childs = get_hosts_by_templateid($templateid);
			while($db_child = DBfetch($db_childs)){
				$linked_hosts[$db_child['hostid']] = $db_child['hostid'];
			}

			$unlink_hosts = array_diff($linked_hosts, $hosts);

			foreach($unlink_hosts as $id => $value){
				$result &= unlink_template($value, $templateid, false);
			}

//-- link --
			$link_hosts = array_diff($hosts, $linked_hosts);

			$result = CTemplate::massAdd(array(
				'templates' => zbx_toObject($templateid, 'templateid'), 
				'hosts' => zbx_toObject($hosts, 'hostid')
			));
			
			
			// $template_name = DBfetch(DBselect('SELECT host FROM hosts WHERE hostid='.$templateid));

			// foreach($link_hosts as $id => $hostid){

				// $host_groups=array();
				// $db_hosts_groups = DBselect('SELECT groupid FROM hosts_groups WHERE hostid='.$hostid);
				// while($hg = DBfetch($db_hosts_groups)) $host_groups[] = $hg['groupid'];

				// $host=get_host_by_hostid($hostid);

				// $templates_tmp=get_templates_by_hostid($hostid);
				// $templates_tmp[$templateid]=$template_name['host'];

				// $result &= update_host($hostid,
					// $host['host'],$host['port'],$host['status'],$host['useip'],$host['dns'],
					// $host['ip'],$host['proxy_hostid'],$templates_tmp,$host['useipmi'],$host['ipmi_ip'],
					// $host['ipmi_port'],$host['ipmi_authtype'],$host['ipmi_privilege'],$host['ipmi_username'],
					// $host['ipmi_password'],null,$host_groups);
			// }
		}
// }
// MACROS {
		if($result){
			$macros = get_request('macros', array());
			$macrostoadd = array();

			foreach($macros as $mnum => $macro){
				$macro['hostid'] = $templateid;
				$macrostoadd[] = $macro;
			}

			if(!empty($macrostoadd))
				$result = CUserMacro::update($macrostoadd);

			if(!$result)
				error(S_ERROR_ADDING_MACRO);
		}

// } MACROS
		$result = DBend($result);

		show_messages($result, $msg_ok, $msg_fail);

		if($result){
			unset($_REQUEST['form']);
			unset($_REQUEST['templateid']);
		}
		unset($_REQUEST['save']);
	}
// delete, delete_and_clear
	else if((isset($_REQUEST['delete']) || isset($_REQUEST['delete_and_clear'])) && isset($_REQUEST['templateid'])){
		$unlink_mode = false;
		if(isset($_REQUEST['delete'])){
			$unlink_mode =  true;
		}

		//$host = get_host_by_hostid($_REQUEST['templateid']);

		DBstart();
		$result = delete_host($_REQUEST['templateid'], $unlink_mode);
		$result = DBend($result);

		show_messages($result, S_HOST_DELETED, S_CANNOT_DELETE_HOST);
		if($result){
/*				add_audit(AUDIT_ACTION_DELETE,AUDIT_RESOURCE_HOST,'Host ['.$host['host'].']');*/
			unset($_REQUEST['form']);
			unset($_REQUEST['templateid']);
		}
		unset($_REQUEST['delete']);
	}
// ---------- GO ---------
	else if(str_in_array($_REQUEST['go'], array('delete', 'delete_and_clear')) && isset($_REQUEST['templates'])){
		$unlink_mode = false;
		if(isset($_REQUEST['delete'])){
			$unlink_mode = true;
		}

		$go_result = true;
		$templates = get_request('templates', array());
		$del_hosts = CTemplate::get(array('templateids' => $templates, 'editable' => 1));
		$del_hosts = zbx_objectValues($del_hosts, 'templateid');

		DBstart();
		$go_result = delete_host($del_hosts, $unlink_mode);
		$go_result = DBend($go_result);

		show_messages($go_result, S_TEMPLATE_DELETED, S_CANNOT_DELETE_TEMPLATE);
	}

	if(($_REQUEST['go'] != 'none') && isset($go_result) && $go_result){
		$url = new CUrl();
		$path = $url->getPath();
		insert_js('cookie.eraseArray("'.$path.'")');
	}
?>
<?php

	$params=array();
	$options = array('only_current_node');
	foreach($options as $option) $params[$option] = 1;

	$PAGE_GROUPS = get_viewed_groups(PERM_READ_WRITE, $params);
	$PAGE_HOSTS = get_viewed_hosts(PERM_READ_WRITE, $PAGE_GROUPS['selected'], $params);

	validate_group($PAGE_GROUPS,$PAGE_HOSTS);

	$frmForm = new CForm();
	$cmbConf = new CComboBox('config', 'templates.php', 'javascript: redirect(this.options[this.selectedIndex].value);');
		$cmbConf->addItem('templates.php', S_TEMPLATES);
		$cmbConf->addItem('hosts.php', S_HOSTS);
		$cmbConf->addItem('items.php', S_ITEMS);
		$cmbConf->addItem('triggers.php', S_TRIGGERS);
		$cmbConf->addItem('graphs.php', S_GRAPHS);
		$cmbConf->addItem('applications.php', S_APPLICATIONS);
	$frmForm->addItem($cmbConf);

	if(!isset($_REQUEST['form'])){
		$frmForm->addItem(new CButton('form', S_CREATE_TEMPLATE));
	}

	show_table_header(S_CONFIGURATION_OF_TEMPLATES, $frmForm);
	echo SBR;
?>
<?php
	if(isset($_REQUEST['form'])){

		$templateid = get_request('templateid', 0);
		$template_name = get_request('template_name', '');
		$newgroup = get_request('newgroup', '');
		$templates = get_request('templates', array());
		$clear_templates = get_request('clear_templates', array());

		$frm_title = S_TEMPLATE;

		if($templateid > 0){
			$db_host = get_host_by_hostid($templateid);
			$template_name = $db_host['host'];
			$frm_title .= SPACE.' ['.$template_name.']';

			$original_templates = get_templates_by_hostid($templateid);
		}
		else{
			$original_templates = array();
		}

		$frmHost = new CForm('templates.php');
		$frmHost->setName('tpl_for');

		$frmHost->addVar('form', get_request('form', 1));
		$from_rfr = get_request('form_refresh', 0);
		$frmHost->addVar('form_refresh', $from_rfr+1);
		$frmHost->addVar('clear_templates', $clear_templates);
		$frmHost->addVar('groupid', $_REQUEST['groupid']);

		if($templateid){
			$frmHost->addVar('templateid', $templateid);
		}

		if(($templateid > 0) && !isset($_REQUEST['form_refresh'])){
// get template groups from db
			$options = array('hostids' => $templateid, 'editable' => 1);
			$groups = CHostGroup::get($options);
			$groups = zbx_objectValues($groups, 'groupid');

// get template hosts from db
			$params = array('templateids' => $templateid, 'editable' => 1, 'templated_hosts' => 1);
			$hosts_linked_to = CHost::get($params);
			$hosts_linked_to = zbx_objectValues($hosts_linked_to, 'hostid');
			$hosts_linked_to = zbx_toHash($hosts_linked_to, 'hostid');
			$templates = $original_templates;
		}
		else{
			$groups = get_request('groups', array());
			$hosts_linked_to = get_request('hosts', array());
		}

		$clear_templates = array_intersect($clear_templates, array_keys($original_templates));
		$clear_templates = array_diff($clear_templates, array_keys($templates));
		natcasesort($templates);
		$frmHost->addVar('clear_templates', $clear_templates);

// TEMPLATE WIDGET {
		$template_tbl = new CTable('', 'tablestripped');
		$template_tbl->setOddRowClass('form_odd_row');
		$template_tbl->setEvenRowClass('form_even_row');
// FORM ITEM : Template name text box [  ]
		$template_tbl->addRow(array(S_NAME, new CTextBox('template_name', $template_name, 54)));

// FORM ITEM : Groups tween box [  ] [  ]
// get all Groups
		$group_tb = new CTweenBox($frmHost, 'groups', $groups, 10);
		$options = array('editable' => 1, 'extendoutput' => 1);
		$all_groups = CHostGroup::get($options);
		order_result($all_groups, 'name');

		foreach($all_groups as $gnum => $group){
			$group_tb->addItem($group['groupid'], $group['name']);
		}
		$template_tbl->addRow(array(S_GROUPS, $group_tb->get(S_IN.SPACE.S_GROUPS,S_OTHER.SPACE.S_GROUPS)));


// FORM ITEM : new group text box [  ]
		$template_tbl->addRow(array(S_NEW_GROUP, new CTextBox('newgroup', $newgroup)));

// FORM ITEM : linked Hosts tween box [  ] [  ]
		// $options = array('editable' => 1, 'extendoutput' => 1);
		// $twb_groups = CHostGroup::get($options);
		$twb_groupid = get_request('twb_groupid', 0);
		if($twb_groupid == 0){
			$gr = reset($all_groups);
			$twb_groupid = $gr['groupid'];
		}
		$cmbGroups = new CComboBox('twb_groupid', $twb_groupid, 'submit()');
		foreach($all_groups as $gnum => $group){
			$cmbGroups->addItem($group['groupid'], $group['name']);
		}

		$host_tb = new CTweenBox($frmHost, 'hosts', $hosts_linked_to, 25);

// get hosts from selected twb_groupid combo
		$params = array(
			'groupids' => $twb_groupid,
			'templated_hosts' => 1,
			'editable' => 1,
			'extendoutput' => 1);
		$db_hosts = CHost::get($params);
		order_result($db_hosts, 'host');

		foreach($db_hosts as $hnum => $db_host){
			if(isset($hosts_linked_to[$db_host['hostid']])) continue;// add all except selected hosts
			$host_tb->addItem($db_host['hostid'], $db_host['host']);
		}

// select selected hosts and add them
		$params = array(
			'hostids' => $hosts_linked_to,
			'templated_hosts' => 1,
			'editable' => 1,
			'extendoutput' => 1);
		$db_hosts = CHost::get($params);
		order_result($db_hosts, 'host');
		foreach($db_hosts as $hnum => $db_host){
			$host_tb->addItem($db_host['hostid'], $db_host['host']);
		}

		$template_tbl->addRow(array(S_HOSTS.'|'.S_TEMPLATES, $host_tb->Get(S_IN, array(S_OTHER.SPACE.'|'.SPACE.S_GROUP.SPACE,$cmbGroups))));

// FORM ITEM : linked Template table
		$template_table = new CTable();
		$template_table->setCellPadding(0);
		$template_table->setCellSpacing(0);
		foreach($templates as $tid => $tname){
			$frmHost->addVar('templates['.$tid.']', $tname);
			$template_table->addRow(array(
				$tname,
				new CButton('unlink['.$tid.']', S_UNLINK),
				isset($original_templates[$tid]) ? new CButton('unlink_and_clear['.$tid.']', S_UNLINK_AND_CLEAR) : SPACE
			));
		}

		$template_tbl->addRow(array(S_LINK_WITH_TEMPLATE, array(
			$template_table,
			new CButton('add_template', S_ADD,
				"return PopUp('popup.php?dstfrm=".$frmHost->GetName().
				"&dstfld1=new_template&srctbl=templates&srcfld1=hostid&srcfld2=host".
				url_param($templates,false,'existed_templates')."',450,450)", 'T')
		)));

// FULL CLONE {
		if($_REQUEST['form'] == 'full_clone'){
// FORM ITEM : Template items
			$items_lbx = new CListBox('items', null, 8);
			$items_lbx->setAttribute('disabled', 'disabled');

			$options = array('editable' => 1, 'hostids' => $templateid, 'extendoutput' => 1);
			$template_items = CItem::get($options);

			if(empty($template_items)){
				$items_lbx->setAttribute('style', 'width: 200px;');
			}
			else{
				foreach($template_items as $inum => $titem){
					$item_description = item_description($titem);
					$items_lbx->addItem($titem['itemid'], $item_description);
				}
			}
			$template_tbl->addRow(array(S_ITEMS, $items_lbx));


// FORM ITEM : Template triggers
			$trig_lbx = new CListBox('triggers', null, 8);
			$trig_lbx->setAttribute('disabled', 'disabled');

			$options = array('editable' => 1, 'hostids' => $templateid, 'extendoutput' => 1);
			$template_triggers = CTrigger::get($options);

			if(empty($template_triggers)){
				$trig_lbx->setAttribute('style','width: 200px;');
			}
			else{
				foreach($template_triggers as $tnum => $ttrigger){
					$trigger_description = expand_trigger_description($ttrigger['triggerid']);
					$trig_lbx->addItem($ttrigger['triggerid'], $trigger_description);
				}
			}
			$template_tbl->addRow(array(S_TRIGGERS, $trig_lbx));


// FORM ITEM : Host graphs
			$graphs_lbx = new CListBox('graphs', null, 8);
			$graphs_lbx->setAttribute('disabled', 'disabled');

			$options = array('editable' => 1, 'hostids' => $templateid, 'extendoutput' => 1);
			$template_graphs = CGraph::get($options);

			if(empty($template_graphs)){
				$graphs_lbx->setAttribute('style','width: 200px;');
			}
			else{
				foreach($template_graphs as $tnum => $tgraph){
					$graphs_lbx->addItem($tgraph['graphid'], $tgraph['name']);
				}
			}
			$template_tbl->addRow(array(S_GRAPHS, $graphs_lbx));
		}
// FULL CLONE }

		$host_footer = array();
		$host_footer[] = new CButton('save', S_SAVE);
		if(($templateid > 0) && ($_REQUEST['form'] != 'full_clone')){
			$host_footer[] = SPACE;
			$host_footer[] = new CButton('clone', S_CLONE);
			$host_footer[] = SPACE;
			$host_footer[] = new CButton('full_clone', S_FULL_CLONE);
			$host_footer[] = SPACE;
			$host_footer[] = new CButtonDelete(S_DELETE_SELECTED_HOST_Q, url_param('form').url_param('templateid').url_param('groupid'));
			$host_footer[] = SPACE;
			$host_footer[] = new CButtonQMessage('delete_and_clear', 'Delete AND clear', S_DELETE_SELECTED_HOSTS_Q, url_param('form').
				url_param('templateid').url_param('groupid'));
		}
		array_push($host_footer, SPACE, new CButtonCancel(url_param('groupid')));

		$host_footer = new CCol($host_footer);
		$host_footer->setColSpan(2);
		$template_tbl->setFooter($host_footer);
		$template_wdgt = new CWidget();
		$template_wdgt->setClass('header');
		$template_wdgt->addHeader($frm_title);
		$template_wdgt->addItem($template_tbl);
// } TEMPLATE WIDGET


// MACROS WIDGET {
		$macros_wdgt = get_macros_widget($templateid);
// } MACROS WIDGET

		$left_table = new CTable();
		$left_table->setCellPadding(4);
		$left_table->setCellSpacing(4);
		$left_table->addRow($template_wdgt);

		$right_table = new CTable();
		$right_table->setCellPadding(4);
		$right_table->setCellSpacing(4);
		$right_table->addRow($macros_wdgt);

		$td_l = new CCol($left_table);
		$td_l->setAttribute('valign','top');
		$td_r = new CCol($right_table);
		$td_r->setAttribute('valign','top');

		$outer_table = new CTable();
		$outer_table->addRow(array($td_l, $td_r));

		$frmHost->addItem($outer_table);
		$frmHost->show();
	}
	else{
// TABLE WITH TEMPLATES
		$template_wdgt = new CWidget();

		$frmForm = new CForm();
		$frmForm->setMethod('get');

// combo for group selection
		$groups = CHostGroup::get(array('editable' => 1, 'extendoutput' => 1));
		order_result($groups, 'name');

		$cmbGroups = new CComboBox('groupid', $PAGE_GROUPS['selected'], 'javascript: submit();');
		foreach($PAGE_GROUPS['groups'] as $groupid => $name){
			$cmbGroups->addItem($groupid, $name);
		}
		$frmForm->addItem(array(S_GROUP.SPACE, $cmbGroups));

// table header
		$numrows = new CDiv();
		$numrows->setAttribute('name', 'numrows');

		$template_wdgt->addHeader(S_TEMPLATES_BIG, $frmForm);
		$template_wdgt->addHeader($numrows);
//------

		$form = new CForm();
		$form->setName('templates');

		$table = new CTableInfo(S_NO_HOSTS_DEFINED);
		$table->setHeader(array(
			new CCheckBox('all_templates', NULL, "checkAll('".$form->getName()."', 'all_templates', 'templates');"),
			make_sorting_header(S_TEMPLATES, 'host'),
			S_APPLICATIONS,
			S_ITEMS,
			S_TRIGGERS,
			S_GRAPHS,
			S_LINKED_TEMPLATES,
			S_LINKED_TO
		));

//$config = select_config();
// get templates

		$sortfield = getPageSortField('host');
		$sortorder = getPageSortOrder();
		$options = array(
			'extendoutput' => 1,
			'editable' => 1,
			'sortfield' => $sortfield,
			'sortorder' => $sortorder,
			'limit' => ($config['search_limit']+1)
		);

		if(($PAGE_GROUPS['selected'] > 0) || empty($PAGE_GROUPS['groupids'])){
			$options['groupids'] = $PAGE_GROUPS['selected'];
		}
		$templates = CTemplate::get($options);

		order_result($templates, $sortfield, $sortorder);
		$paging = getPagingLine($templates);
//--------

		$options = array(
			'templateids' => zbx_objectValues($templates, 'templateid'),
			'extendoutput' => 1,
			'select_hosts' => 1,
			'select_templates' => 1,
			'select_items' => 1,
			'select_triggers' => 1,
			'select_graphs' => 1,
			'select_applications' => 1,
			'nopermissions' => 1
		);

		$templates = CTemplate::get($options);
		order_result($templates, $sortfield, $sortorder);
//-----

		foreach($templates as $tnum => $template){
			$templates_output = array();
			if($template['proxy_hostid']){
				$proxy = get_host_by_hostid($template['proxy_hostid']);
				$templates_output[] = $proxy['host'].':';
			}
			$templates_output[] = new CLink($template['host'], 'templates.php?form=update&templateid='.$template['templateid'].url_param('groupid'));

			$applications = array(new CLink(S_APPLICATIONS,'applications.php?groupid='.$PAGE_GROUPS['selected'].'&hostid='.$template['templateid']),
				' ('.count($template['applications']).')');
			$items = array(new CLink(S_ITEMS,'items.php?groupid='.$PAGE_GROUPS['selected'].'&hostid='.$template['templateid']),
				' ('.count($template['items']).')');
			$triggers = array(new CLink(S_TRIGGERS,'triggers.php?groupid='.$PAGE_GROUPS['selected'].'&hostid='.$template['templateid']),
				' ('.count($template['triggers']).')');
			$graphs = array(new CLink(S_GRAPHS,'graphs.php?groupid='.$PAGE_GROUPS['selected'].'&hostid='.$template['templateid']),
				' ('.count($template['graphs']).')');

			$i = 0;
			$linked_templates_output = array();
			order_result($template['templates'], 'host');
			foreach($template['templates'] as $snum => $linked_template){
				$i++;
				if($i > $config['max_in_table']){
					$linked_templates_output[] = '...';
					$linked_templates_output[] = '//empty element for array_pop';
					break;
				}

				$url = 'templates.php?form=update&templateid='.$linked_template['templateid'].url_param('groupid');
				$linked_templates_output[] = new CLink($linked_template['host'], $url, 'unknown');
				$linked_templates_output[] = ', ';
			}
			array_pop($linked_templates_output);


			$i = 0;
			$linked_to_hosts_output = array();
			order_result($template['hosts'], 'host');
			foreach($template['hosts'] as $snum => $linked_to_host){
				$i++;
				if($i > $config['max_in_table']){
					$linked_to_hosts_output[] = '...';
					$linked_to_hosts_output[] = '//empty element for array_pop';
					break;
				}

				switch($linked_to_host['status']){
					case HOST_STATUS_NOT_MONITORED:
						$style = 'on';
						$url = 'hosts.php?form=update&hostid='.$linked_to_host['hostid'].'&groupid='.$PAGE_GROUPS['selected'];
					break;
					case HOST_STATUS_TEMPLATE:
						$style = 'unknown';
						$url = 'templates.php?form=update&templateid='.$linked_to_host['hostid'];
					break;
					default:
						$style = null;
						$url = 'hosts.php?form=update&hostid='.$linked_to_host['hostid'].'&groupid='.$PAGE_GROUPS['selected'];
					break;
				}

				$linked_to_hosts_output[] = new CLink($linked_to_host['host'], $url, $style);
				$linked_to_hosts_output[] = ', ';
			}
			array_pop($linked_to_hosts_output);


			$table->addRow(array(
				new CCheckBox('templates['.$template['templateid'].']', NULL, NULL, $template['templateid']),
				$templates_output,
				$applications,
				$items,
				$triggers,
				$graphs,
				(empty($linked_templates_output) ? '-' : new CCol($linked_templates_output,'wraptext')),
				(empty($linked_to_hosts_output) ? '-' : new CCol($linked_to_hosts_output,'wraptext'))
			));
		}

// GO{
		$goBox = new CComboBox('go');

		$goOption = new CComboItem('delete',S_DELETE_SELECTED);
		$goOption->setAttribute('confirm','Delete selected templates?');
		$goBox->addItem($goOption);

		$goOption = new CComboItem('delete_and_clear',S_DELETE_SELECTED_WITH_LINKED_ELEMENTS);
		$goOption->setAttribute('confirm','Warning: this will delete selected templates and clear all linked hosts?');
		$goBox->addItem($goOption);

// goButton name is necessary!!!
		$goButton = new CButton('goButton',S_GO);
		$goButton->setAttribute('id','goButton');
		zbx_add_post_js('chkbxRange.pageGoName = "templates";');

		$footer = get_table_header(new CCol(array($goBox, $goButton)));
// }GO

// PAGING FOOTER
		$table = array($paging,$table,$paging,$footer);
//---------

		$form->addItem($table);

		$template_wdgt->addItem($form);
		$template_wdgt->show();
	}

include_once('include/page_footer.php');
?>