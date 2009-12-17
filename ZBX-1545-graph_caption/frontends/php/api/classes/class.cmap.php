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
 * File containing CMap class for API.
 * @package API
 */
/**
 * Class containing methods for operations with Maps
 */
class CMap extends CZBXAPI{
/**
 * Get Map data
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param _array $options
 * @param array $options['nodeids'] Node IDs
 * @param array $options['groupids'] HostGroup IDs
 * @param array $options['hostids'] Host IDs
 * @param boolean $options['monitored_hosts'] only monitored Hosts
 * @param boolean $options['templated_hosts'] include templates in result
 * @param boolean $options['with_items'] only with items
 * @param boolean $options['with_monitored_items'] only with monitored items
 * @param boolean $options['with_historical_items'] only with historical items
 * @param boolean $options['with_triggers'] only with triggers
 * @param boolean $options['with_monitored_triggers'] only with monitored triggers
 * @param boolean $options['with_httptests'] only with http tests
 * @param boolean $options['with_monitored_httptests'] only with monitored http tests
 * @param boolean $options['with_graphs'] only with graphs
 * @param boolean $options['editable'] only with read-write permission. Ignored for SuperAdmins
 * @param int $options['extendoutput'] return all fields for Hosts
 * @param int $options['count'] count Hosts, returned column name is rowscount
 * @param string $options['pattern'] search hosts by pattern in host names
 * @param int $options['limit'] limit selection
 * @param string $options['sortorder']
 * @param string $options['sortfield']
 * @return array|boolean Host data as array or false if error
 */
	public static function get($options=array()){
		global $USER_DETAILS;

		$result = array();
		$user_type = $USER_DETAILS['type'];
		$userid = $USER_DETAILS['userid'];

		$sort_columns = array('name'); // allowed columns for sorting


		$sql_parts = array(
			'select' => array('sysmaps' => 's.sysmapid'),
			'from' => array('sysmaps s'),
			'where' => array(),
			'order' => array(),
			'limit' => null);

		$def_options = array(
			'nodeids'					=> null,
			'sysmapids'					=> null,
			'editable'					=> null,
			'nopermissions'				=> null,
// filtet
			'pattern'					=> '',
// OutPut
			'extendoutput'				=> null,
			'select_selements'			=> null,
			'select_links'				=> null,
			'count'						=> null,
			'preservekeys'				=> null,

			'sortfield'					=> '',
			'sortorder'					=> '',
			'limit'						=> null
		);

		$options = zbx_array_merge($def_options, $options);

// editable + PERMISSION CHECK
		if(defined('ZBX_API_REQUEST')){
			$options['nopermissions'] = false;
		}

// nodeids
		$nodeids = $options['nodeids'] ? $options['nodeids'] : get_current_nodeid();

// sysmapids
		if(!is_null($options['sysmapids'])){
			zbx_value2array($options['sysmapids']);
			$sql_parts['where'][] = DBcondition('s.sysmapid', $options['sysmapids']);
		}

// extendoutput
		if(!is_null($options['extendoutput'])){
			$sql_parts['select']['sysmaps'] = 's.*';
		}

// count
		if(!is_null($options['count'])){
			$options['sortfield'] = '';

			$sql_parts['select'] = array('count(DISTINCT s.sysmapid) as rowscount');
		}

// pattern
		if(!zbx_empty($options['pattern'])){
			$sql_parts['where'][] = ' UPPER(s.name) LIKE '.zbx_dbstr('%'.strtoupper($options['pattern']).'%');
		}

// order
// restrict not allowed columns for sorting
		$options['sortfield'] = str_in_array($options['sortfield'], $sort_columns) ? $options['sortfield'] : '';
		if(!zbx_empty($options['sortfield'])){
			$sortorder = ($options['sortorder'] == ZBX_SORT_DOWN)?ZBX_SORT_DOWN:ZBX_SORT_UP;

			$sql_parts['order'][] = 's.'.$options['sortfield'].' '.$sortorder;

			if(!str_in_array('s.'.$options['sortfield'], $sql_parts['select']) && !str_in_array('s.*', $sql_parts['select'])){
				$sql_parts['select'][] = 's.'.$options['sortfield'];
			}
		}

// limit
		if(zbx_ctype_digit($options['limit']) && $options['limit']){
			$sql_parts['limit'] = $options['limit'];
		}
//-------

		$sysmapids = array();

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
				WHERE '.DBin_node('s.sysmapid', $nodeids).
					$sql_where.
				$sql_order;
		$res = DBselect($sql, $sql_limit);
		while($sysmap = DBfetch($res)){
			if($options['count'])
				$result = $sysmap;
			else{
				$sysmapids[$sysmap['sysmapid']] = $sysmap['sysmapid'];

				if(is_null($options['extendoutput'])){
					$result[$sysmap['sysmapid']] = array('sysmapid' => $sysmap['sysmapid']);
				}
				else{
					if(!isset($result[$sysmap['sysmapid']])) $result[$sysmap['sysmapid']]= array();

					if(!is_null($options['select_selements']) && !isset($result[$sysmap['sysmapid']]['selements'])){
						$result[$sysmap['sysmapid']]['selements'] = array();
					}
					if(!is_null($options['select_links']) && !isset($result[$sysmap['sysmapid']]['links'])){
						$result[$sysmap['sysmapid']]['links'] = array();
					}

					$result[$sysmap['sysmapid']] += $sysmap;
				}
			}
		}

		if((USER_TYPE_SUPER_ADMIN == $user_type) || $options['nopermissions']){
		}
		else{
			if(!empty($result)){
				$hosts_to_check = array();
				$maps_to_check = array();
				$triggers_to_check = array();
				$host_groups_to_check = array();
				$map_elements = array();
				$map_elements['hosts'] = array();
				$map_elements['maps'] = array();
				$map_elements['triggers'] = array();
				$map_elements['hostgroups'] = array();

				$db_elements = DBselect('SELECT * FROM sysmaps_elements WHERE '.DBcondition('sysmapid', $sysmapids));

				while($element = DBfetch($db_elements)){
					switch($element['elementtype']){
						case SYSMAP_ELEMENT_TYPE_HOST:
							$map_elements['hosts'][$element['elementid']] = $element;
							$hosts_to_check[] = $element['elementid'];
						break;
						case SYSMAP_ELEMENT_TYPE_MAP:
							$map_elements['maps'][$element['elementid']] = $element;
							$maps_to_check[] = $element['elementid'];
						break;
						case SYSMAP_ELEMENT_TYPE_TRIGGER:
							$map_elements['triggers'][$element['elementid']] = $element;
							$triggers_to_check[] = $element['elementid'];
						break;
						case SYSMAP_ELEMENT_TYPE_HOST_GROUP:
							$map_elements['hostgroups'][$element['elementid']] = $element;
							$host_groups_to_check[] = $element['elementid'];
						break;
					}
				}
// sdi($hosts_to_check);
// sdi($maps_to_check);
// sdi($triggers_to_check);
// sdi($host_groups_to_check);
				$nodeids = get_current_nodeid(true);
				$host_options = array('hostids' => $hosts_to_check,
									'nodeids' => $nodeids,
									'editable' => isset($options['editable']),
									'preservekeys'=>1);
				$allowed_hosts = CHost::get($host_options);

				$map_options = array('sysmapids' => $maps_to_check,
									'nodeids' => $nodeids,
									'editable' => isset($options['editable']),
									'preservekeys'=>1);
				$allowed_maps = self::get($map_options);

				$trigger_options = array('triggerids' => $triggers_to_check,
									'nodeids' => $nodeids,
									'editable' => isset($options['editable']),
									'preservekeys'=>1);
				$allowed_triggers = CTrigger::get($trigger_options);

				$hostgroup_options = array('groupids' => $host_groups_to_check,
									'nodeids' => $nodeids,
									'editable' => isset($options['editable']),
									'preservekeys'=>1);
				$allowed_host_groups = CHostGroup::get($hostgroup_options);


				$restr_hosts = array_diff($hosts_to_check, $allowed_hosts);
				$restr_maps = array_diff($maps_to_check, $allowed_maps);
				$restr_triggers = array_diff($triggers_to_check, $allowed_triggers);
				$restr_host_groups = array_diff($host_groups_to_check, $allowed_host_groups);

				foreach($restr_hosts as $elementid){
					foreach($map_elements['hosts'] as $map_elementid => $map_element){
						if(isset($map_elements['hosts'][$elementid])){
							unset($result[$map_element['sysmapid']]);
							unset($map_elements[$map_elementid]);
						}
					}
				}
				foreach($restr_maps as $elementid){
					foreach($map_elements['maps'] as $map_elementid => $map_element){
						if(isset($map_elements['maps'][$elementid])){
							unset($result[$map_element['sysmapid']]);
							unset($map_elements[$map_elementid]);
						}
					}
				}
				foreach($restr_triggers as $elementid){
					foreach($map_elements['triggers'] as $map_elementid => $map_element){
						if(isset($map_elements['triggers'][$elementid])){
							unset($result[$map_element['sysmapid']]);
							unset($map_elements[$map_elementid]);
						}
					}
				}
				foreach($restr_host_groups as $elementid){
					foreach($map_elements['hostgroups'] as $map_elementid => $map_element){
						if(isset($map_elements['hostgroups'][$elementid])){
							unset($result[$map_element['sysmapid']]);
							unset($map_elements[$map_elementid]);
						}
					}
				}
			}
		}

		if(is_null($options['extendoutput']) || !is_null($options['count'])){
			if(is_null($options['preservekeys'])) $result = zbx_cleanHashes($result);
			return $result;
		}

// Adding Elements
		if(!is_null($options['select_selements'])){
			if(!isset($map_selements)){
				$map_selements = array();

				$sql = 'SELECT se.* FROM sysmaps_elements se WHERE '.DBcondition('se.sysmapid', $sysmapids);
				$db_selements = DBselect($sql);
				while($selement = DBfetch($db_selements)){
					$map_selements[$selement['selementid']] = $selement;
				}
			}

			foreach($map_selements as $num => $selement){
				if(!isset($result[$selement['sysmapid']]['selements'])){
					$result[$selement['sysmapid']]['selements'] = array();
				}
				$result[$selement['sysmapid']]['selements'][$selement['selementid']] = $selement;
			}
		}

// Adding Links
		if(!is_null($options['select_links'])){
			if(!isset($map_links)){
				$linkids = array();
				$map_links = array();

				$sql = 'SELECT sl.* FROM sysmaps_links sl WHERE '.DBcondition('sl.sysmapid', $sysmapids);
				$db_links = DBselect($sql);
				while($link = DBfetch($db_links)){
					$link['linktriggers'] = array();

					$map_links[$link['linkid']] = $link;
					$linkids[$link['linkid']] = $link['linkid'];
				}

				$sql = 'SELECT slt.* FROM sysmaps_link_triggers slt WHERE '.DBcondition('slt.linkid', $linkids);
				$db_link_triggers = DBselect($sql);
				while($link_trigger = DBfetch($db_link_triggers)){
					$map_links[$link_trigger['linkid']]['linktriggers'][$link_trigger['linktriggerid']] = $link_trigger;
				}
			}

			foreach($map_links as $num => $link){
				if(!isset($result[$link['sysmapid']]['links'])){
					$result[$link['sysmapid']]['links'] = array();
				}

				$result[$link['sysmapid']]['links'][$link['linkid']] = $link;
			}
		}

// removing keys (hash -> array)
		if(is_null($options['preservekeys'])){
			$result = zbx_cleanHashes($result);
		}

	return $result;
	}


/**
 * Add Map
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param _array $maps
 * @param string $maps['name']
 * @param array $maps['width']
 * @param int $maps['height']
 * @param string $maps['backgroundid']
  * @param string $maps['highlight']
 * @param array $maps['label_type']
 * @param int $maps['label_location']
 * @return boolean | array
 */
	public static function add($maps){
		$errors = array();
		$result_maps = array();
		$result = true;

		$maps = zbx_toArray($maps);

		self::BeginTransaction(__METHOD__);
		foreach($maps as $mnum => $map){

			$map_db_fields = array(
				'name' => null,
				'width' => 600,
				'height' => 400,
				'backgroundid' => 0,
				'highlight' => SYSMAP_HIGHLIGH_ON,
				'label_type' => 2,
				'label_location' => 3
			);

			if(!check_db_fields($map_db_fields, $map)){
				$result = false;
				$errors[] = array('errno' => ZBX_API_ERROR_PARAMETERS, 'error' => 'Wrong fields for map');
				break;
			}

			$sysmapid = add_sysmap($map['name'], $map['width'], $map['height'], $map['backgroundid'], $map['highlight'], $map['label_type'], $map['label_location']);
			if(!$sysmapid){
				$result = false;
				break;
			}

			$new_map = array('sysmapid' => $sysmapid);
			$result_maps[] = array_merge($new_map, $map);
		}
		$result = self::EndTransaction($result, __METHOD__);

		if($result){
			return $result_maps;
		}
		else{
			self::setMethodErrors(__METHOD__, $errors);
			return false;
		}
	}

/**
 * Update Map
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param _array $maps multidimensional array with Hosts data
 * @param string $maps['sysmapid']
 * @param string $maps['name']
 * @param array $maps['width']
 * @param int $maps['height']
 * @param string $maps['backgroundid']
 * @param array $maps['label_type']
 * @param int $maps['label_location']
 * @return boolean
 */
	public static function update($maps){
		$result = array();

		$maps = zbx_toArray($maps);

		self::BeginTransaction(__METHOD__);
		foreach($maps as $map){

			$map_db_fields = self::get(array('sysmapids' => $map['sysmapid'], 'extendoutput' => 1));
			$map_db_fields = reset($map_db_fields);


			if(!$map_db_fields){
				$result = false;
				$errors[] = array('errno' => ZBX_API_ERROR_PARAMETERS, 'error' => 'Map with ID ['.$map['sysmapid'].'] does not exists');
				break;
			}

			if(!check_db_fields($map_db_fields, $map)){
				$result = false;
				$errors[] = array('errno' => ZBX_API_ERROR_PARAMETERS, 'error' => 'Wrong fields for map');
				break;
			}

			$sql = 'UPDATE sysmaps SET name='.zbx_dbstr($map['name']).', width='.$map['width'].', height='.$map['height'].', '.
					'backgroundid='.$map['backgroundid'].',label_type='.$map['label_type'].',label_location='.$map['label_location'].
				' WHERE sysmapid='.$map['sysmapid'];
			$result = DBexecute($sql);

			if(!$result) break;
		}
		$result = self::EndTransaction($result, __METHOD__);

		if($result){
			return true;
		}
		else{
			self::setMethodErrors(__METHOD__, $errors);
			return false;
		}
	}


/**
 * Delete Map
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param array $sysmaps
 * @param array $sysmaps['sysmapid']
 * @return boolean
 */
	public static function delete($sysmaps){
		$result = true;
		$errors = array();

		$sysmaps = zbx_objectValues($sysmaps, 'sysmapid');

		self::BeginTransaction(__METHOD__);

		$result &= delete_sysmaps_elements_with_sysmapid($sysmaps);

		$res = DBselect('SELECT linkid FROM sysmaps_links WHERE '.DBcondition('sysmapid', $sysmaps));
		while($rows = DBfetch($res)){
			$result &= delete_link($rows['linkid']);
		}

		$result &= DBexecute('DELETE FROM sysmaps_elements WHERE '.DBcondition('sysmapid', $sysmaps));
		$result &= DBexecute("DELETE FROM profiles WHERE idx='web.favorite.sysmapids' AND source='sysmapid' AND ".DBcondition('value_id', $sysmapids));
		$result &= DBexecute('DELETE FROM screens_items WHERE '.DBcondition('resourceid', $sysmaps).' AND resourcetype='.SCREEN_RESOURCE_MAP);
		$result &= DBexecute('DELETE FROM sysmaps WHERE '.DBcondition('sysmapid', $sysmaps));

		$result = self::EndTransaction($result, __METHOD__);

		if($result)
			return true;
		else{
			self::setMethodErrors(__METHOD__, $errors);
			return false;
		}
	}

/**
 * addLinks Map
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param array $links
 * @param array $links[0,...]['sysmapid']
 * @param array $links[0,...]['selementid1']
 * @param array $links[0,...]['selementid2']
 * @param array $links[0,...]['drawtype']
 * @param array $links[0,...]['color']
 * @return boolean
 */
	public static function addLinks($links){
		$errors = array();
		$result_links = array();
		$result = true;

		$links = zbx_toArray($links);

		self::BeginTransaction(__METHOD__);

		foreach($links as $lnum => $link){

			$link_db_fields = array(
				'sysmapid' => null,
				'label' => '',
				'selementid1' => null,
				'selementid2' => null,
				'drawtype' => 2,
				'color' => 3
			);

			if(!check_db_fields($link_db_fields, $link)){
				$result = false;
				$errors[] = array('errno' => ZBX_API_ERROR_PARAMETERS, 'error' => 'Wrong fields for link');
				break;
			}

			$linkid = add_link($link['sysmapid'], $link['label'], $link['selementid1'], $link['selementid2'], array(), $link['drawtype'], $link['color']);
			if(!$linkid){
				$result = false;
				break;
			}

			$new_link = array('linkid' => $linkid);
			$result_links[] = array_merge($new_link, $link);
		}

		$result = self::EndTransaction($result, __METHOD__);

		if($result)
			return $result_links;
		else{
			self::setMethodErrors(__METHOD__, $errors);
			return false;
		}
	}
/**
 * Add Element to Sysmap
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param array $elements[0,...]['sysmapid']
 * @param array $elements[0,...]['elementid']
 * @param array $elements[0,...]['elementtype']
 * @param array $elements[0,...]['label']
 * @param array $elements[0,...]['x']
 * @param array $elements[0,...]['y']
 * @param array $elements[0,...]['iconid_off']
 * @param array $elements[0,...]['iconid_unknown']
 * @param array $elements[0,...]['iconid_on']
 * @param array $elements[0,...]['iconid_disabled']
 * @param array $elements[0,...]['url']
 * @param array $elements[0,...]['label_location']
 */
	public static function addElements($selements){
		$errors = array();
		$result_selements = array();
		$result = true;

		$elements = zbx_toArray($selements);

		self::BeginTransaction(__METHOD__);
		foreach($selements as $snumm => $selement){

			$selement_db_fields = array(
				'sysmapid' => null,
				'elementid' => null,
				'elementtype' => null,
				'label' => 3,
				'x' => 50,
				'y' => 50,
				'iconid_off' => 15,
				'iconid_unknown' => 15,
				'iconid_on' => 15,
				'iconid_disabled' => 15,
				'url' => '',
				'label_location' => 0
			);

			if(!check_db_fields($element_db_fields, $element)){
				$result = false;
				$errors[] = array('errno' => ZBX_API_ERROR_PARAMETERS, 'error' => 'Wrong fields for element');
				break;
			}

			$selementid = add_element_to_sysmap($selement);
			if(!$selementid){
				$result = false;
				break;
			}

			$new_selement = array('selementid' => $selementid);
			$result_elements[] = array_merge($new_selement, $selement);
		}

		$result = self::EndTransaction($result, __METHOD__);

		if($result)
			return $result_elements;
		else{
			self::setMethodErrors(__METHOD__, $errors);
			return false;
		}
	}

/**
 * Gets the selementid from the hostid (getSeIDFromEID).
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param _array $element_data
 * @param string $element_data[0,...]['sysmapid']
 * @param string $element_data[0,...]['elementid']
 * @return array|boolean selementid as array or false if error
 *
	public static function getSeId($data){

		$element = $selement_data['elementid'];
		$sysmapid = $selement_data['sysmapid'];
		$sql = 'select selementid from sysmaps_elements where elementid='.$element.' and sysmapid='.$sysmapid;
		$map = DBfetch(DBselect($sql));

		$result = $map ? true : false;
		if($result)
			return $map;
		else{
			self::$error[] = array('error' => ZBX_API_ERROR_INTERNAL, 'data' => $error);//'Internal zabbix error');
			return false;
		}
	}
//*/

/**
 * Add link trigger to link (Sysmap)
 *
 * {@source}
 * @access public
 * @static
 * @since 1.8
 * @version 1
 *
 * @param array $links[0,...]['linkid']
 * @param array $links[0,...]['triggerid']
 * @param array $links[0,...]['drawtype']
 * @param array $links[0,...]['color']
 */
	public static function addLinkTrigger($linktriggers){
		$errors = array();
		$result_linktriggers = array();
		$result = false;

		$linktriggers = zbx_toArray($linktriggers);

		self::BeginTransaction(__METHOD__);
		foreach($linktriggers as $linktrigger){

			$linktrigger_db_fields = array(
				'linkid' => null,
				'triggerid' => null,
				'drawtype' => 0,
				'color' => 'DD0000'
			);

			if(!check_db_fields($linktrigger_db_fields, $linktrigger)){
				$result = false;
				$errors[] = array('errno' => ZBX_API_ERROR_PARAMETERS, 'error' => 'Wrong fields for linktrigger');
				break;
			}

			$linktriggerid = get_dbid('sysmaps_link_triggers', 'linktriggerid');
			$sql = 'INSERT INTO sysmaps_link_triggers (linktriggerid, linkid, triggerid, drawtype, color) '.
				' VALUES ('.$linktriggerid.','.$linktrigger['linkid'].','.$linktrigger['triggerid'].', '.
					$linktrigger['drawtype'].','.zbx_dbstr($linktrigger['color']).')';
			$result = DBexecute($sql);
			if(!$result){
				$result = false;
				break;
			}

			$new_linktriggerid = array('linktriggerid' => $linktriggerid);
			$result_linktriggers[] = array_merge($new_linktriggerid, $linktriggerid);
		}
		$result = self::EndTransaction($result, __METHOD__);

		if($result)
			return $result_linktriggers;
		else{
			self::setMethodErrors(__METHOD__, $errors);
			return false;
		}
	}

}

?>