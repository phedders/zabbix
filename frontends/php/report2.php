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
	require_once 'include/hosts.inc.php';
	require_once 'include/reports.inc.php';

	$page['title']	= 'S_AVAILABILITY_REPORT';
	$page['file']	= 'report2.php';
	$page['hist_arg'] = array('config','groupid','hostid','tpl_triggerid');
	$page['scripts'] = array('calendar.js','scriptaculous.js?load=effects');
	$page['type'] = detect_page_type(PAGE_TYPE_HTML);

include_once 'include/page_header.php';

?>
<?php
//		VAR				TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
	$fields=array(
		'config'=>		array(T_ZBX_INT, O_OPT,	P_SYS,	IN('0,1'),		NULL),
		'filter_groupid'=>	array(T_ZBX_INT, O_OPT,	P_SYS,	DB_ID,			NULL),
		'hostgroupid'=>		array(T_ZBX_INT, O_OPT,	P_SYS,	DB_ID,			NULL),
		'filter_hostid'=>	array(T_ZBX_INT, O_OPT,	P_SYS,	DB_ID,			NULL),
		'tpl_triggerid'=>	array(T_ZBX_INT, O_OPT,	P_SYS,	DB_ID,			NULL),

		'triggerid'=>		array(T_ZBX_INT, O_OPT,	P_SYS|P_NZERO,	DB_ID,		NULL),

// filter
		"filter_rst"=>		array(T_ZBX_INT, O_OPT,	P_SYS,	IN(array(0,1)),	NULL),
		"filter_set"=>		array(T_ZBX_STR, O_OPT,	P_SYS,	null,	NULL),

		'filter_timesince'=>	array(T_ZBX_INT, O_OPT,	P_UNSET_EMPTY,	null,	NULL),
		'filter_timetill'=>	array(T_ZBX_INT, O_OPT,	P_UNSET_EMPTY,	null,	NULL),

//ajax
		'favobj'=>		array(T_ZBX_STR, O_OPT, P_ACT,	NULL,			NULL),
		'favid'=>		array(T_ZBX_STR, O_OPT, P_ACT,	NOT_EMPTY,		'isset({favobj})'),
		'state'=>		array(T_ZBX_INT, O_OPT, P_ACT,	NOT_EMPTY,		'isset({favobj}) && ("filter"=={favobj})'),
	);

	check_fields($fields);

/* AJAX */
	if(isset($_REQUEST['favobj'])){
		if('filter' == $_REQUEST['favobj']){
			update_profile('web.avail_report.filter.state',$_REQUEST['state'], PROFILE_TYPE_INT);
		}
	}

	if((PAGE_TYPE_JS == $page['type']) || (PAGE_TYPE_HTML_BLOCK == $page['type'])){
		exit();
	}

//--------
/* FILTER */
	if(isset($_REQUEST['filter_rst'])){
		$_REQUEST['filter_groupid'] = 0;
		$_REQUEST['filter_hostid'] = 0;
		$_REQUEST['filter_timesince'] = 0;
		$_REQUEST['filter_timetill'] = 0;
	}

	$_REQUEST['filter_groupid'] = get_request('filter_groupid',0);
	$_REQUEST['filter_hostid'] = get_request('filter_hostid',0);
	$_REQUEST['filter_timesince'] = get_request('filter_timesince',get_profile('web.avail_report.filter.timesince',0));
	$_REQUEST['filter_timetill'] = get_request('filter_timetill',get_profile('web.avail_report.filter.timetill',0));

	if(($_REQUEST['filter_timetill'] > 0) && ($_REQUEST['filter_timesince'] > $_REQUEST['filter_timetill'])){
		$tmp = $_REQUEST['filter_timesince'];
		$_REQUEST['filter_timesince'] = $_REQUEST['filter_timetill'];
		$_REQUEST['filter_timetill'] = $tmp;
	}

	if(isset($_REQUEST['filter_set']) || isset($_REQUEST['filter_rst'])){
		update_profile('web.avail_report.filter.timesince',$_REQUEST['filter_timesince'], PROFILE_TYPE_INT);
		update_profile('web.avail_report.filter.timetill',$_REQUEST['filter_timetill'], PROFILE_TYPE_INT);
	}

	$_REQUEST['groupid'] = $_REQUEST['filter_groupid'];
	$_REQUEST['hostid'] = $_REQUEST['filter_hostid'];
// --------------

	$config = get_request('config',get_profile('web.avail_report.config',0));
	update_profile('web.avail_report.config', $config, PROFILE_TYPE_INT);

	$params = array();
	$options = array('allow_all_hosts','with_items');

	if(0 == $config) array_push($options,'monitored_hosts');
	else array_push($options,'templated_hosts');

	if(!$ZBX_WITH_ALL_NODES)	array_push($options,'only_current_node');
	foreach($options as $option) $params[$option] = 1;

	$PAGE_GROUPS = get_viewed_groups(PERM_READ_ONLY, $params);
	$PAGE_HOSTS = get_viewed_hosts(PERM_READ_ONLY, $PAGE_GROUPS['selected'], $params);
//SDI($_REQUEST['groupid'].' : '.$_REQUEST['hostid']);

	validate_group_with_host($PAGE_GROUPS,$PAGE_HOSTS);
//SDI($_REQUEST['groupid'].' : '.$_REQUEST['hostid']);
?>
<?php
	$rep2_wdgt = new CWidget();

// HEADER
	if(0 == $config){
		$available_groups = $PAGE_GROUPS['groupids'];
		$available_hosts = $PAGE_HOSTS['hostids'];
	}
	else{
		$available_groups = get_accessible_groups_by_user($USER_DETAILS,PERM_READ_ONLY);
		if($PAGE_HOSTS['selected'] != 0)
			$PAGE_HOSTS['hostids'] = $available_hosts = get_accessible_hosts_by_user($USER_DETAILS,PERM_READ_ONLY);
		else
			$available_hosts = $PAGE_HOSTS['hostids'];
	}

	$available_triggers = get_accessible_triggers(PERM_READ_ONLY,$available_hosts);

	$r_form = new CForm();
	$r_form->setMethod('get');

	$cmbConf = new CComboBox('config',$config,'submit()');
	$cmbConf->addItem(0,S_BY_HOST);
	$cmbConf->addItem(1,S_BY_TRIGGER_TEMPLATE);

	$r_form->addItem(array(S_MODE.SPACE,$cmbConf,SPACE));

	$rep2_wdgt->addHeader(S_AVAILABILITY_REPORT_BIG, $r_form);
//	show_report2_header($config, $PAGE_GROUPS, $PAGE_HOSTS);

	if(isset($_REQUEST['triggerid'])){
		if(isset($available_triggers[$_REQUEST['triggerid']])){
			$sql = 'SELECT DISTINCT t.*, h.host, h.hostid '.
					' FROM triggers t, functions f, items i, hosts h '.
					' WHERE t.triggerid='.$_REQUEST['triggerid'].
						' AND t.triggerid=f.triggerid '.
						' AND f.itemid=i.itemid '.
						' AND i.hostid=h.hostid ';
			$trigger_data = DBfetch(DBselect($sql));
		}
		else{
			unset($_REQUEST['triggerid']);
		}
	}


	if(isset($_REQUEST['triggerid'])){
		$rep2_wdgt->addHeader(array(
									new CLink($trigger_data['host'],'?hostid='.$trigger_data['hostid']),
									' : "',
									expand_trigger_description_by_data($trigger_data),
									'"'),
								SPACE);

		$table = new CTableInfo(null,'graph');
		$table->addRow(new CImg('chart4.php?triggerid='.$_REQUEST['triggerid']));

		$rep2_wdgt->addItem($table);
		$rep2_wdgt->show();
	}
	else if(isset($_REQUEST['hostid'])){

// FILTER
		$filterForm = get_report2_filter($config, $PAGE_GROUPS, $PAGE_HOSTS);
		$rep2_wdgt->addFlicker($filterForm, get_profile('web.avail_report.filter.state',0));
//-------

		$sql_from = '';
		$sql_where = '';

		if(0 == $config){
			if($_REQUEST['groupid'] > 0){
				$sql_from .= ',hosts_groups hg ';
				$sql_where.= ' AND hg.hostid=h.hostid AND hg.groupid='.$_REQUEST['groupid'];
			}

			if($_REQUEST['hostid'] > 0){
				$sql_where.= ' AND h.hostid='.$_REQUEST['hostid'];
			}
		}
		else{
			if($_REQUEST['hostid'] > 0){
				$sql_from.=',hosts_templates ht ';
				$sql_where.=' AND ht.hostid=h.hostid AND ht.templateid='.$_REQUEST['hostid'];
			}

			if(isset($_REQUEST['tpl_triggerid']) && ($_REQUEST['tpl_triggerid'] > 0))
				$sql_where.= ' AND t.templateid='.$_REQUEST['tpl_triggerid'];
		}

		$result = DBselect('SELECT DISTINCT h.hostid,h.host,t.triggerid,t.expression,t.description,t.value '.
			' FROM triggers t,hosts h,items i,functions f '.$sql_from.
			' WHERE h.status='.HOST_STATUS_MONITORED.
				' AND '.DBcondition('h.hostid',$available_hosts).
				' AND i.hostid=h.hostid '.
				' AND i.status='.ITEM_STATUS_ACTIVE.
				' AND f.itemid=i.itemid '.
				' AND t.triggerid=f.triggerid '.
				' AND t.status='.TRIGGER_STATUS_ENABLED.
				$sql_where.
			' ORDER BY h.host, t.description');


		$table = new CTableInfo();
		$table->setHeader(
				array(is_show_all_nodes()?S_NODE : null,
				(($_REQUEST['hostid'] == 0) || (1 == $config))?S_HOST:NULL,
				S_NAME,
				S_PROBLEMS,
				S_OK,
				S_UNKNOWN,
				S_GRAPH));

		while($row=DBfetch($result)){
			if(!check_right_on_trigger_by_triggerid(null, $row['triggerid'])) continue;

			$availability = calculate_availability($row['triggerid'],$_REQUEST['filter_timesince'],$_REQUEST['filter_timetill']);

			$true	= new CSpan(sprintf("%.4f%%",$availability['true']), 'on');
			$false	= new CSpan(sprintf("%.4f%%",$availability['false']), 'off');
			$unknown= new CSpan(sprintf("%.4f%%",$availability['unknown']), 'unknown');
			$actions= new CLink(S_SHOW,'report2.php?hostid='.$_REQUEST['hostid'].'&triggerid='.$row['triggerid'],'action');

			$table->addRow(array(
				get_node_name_by_elid($row['hostid']),
				(($_REQUEST['hostid'] == 0) || (1 == $config))?$row['host']:NULL,
				new CLink(
					expand_trigger_description_by_data($row),
					'events.php?triggerid='.$row['triggerid'],'action'),
				$true,
				$false,
				$unknown,
				$actions
				));
		}

		$rep2_wdgt->addItem($table);
		$rep2_wdgt->show();
	}

include_once 'include/page_footer.php';
?>
