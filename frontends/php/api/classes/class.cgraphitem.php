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
/**
 * File containing CGraphItem class for API.
 * @package API
 */
/**
 * Class containing methods for operations with GraphItems
 */
class CGraphItem {

	public static $error;

	/**
	* Get GraphItems data
	*
	* @static
	* @param array $options
	* @return array|boolean
	*/
	public static function get($options = array()){
		global $USER_DETAILS;

		$result = array();
		$user_type = $USER_DETAILS['type'];
		$userid = $USER_DETAILS['userid'];
		$result = array();

		$sort_columns = array('gitemid'); // allowed columns for sorting

		$sql_parts = array(
			'select' => array('gitems' => 'gi.gitemid'),
			'from' => array('graphs_items gi'),
			'where' => array(),
			'order' => array(),
			'limit' => null,
		);

		$def_options = array(
			'nodeids' 				=> null,
			'graphids' 				=> null,
			'itemids' 				=> null,
			'type' 					=> null,
			'editable'				=> null,
			'nopermission'			=> null,
// output
			'expand_data'			=> null,
			'extendoutput'			=> null,
			'count'					=> null,
			'sortfield'				=> '',
			'sortorder'				=> '',
			'limit'					=> null
		);

		$options = zbx_array_merge($def_options, $options);

// editable + PERMISSION CHECK
		if(defined('ZBX_API_REQUEST')){
			$options['nopermissions'] = false;
		}

		if((USER_TYPE_SUPER_ADMIN == $user_type) || $options['nopermissions']){
		}
		else{
			$permission = $options['editable'] ? PERM_READ_WRITE : PERM_READ_ONLY;

			$sql_parts['from']['i'] = 'items i';
			$sql_parts['from']['hg'] = 'hosts_groups hg';
			$sql_parts['from']['r'] = 'rights r';
			$sql_parts['from']['ug'] = 'users_groups ug';
			$sql_parts['where']['igi'] = 'i.itemid=gi.itemid';
			$sql_parts['where']['hgi'] = 'hg.hostid=i.hostid';
			$sql_parts['where'][] = 'r.id=hg.groupid ';
			$sql_parts['where'][] = 'r.groupid=ug.usrgrpid';
			$sql_parts['where'][] = 'ug.userid='.$userid;
			$sql_parts['where'][] = 'r.permission>='.$permission;
			$sql_parts['where'][] = 'NOT EXISTS( '.
										' SELECT hgg.groupid '.
										' FROM hosts_groups hgg, rights rr, users_groups ugg '.
										' WHERE i.hostid=hgg.hostid '.
											' AND rr.id=hgg.groupid '.
											' AND rr.groupid=ugg.usrgrpid '.
											' AND ugg.userid='.$userid.
											' AND rr.permission<'.$permission.')';
		}

// nodeids
		$nodeids = $options['nodeids'] ? $options['nodeids'] : get_current_nodeid(false);

// graphids
		if(!is_null($options['graphids'])){
			zbx_value2array($options['graphids']);
			if(!is_null($options['extendoutput'])){
				$sql_parts['select']['graphid'] = 'gi.graphid';
			}
			$sql_parts['from']['g'] = 'graphs g';
			$sql_parts['where']['gig'] = 'gi.graphid=g.graphid';
			$sql_parts['where'][] = DBcondition('g.graphid', $options['graphids']);
		}
// itemids
		if(!is_null($options['itemids'])){
			zbx_value2array($options['itemids']);
			if(!is_null($options['extendoutput'])){
				$sql_parts['select']['itemid'] = 'gi.itemid';
			}
			$sql_parts['where'][] = DBcondition('gi.itemid', $options['itemids']);
		}
// type
		if(!is_null($options['type'] )){
			$sql_parts['where'][] = 'gi.type='.$options['type'];
		}
// extendoutput
		if(!is_null($options['extendoutput'])){
			$sql_parts['select']['gitems'] = 'gi.*';
		}
// expand_data
		if(!is_null($options['expand_data'])){
			$sql_parts['select']['key'] = 'i.key_';
			$sql_parts['select']['host'] = 'h.host';
			$sql_parts['from']['i'] = 'items i';
			$sql_parts['from']['h'] = 'hosts h';
			$sql_parts['where']['gii'] = 'gi.itemid=i.itemid';
			$sql_parts['where']['hi'] = 'h.hostid=i.hostid';
		}

// count
		if(!is_null($options['count'])){
			$sql_parts['select']['gitems'] = 'count(*) as count';
		}

// order
// restrict not allowed columns for sorting
		$options['sortfield'] = str_in_array($options['sortfield'], $sort_columns) ? $options['sortfield'] : '';
		if(!zbx_empty($options['sortfield'])){
			$sortorder = ($options['sortorder'] == ZBX_SORT_DOWN)?ZBX_SORT_DOWN:ZBX_SORT_UP;

			$sql_parts['order'][] = 'gi.'.$options['sortfield'].' '.$sortorder;

			if(!str_in_array('gi.'.$options['sortfield'], $sql_parts['select']) && !str_in_array('gi.*', $sql_parts['select'])){
				$sql_parts['select'][] = 'gi.'.$options['sortfield'];
			}
		}

// limit
		if(zbx_ctype_digit($options['limit']) && $options['limit']){
			$sql_parts['limit'] = $options['limit'];
		}
//------------

		$gitemids = array();

		$sql_parts['select'] = array_unique($sql_parts['select']);
		$sql_parts['from'] = array_unique($sql_parts['from']);
		$sql_parts['where'] = array_unique($sql_parts['where']);
		$sql_parts['order'] = array_unique($sql_parts['order']);

		$sql_select = '';
		$sql_from = '';
		$sql_where = '';
		$sql_order = '';
		if(!empty($sql_parts['select']))	$sql_select.= implode(',',$sql_parts['select']);
		if(!empty($sql_parts['from']))		$sql_from.= implode(',',$sql_parts['from']);
		if(!empty($sql_parts['where']))		$sql_where.= ' AND '.implode(' AND ',$sql_parts['where']);
		if(!empty($sql_parts['order']))		$sql_order.= ' ORDER BY '.implode(',',$sql_parts['order']);
		$sql_limit = $sql_parts['limit'];

		$sql = 'SELECT '.$sql_select.
				' FROM '.$sql_from.
				' WHERE '.DBin_node('gi.gitemid', $nodeids).
					$sql_where.
				$sql_order;
		$db_res = DBselect($sql, $sql_limit);
		while($gitem = DBfetch($db_res)){
			if($options['count'])
				$result = $gitem;
			else{
				if(is_null($options['extendoutput'])){
					$result[$gitem['gitemid']] = $gitem['gitemid'];
				}
				else{
					$gitemids[$gitem['gitemid']] = $gitem['gitemid'];

					if(!isset($result[$gitem['gitemid']]))
						$result[$gitem['gitemid']]= array();

					// graphids
					if(isset($gitem['graphid'])){
						if(!isset($result[$gitem['gitemid']]['graphids'])) $result[$gitem['gitemid']]['graphids'] = array();

						$result[$gitem['gitemid']]['graphids'][$gitem['graphid']] = $gitem['graphid'];
						unset($gitem['graphid']);
					}
					// itemids
					if(isset($gitem['itemid']) && !is_null($options['itemids'])){
						if(!isset($result[$gitem['gitemid']]['itemid'])) $result[$gitem['gitemid']]['itemid'] = array();

						$result[$gitem['gitemid']]['itemids'][$gitem['itemid']] = $gitem['itemid'];
						unset($gitem['itemid']);
					}

					$result[$gitem['gitemid']] += $gitem;
				}
			}
		}

		if(is_null($options['extendoutput']) || !is_null($options['count'])) return $result;

	return $result;
	}

	/**
	 * Gets all Graphitem data from DB by Graph ID
	 *
	 * @static
	 * @param _array $gitem_data
	 * @param string $gitem_data['gitemid']
	 * @return array|boolean host data as array or false if error
	 */
	public static function getById($gitem_data){
		$graph = get_graphitem_by_gitemid($gitem_data['gitemid']);

		$result = $graph ? true : false;
		if($result)
			return $graph;
		else{
			self::$error = array('error' => ZBX_API_ERROR_INTERNAL, 'data' => 'Graph with id: '.$gitem_data['gitemid'].' doesn\'t exists.');
			return false;
		}
	}

	/**
	 * Get graphid by graph name
	 *
	 * @static
	 * @param _array $gitem_data
	 * @param array $gitem_data['itemid']
	 * @param array $gitem_data['graphid']
	 * @return string|boolean graphid
	 */
	public static function getId($gitem_data){
		$result = false;

		$sql = 'SELECT gi.gitemid '.
				' FROM graphs_items gi '.
				' WHERE gi.itemid='.$gitem_data['itemid'].
					' AND gi.graphid='.$gitem_data['graphid'].
		$db_res = DBselect($sql);
		if($graph = DBfetch($db_res))
			$result = $graph['graphid'];
		else{
			self::$error = array('error' => ZBX_API_ERROR_INTERNAL, 'data' => 'Host with name: "'.$graph_data['name'].'" doesn\'t exists.');
		}

	return $result;
	}

}
?>