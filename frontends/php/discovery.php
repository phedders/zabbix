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

	require_once('include/config.inc.php');
	require_once('include/discovery.inc.php');
	$page['hist_arg'] = array('druleid');

	$page['file'] = 'discovery.php';
	$page['title'] = "S_STATUS_OF_DISCOVERY";

include_once('include/page_header.php');


//		VAR			TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
	$fields=array(
		'druleid'=>		array(T_ZBX_INT, O_OPT, P_SYS,	DB_ID, null),
		'fullscreen'=>	array(T_ZBX_INT, O_OPT,	P_SYS,	IN('0,1'),		NULL),
//ajax
		'favobj'=>		array(T_ZBX_STR, O_OPT, P_ACT,	NULL,			'isset({favid})'),
		'favid'=>		array(T_ZBX_STR, O_OPT, P_ACT,  NOT_EMPTY,		NULL),
		'state'=>		array(T_ZBX_INT, O_OPT, P_ACT,  NOT_EMPTY,		'isset({favobj})'),
	);

	check_fields($fields);

/* AJAX	*/
	if(isset($_REQUEST['favobj'])){
		if('hat' == $_REQUEST['favobj']){
			update_profile('web.discovery.hats.'.$_REQUEST['favid'].'.state',$_REQUEST['state'], PROFILE_TYPE_INT);
		}
	}

	if((PAGE_TYPE_JS == $page['type']) || (PAGE_TYPE_HTML_BLOCK == $page['type'])){
		exit();
	}
//--------

	validate_sort_and_sortorder('ip',ZBX_SORT_UP);


	$dscvry_wdgt = new CWidget('hat_discovery');

// HEADER
	$r_form = new CForm();
	$r_form->setMethod('get');

	$druleid = get_request('druleid', 0);
	$fullscreen = get_request('fullscreen', 0);

	$url = '?fullscreen='.($_REQUEST['fullscreen']?'0':'1').'&amp;druleid='.$druleid;

	$fs_icon = new CDiv(SPACE,'fullscreen');
	$fs_icon->setAttribute('title',$_REQUEST['fullscreen']?S_NORMAL.' '.S_VIEW:S_FULLSCREEN);
	$fs_icon->addAction('onclick',new CScript("javascript: document.location = '".$url."';"));

	$row_count = 0;
	$numrows = new CSpan(null,'info');
	$numrows->setAttribute('name','numrows');
	$header = array(S_STATUS_OF_DISCOVERY_BIG,
			new CSpan(SPACE.SPACE.'|'.SPACE.SPACE, 'divider'),
			S_FOUND.': ',$numrows);
	$dscvry_wdgt->addHeader($header, $fs_icon);

// 2nd header
	$cmbDRules = new CComboBox('druleid',$druleid,'submit()');
	$cmbDRules->addItem(0,S_ALL_SMALL);
	$sql = 'SELECT DISTINCT druleid,name '.
			' FROM drules '.
			' WHERE '.DBin_node('druleid').
				' AND status='.DRULE_STATUS_ACTIVE.
			' ORDER BY name';
	$db_drules = DBselect($sql);
	while($drule = DBfetch($db_drules))
		$cmbDRules->addItem(
				$drule['druleid'],
				get_node_name_by_elid($drule['druleid']).$drule['name']
				);
	$r_form->addVar('fullscreen', $fullscreen);
	$r_form->addItem(array(S_DISCOVERY_RULE.SPACE,$cmbDRules));

	$dscvry_wdgt->addHeader(SPACE, $r_form);
//-------------


	$services = array();

	$sql_where='';
	if($druleid>0){
		$sql_where = ' AND h.druleid='.$druleid;
	}

	$sql = 'SELECT s.type,s.port,s.key_ '.
			' FROM dservices s,dhosts h,drules r '.
			' WHERE s.dhostid=h.dhostid'.
				' AND h.druleid=r.druleid'.
				' AND r.status='.DRULE_STATUS_ACTIVE.
				$sql_where.
				' AND '.DBin_node('s.dserviceid');
	$db_dservices = DBselect($sql);
	while ($dservice = DBfetch($db_dservices)) {
		$service_name = discovery_check_type2str($dservice['type']).
				discovery_port2str($dservice['type'], $dservice['port']).
				(empty($dservice['key_']) ? '' : ':'.$dservice['key_']);
		$services[$service_name] = 1;
	}

	ksort($services);

	$header = array(
			is_show_all_nodes() ? new CCol(S_NODE, 'center') : null,
			new CCol(make_sorting_link(S_DISCOVERED_DEVICE,'ip'), 'center'),
			new CCol(S_MONITORED_HOST, 'center'),
			new CCol(array(S_UPTIME.'/',S_DOWNTIME),'center')
			);

	foreach ($services as $name => $foo) {
		$header[] = new CImg('vtext.php?text='.$name);
	}

	$table  = new CTableInfo();
	$table->setHeader($header,'vertical_header');

	$sql_where='';
	if($druleid>0){
		$sql_where = ' AND druleid='.$druleid;
	}
	$sql = 'SELECT DISTINCT druleid,proxy_hostid,name '.
			' FROM drules '.
			' WHERE '.DBin_node('druleid').
				$sql_where.
				' AND status='.DRULE_STATUS_ACTIVE.
			' ORDER BY name';
	$db_drules = DBselect($sql);
	while($drule = DBfetch($db_drules)) {
		$discovery_info = array();

		$db_dhosts = DBselect('SELECT dh.dhostid,dh.druleid,dh.ip,dh.status,dh.lastup,dh.lastdown,h.host'.
				' FROM dhosts dh'.
				' LEFT JOIN hosts h ON h.ip=dh.ip and h.proxy_hostid='.$drule['proxy_hostid'].
				' WHERE '.DBin_node('dh.dhostid').
					' AND dh.druleid='.$drule['druleid'].
				order_by('dh.ip','dh.dhostid,dh.status'));
		while($dhost = DBfetch($db_dhosts)){
			$class = 'enabled';
			$time = 'lastup';
			if(DHOST_STATUS_DISABLED == $dhost['status']){
				$class = 'disabled';
				$time = 'lastdown';
			}

			$discovery_info[$dhost['ip']] = array('class' => $class, 'host' => $dhost['host'],
					'time' => $dhost[$time], 'druleid' => $dhost['druleid']);

			$db_dservices = DBselect('SELECT type,port,key_,status,lastup,lastdown FROM dservices '.
					' WHERE dhostid='.$dhost['dhostid'].
					' order by status,type,port');
			while($dservice = DBfetch($db_dservices)){
				$class = 'active';
				$time = 'lastup';

				if(DSVC_STATUS_DISABLED == $dservice['status']){
					$class = 'inactive';
					$time = 'lastdown';
				}

				$service_name = discovery_check_type2str($dservice['type']).
						discovery_port2str($dservice['type'], $dservice['port']).
						(empty($dservice['key_']) ? '' : ':'.$dservice['key_']);

				$discovery_info
					[$dhost['ip']]
					['services']
					[$service_name] = array('class' => $class, 'time' => $dservice[$time]);
			}
		}

		if ($druleid == 0 && !empty($discovery_info)) {
			$col = new CCol(array(bold($drule['name']),
				SPACE.'('.count($discovery_info).SPACE.S_ITEMS.')'));
			$col->setColSpan(count($services) + 3);

			$table->addRow(array(get_node_name_by_elid($drule['druleid']),$col));
		}

		foreach($discovery_info as $ip => $h_data){
			$table_row = array(
				get_node_name_by_elid($h_data['druleid']),
				new CSpan($ip, $h_data['class']),
				new CSpan(empty($h_data['host']) ? '-' : $h_data['host']),
				new CSpan(($h_data['time'] == 0 ? '' : convert_units(time() - $h_data['time'], 'uptime')), $h_data['class'])
				);
			foreach($services as $name => $foo){
				$class = null;
				$time = SPACE;

				$hint = new CDiv(SPACE, $class);
				$hintTable = new CTableInfo();
				$hintTable->setAttribute('style','width: auto;');


				if(isset($h_data['services'][$name])){
					$class = $h_data['services'][$name]['class'];
					$time = $h_data['services'][$name]['time'];

					if ($class == 'active') {
						$hintTable->setHeader(S_UP_TIME);
					}
					else if ($class == 'inactive') {
						$hintTable->setHeader(S_DOWN_TIME);
					}

					$timeColumn = new CCol(zbx_date2age($h_data['services'][$name]['time']), $class);
					$hintTable->addRow($timeColumn);
					$hint->setHint($hintTable);
				}

				$table_row[] = new CCol($hint, $class);
			}
			$table->addRow($table_row);
			$row_count++;
		}
	}
	if(isset($row_count))
		zbx_add_post_js('insert_in_element("numrows","'.$row_count.'");');

	$dscvry_wdgt->addItem($table);
	$dscvry_wdgt->show();
?>
<?php

include_once "include/page_footer.php";

?>
