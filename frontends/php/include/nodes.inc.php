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
	function init_nodes(){
		/* Init CURRENT NODE ID */
		if(defined('ZBX_NODES_INITIALIZED')) return;

		global $USER_DETAILS;
		global $ZBX_LOCALNODEID, $ZBX_LOCMASTERID,
			$ZBX_CURRENT_NODEID, $ZBX_CURMASTERID,
			$ZBX_NODES, $ZBX_NODES_IDS,
			$ZBX_AVAILABLE_NODES, $ZBX_VIEWED_NODES,
			$ZBX_WITH_ALL_NODES;

		$ZBX_AVAILABLE_NODES = array();
		$ZBX_NODES_IDS = array();
		$ZBX_NODES = array();
		$ZBX_CURRENT_NODEID = $ZBX_LOCALNODEID;

		$ZBX_WITH_ALL_NODES = !defined('ZBX_NOT_ALLOW_ALL_NODES');

		if(!defined('ZBX_PAGE_NO_AUTHERIZATION') && ZBX_DISTRIBUTED) {

			if($USER_DETAILS['type'] == USER_TYPE_SUPER_ADMIN) {
				$sql = 'SELECT DISTINCT n.nodeid,n.name,n.masterid FROM nodes n ';
			}
			else {
				$sql = 'SELECT DISTINCT n.nodeid,n.name,n.masterid '.
					' FROM nodes n, groups hg,rights r, users_groups g '.
					' WHERE r.id=hg.groupid '.
					 	' AND r.groupid=g.usrgrpid '.
					 	' AND g.userid='.$USER_DETAILS['userid'].
					 	' AND n.nodeid='.DBid2nodeid('hg.groupid');
			}
			$db_nodes = DBselect($sql);
			while($node = DBfetch($db_nodes)) {
				$ZBX_NODES[$node['nodeid']] = $node;
				$ZBX_NODES_IDS[$node['nodeid']] = $node['nodeid'];
			}

			$ZBX_AVAILABLE_NODES = get_accessible_nodes_by_user($USER_DETAILS, PERM_READ_LIST, PERM_RES_IDS_ARRAY, $ZBX_NODES_IDS);

			$ZBX_VIEWED_NODES = get_viewed_nodes();
			$ZBX_CURRENT_NODEID = $ZBX_VIEWED_NODES['selected'];

			if($node_data = DBfetch(DBselect('SELECT masterid FROM nodes WHERE nodeid='.$ZBX_CURRENT_NODEID))){
				$ZBX_CURMASTERID = $node_data['masterid'];
			}

			if(!isset($ZBX_NODES[$ZBX_CURRENT_NODEID])) {
				$ZBX_CURRENT_NODEID = $ZBX_LOCALNODEID;
				$ZBX_CURMASTERID = $ZBX_LOCMASTERID;
			}

			if(isset($_REQUEST['select_nodes']))
				update_profile('web.nodes.selected', $ZBX_VIEWED_NODES['nodeids'], PROFILE_TYPE_ARRAY_ID);
			if(isset($_REQUEST['switch_node']))
				update_profile('web.nodes.switch_node', $ZBX_VIEWED_NODES['selected'], PROFILE_TYPE_ID);
		}
		else {
			$ZBX_CURRENT_NODEID = $ZBX_LOCALNODEID;
			$ZBX_CURMASTERID = $ZBX_LOCMASTERID;
		}

		// zbx_set_post_cookie('zbx_current_nodeid', $ZBX_CURRENT_NODEID);
		define('ZBX_NODES_INITIALIZED', 1);
	}

	function get_current_nodeid($forse_all_nodes = null, $perm = null){
		global $USER_DETAILS, $ZBX_CURRENT_NODEID, $ZBX_AVAILABLE_NODES, $ZBX_VIEWED_NODES;
		if(!isset($ZBX_CURRENT_NODEID)) {
			init_nodes();
		}

		if(!is_null($perm)){
			return get_accessible_nodes_by_user($USER_DETAILS, $perm, PERM_RES_IDS_ARRAY, $ZBX_AVAILABLE_NODES);
		}
		else if(is_null($forse_all_nodes)){
			if($ZBX_VIEWED_NODES['selected'] == 0) {
				$result = $ZBX_VIEWED_NODES['nodeids'];
			}
			else {
				$result = $ZBX_VIEWED_NODES['selected'];
			}
			if(empty($result)){
				$result = $USER_DETAILS['node']['nodeid'];
			}
		}
		else if($forse_all_nodes) {
			$result = $ZBX_AVAILABLE_NODES;
		}
		else {
			$result = $ZBX_CURRENT_NODEID;
		}

	return $result;
	}

	function get_viewed_nodes($options=array()) {
		global $USER_DETAILS;
		global $ZBX_LOCALNODEID, $ZBX_AVAILABLE_NODES;

		$config = select_config();

		$def_options = array(
			'allow_all' => 0
		);
		$options = array_merge($def_options, $options);

		$result = array('selected' => 0, 'nodes' => array(), 'nodeids' => array());

		if(!defined('ZBX_NOT_ALLOW_ALL_NODES')){
			$result['nodes'][0] = array('nodeid' => 0, 'name' => S_ALL_S);
		}

		$available_nodes = get_accessible_nodes_by_user($USER_DETAILS, PERM_READ_LIST, PERM_RES_DATA_ARRAY);
		$available_nodes = get_tree_by_parentid($ZBX_LOCALNODEID, $available_nodes, 'masterid'); //remove parent nodes

		$selected_nodeids = get_request('selected_nodes', get_profile('web.nodes.selected', array($USER_DETAILS['node']['nodeid'])));

// +++ Fill $result['NODEIDS'], $result['NODES'] +++
		$nodes = array();
		$nodeids = array();
		foreach($selected_nodeids as $num => $nodeid) {
			if(isset($available_nodes[$nodeid])) {
				$result['nodes'][$nodeid] = array(
					'nodeid' => $available_nodes[$nodeid]['nodeid'],
					'name' => $available_nodes[$nodeid]['name'],
					'masterid' => $available_nodes[$nodeid]['masterid']);
				$nodeids[$nodeid] = $nodeid;
			}
		}
// --- ---

		$switch_node = get_request('switch_node', get_profile('web.nodes.switch_node', -1));

		if(!isset($available_nodes[$switch_node]) || !uint_in_array($switch_node, $selected_nodeids)) { //check switch_node
			$switch_node = 0;
		}

		$result['nodeids'] = $nodeids;
		if(!defined('ZBX_NOT_ALLOW_ALL_NODES')) {
			$result['selected'] = $switch_node;
		}
		else if(!empty($nodeids)){
			$result['selected'] = ($switch_node > 0) ? $switch_node : array_shift($nodeids);
		}

	return $result;
	}

	function get_node_name_by_elid($id_val, $forse_with_all_nodes = null){
		global $ZBX_NODES, $ZBX_VIEWED_NODES;

		if($forse_with_all_nodes === false || (is_null($forse_with_all_nodes) && ($ZBX_VIEWED_NODES['selected'] != 0))) {
			return null;
		}

		$nodeid = id2nodeid($id_val);
//SDI($nodeid.' - '.$ZBX_NODES[$nodeid]['name']);
		if ( !isset($ZBX_NODES[$nodeid]) )
			return null;

		return '['.$ZBX_NODES[$nodeid]['name'].'] ';
	}

	function is_show_all_nodes(){
		global	$ZBX_VIEWED_NODES;

	return (ZBX_DISTRIBUTED && ($ZBX_VIEWED_NODES['selected'] == 0));
	}

	function detect_node_type($node_data){
		global $ZBX_CURMASTERID;

		if(bccomp($node_data['nodeid'],get_current_nodeid(false)) == 0)		$node_type = ZBX_NODE_LOCAL;
		else if(bccomp($node_data['nodeid'] ,$ZBX_CURMASTERID)==0)		$node_type = ZBX_NODE_MASTER;
		else if(bccomp($node_data['masterid'], get_current_nodeid(false))==0)	$node_type = ZBX_NODE_REMOTE;
		else $node_type = -1;

	return $node_type;
	}

	function node_type2str($node_type){
		$result = '';
		switch($node_type){
			case ZBX_NODE_REMOTE:	$result = S_REMOTE;	break;
			case ZBX_NODE_MASTER:	$result = S_MASTER;	break;
			case ZBX_NODE_LOCAL:	$result = S_LOCAL;	break;
			default:		$result = S_UNKNOWN;	break;
		}

		return $result;
	}

	function add_node($new_nodeid,$name,$timezone,$ip,$port,$slave_history,$slave_trends,$node_type){
		global $ZBX_CURMASTERID;

		if(!eregi('^'.ZBX_EREG_NODE_FORMAT.'$', $name) ){
			error("Incorrect characters used for Node name");
			return false;
		}

		switch($node_type){
			case ZBX_NODE_REMOTE:
				$masterid = get_current_nodeid(false);
				$nodetype = 0;
				break;
			case ZBX_NODE_MASTER:
				$masterid = 0;
				$nodetype = 0;
				if($ZBX_CURMASTERID){
					error('Master node already exist');
					return false;
				}
				break;
			case ZBX_NODE_LOCAL:
				$masterid = $ZBX_CURMASTERID;
				$nodetype = 1;
				break;
			default:
				error('Incorrect node type');
				return false;
				break;
		}

		if(DBfetch(DBselect('select nodeid from nodes where nodeid='.$new_nodeid))){
			error('Node with same ID already exist.');
			return false;
		}

		$result = DBexecute('insert into nodes (nodeid,name,timezone,ip,port,slave_history,slave_trends,'.
				'nodetype,masterid) values ('.
				$new_nodeid.','.zbx_dbstr($name).','.$timezone.','.zbx_dbstr($ip).','.$port.','.$slave_history.','.$slave_trends.','.
				$nodetype.','.$masterid.')');

		if($result && $node_type == ZBX_NODE_MASTER){
			DBexecute('update nodes set masterid='.$new_nodeid.' where nodeid='.get_current_nodeid(false));
			$ZBX_CURMASTERID = $new_nodeid; /* applay Master node for this script */
		}

	return ($result ? $new_nodeid : $result);
	}

	function update_node($nodeid,$new_nodeid,$name,$timezone,$ip,$port,$slave_history,$slave_trends){
		if( !eregi('^'.ZBX_EREG_NODE_FORMAT.'$', $name) ){
			error("Incorrect characters used for Node name");
			return false;
		}

		$result = DBexecute('update nodes set nodeid='.$new_nodeid.',name='.zbx_dbstr($name).','.
				'timezone='.$timezone.',ip='.zbx_dbstr($ip).',port='.$port.','.
				'slave_history='.$slave_history.',slave_trends='.$slave_trends.
				' where nodeid='.$nodeid);
	return $result;
	}

	function delete_node($nodeid){
		$result = false;
		$node_data = DBfetch(DBselect('select * from nodes where nodeid='.$nodeid));

		$node_type = detect_node_type($node_data);

		if($node_type == ZBX_NODE_LOCAL)
		{
			error('Unable to remove local node');
		}
		else
		{
			$housekeeperid = get_dbid('housekeeper','housekeeperid');
			$result = (
				DBexecute("insert into housekeeper (housekeeperid,tablename,field,value)".
					" values ($housekeeperid,'nodes','nodeid',$nodeid)") &&
				DBexecute('delete from nodes where nodeid='.$nodeid) &&
				DBexecute('update nodes set masterid=0 where masterid='.$nodeid)
				);
			error('Please be aware that database still contains data related to the deleted Node');
		}
		return $result;
	}

	function	get_node_by_nodeid($nodeid)
	{
		return DBfetch(DBselect('select * from nodes where nodeid='.$nodeid));
	}

	function	get_node_path($nodeid, $result='/')
	{
		if($node_data = get_node_by_nodeid($nodeid))
		{
			if($node_data['masterid'])
			{
				$result = get_node_path($node_data['masterid'],$result);
			}
			$result .= $node_data['name'].'/';
		}
		return $result;
	}
?>
