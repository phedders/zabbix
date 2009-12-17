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
	require_once('include/acknow.inc.php');
	require_once('include/triggers.inc.php');
	require_once('include/forms.inc.php');

	$page['title']	= 'S_ACKNOWLEDGES';
	$page['file']	= 'acknow.php';
	$page['hist_arg'] = array('eventid');

include_once('include/page_header.php');

?>
<?php
	$_REQUEST['go'] = get_request('go', 'none');
	$bulk = ($_REQUEST['go'] == 'bulkacknowledge');

//		VAR				TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
	$fields=array(
		'eventid'=>			array(T_ZBX_INT, O_OPT, P_SYS,	DB_ID,	null),
		'triggers' =>		array(T_ZBX_INT, O_OPT,	P_SYS,	DB_ID,	null),
		'events'=>			array(T_ZBX_INT, O_OPT,	P_SYS,	DB_ID,	null),
		'message'=>			array(T_ZBX_STR, O_OPT,	NULL,	$bulk ? NULL : NOT_EMPTY,	'isset({save})||isset({saveandreturn})'),
// Actions
		'go'=>				array(T_ZBX_STR, O_OPT, P_SYS|P_ACT, NULL, NULL),
// form
		'saveandreturn' =>	array(T_ZBX_STR,O_OPT,	P_ACT|P_SYS, NULL,	NULL),
		'save'=>			array(T_ZBX_STR,O_OPT,	P_ACT|P_SYS, NULL,	NULL),
		'cancel'=>			array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	null,	null)
	);
	check_fields($fields);


	if(isset($_REQUEST['cancel'])){
		$last_page = $USER_DETAILS['last_page'];
		$url = $last_page ? new CUrl($last_page['url']) : new CUrl('tr_status.php?hostid='.get_profile('web.tr_status.hostid', 0));
		redirect($url->getUrl());
		exit;
	}

	if(!isset($_REQUEST['events']) && !isset($_REQUEST['eventid']) && !isset($_REQUEST['triggers'])){
		show_message(S_NO_EVENTS_TO_ACKNOWLEDGE);
		include_once('include/page_footer.php');
	}

	$bulk = !isset($_REQUEST['eventid']);
?>
<?php


	if(!$bulk){
		$options = array('extendoutput' => 1, 'select_triggers' => 1, 'eventids' => $_REQUEST['eventid']);
		$events = CEvent::get($options);
		$event = reset($events);
		$event_trigger = reset($event['triggers']);
		$event_acknowledged = $event['acknowledged'];
		$_REQUEST['events'] = $_REQUEST['eventid'];
	}

	if(isset($_REQUEST['save']) || isset($_REQUEST['saveandreturn'])){

		if($bulk){
			$_REQUEST['message'] .= ($_REQUEST['message'] == '' ? '' : "\n\r") . S_SYS_BULK_ACKNOWLEDGE;
		}

		$events_data = array(
			'events' => zbx_toObject($_REQUEST['events'], 'eventid'),
			'triggers' => zbx_toObject($_REQUEST['triggers'], 'triggerid'),
			'message' => $_REQUEST['message']);
		$result = CEvent::acknowledge($events_data);

		show_messages($result, S_EVENT_ACKNOWLEDGED, S_CANNOT_ACKNOWLEDGE_EVENT);
		if($result){
			$event_acknowledged = true;
			add_audit(AUDIT_ACTION_UPDATE, AUDIT_RESOURCE_TRIGGER, S_ACKNOWLEDGE_ADDED.
				' ['.($bulk) ? ' BULK ACKNOWLEDGE ' : (expand_trigger_description_by_data($event_trigger)).']'.
				' ['.$_REQUEST['message'].']');
		}

		if(isset($_REQUEST['saveandreturn'])){
			$last_page = $USER_DETAILS['last_page'];

			if(!$last_page){
				$url = new CUrl('tr_status.php?hostid='.get_profile('web.tr_status.hostid', 0));
			}
			else{
				$url = new CUrl($last_page['url']);
			}
			redirect($url->getUrl());
			exit;
		}

 	}

?>
<?php
	$msg = $bulk ? ' BULK ACKNOWLEDGE ' : expand_trigger_description_by_data($event_trigger);
	show_table_header(array(S_ALARM_ACKNOWLEDGES_BIG.': ', $msg));

	if($bulk){
		$title = S_ACKNOWLEDGE_ALARM_BY;
		$btn_txt2 = S_ACKNOWLEDGE.' '.S_AND_SYMB.' '.S_RETURN;
	}
	else{
		$db_acks = get_acknowledges_by_eventid($_REQUEST['eventid']);
		if($db_acks){
			$table = new CTable(null, 'ack_msgs');
			$table->setAlign('center');

			while($db_ack = DBfetch($db_acks)){
				$db_user = CUser::get(array('userids' => $db_ack['userid'], 'extendoutput' => 1));
				$db_user = reset($db_user);

				$table->addRow(array(
					new CCol($db_user['alias'], 'user'),
					new CCol(date(S_DATE_FORMAT_YMDHMS, $db_ack['clock']), 'time')),
					'title');

				$msgCol = new CCol(zbx_nl2br($db_ack['message']));
				$msgCol->setColspan(2);
				$table->addRow($msgCol, 'msg');
			}

			$table->Show();
		}

		if($event_acknowledged){
			$title = S_ADD_COMMENT_BY;
			$btn_txt = S_SAVE;
			$btn_txt2 = S_SAVE.' '.S_AND_SYMB.' '.S_RETURN;
		}
		else{
			$title = S_ACKNOWLEDGE_ALARM_BY;
			$btn_txt = S_ACKNOWLEDGE;
			$btn_txt2 = S_ACKNOWLEDGE.' '.S_AND_SYMB.' '.S_RETURN;
		}
	}


	$frmMsg = new CFormTable($title.' "'.$USER_DETAILS['alias'].'"');

	if(isset($_REQUEST['eventid'])){
		$frmMsg->addVar('eventid', $_REQUEST['eventid']);
	}
	else if(isset($_REQUEST['triggers'])){
		foreach($_REQUEST['triggers'] as $triggerid){
			$frmMsg->addVar('triggers['.$triggerid.']', $triggerid);
		}
	}
	else if(isset($_REQUEST['events'])){
		foreach($_REQUEST['events'] as $eventid){
			$frmMsg->addVar('events['.$eventid.']', $eventid);
		}
	}


	$frmMsg->addRow(S_MESSAGE, new CTextArea('message', '', 80, 6));
	$frmMsg->addItemToBottomRow(new CButton('saveandreturn', $btn_txt2));
	$bulk ? '' : $frmMsg->addItemToBottomRow(new CButton('save', $btn_txt));
	$frmMsg->addItemToBottomRow(new CButtonCancel());

	$frmMsg->show(false);

include_once('include/page_footer.php');
?>