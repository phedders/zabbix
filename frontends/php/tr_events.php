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
	require_once('include/config.inc.php');
	require_once('include/acknow.inc.php');
	require_once('include/actions.inc.php');
	require_once('include/events.inc.php');
	require_once('include/triggers.inc.php');
	require_once('include/users.inc.php');
	require_once('include/html.inc.php');

	$page["title"]		= "S_EVENT_DETAILS";
	$page["file"]		= "tr_events.php";
	$page['hist_arg'] = array('triggerid','eventid');
	$page['scripts'] = array('calendar.js', 'scriptaculous.js?load=effects');

	$page['type'] = detect_page_type(PAGE_TYPE_HTML);

	include_once "include/page_header.php";
?>
<?php
	define('PAGE_SIZE',	100);

//		VAR			TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
	$fields=array(
		'triggerid'=>		array(T_ZBX_INT, O_OPT, P_SYS,	DB_ID,		PAGE_TYPE_HTML.'=='.$page['type']),
		'eventid'=>			array(T_ZBX_INT, O_OPT, P_SYS,	DB_ID,		PAGE_TYPE_HTML.'=='.$page['type']),
		'fullscreen'=>		array(T_ZBX_INT, O_OPT,	P_SYS,	IN('0,1'),		NULL),

/* actions */
		"save"=>		array(T_ZBX_STR,O_OPT,	P_ACT|P_SYS, null,	null),
		"cancel"=>		array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	null,	null),

// ajax
		'favobj'=>		array(T_ZBX_STR, O_OPT, P_ACT,	IN("'filter','hat'"),		NULL),
		'favid'=>		array(T_ZBX_STR, O_OPT, P_ACT,  NOT_EMPTY,	'isset({favobj})'),
		'state'=>		array(T_ZBX_INT, O_OPT, P_ACT,	NOT_EMPTY,	'isset({favobj})'),
	);

	check_fields($fields);

/* AJAX */
	if(isset($_REQUEST['favobj'])){
		if('hat' == $_REQUEST['favobj']){
			update_profile('web.tr_events.hats.'.$_REQUEST['favid'].'.state',$_REQUEST['state'],PROFILE_TYPE_INT);
		}
	}

	if((PAGE_TYPE_JS == $page['type']) || (PAGE_TYPE_HTML_BLOCK == $page['type'])){
		exit();
	}
//--------

	if(!check_right_on_trigger_by_triggerid(PERM_READ_ONLY, $_REQUEST["triggerid"]))
		access_deny();

	$trigger_data = DBfetch(DBselect('SELECT h.host, t.* '.
						' FROM hosts h, items i, functions f, triggers t '.
	                   	' WHERE i.itemid=f.itemid '.
							' AND f.triggerid=t.triggerid '.
							' AND t.triggerid='.$_REQUEST["triggerid"].
							' AND h.hostid=i.hostid '.
							' AND '.DBin_node('t.triggerid')));
?>
<?php
	$tr_events_wdgt = new CWidget();
//Header
	$trigger_data['exp_expr'] = explode_exp($trigger_data["expression"],1);
	$trigger_data['exp_desc'] = expand_trigger_description_by_data($trigger_data);

	$text = array(S_EVENTS_BIG.': "'.$trigger_data['exp_desc'].'"');

	$url = '?fullscreen='.($_REQUEST['fullscreen']?'0':'1').url_param('triggerid').url_param('eventid');

	$fs_icon = new CDiv(SPACE,'fullscreen');
	$fs_icon->setAttribute('title',$_REQUEST['fullscreen']?S_NORMAL.' '.S_VIEW:S_FULLSCREEN);
	$fs_icon->addAction('onclick',new CScript("javascript: document.location = '".$url."';"));

	$tr_events_wdgt->addHeader($text, $fs_icon);
//-------
	$left_tab = new CTable();
	$left_tab->setCellPadding(3);
	$left_tab->setCellSpacing(3);

	$left_tab->setAttribute('border',0);

// tr details
	$tr_dtl = new CWidget('hat_triggerdetails',
							make_trigger_details($_REQUEST['triggerid'],$trigger_data) //null,
						);
	$tr_dtl->addHeader(S_EVENT.SPACE.S_SOURCE.SPACE.S_DETAILS, SPACE);
	$left_tab->addRow($tr_dtl);
//----------------

// event details
	$event_dtl = new CWidget('hat_eventdetails',
						make_event_details($_REQUEST['eventid'])//null,
						);
	$event_dtl->addHeader(S_EVENT_DETAILS, SPACE);
	$left_tab->addRow($event_dtl);
//----------------


	$right_tab = new CTable();
	$right_tab->setCellPadding(3);
	$right_tab->setCellSpacing(3);

	$right_tab->setAttribute('border',0);


// event ack
	$event_ack = new CWidget('hat_eventack',
						make_acktab_by_eventid($_REQUEST['eventid']),//null,
						get_profile('web.tr_events.hats.hat_eventack.state',1)
						);
	$event_ack->addHeader(S_ACKNOWLEDGES);
	$right_tab->addRow($event_ack);
//----------------


// event sms actions
	$actions_sms = new CWidget('hat_eventactionmsgs',
						get_action_msgs_for_event($_REQUEST['eventid']),//null,
						get_profile('web.tr_events.hats.hat_eventactionmsgs.state',1)
						);
	$actions_sms->addHeader(S_MESSAGE_ACTIONS);
	$right_tab->addRow($actions_sms);
//----------------

// event cmd actions
	$actions_cmd = new CWidget('hat_eventactionmcmds',
						get_action_cmds_for_event($_REQUEST['eventid']),//null,
						get_profile('web.tr_events.hats.hat_eventactioncmds.state',1)
						);
	$actions_cmd->addHeader(S_COMMAND_ACTIONS);
	$right_tab->addRow($actions_cmd);
//----------------

// event history
	$events_histry = new CWidget('hat_eventlist',
						make_small_eventlist($_REQUEST['eventid'], $trigger_data),
						get_profile('web.tr_events.hats.hat_eventlist.state',1)
						);
	$events_histry->addHeader(S_EVENTS.SPACE.S_LIST.SPACE.'['.S_PREVIOUS.' 20]');
	$right_tab->addRow($events_histry);
//----------------

	$td_l = new CCol($left_tab);
	$td_l->setAttribute('valign','top');

	$td_r = new CCol($right_tab);
	$td_r->setAttribute('valign','top');

	$outer_table = new CTable();
	$outer_table->setAttribute('border',0);
	$outer_table->setCellPadding(1);
	$outer_table->setCellSpacing(1);
	$outer_table->addRow(array($td_l,$td_r));

	$tr_events_wdgt->addItem($outer_table);
	$tr_events_wdgt->show();
?>
<?php

include_once('include/page_footer.php');

?>
