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
	require_once('include/triggers.inc.php');
	require_once('include/forms.inc.php');

	$page['title'] = "S_CONFIGURATION_OF_TRIGGERS";
	$page["file"] = "triggers.php";
	$page['hist_arg'] = array('hostid','groupid');

	include_once('include/page_header.php');

	$_REQUEST['config'] = get_request('config', 'triggers.php');
?>
<?php
//		VAR			TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
	$fields=array(
//  NEW  templates.php; hosts.php; items.php; triggers.php; graphs.php; maintenances.php;
// 	OLD  0 - hosts; 1 - groups; 2 - linkages; 3 - templates; 4 - applications; 5 - Proxies; 6 - maintenance
		'config'=>			array(T_ZBX_STR, O_OPT, P_SYS,	NULL,	NULL),
		
		'groupid'=>			array(T_ZBX_INT, O_OPT,	 P_SYS,	DB_ID, null),
		'hostid'=>			array(T_ZBX_INT, O_OPT,  P_SYS,	DB_ID, null),

		'triggerid'=>		array(T_ZBX_INT, O_OPT,  P_SYS,	DB_ID,'(isset({form})&&({form}=="update"))'),

		'copy_type'	=>		array(T_ZBX_INT, O_OPT,	 P_SYS,	IN('0,1'),'isset({copy})'),
		'copy_mode'	=>		array(T_ZBX_INT, O_OPT,	 P_SYS,	IN('0'),NULL),

		'type'=>			array(T_ZBX_INT, O_OPT,  NULL, 		IN('0,1'),	'isset({save})'),
		'description'=>		array(T_ZBX_STR, O_OPT,  NULL,	NOT_EMPTY,'isset({save})'),
		'expression'=>		array(T_ZBX_STR, O_OPT,  NULL,	NOT_EMPTY,'isset({save})'),
		'priority'=>		array(T_ZBX_INT, O_OPT,  NULL,  IN('0,1,2,3,4,5'),'isset({save})'),
		'comments'=>		array(T_ZBX_STR, O_OPT,  NULL,	NULL,'isset({save})'),
		'url'=>				array(T_ZBX_STR, O_OPT,  NULL,	NULL,'isset({save})'),
		'status'=>			array(T_ZBX_STR, O_OPT,  NULL,	NULL, NULL),

		'dependencies'=>	array(T_ZBX_INT, O_OPT,  NULL,	DB_ID, NULL),
		'new_dependence'=>	array(T_ZBX_INT, O_OPT,  NULL,	DB_ID.'{}>0','isset({add_dependence})'),
		'rem_dependence'=>	array(T_ZBX_INT, O_OPT,  NULL,	DB_ID, NULL),

		'g_triggerid'=>		array(T_ZBX_INT, O_OPT,  NULL,	DB_ID, NULL),
		'copy_targetid'=>	array(T_ZBX_INT, O_OPT,	NULL,	DB_ID, NULL),
		'filter_groupid'=>	array(T_ZBX_INT, O_OPT, P_SYS,	DB_ID, 'isset({copy})&&(isset({copy_type})&&({copy_type}==0))'),

		'showdisabled'=>	array(T_ZBX_INT, O_OPT, P_SYS, IN('0,1'),	NULL),
/* mass update*/
		'massupdate'=>		array(T_ZBX_STR, O_OPT, P_SYS,	NULL,	NULL),
		'visible'=>			array(T_ZBX_STR, O_OPT,	null, 	null,	null),
// Actions
		'go'=>					array(T_ZBX_STR, O_OPT, P_SYS|P_ACT, NULL, NULL),
// form
		'add_dependence'=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'del_dependence'=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'group_enable'=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'group_disable'=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'group_delete'=>	array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'copy'=>			array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'clone'=>			array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'save'=>			array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'mass_save'=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'delete'=>			array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	NULL,	NULL),
		'cancel'=>			array(T_ZBX_STR, O_OPT, P_SYS,	NULL,	NULL),
/* other */
		'form'=>			array(T_ZBX_STR, O_OPT, P_SYS,	NULL,	NULL),
		'form_refresh'=>	array(T_ZBX_INT, O_OPT,	NULL,	NULL,	NULL)
	);

	$_REQUEST['showdisabled'] = get_request('showdisabled', get_profile('web.triggers.showdisabled', 0));

	check_fields($fields);
	validate_sort_and_sortorder('t.description',ZBX_SORT_UP);

	$_REQUEST['go'] = get_request('go','none');
?>
<?php	
// triggerid permission check
	$available_triggers = CTrigger::get(array('editable' => 1));
	
	if(isset($_REQUEST['triggerid']))
		//if(!check_right_on_trigger_by_triggerid(PERM_READ_WRITE, $_REQUEST['triggerid']))
		if(!in_array($_REQUEST['triggerid'], $available_triggers))
			access_deny();
//----

	$showdisabled = get_request('showdisabled', 0);
	update_profile('web.triggers.showdisabled',$showdisabled,PROFILE_TYPE_INT);

	
/* FORM ACTIONS */
	if(isset($_REQUEST['clone']) && isset($_REQUEST['triggerid'])){
		unset($_REQUEST['triggerid']);
		$_REQUEST['form'] = 'clone';
	}
	else if(isset($_REQUEST['mass_save']) && isset($_REQUEST['g_triggerid'])){
		show_messages();

		$result = false;

		$visible = get_request('visible',array());
		$_REQUEST['dependencies'] = get_request('dependencies',array());

		$triggers = $_REQUEST['g_triggerid'];
		$triggers = zbx_uint_array_intersect($triggers, $available_triggers);

		DBstart();
		foreach($triggers as $id => $triggerid){
			$db_trig = get_trigger_by_triggerid($triggerid);
			$db_trig['dependencies'] = get_trigger_dependencies_by_triggerid($triggerid);

			foreach($db_trig as $key => $value){
				if(isset($visible[$key])){
					$db_trig[$key] = $_REQUEST[$key];
				}
			}

			$result2=update_trigger($db_trig['triggerid'],
				null,null,null,
				$db_trig['priority'],null,null,null,
				$db_trig['dependencies'],null);

			$result |= $result2;
		}
		$result = DBend($result);

		show_messages($result, S_TRIGGER_UPDATED, S_CANNOT_UPDATE_TRIGGER);
		if($result){
			unset($_REQUEST['massupdate']);
			unset($_REQUEST['form']);
		}
	}
	else if(isset($_REQUEST['save'])){
		show_messages();

		if(!check_right_on_trigger_by_expression(PERM_READ_WRITE, $_REQUEST['expression']))
			access_deny();

		$now = time();
		$status = isset($_REQUEST['status'])?TRIGGER_STATUS_DISABLED:TRIGGER_STATUS_ENABLED;

		$type = $_REQUEST['type'];

		$deps = get_request('dependencies',array());

		if(isset($_REQUEST['triggerid'])){
			$trigger_data = get_trigger_by_triggerid($_REQUEST['triggerid']);
			if($trigger_data['templateid']){
				$_REQUEST['description'] = $trigger_data['description'];
				$_REQUEST['expression'] = explode_exp($trigger_data['expression'],0);
			}

			DBstart();
			$result = update_trigger($_REQUEST['triggerid'],
				$_REQUEST['expression'],$_REQUEST['description'],$type,
				$_REQUEST['priority'],$status,$_REQUEST['comments'],$_REQUEST['url'],
				$deps, $trigger_data['templateid']);
			$result = DBend($result);

			$triggerid = $_REQUEST['triggerid'];

			show_messages($result, S_TRIGGER_UPDATED, S_CANNOT_UPDATE_TRIGGER);
		}
		else {
			DBstart();
			$triggerid = add_trigger($_REQUEST['expression'],$_REQUEST['description'],$type,
				$_REQUEST['priority'],$status,$_REQUEST['comments'],$_REQUEST['url'],
				$deps);
			$result = DBend($triggerid);
			show_messages($result, S_TRIGGER_ADDED, S_CANNOT_ADD_TRIGGER);
		}
		if($result)
			unset($_REQUEST['form']);
	}
	else if(isset($_REQUEST['delete'])&&isset($_REQUEST['triggerid'])){
		$result = false;

		if(!isset($available_triggers[$_REQUEST['triggerid']]))
			access_deny();

		if($trigger_data = DBfetch(
			DBselect('SELECT DISTINCT t.triggerid,t.description,t.expression,h.host '.
				' FROM triggers t '.
					' LEFT JOIN functions f on t.triggerid=f.triggerid '.
					' LEFT JOIN items i on f.itemid=i.itemid '.
					' LEFT JOIN hosts h on i.hostid=h.hostid '.
				' WHERE t.triggerid='.$_REQUEST['triggerid'].
					' AND t.templateid=0')
			))
		{
			DBstart();
			$result = delete_trigger($_REQUEST['triggerid']);
			$result = DBend($result);
			if($result){
				add_audit_ext(AUDIT_ACTION_DELETE, AUDIT_RESOURCE_TRIGGER, $_REQUEST['triggerid'], $trigger_data['description'], NULL, NULL, NULL);
			}
		}

		show_messages($result, S_TRIGGER_DELETED, S_CANNOT_DELETE_TRIGGER);

		if($result){
			//add_audit(AUDIT_ACTION_DELETE, AUDIT_RESOURCE_TRIGGER,
			//	S_TRIGGER.' ['.$_REQUEST['triggerid'].'] ['.expand_trigger_description_by_data($trigger_data).'] ');
			unset($_REQUEST['form']);
			unset($_REQUEST['triggerid']);
		}
	}
/* DEPENDENCE ACTIONS */
	else if(isset($_REQUEST['add_dependence'])&&isset($_REQUEST['new_dependence'])){
		if(!isset($_REQUEST['dependencies']))
			$_REQUEST['dependencies'] = array();

			foreach($_REQUEST['new_dependence'] as $triggerid) {
			if(!uint_in_array($triggerid, $_REQUEST['dependencies']))
				array_push($_REQUEST['dependencies'], $triggerid);
		}
	}
	else if(isset($_REQUEST['del_dependence'])&&isset($_REQUEST['rem_dependence'])){
		if(isset($_REQUEST['dependencies'])){
			foreach($_REQUEST['dependencies'] as $key => $val){
				if(!uint_in_array($val, $_REQUEST['rem_dependence']))	continue;
				unset($_REQUEST['dependencies'][$key]);
			}
		}
	}
// ------- GO ---------
	else if(str_in_array($_REQUEST['go'], array('activate','disable')) && isset($_REQUEST['g_triggerid'])){

		$_REQUEST['g_triggerid'] = array_intersect($_REQUEST['g_triggerid'],$available_triggers);

		$sql = 'SELECT triggerid, description FROM triggers'.
				' WHERE '.DBcondition('triggerid',$_REQUEST['g_triggerid']);
		$result = DBSelect($sql);
		while($trigger = DBfetch($result)) {
			$triggers[$trigger['triggerid']] = $trigger;
		}

		if(($_REQUEST['go'] == 'activate')){
			$status = TRIGGER_STATUS_ENABLED;
			$status_old = array('status'=>0);
			$status_new = array('status'=>1);
		}
		else {
			$status = TRIGGER_STATUS_DISABLED;
			$status_old = array('status'=>1);
			$status_new = array('status'=>0);
		}

		DBstart();
		$result = update_trigger_status($_REQUEST['g_triggerid'],$status);

		if($result){
			foreach($_REQUEST['g_triggerid'] as $id => $triggerid){
				$serv_status = (isset($_REQUEST['group_enable'])) ? get_service_status_of_trigger($triggerid) : 0;
				update_services($triggerid, $serv_status); // updating status to all services by the dependency
				add_audit_ext(AUDIT_ACTION_UPDATE, AUDIT_RESOURCE_TRIGGER, $triggerid, $triggers[$triggerid]['description'], 'triggers', $status_old, $status_new);
			}
		}
		$result = DBend($result);
		show_messages($result, S_STATUS_UPDATED, S_CANNOT_UPDATE_STATUS);

	}
	else if(isset($_REQUEST['copy']) && isset($_REQUEST['g_triggerid']) && ($_REQUEST['go'] == 'copy_to')){
		if(isset($_REQUEST['copy_targetid']) && ($_REQUEST['copy_targetid'] > 0) && isset($_REQUEST['copy_type'])){
			if(0 == $_REQUEST['copy_type']){ /* hosts */
				$hosts_ids = $_REQUEST['copy_targetid'];
			}
			else{ /* groups */
				$hosts_ids = array();
				$group_ids = $_REQUEST['copy_targetid'];

				$db_hosts = DBselect('SELECT DISTINCT h.hostid '.
					' FROM hosts h, hosts_groups hg'.
					' WHERE h.hostid=hg.hostid '.
						' AND '.DBcondition('hg.groupid',$group_ids));
				while($db_host = DBfetch($db_hosts)){
					array_push($hosts_ids, $db_host['hostid']);
				}
			}

			$result = false;
			$new_triggerids = array();

			DBstart();
			foreach($hosts_ids as $num => $host_id){
				foreach($_REQUEST['g_triggerid'] as $tnum => $trigger_id){
					$newtrigid = copy_trigger_to_host($trigger_id, $host_id, true);

					$new_triggerids[$trigger_id] = $newtrigid;
					$result |= (bool) $newtrigid;
				}

				replace_triggers_depenedencies($new_triggerids);
			}

			$result = DBend($result);
			$_REQUEST['go'] = 'none';
		}
		else{
			error('No target selection.');
		}
		show_messages($result, S_TRIGGER_ADDED, S_CANNOT_ADD_TRIGGER);
	}
	else if(($_REQUEST['go'] == 'delete') && isset($_REQUEST['g_triggerid'])){
		$_REQUEST['g_triggerid'] = array_intersect($_REQUEST['g_triggerid'],$available_triggers);

		DBstart();
		foreach($_REQUEST['g_triggerid'] as $id => $triggerid){
			$row = DBfetch(DBselect('SELECT triggerid,templateid FROM triggers t WHERE t.triggerid='.$triggerid));
			if($row['templateid'] <> 0){
				unset($_REQUEST['g_triggerid'][$id]);
				continue;
			}
			$description = expand_trigger_description($triggerid);
			add_audit_ext(AUDIT_ACTION_DELETE, AUDIT_RESOURCE_TRIGGER, $triggerid, $description, NULL, NULL, NULL);
		}
		$result = delete_trigger($_REQUEST['g_triggerid']);

		$result = DBend($result);
		show_messages($result, S_TRIGGERS_DELETED, S_CANNOT_DELETE_TRIGGERS);
	}
?>
<?php
	if(isset($_REQUEST['hostid']) && !isset($_REQUEST['groupid']) && !isset($_REQUEST['triggerid'])){
		$sql = 'SELECT DISTINCT hg.groupid '.
				' FROM hosts_groups hg '.
				' WHERE hg.hostid='.$_REQUEST['hostid'];
		if($group=DBfetch(DBselect($sql, 1))){
			$_REQUEST['groupid'] = $group['groupid'];
		}
	}

	if(isset($_REQUEST['triggerid']) && ($_REQUEST['triggerid']>0)){
		$sql_from = '';
		$sql_where = '';
		if(isset($_REQUEST['groupid']) && ($_REQUEST['groupid'] > 0)){
			$sql_where.= ' AND hg.groupid='.$_REQUEST['groupid'];
		}

		if(isset($_REQUEST['hostid']) && ($_REQUEST['hostid'] > 0)){
			$sql_where.= ' AND hg.hostid='.$_REQUEST['hostid'];
		}

		$sql = 'SELECT DISTINCT hg.groupid, hg.hostid '.
				' FROM hosts_groups hg '.
				' WHERE EXISTS( SELECT i.itemid '.
								' FROM items i, functions f'.
								' WHERE i.hostid=hg.hostid '.
									' AND f.itemid=i.itemid '.
									' AND f.triggerid='.$_REQUEST['triggerid'].')'.
						$sql_where;
		if($host_group = DBfetch(DBselect($sql,1))){
			if(!isset($_REQUEST['groupid']) || !isset($_REQUEST['hostid'])){
				$_REQUEST['groupid'] = $host_group['groupid'];
				$_REQUEST['hostid'] = $host_group['hostid'];
			}
			else if(($_REQUEST['groupid']!=$host_group['groupid']) || ($_REQUEST['hostid']!=$host_group['hostid'])){
				$_REQUEST['triggerid'] = 0;
			}
		}
		else{
//			$_REQUEST['triggerid'] = 0;
		}
	}

	$params=array();
	$options = array('with_items','only_current_node','not_proxy_hosts');
	foreach($options as $option) $params[$option] = 1;

	$PAGE_GROUPS = get_viewed_groups(PERM_READ_WRITE, $params);
	$PAGE_HOSTS = get_viewed_hosts(PERM_READ_WRITE, $PAGE_GROUPS['selected'], $params);

	validate_group_with_host($PAGE_GROUPS,$PAGE_HOSTS);

	$available_groups = $PAGE_GROUPS['groupids'];
	$available_hosts = $PAGE_HOSTS['hostids'];

	$available_triggers = CTrigger::get(array('editable' => 1, 'hostids' => $PAGE_HOSTS['hostids']));
?>
<?php

	$form = new CForm();
	$form->setMethod('get');
	
// Config
	$cmbConf = new CComboBox('config','triggers.php','javascript: submit()');
	$cmbConf->setAttribute('onchange','javascript: redirect(this.options[this.selectedIndex].value);');	
		$cmbConf->addItem('templates.php',S_TEMPLATES);
		$cmbConf->addItem('hosts.php',S_HOSTS);
		$cmbConf->addItem('items.php',S_ITEMS);
		$cmbConf->addItem('triggers.php',S_TRIGGERS);
		$cmbConf->addItem('graphs.php',S_GRAPHS);
		$cmbConf->addItem('applications.php',S_APPLICATIONS);
		
	$form->addItem($cmbConf);
	if(!isset($_REQUEST['form'])){
		$form->addItem(new CButton('form', S_CREATE_TRIGGER));
	}
	
	show_table_header(S_CONFIGURATION_OF_TRIGGERS_BIG, $form);
	echo SBR;
?>
<?php
	if(($_REQUEST['go'] == 'massupdate') && isset($_REQUEST['g_triggerid'])){
		insert_mass_update_trigger_form();
	}
	else if(isset($_REQUEST['form'])){
/* FORM */
		insert_trigger_form();
	}
	else if(($_REQUEST['go'] == 'copy_to') && isset($_REQUEST['g_triggerid'])){
		insert_copy_elements_to_forms('g_triggerid');
	}
	else{
/* TABLE */
		$r_form = new CForm();
		$r_form->setMethod('get');
		$r_form->addItem(array('[',
			new CLink($showdisabled ? S_HIDE_DISABLED_TRIGGERS : S_SHOW_DISABLED_TRIGGERS,
				'triggers.php?showdisabled='.($showdisabled ? 0 : 1),NULL),
			']', SPACE));

		$cmbGroups = new CComboBox('groupid',$PAGE_GROUPS['selected'],'javascript: submit();');
		$cmbHosts = new CComboBox('hostid',$PAGE_HOSTS['selected'],'javascript: submit();');

		foreach($PAGE_GROUPS['groups'] as $groupid => $name){
			$cmbGroups->addItem($groupid, get_node_name_by_elid($groupid).$name);
		}
		foreach($PAGE_HOSTS['hosts'] as $hostid => $name){
			$cmbHosts->addItem($hostid, get_node_name_by_elid($hostid).$name);
		}

		$r_form->addItem(array(S_GROUP.SPACE,$cmbGroups));
		$r_form->addItem(array(SPACE.S_HOST.SPACE,$cmbHosts));

		$numrows = new CSpan(null,'info');
		$numrows->setAttribute('name','numrows');
		$header = get_table_header(array(S_TRIGGERS_BIG,
						new CSpan(SPACE.SPACE.'|'.SPACE.SPACE, 'divider'),
						S_FOUND.': ',$numrows,)
						);
		show_table_header($header, $r_form);
	
// <<<--- SELECTED HOST HEADER INFORMATION --->>>	
		if($PAGE_HOSTS['selected'] > 0){
		
			$header_host = CHost::get(array(
				'hostids' => $PAGE_HOSTS['selected'],
				'nopermissions' => 1,
				'extendoutput' => 1,
				'select_items' => 1,
				'select_graphs' => 1));
			$header_host = array_pop($header_host);
			
			$description = array();
			if($header_host['proxy_hostid']){
				$proxy = get_host_by_hostid($header_host['proxy_hostid']);
				$description[] = $proxy['host'].':';
			}			
			
			$description[] = new CLink($header_host['host'], 'hosts.php?form=update&hostid='.$header_host['hostid'].url_param('groupid'));

			$items = array(new CLink(S_ITEMS, 'items.php?groupid='.$PAGE_GROUPS['selected'].'&hostid='.$header_host['hostid']),
				' ('.count($header_host['itemids']).')');

			$graphs = array(new CLink(S_GRAPHS, 'graphs.php?groupid='.$PAGE_GROUPS['selected'].'&hostid='.$header_host['hostid']),
				' ('.count($header_host['graphids']).')');
			
			$dns = empty($header_host['dns']) ? '-' : $header_host['dns'];
			$ip = empty($header_host['ip']) ? '-' : $header_host['ip'];
			$port = empty($header_host['port']) ? '-' : $header_host['port'];
			if(1 == $header_host['useip'])
				$ip = bold($ip);
			else
				$dns = bold($dns);
				
				
			switch($header_host['status']){
				case HOST_STATUS_MONITORED:
					$status = new CSpan(S_MONITORED, 'off');
					break;
				case HOST_STATUS_NOT_MONITORED:
					$status = new CSpan(S_NOT_MONITORED, 'off');
					break;
				default:
					$status = S_UNKNOWN;
			}

			if($header_host['available'] == HOST_AVAILABLE_TRUE)
				$available = new CSpan(S_AVAILABLE, 'off');
			else if($header_host['available'] == HOST_AVAILABLE_FALSE)
				$available = new CSpan(S_NOT_AVAILABLE, 'on');
			else if($header_host['available'] == HOST_AVAILABLE_UNKNOWN)
				$available = new CSpan(S_UNKNOWN, 'unknown');

				
			$tbl_header_host = new CTableInfo();
			$tbl_header_host->addRow(array(
				new CLink(bold(S_HOST_LIST), 'hosts.php?hostid='.$header_host['hostid'].url_param('groupid')),
				$description,
				$items,
				$graphs,
				array(bold(S_DNS.': '), $dns),
				array(bold(S_IP.': '), $ip),
				array(bold(S_PORT.': '), $port),
				array(bold(S_STATUS.': '), $status),
				array(bold(S_AVAILABILITY.': '), $available)));
			$tbl_header_host->setClass('infobox');
			
			$tbl_header_host->show();
		}
// --->>> SELECTED HOST HEADER INFORMATION <<<---

		$form = new CForm('triggers.php');
		$form->setName('triggers');
		$form->setMethod('post');
		$form->addVar('hostid', $_REQUEST['hostid']);

		$table = new CTableInfo(S_NO_TRIGGERS_DEFINED);
		$table->setHeader(array(
			new CCheckBox('all_triggers', NULL, "checkAll('".$form->GetName()."','all_triggers','g_triggerid');"),
			make_sorting_link(S_SEVERITY, 't.priority'),
			make_sorting_link(S_STATUS, 't.status'),
			($_REQUEST['hostid'] > 0) ? NULL : make_sorting_link(S_HOST,'h.host'),
			make_sorting_link(S_NAME, 't.description'),
			S_EXPRESSION,
			S_ERROR));

		$options = array('select_hosts' => 1, 'editable' => 1, 'extendoutput' => 1);
		if($showdisabled == 0){
		    $options += array('status' => TRIGGER_STATUS_ENABLED);
		}
		if($PAGE_HOSTS['selected'] > 0){
			$options += array('hostids' => $PAGE_HOSTS['selected']);
		}
		else if($PAGE_GROUPS['selected'] > 0){
			$options += array('groupids' => $PAGE_GROUPS['selected']);
		}
		$triggers = CTrigger::get($options);
		
		foreach($triggers as $triggerid => $trigger){

			$description = array();

			if($trigger['templateid'] > 0){
				$real_hosts = get_realhosts_by_triggerid($triggerid);
				$real_host = DBfetch($real_hosts);
				$description[] = new CLink($real_host['host'], 'triggers.php?&hostid='.$real_host['hostid'], 'unknown');
				$description[] = ':';
			}
			
			$description[] = new CLink(expand_trigger_description($triggerid), 'triggers.php?form=update&triggerid='.$triggerid);

// <<<--- add dependencies --->>>
			$deps = get_trigger_dependencies_by_triggerid($triggerid);
			if(count($deps) > 0){
				$description[] = array(BR(), bold(S_DEPENDS_ON.' : '));
				foreach($deps as $num => $dep_triggerid) {
					$description[] = BR();
					
					$hosts = get_hosts_by_triggerid($dep_triggerid);
					while($host = DBfetch($hosts)){
						$description[] = $host['host'];
						$description[] = ', ';
					}
					
					array_pop($description);
					$description[] = ' : ';
					$description[] = expand_trigger_description($dep_triggerid);
				}
			}
// --->>> add dependencies <<<---

			if($trigger['status'] != TRIGGER_STATUS_UNKNOWN){ 
				$trigger['error'] = '';
			}
			if(!zbx_empty($trigger['error']) && (HOST_STATUS_TEMPLATE != $trigger['hoststatus'])){
				$error = new CDiv(SPACE, 'error_icon');
				$error->setHint($trigger['error'], '', 'on');
			}
			else{
				$error = new CDiv(SPACE,'ok_icon');
			}

			switch($trigger['priority']){
				case 0: $priority = S_NOT_CLASSIFIED; break;
				case 1: $priority = new CCol(S_INFORMATION, 'information'); break;
				case 2: $priority = new CCol(S_WARNING, 'warning'); break;
				case 3: $priority = new CCol(S_AVERAGE, 'average'); break;
				case 4: $priority = new CCol(S_HIGH, 'high'); break;
				case 5: $priority = new CCol(S_DISASTER, 'disaster'); break;
				default: $priority = $trigger['priority'];
			}

			$status_link = 'triggers.php?go='.(($trigger['status'] == TRIGGER_STATUS_DISABLED) ? 'activate' : 'disable').
				'&g_triggerid%5B%5D='.$triggerid;
				
			if($trigger['status'] == TRIGGER_STATUS_DISABLED){
				$status = new CLink(S_DISABLED, $status_link, 'disabled');
			}
			else if($trigger['status'] == TRIGGER_STATUS_UNKNOWN){
				$status = new CLink(S_UNKNOWN, $status_link, 'unknown');
			}
			else if($trigger['status'] == TRIGGER_STATUS_ENABLED){
				$status = new CLink(S_ENABLED, $status_link, 'enabled');
			}

			if($_REQUEST['hostid'] > 0){
				$table->addRow(array(
					new CCheckBox('g_triggerid['.$triggerid.']', NULL, NULL, $triggerid),
					$priority,
					$status,
					$description,
					explode_exp($trigger['expression'], 1),
					$error
				));	
			}
			else{
				foreach($trigger['hosts'] as $host){
				
					$table->addRow(array(
						new CCheckBox('g_triggerid['.$triggerid.']', NULL, NULL, $triggerid),
						$priority,
						$status,
						$host['host'],
						$description,
						explode_exp($trigger['expression'], 1),
						$error
					));
				}
			}			
		}

//----- GO ------
		$goBox = new CComboBox('go');
		$goBox->addItem('activate',S_ACTIVATE_SELECTED);
		$goBox->addItem('disable',S_DISABLE_SELECTED);
		$goBox->addItem('massupdate',S_MASS_UPDATE);
		$goBox->addItem('copy_to',S_COPY_SELECTED_TO);
		$goBox->addItem('delete',S_DELETE_SELECTED);

// goButton name is necessary!!!
		$goButton = new CButton('goButton',S_GO.' (0)');
		$goButton->setAttribute('id','goButton');
		zbx_add_post_js('chkbxRange.pageGoName = "g_triggerid";');

		$table->setFooter(new CCol(array($goBox, $goButton)));
//----

		$form->addItem($table);
		$form->show();
		zbx_add_post_js('insert_in_element("numrows","'.$table->getNumRows().'");');
	}

include_once('include/page_footer.php');
?>