<?php
	validate_sort_and_sortorder('i.description',ZBX_SORT_UP);
	
	$options = array('allow_all_hosts','monitored_hosts','with_monitored_items');
	
	$_REQUEST['hostid'] = get_request('hostid',get_profile('web.latest.last.hostid', null, PROFILE_TYPE_ID));
	if(is_null($_REQUEST['hostid'])){
		array_push($options,'always_select_first_host');
		
		$_REQUEST['groupid'] = get_request('groupid',get_profile('web.latest.last.groupid', null, PROFILE_TYPE_ID));
		if(is_null($_REQUEST['groupid'])){
			validate_group(PERM_READ_ONLY,array('allow_all_hosts','monitored_hosts','with_monitored_items','always_select_first_group'),'web.latest.last.groupid');
		}
	}

	if(!$ZBX_WITH_SUBNODES)	array_push($options,'only_current_node');

	validate_group_with_host(PERM_READ_ONLY,$options,'web.latest.last.groupid','web.latest.last.hostid');

?>
<?php

	$_REQUEST['select'] = get_request('select','');

	$_REQUEST['groupbyapp'] = get_request('groupbyapp',get_profile('web.latest.groupbyapp',1));
	update_profile('web.latest.groupbyapp',$_REQUEST['groupbyapp'],PROFILE_TYPE_INT);

	$_REQUEST['applications'] = get_request('applications',get_profile('web.latest.applications',array(),PROFILE_TYPE_ARRAY_ID));

	if(isset($_REQUEST['open'])){
		if(!isset($_REQUEST['applicationid'])){
			$_REQUEST['applications'] = array();
			$show_all_apps = 1;
		}
		else if(!uint_in_array($_REQUEST['applicationid'],$_REQUEST['applications'])){
			array_push($_REQUEST['applications'],$_REQUEST['applicationid']);
		}
		
	} 
	else if(isset($_REQUEST['close'])){
		if(!isset($_REQUEST['applicationid'])){
			$_REQUEST['applications'] = array();
		}
		else if(($i=array_search($_REQUEST['applicationid'], $_REQUEST['applications'])) !== FALSE){
			unset($_REQUEST['applications'][$i]);
		}
	}

	/* limit opened application count */
	while(count($_REQUEST['applications']) > 25){
		array_shift($_REQUEST['applications']);
	}

	update_profile('web.latest.applications',$_REQUEST['applications'],PROFILE_TYPE_ARRAY_ID);
?>
<?php
	
// Header
	$r_form = new CForm();
	$r_form->SetMethod('get');
//	$r_form->AddOption('onsubmit',"return updater.onetime_update('".ZBX_PAGE_MAIN_HAT."',this.form);");

	$r_form->AddVar("select",$_REQUEST["select"]);
	
	$cmbGroup = new CComboBox("groupid",$_REQUEST["groupid"],"javascript: return updater.onetime_update('".ZBX_PAGE_MAIN_HAT."',this.form);");
	$cmbHosts = new CComboBox("hostid",$_REQUEST["hostid"],"javascript: return updater.onetime_update('".ZBX_PAGE_MAIN_HAT."',this.form);");


	$cmbGroup->AddItem(0,S_ALL_SMALL);
	$cmbHosts->AddItem(0,S_ALL_SMALL);
	
	$available_groups= get_accessible_groups_by_user($USER_DETAILS,PERM_READ_LIST);
	$available_hosts = get_accessible_hosts_by_user($USER_DETAILS,PERM_READ_LIST,PERM_RES_IDS_ARRAY);

	$result=DBselect('SELECT DISTINCT g.groupid,g.name '.
					' FROM groups g, hosts_groups hg, hosts h, items i '.
					' WHERE g.groupid in ('.$available_groups.') '.
						' AND hg.groupid=g.groupid '.
						' AND h.status='.HOST_STATUS_MONITORED.
						' AND h.hostid=i.hostid '.
						' AND hg.hostid=h.hostid '.
						' AND i.status='.ITEM_STATUS_ACTIVE.
					' ORDER BY g.name');
	while($row=DBfetch($result)){
		$cmbGroup->AddItem(
				$row['groupid'],
				get_node_name_by_elid($row['groupid']).$row['name']
				);
	}
	$r_form->AddItem(array(S_GROUP.SPACE,$cmbGroup));
	
	if($_REQUEST['groupid'] > 0){
		$sql='SELECT DISTINCT h.hostid,h.host '.
			' FROM hosts h,items i,hosts_groups hg '.
			' WHERE h.status='.HOST_STATUS_MONITORED.
				' AND h.hostid=i.hostid '.
				' AND hg.groupid='.$_REQUEST['groupid'].
				' AND hg.hostid=h.hostid'.
				' AND i.status='.ITEM_STATUS_ACTIVE.
				' AND '.DBcondition('h.hostid',$available_hosts).
			' ORDER BY h.host';
	}
	else{
		$sql='SELECT DISTINCT h.hostid,h.host '.
			' FROM hosts h,items i '.
			' WHERE h.status='.HOST_STATUS_MONITORED.
				' AND i.status='.ITEM_STATUS_ACTIVE.
				' AND h.hostid=i.hostid'.
				' AND '.DBcondition('h.hostid',$available_hosts).
			' ORDER BY h.host';
	}
	
	$result=DBselect($sql);
	while($row=DBfetch($result)){
		$cmbHosts->AddItem(
				$row['hostid'],
				get_node_name_by_elid($row['hostid']).$row['host']
				);
	}

	$r_form->AddItem(array(SPACE.S_HOST.SPACE,$cmbHosts));
	
	$l_form = new CForm();
	$l_form->SetMethod('get');
	
	$l_form->AddVar("hostid",$_REQUEST["hostid"]);
	$l_form->AddVar("groupid",$_REQUEST["groupid"]);

	$l_form->AddItem(array(S_SHOW_ITEMS_WITH_DESCRIPTION_LIKE, new CTextBox("select",$_REQUEST["select"],20)));
	$l_form->AddItem(array(SPACE, new CButton("show",S_SHOW,"javascript: return updater.onetime_update('".ZBX_PAGE_MAIN_HAT."',this.form);")));

	show_table_header($l_form,$r_form);
//-------------

?>
<?php
	if(isset($show_all_apps)){
		$url = '?close=1'.
			url_param('groupid').
			url_param('hostid').
			url_param('applications').
			url_param('select');
			
		$link = new CLink(new CImg("images/general/opened.gif"),$url,null,"javascript: return updater.onetime_update('".ZBX_PAGE_MAIN_HAT."','".$url."');");
	}
	else{
		$url = '?open=1'.
			url_param('groupid').
			url_param('hostid').
			url_param('applications').
			url_param('select');
			
		$link = new CLink(new CImg("images/general/closed.gif"),$url,null,"javascript: return updater.onetime_update('".ZBX_PAGE_MAIN_HAT."','".$url."');");
	}
	
	$table=new CTableInfo();
	$table->SetHeader(array(
		is_show_subnodes()?make_sorting_link(S_NODE,'h.hostid') : null,
		($_REQUEST["hostid"] ==0)?make_sorting_link(S_HOST,'h.host') : NULL,
		array($link,SPACE,make_sorting_link(S_DESCRIPTION,'i.description')),
		make_sorting_link(S_LAST_CHECK,'i.lastclock'),
		S_LAST_VALUE,
		S_CHANGE,
		S_HISTORY));
		
//	$table->ShowStart();

	$compare_host = $_REQUEST['hostid']?' AND h.hostid='.$_REQUEST['hostid']:'';
	$compare_host.= $_REQUEST['groupid']?' AND hg.hostid=h.hostid AND hg.groupid ='.$_REQUEST['groupid']:'';

	$db_applications = DBselect('SELECT DISTINCT h.host,h.hostid,a.* '.
					' FROM applications a, hosts h'.($_REQUEST['groupid']?', hosts_groups hg ':'').
					' WHERE a.hostid=h.hostid'.
						$compare_host.
						' AND '.DBcondition('h.hostid',$available_hosts).
						' AND h.status='.HOST_STATUS_MONITORED.
					order_by('h.host,h.hostid','a.name,a.applicationid'));
					
	while($db_app = DBfetch($db_applications)){
		$db_items = DBselect('SELECT DISTINCT i.* '.
					' FROM items i,items_applications ia'.
					' WHERE ia.applicationid='.$db_app['applicationid'].
						' AND i.itemid=ia.itemid'.
						' AND i.status='.ITEM_STATUS_ACTIVE.
					order_by('i.description,i.itemid,i.lastclock'));

		$app_rows = array();
		$item_cnt = 0;
		while($db_item = DBfetch($db_items)){
			$description = item_description($db_item["description"],$db_item["key_"]);

			if(!empty($_REQUEST["select"]) && !zbx_stristr($description, $_REQUEST["select"]) ) continue;

			++$item_cnt;
			if(!uint_in_array($db_app["applicationid"],$_REQUEST["applications"]) && !isset($show_all_apps)) continue;

			if(isset($db_item["lastclock"]))
				$lastclock=date(S_DATE_FORMAT_YMDHMS,$db_item["lastclock"]);
			else
				$lastclock = new CCol('-', 'center');

			$lastvalue=format_lastvalue($db_item);

			if( isset($db_item["lastvalue"]) && isset($db_item["prevvalue"]) &&
				($db_item["value_type"] == 0) && ($db_item["lastvalue"]-$db_item["prevvalue"] != 0) )
			{
				if($db_item["lastvalue"]-$db_item["prevvalue"]<0){
					$change=convert_units($db_item["lastvalue"]-$db_item["prevvalue"],$db_item["units"]);
				}
				else{
					$change="+".convert_units($db_item["lastvalue"]-$db_item["prevvalue"],$db_item["units"]);
				}
				$change=nbsp($change);
			}
			else{
				$change=new CCol("-","center");
			}
			
			if(($db_item["value_type"]==ITEM_VALUE_TYPE_FLOAT) || ($db_item["value_type"]==ITEM_VALUE_TYPE_UINT64)){
				$actions=new CLink(S_GRAPH,"history.php?action=showgraph&itemid=".$db_item["itemid"],"action");
			}
			else{
				$actions=new CLink(S_HISTORY,"history.php?action=showvalues&period=3600&itemid=".$db_item["itemid"],"action");
			}
			
			array_push($app_rows, new CRow(array(
				is_show_subnodes()?SPACE:null,
				($_REQUEST["hostid"]>0)?NULL:SPACE,
				str_repeat(SPACE,6).$description,
				$lastclock,
				new CCol($lastvalue, $lastvalue=='-' ? 'center' : null),
				$change,
				$actions
				)));
		}

		if($item_cnt > 0){
			if(uint_in_array($db_app["applicationid"],$_REQUEST["applications"]) || isset($show_all_apps)){					
				$url = '?close=1&applicationid='.$db_app["applicationid"].
					url_param('groupid').
					url_param('hostid').
					url_param('applications').
					url_param('fullscreen').
					url_param('select');
					
				$link = new CLink(new CImg("images/general/opened.gif"),$url,null,"javascript: return updater.onetime_update('".ZBX_PAGE_MAIN_HAT."','".$url."');");
			}
			else{
				$url = '?open=1&applicationid='.$db_app["applicationid"].
					url_param('groupid').
					url_param('hostid').
					url_param('applications').
					url_param('fullscreen').
					url_param('select');
					
				$link = new CLink(new CImg("images/general/closed.gif"),$url,null,"javascript: return updater.onetime_update('".ZBX_PAGE_MAIN_HAT."','".$url."');");
			}

			$col = new CCol(array($link,SPACE,bold($db_app["name"]),SPACE.'('.$item_cnt.SPACE.S_ITEMS.')'));
			$col->SetColSpan(5);

			$table->AddRow(array(
					get_node_name_by_elid($db_app['hostid']),
					($_REQUEST["hostid"]>0)?NULL:$db_app["host"],
					$col
					));

			foreach($app_rows as $row)	$table->AddRow($row);
		}
	}
	

	$sql = 'SELECT DISTINCT h.host,h.hostid '.
			' FROM hosts h'.($_REQUEST['groupid']?', hosts_groups hg ':'').', items i '.
				' LEFT JOIN items_applications ia ON ia.itemid=i.itemid'.
			' WHERE ia.itemid is NULL '.
				$compare_host.
				' AND h.hostid=i.hostid '.
				' AND h.status='.HOST_STATUS_MONITORED.
				' AND i.status='.ITEM_STATUS_ACTIVE.
				' AND '.DBcondition('h.hostid',$available_hosts).
			' ORDER BY h.host';
		
	$db_appitems = DBselect($sql);
	
	while($db_appitem = DBfetch($db_appitems)){

		$sql = 'SELECT h.host,h.hostid,i.* '.
				' FROM hosts h'.($_REQUEST['groupid']?', hosts_groups hg ':'').', items i '.
					' LEFT JOIN items_applications ia ON ia.itemid=i.itemid'.
				' WHERE ia.itemid is NULL '.
					$compare_host.
					' AND h.hostid=i.hostid '.
					' AND h.status='.HOST_STATUS_MONITORED.
					' AND i.status='.ITEM_STATUS_ACTIVE.
					' AND h.hostid='.$db_appitem['hostid'].
				' ORDER BY i.description,i.itemid';
				
		$db_items = DBselect($sql);
	
		$app_rows = array();
		$item_cnt = 0;
		
		while($db_item = DBfetch($db_items)){
			$description = item_description($db_item["description"],$db_item["key_"]);
	
			if(!empty($_REQUEST["select"]) && !zbx_stristr($description, $_REQUEST["select"]) ) continue;
	
			++$item_cnt;

			if(!uint_in_array(0,$_REQUEST["applications"]) && !isset($show_all_apps)) continue;
	
			if(isset($db_item["lastclock"]))
				$lastclock=zbx_date2str(S_DATE_FORMAT_YMDHMS,$db_item["lastclock"]);
			else
				$lastclock = new CCol('-', 'center');
	
			$lastvalue=format_lastvalue($db_item);
	
			if( isset($db_item["lastvalue"]) && isset($db_item["prevvalue"]) &&
				($db_item["value_type"] == ITEM_VALUE_TYPE_FLOAT || $db_item["value_type"] == ITEM_VALUE_TYPE_UINT64) &&
				($db_item["lastvalue"]-$db_item["prevvalue"] != 0) )
			{
				if($db_item["lastvalue"]-$db_item["prevvalue"]<0){
					$change=convert_units($db_item["lastvalue"]-$db_item["prevvalue"],$db_item["units"]);
					$change=nbsp($change);
				}
				else{
					$change="+".convert_units($db_item["lastvalue"]-$db_item["prevvalue"],$db_item["units"]);
					$change=nbsp($change);
				}
			}
			else{
				$change=new CCol("-","center");
			}
			
			if(($db_item["value_type"]==ITEM_VALUE_TYPE_FLOAT) || ($db_item["value_type"]==ITEM_VALUE_TYPE_UINT64)){
				$actions=new CLink(S_GRAPH,"history.php?action=showgraph&itemid=".$db_item["itemid"],"action");
			}
			else{
				$actions=new CLink(S_HISTORY,"history.php?action=showvalues&period=3600&itemid=".$db_item["itemid"],"action");
			}
			
			array_push($app_rows, new CRow(array(
				is_show_subnodes()?($item_cnt?SPACE:get_node_name_by_elid($db_item['itemid'])):null,
				$_REQUEST["hostid"]?NULL:($item_cnt?SPACE:$db_item["host"]),
				str_repeat(SPACE, 6).$description,
				$lastclock,
				new CCol($lastvalue, $lastvalue == '-' ? 'center' : null),
				$change,
				$actions
				)));
		}
	
		if($item_cnt > 0){

			if(uint_in_array(0,$_REQUEST["applications"]) || isset($show_all_apps)){
				$url = '?close=1&applicationid=0'.
					url_param('groupid').
					url_param('hostid').
					url_param('applications').
					url_param('select');
					
				$link = new CLink(new CImg("images/general/opened.gif"),$url,null,"javascript: return updater.onetime_update('".ZBX_PAGE_MAIN_HAT."','".$url."');");				
			}
			else{
				$url = '?open=1&applicationid=0'.
					url_param('groupid').
					url_param('hostid').
					url_param('applications').
					url_param('select');
				$link = new CLink(new CImg("images/general/closed.gif"),$url,null,"javascript: return updater.onetime_update('".ZBX_PAGE_MAIN_HAT."','".$url."');");
			}

			$col = new CCol(array($link,SPACE,bold(S_MINUS_OTHER_MINUS),SPACE.'('.$item_cnt.SPACE.S_ITEMS.')'));
			$col->SetColSpan(5);
			
			$table->AddRow(array(
					get_node_name_by_elid($db_appitem['hostid']),
					$_REQUEST["hostid"] > 0 ? NULL : $db_appitem["host"],
					$col
					));	
					
			foreach($app_rows as $row)	$table->AddRow($row);
		}
	}
	
	$table->Show();
?>