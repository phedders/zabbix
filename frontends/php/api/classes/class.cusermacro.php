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
 * File containing CUserMacro class for API.
 * @package API
 */
/**
 * Class containing methods for operations with UserMacro
 */
class CUserMacro {

	public static $error;

/**
 * Get UserMacros data
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param _array $options
 * @param array $options['nodeids'] Node IDs
 * @param array $options['groupids'] UserMacrosGroup IDs
 * @param array $options['macroids'] UserMacros IDs
 * @param boolean $options['monitored_macros'] only monitored UserMacross
 * @param boolean $options['templated_macros'] include templates in result
 * @param boolean $options['with_items'] only with items
 * @param boolean $options['with_monitored_items'] only with monitored items
 * @param boolean $options['with_historical_items'] only with historical items
 * @param boolean $options['with_triggers'] only with triggers
 * @param boolean $options['with_monitored_triggers'] only with monitores triggers
 * @param boolean $options['with_httptests'] only with http tests
 * @param boolean $options['with_monitored_httptests'] only with monitores http tests
 * @param boolean $options['with_graphs'] only with graphs
 * @param boolean $options['editable'] only with read-write permission. Ignored for SuperAdmins
 * @param int $options['extendoutput'] return all fields for UserMacross
 * @param int $options['count'] count UserMacross, returned column name is rowscount
 * @param string $options['pattern'] search macros by pattern in macro names
 * @param int $options['limit'] limit selection
 * @param string $options['order'] depricated parametr (for now)
 * @return array|boolean UserMacros data as array or false if error
 */
	public static function get($options=array()){
		global $USER_DETAILS;

		$result = array();
		$user_type = $USER_DETAILS['type'];
		$userid = $USER_DETAILS['userid'];

		$sort_columns = array('macro'); // allowed columns for sorting

		$sql_parts = array(
			'select' => array('macros' => 'hm.hostmacroid'),
			'from' => array('hostmacro hm'),
			'where' => array(),
			'order' => array(),
			'limit' => null);
			
		$sql_parts_global = array(
			'select' => array('macros' => 'gm.globalmacroid'),
			'from' => array('globalmacro gm'),
			'where' => array(),
			'order' => array(),
			'limit' => null);

		$def_options = array(
			'nodeids'					=> null,
			'groupids'					=> null,
			'hostids'					=> null,
			'hostmacroids'				=> null,
			'globalmacroids'			=> null,
			'templateids'				=> null,
			'macros'					=> null,
			'editable'					=> null,
			'nopermissions'				=> null,
// OutPut
			'globalmacro'				=> null,
			'extendoutput'				=> null,
			'select_groups'				=> null,
			'select_hosts'				=> null,
			'select_templates'			=> null,

			'count'						=> null,
			'pattern'					=> '',
			'sortfield'					=> '',
			'sortorder'					=> '',
			'limit'						=> null
		);

		$options = zbx_array_merge($def_options, $options);

// editable + PERMISSION CHECK
		if(defined('ZBX_API_REQUEST')){
			$options['nopermissions'] = false;
		}

		if((USER_TYPE_SUPER_ADMIN == $user_type) || $options['nopermissions'] || !is_null($options['globalmacros'])){
		}
		else{
			$permission = $options['editable'] ? PERM_READ_WRITE : PERM_READ_ONLY;

			$sql_parts['from']['hg'] = 'hosts_groups hg';
			$sql_parts['from']['r'] = 'rights r';
			$sql_parts['from']['ug'] = 'users_groups ug';
			$sql_parts['where']['hgh'] = 'hg.hostid=hm.hostid';
			$sql_parts['where'][] = 'r.id=hg.groupid ';
			$sql_parts['where'][] = 'r.groupid=ug.usrgrpid';
			$sql_parts['where'][] = 'ug.userid='.$userid;
			$sql_parts['where'][] = 'r.permission>='.$permission;
			$sql_parts['where'][] = 'NOT EXISTS( '.
									' SELECT hgg.groupid '.
									' FROM hosts_groups hgg, rights rr, users_groups gg '.
									' WHERE hgg.hostid=hg.hostid '.
										' AND rr.id=hgg.groupid '.
										' AND rr.groupid=gg.usrgrpid '.
										' AND gg.userid='.$userid.
										' AND rr.permission<'.$permission.')';
		}

// nodeids
		$nodeids = $options['nodeids'] ? $options['nodeids'] : get_current_nodeid(false);
		
// Global Macro
		if(!is_null($options['globalmacro'])){
			$options['groupids'] = null;
			$options['hostmacroids'] = null;
			$options['triggerids'] = null;
			$options['hostids'] = null;
			$options['itemids'] = null;
			
			$options['select_groups'] = null;
			$options['select_templates'] = null;
			$options['select_hosts'] = null;
		}

// globalmacroids
		if(!is_null($options['globalmacroids'])){
			zbx_value2array($options['globalmacroids']);
			$sql_parts_global['where'][] = DBcondition('gm.globalmacroid', $options['globalmacroids']);
		}
		
// hostmacroids
		if(!is_null($options['hostmacroids'])){
			zbx_value2array($options['hostmacroids']);
			$sql_parts['where'][] = DBcondition('hm.hostmacroid', $options['hostmacroids']);
		}

// groupids
		if(!is_null($options['groupids'])){
			zbx_value2array($options['groupids']);
			if(!is_null($options['extendoutput'])){
				$sql_parts['select']['groupid'] = 'hg.groupid';
			}

			$sql_parts['from']['hg'] = 'hosts_groups hg';
			$sql_parts['where'][] = DBcondition('hg.groupid', $options['groupids']);
			$sql_parts['where']['hgh'] = 'hg.hostid=hm.hostid';
		}

// hostids
		if(!is_null($options['hostids'])){
			zbx_value2array($options['hostids']);
			$sql_parts['where'][] = DBcondition('hm.hostid', $options['hostids']);
		}
		
// templateids
		if(!is_null($options['templateids'])){
			zbx_value2array($options['templateids']);
			if(!is_null($options['extendoutput'])){
				$sql_parts['select']['templateid'] = 'ht.templateid';
			}

			$sql_parts['from']['ht'] = 'macros_templates ht';
			$sql_parts['where'][] = DBcondition('ht.templateid', $options['templateids']);
			$sql_parts['where']['hht'] = 'hm.hostid=ht.macroid';
		}

// macros
		if(!is_null($options['macros'])){
			zbx_value2array($options['macros']);
			if(!is_null($options['extendoutput'])){
				$sql_parts['select']['macro'] = 'hm.macro';
				$sql_parts_global['select']['macro'] = 'gm.macro';
			}

			$sql_parts['where'][] = DBcondition('hm.macro', $options['macros'], null, true);
			$sql_parts_global['where'][] = DBcondition('gm.macro', $options['macros'], null, true);
		}

// extendoutput
		if(!is_null($options['extendoutput'])){
			$sql_parts['select']['macros'] = 'hm.*';
			$sql_parts_global['select']['macros'] = 'gm.*';
		}

// count
		if(!is_null($options['count'])){
			$options['sortfield'] = '';

			$sql_parts['select'] = array('count(DISTINCT hm.hostmacroid) as rowscount');
			$sql_parts_global['select'] = array('count(DISTINCT gm.globalmacroid) as rowscount');
		}

// pattern
		if(!zbx_empty($options['pattern'])){
			$sql_parts['where'][] = ' UPPER(hm.macro) LIKE '.zbx_dbstr('%'.strtoupper($options['pattern']).'%');
			$sql_parts_global['where'][] = ' UPPER(gm.macro) LIKE '.zbx_dbstr('%'.strtoupper($options['pattern']).'%');
		}

// order
// restrict not allowed columns for sorting
		$options['sortfield'] = str_in_array($options['sortfield'], $sort_columns) ? $options['sortfield'] : '';
		if(!zbx_empty($options['sortfield'])){
			$sortorder = ($options['sortorder'] == ZBX_SORT_DOWN)?ZBX_SORT_DOWN:ZBX_SORT_UP;

			$sql_parts['order'][] = 'hm.'.$options['sortfield'].' '.$sortorder;
			$sql_parts_global['order'][] = 'gm.'.$options['sortfield'].' '.$sortorder;

			if(!str_in_array('hm.'.$options['sortfield'], $sql_parts['select']) && !str_in_array('hm.*', $sql_parts['select'])){
				$sql_parts['select'][] = 'hm.'.$options['sortfield'];
			}
			
			if(!str_in_array('gm.'.$options['sortfield'], $sql_parts_global['select']) && !str_in_array('gm.*', $sql_parts_global['select'])){
				$sql_parts_global['select'][] = 'gm.'.$options['sortfield'];
			}
		}

// limit
		if(zbx_ctype_digit($options['limit']) && $options['limit']){
			$sql_parts['limit'] = $options['limit'];
			$sql_parts_global['limit'] = $options['limit'];
		}
//-------
	
// init GLOBALS
		if(!is_null($options['globalmacro'])){
			$sql_parts_global['select'] = array_unique($sql_parts_global['select']);
			$sql_parts_global['from'] = array_unique($sql_parts_global['from']);
			$sql_parts_global['where'] = array_unique($sql_parts_global['where']);
			$sql_parts_global['order'] = array_unique($sql_parts_global['order']);
	
			$sql_select = '';
			$sql_from = '';
			$sql_where = '';
			$sql_order = '';
			if(!empty($sql_parts_global['select']))		$sql_select.= implode(',',$sql_parts_global['select']);
			if(!empty($sql_parts_global['from']))		$sql_from.= implode(',',$sql_parts_global['from']);
			if(!empty($sql_parts_global['where']))		$sql_where.= ' AND '.implode(' AND ',$sql_parts_global['where']);
			if(!empty($sql_parts_global['order']))		$sql_order.= ' ORDER BY '.implode(',',$sql_parts_global['order']);
			$sql_limit = $sql_parts_global['limit'];
	
			$sql = 'SELECT '.$sql_select.'
					FROM '.$sql_from.'
					WHERE '.DBin_node('gm.globalmacroid', $nodeids).
					$sql_where.
					$sql_order;
			$res = DBselect($sql, $sql_limit);
			while($macro = DBfetch($res)){
				if($options['count'])
					$result = $macro;
				else{
					$globalmacroids[$macro['globalmacroid']] = $macro['globalmacroid'];
	
					if(is_null($options['extendoutput'])){
						$result[$macro['globalmacroid']] = $macro['globalmacroid'];
					}
					else{
						if(!isset($result[$macro['globalmacroid']])) $result[$macro['globalmacroid']]= array();
	
						$result[$macro['globalmacroid']] += $macro;
					}
				}
			}
		}
// init HOSTS
		else{
			$hostids = array();

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
	
			$sql = 'SELECT '.$sql_select.'
					FROM '.$sql_from.'
					WHERE '.DBin_node('hm.hostmacroid', $nodeids).
					$sql_where.
					$sql_order;
			$res = DBselect($sql, $sql_limit);
			while($macro = DBfetch($res)){
				if($options['count'])
					$result = $macro;
				else{
					$hostmacroids[$macro['hostmacroid']] = $macro['hostmacroid'];
	
					if(is_null($options['extendoutput'])){
						$result[$macro['hostmacroid']] = $macro['hostmacroid'];
					}
					else{
						$hostids[$macro['hostid']] = $macro['hostid'];
						
						if(!isset($result[$macro['hostmacroid']])) $result[$macro['hostmacroid']]= array();
	
						if($options['select_groups'] && !isset($result[$macro['hostmacroid']]['groupids'])){
							$result[$macro['hostmacroid']]['groupids'] = array();
							$result[$macro['hostmacroid']]['groups'] = array();
						}
	
						if($options['select_templates'] && !isset($result[$macro['hostmacroid']]['templateids'])){
							$result[$macro['hostmacroid']]['templateids'] = array();
							$result[$macro['hostmacroid']]['templates'] = array();
						}
	
						if($options['select_hosts'] && !isset($result[$macro['hostmacroid']]['hostids'])){
							$result[$macro['hostmacroid']]['hostids'] = array();
							$result[$macro['hostmacroid']]['hosts'] = array();
						}
	
	
						// groupids
						if(isset($macro['groupid'])){
							if(!isset($result[$macro['hostmacroid']]['groupids']))
								$result[$macro['hostmacroid']]['groupids'] = array();
	
							$result[$macro['hostmacroid']]['groupids'][$macro['groupid']] = $macro['groupid'];
							unset($macro['groupid']);
						}
						// templateids
						if(isset($macro['templateid'])){
							if(!isset($result[$macro['hostmacroid']]['templateids']))
								$result[$macro['hostmacroid']]['templateids'] = array();
	
							$result[$macro['hostmacroid']]['templateids'][$macro['templateid']] = $macro['templateid'];
							unset($macro['templateid']);
						}
						// hostids
						if(isset($macro['hostid'])){
							if(!isset($result[$macro['hostmacroid']]['hostids']))
								$result[$macro['hostmacroid']]['hostids'] = array();
	
							$result[$macro['hostmacroid']]['hostids'][$macro['hostid']] = $macro['hostid'];
							unset($macro['hostid']);
						}
	
						$result[$macro['hostmacroid']] += $macro;
					}
				}
			}
		}

		if(is_null($options['extendoutput']) || !is_null($options['count'])) return $result;

// Adding Objects
// Adding Groups
		if($options['select_groups']){
			$obj_params = array('extendoutput' => 1, 'hostids' => $hostids);
			$groups = CHostgroup::get($obj_params);
			foreach($groups as $groupid => $group){
				foreach($group['hostids'] as $num => $hostid){
					foreach($result as $macroid => $macro){
						if($macro['hostid'] == $hostid){
							$result[$macroid]['groups'][$groupid] = $group;
							$result[$macroid]['groupids'][$groupid] = $groupid;
						}
					}
				}
			}
		}
		
// Adding Templates
		if($options['select_templates']){
			$obj_params = array('extendoutput' => 1, 'templateids' => $hostids);
			$templates = CTemplate::get($obj_params);
			foreach($templates as $templateid => $template){
				foreach($template['hostids'] as $num => $hostid){
					foreach($result as $macroid => $macro){
						if($macro['hostid'] == $hostid){
							$result[$macroid]['templates'][$templateid] = $template;
							$result[$macroid]['templateids'][$templateid] = $templateid;
						}
					}				}
			}
		}

// Adding Hosts
		if($options['select_hosts']){
			$obj_params = array('extendoutput' => 1, 'hostids' => $hostids);
			$hosts = CHost::get($obj_params);
			foreach($hosts as $id => $host){
				foreach($template['hostids'] as $num => $hostid){
					foreach($result as $macroid => $macro){
						if($macro['hostid'] == $hostid){
							$result[$macroid]['hosts'][$hostid] = $host;
							$result[$macroid]['hostids'][$hostid] = $hostid;
						}
					}
				}
			}
		}

	return $result;
	}

/**
 * Gets all UserMacros data from DB by UserMacros ID
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param _array $macro_data
 * @param string $macro_data['macroid']
 * @return array|boolean UserMacros data as array or false if error
 */
	public static function getHostMacroById($macro_data){
		$sql = 'SELECT * FROM hostmacro WHERE hostmacroid='.$macro_data['hostmacroid'];
		$macro = DBfetch(DBselect($sql));

		$result = $macro ? true : false;
		if($result)
			return $macro;
		else{
			self::$error = array('error' => ZBX_API_ERROR_NO_HOST, 'data' => 'HostMacro with id: '.$macro_data['macroid'].' doesn\'t exists.');
			return false;
		}
	}

	
/**
 * Update host macros, replace all with new ones
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param _array $macros
 * @param string $macros['hostid']
 * @param string $macros['macros'][0..]['macro']
 * @param string $macros['macros'][0..]['value']
 * @return array|boolean 
 */
	public static function update($macros){
		$result = true;

		DBstart(false);
		
		$sql = 'DELETE FROM hostmacro WHERE hostid='.$macros['hostid'];
		DBexecute($sql);

		foreach($macros['macros'] as $macro){
			$hostmacroid = get_dbid('hostmacro', 'hostmacroid');
			
			$sql = 'INSERT INTO hostmacro (hostmacroid, hostid, macro, value) 
				VALUES('.$hostmacroid.', '.$macros['hostid'].', '.zbx_dbstr($macro['macro']).', '.zbx_dbstr($macro['value']).')';
			$result = DBExecute($sql);
			
			if(!$result) break;
		}
		DBend($result);	
		
		if($result)
			return true;
		else{
			self::$error = array('error' => ZBX_API_ERROR_NO_HOST, 'data' => '');
			return false;
		}
	}

/**
 * Add global macros
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param _array $macros

 * @param string $macros[0..]['macro']
 * @param string $macros[0..]['value']
 * @return array|boolean 
 */	
	public static function addGlobal($macros){
		$result = true;
		DBstart(false);
		foreach($macros as $macro){

			$globalmacroid = get_dbid('globalmacro', 'globalmacroid');
			
			$sql = 'INSERT INTO globalmacro (globalmacroid, macro, value) 
				VALUES('.$globalmacroid.', '.zbx_dbstr($macro['macro']).', '.zbx_dbstr($macro['value']).')';
			$result = DBExecute($sql);
			
			if(!$result) break;
		}
		DBend($result);	
		
		if($result)
			return true;
		else{
			self::$error = array('error' => ZBX_API_ERROR_NO_HOST, 'data' => '');
			return false;
		}
	}
	
/**
 * Validates macros expression
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param _array $macros array with macros expressions
 * @return array|boolean 
 */
	public static function validate($macros){
		zbx_value2array($macros);
		$result = true;
	
		foreach($macros as $macro){
			if(!preg_match('/^'.ZBX_PREG_EXPRESSION_USER_MACROS.'$/', $macro)){
				$result = false;
				break;
			}
		}
		
		if($result)
			return true;
		else{
			self::$error = array('error' => ZBX_API_ERROR_NO_HOST, 'data' => '');
			return false;
		}
	}
	
/**
 * Gets all UserMacros data from DB by UserMacros ID
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param _array $macro_data
 * @param string $macro_data['macroid']
 * @return array|boolean UserMacros data as array or false if error
 */
	public static function getGlobalMacroById($macro_data){
		$sql = 'SELECT * FROM globalmacro WHERE globalmacroid='.$macro_data['globalmacroid'];
		$macro = DBfetch(DBselect($sql));

		$result = $macro ? true : false;
		if($result)
			return $macro;
		else{
			self::$error = array('error' => ZBX_API_ERROR_NO_HOST, 'data' => 'GlobalMacro with id: '.$macro_data['macroid'].' doesn\'t exists.');
			return false;
		}
	}

/**
 * Get UserMacros ID by UserMacros name
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param _array $macro_data
 * @param string $macro_data['macro']
 * @param string $macro_data['hostid']
 * @return int|boolean
 */
	public static function getHostMacroId($macro_data){
		$result = false;

		$sql = 'SELECT hostmacroid '.
				' FROM hostmacro '.
				' WHERE macro='.zbx_dbstr($macro_data['macro']).
					' AND hostid='.$macro_data['hostid'].
					' AND '.DBin_node('hostmacroid', get_current_nodeid(false));
		$res = DBselect($sql);
		if($macroid = DBfetch($res))
			$result = $macroid['hostmacroid'];
		else{
			self::$error = array('error' => ZBX_API_ERROR_NO_HOST, 'data' => 'HostMacros with name: "'.$macro_data['macro'].'" doesn\'t exists.');
		}

	return $result;
	}


/**
 * Get UserMacros ID by UserMacros name
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param _array $macro_data
 * @param string $macro_data['macro']
 * @return int|boolean
 */
	public static function getGlobalMacroId($macro_data){
		$result = false;

		$sql = 'SELECT globalmacroid '.
				' FROM globalmacro '.
				' WHERE macro='.zbx_dbstr($macro_data['macro']).
					' AND '.DBin_node('globalmacroid', get_current_nodeid(false));
		$res = DBselect($sql);
		if($macroid = DBfetch($res))
			$result = $macroid['globalmacroid'];
		else{
			self::$error = array('error' => ZBX_API_ERROR_NO_HOST, 'data' => 'GlobalMacros with name: "'.$macro_data['macro'].'" doesn\'t exists.');
		}

	return $result;
	}


	public static function getMacros($macros, $options){
		zbx_value2array($macros);
		
		$def_options = array( 'itemid' => null, 'triggerid' => null);
		$options = zbx_array_merge($def_options, $options);
		
		$hmacro = array();
		$obj_options = array(
				'extendoutput' => 1,
				'globalmacro' => 1,
				'nopermissions' => 1,
				'macros' => $macros
				);
		$gmacros = self::get($obj_options);

		$obj_options = array(
			'nopermissions' => 1,
			'itemids' => $options['itemid'],
			'triggerids' => $options['triggerid'],
			);
		$hosts = CHost::get($obj_options);

		$hmacros = array();
		while((count($hmacro) < count($macros)) && !empty($hosts)){
			$obj_options = array(
				'nopermissions' => 1,
				'extendoutput' => 1,
				'macros' => $macros,
				'hostids' => $hosts,
				);

			$tmacros = self::get($obj_options);
			$hmacros = zbx_array_merge($tmacros, $hmacros);		

			$obj_options = array(
				'nopermissions' => 1,
				'hostids' => $hosts,
				);
			$hosts = CTemplate::get($obj_options);
		}
		
		$macros = zbx_array_merge($gmacros + $hmacros);
		
		$result = array();
		foreach($macros as $macroid => $macro){
			$result[$macro['macro']] = $macro['value'];
		}

//SDII($result);

	return $result;
	}
	
	public static function resolveTrigger(&$triggers){
		$single = false;
		if(isset($triggers['triggerid'])){
			$single = true;
			$triggers = array($triggers);
		}
		
		foreach($triggers as $num => $trigger){
			if(!isset($trigger['triggerid']) || !isset($trigger['expression'])) continue;

			if($res = preg_match_all('/'.ZBX_PREG_EXPRESSION_USER_MACROS.'/', $trigger['expression'], $arr)){
				$macros = self::getMacros($arr[1], array('triggerid' => $trigger['triggerid']));

				$search = array_keys($macros);
				$values = array_values($macros);
				$triggers[$num]['expression'] = str_replace($search, $values, $trigger['expression']);
			}
		}
		
		if($single) $triggers = $triggers[0];
	}
	
	
	public static function resolveItem(&$items){
		$single = false;
		if(isset($items['itemid'])){
			$single = true;
			$items = array($items);
		}

		foreach($items as $num => $item){
			if(!isset($item['itemid']) || !isset($item['key_'])) continue;
			
			if($res = preg_match_all('/'.ZBX_PREG_EXPRESSION_USER_MACROS.'/', $item['key_'], $arr)){
				$macros = self::getMacros($arr[1], array('itemid' => $item['itemid']));

				$search = array_keys($macros);
				$values = array_values($macros);
				$items[$num]['key_'] = str_replace($search, $values, $item['key_']);
			}
		}

		if($single) $items = $items[0];
	}
	

/**
 * Delete UserMacros
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param array $macroids
 * @return boolean
 */
	public static function deleteHostMacro($hostmacroids){
		zbx_value2array($hostmacroids);
		
		$sql = 'DELETE FROM hostmacro WHERE '.DBcondition('hostmacroid', $hostmacroids);
		$result = DBexecute($sql);
		if($result)
			return $hostmacroids;
		else{
			self::$error = array('error' => ZBX_API_ERROR_INTERNAL, 'data' => 'Internal zabbix error');
			return false;
		}
	}
	
/**
 * Delete UserMacros
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param array $macroids
 * @return boolean
 */
	public static function deleteGlobalMacro($globalmacroids){
		zbx_value2array($globalmacroids);
		
		$sql = 'DELETE FROM globalmacro WHERE '.DBcondition('globalmacroid', $globalmacroids);
		$result = DBexecute($sql);
		if($result)
			return $globalmacroids;
		else{
			self::$error = array('error' => ZBX_API_ERROR_INTERNAL, 'data' => 'Internal zabbix error');
			return false;
		}
	}
}
?>