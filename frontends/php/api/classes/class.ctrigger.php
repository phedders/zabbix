<?php
/**
 * File containing CTrigger class for API.
 * @package API
 */
/**
 * Class containing methods for operations with Triggers
 *
 */
class CTrigger {

	public static $error = array();

	/**
	 * Get Triggers data
	 *
	 * {@source}
	 * @access public
	 * @static
	 * @since 1.8
	 * @version 1
	 *
	 * @param _array $options
	 * @param array $options['itemids']
	 * @param array $options['hostids']
	 * @param array $options['groupids']
	 * @param array $options['triggerids']
	 * @param array $options['applicationids']
	 * @param array $options['status']
	 * @param array $options['templated_items']
	 * @param array $options['editable']
	 * @param array $options['extendoutput']
	 * @param array $options['count']
	 * @param array $options['pattern']
	 * @param array $options['limit']
	 * @param array $options['order']
	 * @return array|int item data as array or false if error
	 */
	public static function get($options=array()){
		global $USER_DETAILS;

		$result = array();
		$user_type = $USER_DETAILS['type'];
		$userid = $USER_DETAILS['userid'];
		
		$sort_columns = array('triggerid'); // allowed columns for sorting

		
		$sql_parts = array(
			'select' => array('triggers' => 't.triggerid'),
			'from' => array('triggers t'),
			'where' => array(),
			'order' => array(),
			'limit' => null,
			);

		$def_options = array(
			'nodeids'				=> 0,
			'groupids'				=> 0,
			'hostids'				=> 0,
			'triggerids'			=> 0,
			'itemids'				=> 0,
			'applicationids'		=> 0,
			'status'				=> null,
			'severity'				=> null,
			'templated_triggers'	=> 0,
			'editable'				=> 0,
			'nopermissions'			=> 0,
// OutPut
			'extendoutput'			=> 0,
			'select_hosts'			=> 0,
			'select_items'			=> 0,
			'count'					=> 0,
			'pattern'				=> '',
			'limit'					=> 0,
			'order'					=> '');

		$options = array_merge($def_options, $options);

		
// editable + PERMISSION CHECK
		if(defined('ZBX_API_REQUEST')){
			$options['nopermissions'] = false;
		}
		
		if((USER_TYPE_SUPER_ADMIN == $user_type) || $options['nopermissions']){
		}
		else{
			$permission = $options['editable']?PERM_READ_WRITE:PERM_READ_ONLY;
			   
			$sql_parts['from']['f'] = 'functions f';
			$sql_parts['from']['i'] = 'items i';
			$sql_parts['from']['hg'] = 'hosts_groups hg';
			$sql_parts['from']['r'] = 'rights r';
			$sql_parts['from']['ug'] = 'users_groups ug';
			$sql_parts['where']['ft'] = 'f.triggerid=t.triggerid';
			$sql_parts['where']['fi'] = 'f.itemid=i.itemid';
			$sql_parts['where']['hgi'] = 'hg.hostid=i.hostid';
			$sql_parts['where'][] = 'r.id=hg.groupid ';
			$sql_parts['where'][] = 'r.groupid=ug.usrgrpid';
			$sql_parts['where'][] = 'ug.userid='.$userid;
			$sql_parts['where'][] = 'r.permission>='.$permission;
			$sql_parts['where'][] = 'NOT EXISTS( '.
											' SELECT ff.triggerid '.
											' FROM functions ff, items ii '.
											' WHERE ff.triggerid=t.triggerid '.
												' AND ff.itemid=ii.itemid '.
												' AND EXISTS( '.
													' SELECT hgg.groupid '.
													' FROM hosts_groups hgg, rights rr, users_groups gg '.
													' WHERE hgg.hostid=ii.hostid '.
														' AND rr.id=hgg.groupid '.
														' AND rr.groupid=gg.usrgrpid '.
														' AND gg.userid='.$userid.
														' AND rr.permission<'.$permission.'))';
		}

// nodeids
		$nodeids = $options['nodeids'] ? $options['nodeids'] : get_current_nodeid(false);

// groupids
		if($options['groupids'] != 0){
			zbx_value2array($options['groupids']);

			if($options['extendoutput'] != 0){
				$sql_parts['select']['groupid'] = 'hg.groupid';
			}

			$sql_parts['from']['f'] = 'functions f';
			$sql_parts['from']['i'] = 'items i';
			$sql_parts['from']['hg'] = 'hosts_groups hg';
			$sql_parts['where'][] = DBcondition('hg.groupid', $options['groupids']);
			$sql_parts['where'][] = 'hg.hostid=i.hostid';
			$sql_parts['where'][] = 'f.triggerid=t.triggerid';
			$sql_parts['where'][] = 'f.itemid=i.itemid';
		}

// hostids
		if($options['hostids'] != 0){
			zbx_value2array($options['hostids']);

			if($options['extendoutput'] != 0){
				$sql_parts['select']['hostid'] = 'i.hostid';
			}

			$sql_parts['from']['f'] = 'functions f';
			$sql_parts['from']['i'] = 'items i';
			$sql_parts['where'][] = DBcondition('i.hostid', $options['hostids']);
			$sql_parts['where']['ft'] = 'f.triggerid=t.triggerid';
			$sql_parts['where']['fi'] = 'f.itemid=i.itemid';
		}

// triggerids
		if($options['triggerids'] != 0){
			$sql_parts['where'][] = DBcondition('t.triggerid', $options['triggerids']);
		}

// itemids
		if($options['itemids'] != 0){
			zbx_value2array($options['itemids']);
			if($options['extendoutput'] != 0){
				$sql_parts['select']['itemid'] = 'f.itemid';
			}
			$sql_parts['from']['f'] = 'functions f';
			$sql_parts['where'][] = DBcondition('f.itemid', $options['itemids']);
			$sql_parts['where']['ft'] = 'f.triggerid=t.triggerid';
		}

// applicationids
		if($options['applicationids'] != 0){
			$sql_parts['from']['f'] = 'functions f';
			$sql_parts['from']['i'] = 'items i';
			$sql_parts['from']['a'] = 'applications a';
			$sql_parts['where'][] = DBcondition('a.applicationid', $options['applicationids']);
			$sql_parts['where'][] = 'i.hostid=a.hostid';
			$sql_parts['where'][] = 'f.triggerid=t.triggerid';
			$sql_parts['where'][] = 'f.itemid=i.itemid';
		}

// status
		if(!is_null($options['status'])){
			$sql_parts['where'][] = 't.status='.$options['status'];
		}

// severity
		if(!is_null($options['severity'])){
			$sql_parts['where'][] = 't.priority='.$options['severity'];
		}

// templated_triggers
		if($options['templated_triggers'] != 0){
			$sql_parts['where'][] = 't.templateid<>0';
		}
		
// extendoutput
		if($options['extendoutput'] != 0){
			$sql_parts['select']['triggers'] = 't.*';
		}
		
// count
		if($options['count'] != 0){
			$sql_parts['select']['triggers'] = 'count(t.triggerid) as rowscount';
		}
		
// pattern
		if(!zbx_empty($options['pattern'])){
			$sql_parts['where'][] = ' UPPER(t.description) LIKE '.zbx_dbstr('%'.strtoupper($options['pattern']).'%');
		}

// order
		// restrict not allowed columns for sorting
		$options['order'] = in_array($options['order'], $sort_columns) ? $options['order'] : '';
		
		if(!zbx_empty($options['order'])){
			$sql_parts['order'][] = 't.'.$options['order'];
			if(!str_in_array('t.'.$options['order'], $sql_parts['select'])) $sql_parts['select'][] = 't.'.$options['order'];
		}

// limit
		if(zbx_ctype_digit($options['limit']) && $options['limit']){
			$sql_parts['limit'] = $options['limit'];
		}
//---------------

		$triggerids = array();
		
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
				' WHERE '.DBin_node('t.triggerid', $nodeids).
					$sql_where.
				$sql_order;
		$db_res = DBselect($sql, $sql_limit);
		while($trigger = DBfetch($db_res)){
			if($options['count'])
				$result = $trigger;
			else{
				$triggerids[$trigger['triggerid']] = $trigger['triggerid'];
				
				if($options['extendoutput'] == 0){
					$result[$trigger['triggerid']] = $trigger['triggerid'];
				}
				else{
					if(!isset($result[$trigger['triggerid']])) $result[$trigger['triggerid']]= array();

					if($options['select_hosts'] && !isset($result[$trigger['triggerid']]['hostids'])){ 
						$result[$trigger['triggerid']]['hostids'] = array();
						$result[$trigger['triggerid']]['hosts'] = array();
					}
					if($options['select_items'] && !isset($result[$trigger['triggerid']]['itemids'])){ 
						$result[$trigger['triggerid']]['itemids'] = array();
						$result[$trigger['triggerid']]['items'] = array();
					}
					
					// hostids
					if(isset($trigger['hostid'])){
						if(!isset($result[$trigger['triggerid']]['hostids'])) $result[$trigger['triggerid']]['hostids'] = array();

						$result[$trigger['triggerid']]['hostids'][$trigger['hostid']] = $trigger['hostid'];
						unset($trigger['hostid']);
					}
					// itemids
					if(isset($trigger['itemid'])){
						if(!isset($result[$trigger['triggerid']]['itemids'])) $result[$trigger['triggerid']]['itemids'] = array();

						$result[$trigger['triggerid']]['itemids'][$trigger['itemid']] = $trigger['itemid'];
						unset($trigger['itemid']);
					}
					
					$result[$trigger['triggerid']] += $trigger;
				}
			}
		}
		
// Adding Objects

// Adding hosts
		if($options['select_hosts']){
			$obj_params = array('templated_hosts' => 1, 'extendoutput' => 1, 'triggerids' => $triggerids, 'nopermissions' => 1);
			$hosts = CHost::get($obj_params);
			foreach($hosts as $hostid => $host){
				foreach($host['triggerids'] as $num => $triggerid){
					$result[$triggerid]['hostids'][$hostid] = $hostid;
					$result[$triggerid]['hosts'][$hostid] = $host;
				}
			}
		}

// Adding Items
		if($options['select_items']){
			$obj_params = array('extendoutput' => 1, 'triggerids' => $triggerids, 'nopermissions' => 1);
			$items = CItem::get($obj_params);
			foreach($items as $itemid => $item){
				foreach($item['triggerids'] as $num => $triggerid){
					$result[$triggerid]['itemids'][$itemid] = $itemid;
					$result[$triggerid]['items'][$itemid] = $item;
				}
			}
		}
		
	return $result;
	}

	/**
	 * Gets all trigger data from DB by Trigger ID
	 *
	 * {@source}
	 * @access public
	 * @static
	 * @since 1.8
	 * @version 1
	 *
	 * @param _array $trigger
	 * @param array $trigger['triggerid']
	 * @return array|boolean array of trigger data || false if error
	 */
	public static function getById($trigger){
		$trigger =  get_trigger_by_triggerid($trigger['triggerid']);
		$result = $trigger ? true : false;
		if($result)
			$result = $trigger;
		else
			self::$error[] = array('error' => ZBX_API_ERROR_INTERNAL, 'data' => 'Internal zabbix error');

		return $result;
	}

	/**
	 * Get triggerid by host.host and trigger.expression
	 *
	 * {@source}
	 * @access public
	 * @static
	 * @since 1.8
	 * @version 1
	 *
	 * @param _array $trigger
	 * @param array $trigger['expression']
	 * @param array $trigger['host']
	 * @param array $trigger['hostid']
	 * @param array $trigger['description']
	 * @return string|boolean triggerid || false if error
	 */
	public static function getId($trigger){

		$result = false;

		$sql_where = '';
		$sql_from = '';
		if(isset($trigger['hostid']) && isset($trigger['host'])) {
			$sql_where .= ''.
					' i.hostid='.$trigger['hostid'].
					' AND f.itemid=i.itemid '.
					' AND f.triggerid=t.triggerid'.
					' AND i.hostid=h.hostid'.
					' AND h.host='.zbx_dbstr($trigger['host']);
			$sql_from .= ', functions f, items i, hosts h ';
		}
		else{
			if(isset($trigger['hostid'])) {
				$sql_where .= ''.
					' i.hostid='.$trigger['hostid'].
					' AND f.itemid=i.itemid '.
					' AND f.triggerid=t.triggerid';
				$sql_from .= ', functions f, items i';
			}
			if(isset($trigger['host'])) {
				$sql_where .= ''.
					' f.itemid=i.itemid '.
					' AND f.triggerid=t.triggerid'.
					' AND i.hostid=h.hostid'.
					' AND h.host='.zbx_dbstr($trigger['host']);
				$sql_from .= ', functions f, items i, hosts h ';
			}
		}
		if(isset($trigger['description'])) {
			$sql_where .= ' AND t.description='.zbx_dbstr($trigger['description']);
		}

		$sql = 'SELECT DISTINCT t.triggerid, t.expression '.
				' FROM triggers t'.$sql_from.
				' WHERE '.$sql_where;
		if($db_triggers = DBselect($sql)){
			$result = true;
			$triggerid = null;

			while($tmp_trigger = DBfetch($db_triggers)) {
				$tmp_exp = explode_exp($tmp_trigger['expression'], false);
				if(strcmp($tmp_exp, $trigger['expression']) == 0) {
					$triggerid = $tmp_trigger['triggerid'];
					break;
				}
			}
		}

		if($result)
			return $triggerid;
		else{
			self::$error[] = array('error' => ZBX_API_ERROR_INTERNAL, 'data' => 'Internal zabbix error');
			return false;
		}
	}

	/**
	 * Add triggers
	 *
	 * {@source}
	 * @access public
	 * @static
	 * @since 1.8
	 * @version 1
	 *
	 * @param _array $triggers multidimensional array with triggers data
	 * @param array $triggers[0,...]['expression']
	 * @param array $triggers[0,...]['description']
	 * @param array $triggers[0,...]['type'] OPTIONAL
	 * @param array $triggers[0,...]['priority'] OPTIONAL
	 * @param array $triggers[0,...]['status'] OPTIONAL
	 * @param array $triggers[0,...]['comments'] OPTIONAL
	 * @param array $triggers[0,...]['url'] OPTIONAL
	 * @return boolean
	 */
	public static function add($triggers){

		$triggerids = array();
		DBstart(false);

		$result = false;
		foreach($triggers as $trigger){
			$trigger_db_fields = array(
				'expression'	=> null,
				'description'	=> null,
				'type'		=> 0,
				'priority'	=> 1,
				'status'	=> TRIGGER_STATUS_DISABLED,
				'comments'	=> '',
				'url'		=> ''
			);

			if(!check_db_fields($trigger_db_fields, $trigger)){
				$result = false;
				break;
			}

			$result = add_trigger($trigger['expression'], $trigger['description'], $trigger['type'], $trigger['priority'],
			$trigger['status'], $trigger['comments'], $trigger['url']);
			if(!$result) break;
			$triggerids[$result] = $result;
		}

		$result = DBend($result);
		if($result)
			return $triggerids;
		else{
			self::$error[] = array('error' => ZBX_API_ERROR_INTERNAL, 'data' => 'Internal zabbix error');
			return false;
		}
	}

	/**
	 * Update triggers
	 *
	 * {@source}
	 * @access public
	 * @static
	 * @since 1.8
	 * @version 1
	 *
	 * @param _array $triggers multidimensional array with triggers data
	 * @param array $triggers[0,...]['expression']
	 * @param array $triggers[0,...]['description'] OPTIONAL
	 * @param array $triggers[0,...]['type'] OPTIONAL
	 * @param array $triggers[0,...]['priority'] OPTIONAL
	 * @param array $triggers[0,...]['status'] OPTIONAL
	 * @param array $triggers[0,...]['comments'] OPTIONAL
	 * @param array $triggers[0,...]['url'] OPTIONAL
	 * @return boolean
	 */
	public static function update($triggers){

		$result = false;
		$triggerids = array();
		DBstart(false);
		foreach($triggers as $trigger){
			$trigger_db_fields = self::getById($trigger);
			if(!isset($trigger_db_fields)) {
				$result = false;
				break;
			}

			if(!check_db_fields($trigger_db_fields, $trigger)){
				error('Incorrect arguments pasted to function [CTrigger::update]');
				$result = false;
				break;
			}

			$trigger['expression'] = explode_exp($trigger['expression'], false);
			$result = update_trigger($trigger['triggerid'], $trigger['expression'], $trigger['description'], $trigger['type'],
				$trigger['priority'], $trigger['status'], $trigger['comments'], $trigger['url']);
			if(!$result) break;
			$triggerids[$result] = $result;
		}
		$result = DBend($result);

		if($result)
			return $triggerids;
		else{
			self::$error[] = array('error' => ZBX_API_ERROR_INTERNAL, 'data' => 'Internal zabbix error');
			return false;
		}
	}

	/**
	 * Delete triggers
	 *
	 * {@source}
	 * @access public
	 * @static
	 * @since 1.8
	 * @version 1
	 *
	 * @param _array $triggerids
	 * @return boolean
	 */
	public static function delete($triggerids){
		$result = delete_trigger($triggerids);
		if($result)
			return $result;
		else{
			self::$error[] = array('error' => ZBX_API_ERROR_INTERNAL, 'data' => 'Internal zabbix error');
			return false;
		}
	}

	/**
	 * Add dependency for trigger
	 *
	 * {@source}
	 * @access public
	 * @static
	 * @since 1.8
	 * @version 1
	 *
	 * @param _array $triggers_data
	 * @param array $triggers_data['triggerid]
	 * @param array $triggers_data['depends_on_triggerid']
	 * @return boolean
	 */
	public static function addDependency($triggers_data){
		$result = insert_dependency($triggers_data['triggerid'], $triggers_data['depends_on_triggerid']);
		if($result)
			return $groupids;
		else{
			self::$error[] = array('error' => ZBX_API_ERROR_INTERNAL, 'data' => 'Internal zabbix error');
			return false;
		}
	}
}
?>
