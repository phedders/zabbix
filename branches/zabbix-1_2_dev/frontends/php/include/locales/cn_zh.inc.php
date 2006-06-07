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
	global $TRANSLATION;

	$TRANSLATION=array(

	"S_DATE_FORMAT_YMDHMS"=>		"d M H:i:s",
	"S_DATE_FORMAT_YMD"=>			"d M Y",
	"S_HTML_CHARSET"=>			"GB2312",

//	actions.php
	"S_ACTIONS"=>				"报警",
	"S_ACTION_ADDED"=>			"添加操作",
	"S_CANNOT_ADD_ACTION"=>			"无法添加操作",
	"S_ACTION_UPDATED"=>			"更新操作Action updated",
	"S_CANNOT_UPDATE_ACTION"=>		"无法更新操作Cannot update action",
	"S_ACTION_DELETED"=>			"删除操作Action deleted",
	"S_CANNOT_DELETE_ACTION"=>		"无法删除操作Cannot delete action",
	"S_SCOPE"=>				"Scope",
	"S_SEND_MESSAGE_TO"=>			"发送信息到",
	"S_WHEN_TRIGGER"=>			"When trigger",
	"S_DELAY"=>				"延迟",
	"S_SUBJECT"=>				"主题",
	"S_ON"=>				"开",
	"S_OFF"=>				"关",
	"S_NO_ACTIONS_DEFINED"=>		"没有定义操作",
	"S_NEW_ACTION"=>			"新的操作",
	"S_SINGLE_USER"=>			"单用户",
	"S_USER_GROUP"=>			"用户组",
	"S_GROUP"=>				"组名",
	"S_USER"=>				"用户",
	"S_WHEN_TRIGGER_BECOMES"=>		"当触发器",
	"S_ON_OR_OFF"=>				"开或关",
	"S_DELAY_BETWEEN_MESSAGES_IN_SEC"=>	"信息发送间隔(秒)",
	"S_MESSAGE"=>				"信息",
	"S_THIS_TRIGGER_ONLY"=>			"本触发器",
	"S_ALL_TRIGGERS_OF_THIS_HOST"=>		"本主机的所有触发器",
	"S_ALL_TRIGGERS"=>			"所有触发器",
	"S_USE_IF_TRIGGER_SEVERITY"=>		"相等或者大于时使用触发器",
	"S_NOT_CLASSIFIED"=>			"未分类",
	"S_INFORMATION"=>			"信息",
	"S_WARNING"=>				"警告",
	"S_AVERAGE"=>				"Average",
	"S_HIGH"=>				"严重",
	"S_DISASTER"=>				"灾难",
	"S_REPEAT"=>				"重复",
	"S_REPEATS"=>				"重复",
	"S_NO_REPEATS"=>			"不重复",
	"S_NUMBER_OF_REPEATS"=>			"重复次数",
	"S_DELAY_BETWEEN_REPEATS"=>		"重复间隔",

//	alarms.php
	"S_ALARMS"=>				"alarms",
	"S_ALARMS_SMALL"=>			"Alarms",
	"S_ALARMS_BIG"=>			"ALARMS",
	"S_SHOW_ONLY_LAST_100"=>		"只显示最新的100条",
	"S_SHOW_ALL"=>				"显示所有",
	"S_TIME"=>				"时间",
	"S_STATUS"=>				"状态",
	"S_DURATION"=>				"持续时间",
	"S_SUM"=>				"Sum",
	"S_TRUE_BIG"=>				"TRUE",
	"S_FALSE_BIG"=>				"FALSE",
	"S_DISABLED_BIG"=>			"DISABLED",
	"S_UNKNOWN_BIG"=>			"UNKNOWN",

//	alerts.php
	"S_ALERT_HISTORY_SMALL"=>		"报警历史",
	"S_ALERT_HISTORY_BIG"=>			"报警记录",
	"S_ALERTS_BIG"=>			"ALERTS",
	"S_TYPE"=>				"类型",
	"S_RECIPIENTS"=>			"收件人",
	"S_ERROR"=>				"错误",
	"S_SENT"=>				"已发送",
	"S_NOT_SENT"=>				"未发送",
	"S_NO_ALERTS"=>				"无报警",
	"S_SHOW_NEXT_100"=>			"后100条",
	"S_SHOW_PREVIOUS_100"=>			"前100条",

//	charts.php
	"S_CUSTOM_GRAPHS"=>			"Custom graphs",
	"S_GRAPHS_BIG"=>			"GRAPHS",
	"S_NO_GRAPHS_TO_DISPLAY"=>		"No graphs to display",
	"S_SELECT_GRAPH_TO_DISPLAY"=>		"请选择图表",
	"S_PERIOD"=>				"Period",
	"S_1H"=>				"1h",
	"S_2H"=>				"2h",
	"S_4H"=>				"4h",
	"S_8H"=>				"8h",
	"S_12H"=>				"12h",
	"S_24H"=>				"24h",
	"S_WEEK_SMALL"=>			"week",
	"S_MONTH_SMALL"=>			"month",
	"S_YEAR_SMALL"=>			"year",
	"S_KEEP_PERIOD"=>			"保存时间",
	"S_ON_C"=>				"On",
	"S_OFF_C"=>				"Off",
	"S_MOVE"=>				"Move",
	"S_NAVIGATE"=>				"Navigate",
	"S_INCREASE"=>				"Increase",
	"S_DECREASE"=>				"Decrease",
	"S_NAVIGATE"=>				"Navigate",
	"S_RIGHT_DIR"=>				"Right",
	"S_LEFT_DIR"=>				"Left",
	"S_SELECT_GRAPH_DOT_DOT_DOT"=>		"选择图表...",

// Colors
	"S_BLACK"=>				"Black",
	"S_BLUE"=>				"Blue",
	"S_CYAN"=>				"Cyan",
	"S_DARK_BLUE"=>				"Dark blue",
	"S_DARK_GREEN"=>			"Dark green",
	"S_DARK_RED"=>				"Dark red",
	"S_DARK_YELLOW"=>			"Dark yellow",
	"S_GREEN"=>				"Green",
	"S_RED"=>				"Red",
	"S_WHITE"=>				"White",
	"S_YELLOW"=>				"Yellow",

//	config.php
	"S_CONFIGURATION_OF_ZABBIX"=>		"ZABBIX配置",
	"S_CONFIGURATION_OF_ZABBIX_BIG"=>	"ZABBIX配置类型",
	"S_CONFIGURATION_UPDATED"=>		"Configuration updated",
	"S_CONFIGURATION_WAS_NOT_UPDATED"=>	"Configuration was not updated",
	"S_ADDED_NEW_MEDIA_TYPE"=>		"Added new media type",
	"S_NEW_MEDIA_TYPE_WAS_NOT_ADDED"=>	"New media type was not added",
	"S_MEDIA_TYPE_UPDATED"=>		"Media type updated",
	"S_MEDIA_TYPE_WAS_NOT_UPDATED"=>	"Media type was not updated",
	"S_MEDIA_TYPE_DELETED"=>		"Media type deleted",
	"S_MEDIA_TYPE_WAS_NOT_DELETED"=>	"Media type was not deleted",
	"S_CONFIGURATION"=>			"配置系统",
	"S_DO_NOT_KEEP_ACTIONS_OLDER_THAN"=>	"Do not keep actions older than (in days)",
	"S_DO_NOT_KEEP_EVENTS_OLDER_THAN"=>	"Do not keep events older than (in days)",
	"S_MEDIA_TYPES_BIG"=>			"介质类型",
	"S_NO_MEDIA_TYPES_DEFINED"=>		"未定义介质类型",
	"S_SMTP_SERVER"=>			"SMTP服务器",
	"S_SMTP_HELO"=>				"SMTP HELO",
	"S_SMTP_EMAIL"=>			"SMTP发件人",
	"S_SCRIPT_NAME"=>			"脚本名称",
	"S_DELETE_SELECTED_MEDIA"=>		"Delete selected media?",
	"S_DELETE_SELECTED_IMAGE"=>		"Delete selected image?",
	"S_HOUSEKEEPER"=>			"Housekeeper",
	"S_MEDIA_TYPES"=>			"Media types",
	"S_ESCALATION_RULES"=>			"Escalation rules",
	"S_ESCALATION"=>			"Escalation",
	"S_ESCALATION_RULES_BIG"=>		"ESCALATION RULES",
	"S_NO_ESCALATION_RULES_DEFINED"=>	"No escalation rules defined",
	"S_NO_ESCALATION_DETAILS"=>		"No escalation details",
	"S_ESCALATION_DETAILS_BIG"=>		"ESCALATION DETAILS",
	"S_ESCALATION_ADDED"=>			"Escalation added",
	"S_ESCALATION_WAS_NOT_ADDED"=>		"Escalation was not added",
	"S_ESCALATION_RULE_ADDED"=>		"Escalation rule added",
	"S_ESCALATION_RULE_WAS_NOT_ADDED"=>	"Escalation rule was not added",
	"S_ESCALATION_RULE_UPDATED"=>		"Escalation rule updated",
	"S_ESCALATION_RULE_WAS_NOT_UPDATED"=>	"Escalation rule was not updated",
	"S_ESCALATION_RULE_DELETED"=>		"Escalation rule deleted",
	"S_ESCALATION_RULE_WAS_NOT_DELETED"=>	"Escalation rule was not deleted",
	"S_ESCALATION_UPDATED"=>		"Escalation updated",
	"S_ESCALATION_WAS_NOT_UPDATED"=>	"Escalation was not updated",
	"S_ESCALATION_DELETED"=>		"Escalation deleted",
	"S_ESCALATION_WAS_NOT_DELETED"=>	"Escalation was not deleted",
	"S_ESCALATION_RULE"=>			"Escalation rule",
	"S_DO"=>				"Do",
	"S_DEFAULT"=>				"默认",
	"S_IS_DEFAULT"=>			"设为默认",
	"S_LEVEL"=>				"等级",
	"S_DELAY_BEFORE_ACTION"=>		"Delay before action",
	"S_IMAGES"=>				"Images",
	"S_IMAGE"=>				"Image",
	"S_IMAGES_BIG"=>			"IMAGES",
	"S_NO_IMAGES_DEFINED"=>			"No images defined",
	"S_BACKGROUND"=>			"Background",
	"S_UPLOAD"=>				"Upload",
	"S_IMAGE_ADDED"=>			"Image added",
	"S_CANNOT_ADD_IMAGE"=>			"Cannot add image",
	"S_IMAGE_DELETED"=>			"Image deleted",
	"S_CANNOT_DELETE_IMAGE"=>		"Cannot delete image",
	"S_IMAGE_UPDATED"=>			"Image updated",
	"S_CANNOT_UPDATE_IMAGE"=>		"Cannot update image",
	"S_UPDATE_SELECTED_IMAGE"=>		"Update selected image?",
	"S_AUTOREGISTRATION"=>			"Autoregistration",
	"S_AUTOREGISTRATION_RULES_BIG"=>	"AUTOREGISTRATION RULES",
	"S_PRIORITY"=>				"优先级",
	"S_PATTERN"=>				"Pattern",
	"S_NO_AUTOREGISTRATION_RULES_DEFINED"=>	"No autoregistration rules defined",
	"S_AUTOREGISTRATION_ADDED"=>		"Autoregistration added",
	"S_CANNOT_ADD_AUTOREGISTRATION"=>	"Canot add autoregistration",
	"S_AUTOREGISTRATION_UPDATED"=>		"Autoregistration updated",
	"S_AUTOREGISTRATION_WAS_NOT_UPDATED"=>	"Autoregistration was not updated",
	"S_AUTOREGISTRATION_DELETED"=>		"Autoregistration deleted",
	"S_AUTOREGISTRATION_WAS_NOT_DELETED"=>	"Autoregistration was not deleted",
	"S_OTHER"=>				"Other",
	"S_OTHER_PARAMETERS"=>			"其他参数",
	"S_REFRESH_UNSUPPORTED_ITEMS"=>		"Refresh unsupported items (in sec)",

//	Latest values
	"S_LATEST_VALUES"=>			"最新数据",
	"S_NO_PERMISSIONS"=>			"权限不足!",
	"S_LATEST_DATA_BIG"=>			"最新数据",
	"S_ALL_SMALL"=>				"all",
	"S_DESCRIPTION_LARGE"=>			"DESCRIPTION",
	"S_DESCRIPTION_SMALL"=>			"Description",
	"S_GRAPH"=>				"图形显示",
	"S_TREND"=>				"Trend",
	"S_COMPARE"=>				"对比",

//	Footer
	"S_COPYRIGHT_BY"=>			"Copyright 2001-2006 by ",
	"S_CONNECTED_AS"=>			"Connected as",
	"S_SIA_ZABBIX"=>			"SIA Zabbix",

//	graph.php
	"S_CONFIGURATION_OF_GRAPH"=>		"图表显示配置",
	"S_CONFIGURATION_OF_GRAPH_BIG"=>	"图表显示配置",
	"S_ITEM_ADDED"=>			"Item added",
	"S_ITEM_UPDATED"=>			"Item updated",
	"S_SORT_ORDER_UPDATED"=>		"Sort order updated",
	"S_CANNOT_UPDATE_SORT_ORDER"=>		"Cannot update sort order",
	"S_DISPLAYED_PARAMETERS_BIG"=>		"显示参数",
	"S_SORT_ORDER"=>			"Sort order",
	"S_PARAMETER"=>				"参数",
	"S_COLOR"=>				"颜色",
	"S_UP"=>				"Up",
	"S_DOWN"=>				"Down",
	"S_NEW_ITEM_FOR_THE_GRAPH"=>		"New item for the graph",
	"S_SORT_ORDER_1_100"=>			"Sort order (0->100)",
	"S_YAXIS_SIDE"=>			"Y axis side",
	"S_LEFT"=>				"向左",

//	graphs.php
	"S_CONFIGURATION_OF_GRAPHS"=>		"图表显示配置",
	"S_CONFIGURATION_OF_GRAPHS_BIG"=>	"图表显示配置",
	"S_GRAPH_ADDED"=>			"Graph added",
	"S_GRAPH_UPDATED"=>			"Graph updated",
	"S_CANNOT_UPDATE_GRAPH"=>		"Cannot update graph",
	"S_GRAPH_DELETED"=>			"Graph deleted",
	"S_CANNOT_DELETE_GRAPH"=>		"Cannot delete graph",
	"S_CANNOT_ADD_GRAPH"=>			"Cannot add graph",
	"S_ID"=>				"ID",
	"S_NO_GRAPHS_DEFINED"=>			"No graphs defined",
	"S_DELETE_GRAPH_Q"=>			"Delete graph?",
	"S_YAXIS_TYPE"=>			"Y axis type",
	"S_YAXIS_MIN_VALUE"=>			"Y轴最小值",
	"S_YAXIS_MAX_VALUE"=>			"Y轴最大值",
	"S_CALCULATED"=>			"Calculated",
	"S_FIXED"=>				"Fixed",

//	history.php
	"S_LAST_HOUR_GRAPH"=>			"最近一小时",
	"S_VALUES_OF_LAST_HOUR"=>		"最近一小时",
	"S_500_LATEST_VALUES"=>			"最近500个值",
	"S_VALUES_OF_SPECIFIED_PERIOD"=>	"Values of specified period",
	"S_VALUES_IN_PLAIN_TEXT_FORMAT"=>	"以文本形式显示",
	"S_TIMESTAMP"=>				"时间戳",
	"S_LOCAL"=>				"Local",
	"S_SOURCE"=>				"Source",

//	hosts.php
	"S_HOSTS"=>				"主机",
	"S_ITEMS"=>				"条目",
	"S_TRIGGERS"=>				"触发器",
	"S_GRAPHS"=>				"图形显示",
	"S_HOST_ADDED"=>			"已添加主机",
	"S_CANNOT_ADD_HOST"=>			"无法添加主机",
	"S_ITEMS_ADDED"=>			"添加条目",
	"S_CANNOT_ADD_ITEMS"=>			"无法添加条目",
	"S_HOST_UPDATED"=>			"主机已更新",
	"S_CANNOT_UPDATE_HOST"=>		"无法更新主机",
	"S_HOST_STATUS_UPDATED"=>		"主机状态已更新",
	"S_CANNOT_UPDATE_HOST_STATUS"=>		"无法更新主机状态",
	"S_HOST_DELETED"=>			"已删除主机",
	"S_CANNOT_DELETE_HOST"=>		"无法删除主机",
	"S_TEMPLATE_LINKAGE_ADDED"=>		"新模板链接已添加",
	"S_CANNOT_ADD_TEMPLATE_LINKAGE"=>	"无法添加新模板链接",
	"S_TEMPLATE_LINKAGE_UPDATED"=>		"Template linkage updated",
	"S_CANNOT_UPDATE_TEMPLATE_LINKAGE"=>	"Cannot update template linkage",
	"S_TEMPLATE_LINKAGE_DELETED"=>		"Template linkage deleted",
	"S_CANNOT_DELETE_TEMPLATE_LINKAGE"=>	"Cannot delete template linkage",
	"S_CONFIGURATION_OF_HOSTS_AND_HOST_GROUPS"=>"配置主机和主机组",
	"S_HOST_GROUPS_BIG"=>			"HOST GROUPS",
	"S_NO_HOST_GROUPS_DEFINED"=>		"没有定义主机组",
	"S_NO_LINKAGES_DEFINED"=>		"没有定义链接",
	"S_NO_HOSTS_DEFINED"=>			"没有定义主机",
	"S_HOSTS_BIG"=>				"主机",
	"S_HOST"=>				"主机",
	"S_IP"=>				"IP地址",
	"S_PORT"=>				"端口",
	"S_MONITORED"=>				"检测中",
	"S_NOT_MONITORED"=>			"未被检测",
	"S_UNREACHABLE"=>			"无法访问",
	"S_TEMPLATE"=>				"模本",
	"S_DELETED"=>				"已删除",
	"S_UNKNOWN"=>				"状态未知",
	"S_GROUPS"=>				"组",
	"S_NEW_GROUP"=>				"新主机组",
	"S_USE_IP_ADDRESS"=>			"使用IP地址",
	"S_IP_ADDRESS"=>			"IP地址",
//	"S_USE_THE_HOST_AS_A_TEMPLATE"=>		"Use the host as a template",
	"S_USE_TEMPLATES_OF_THIS_HOST"=>	"Use templates of this host",
	"S_DELETE_SELECTED_HOST_Q"=>		"Delete selected host?",
	"S_GROUP_NAME"=>			"用户组",
	"S_HOST_GROUP"=>			"主机组",
	"S_HOST_GROUPS"=>			"主机组",
	"S_UPDATE"=>				"更新",
	"S_AVAILABILITY"=>			"Availability",
	"S_AVAILABLE"=>				"可用",
	"S_NOT_AVAILABLE"=>			"不可用",
//	Host profiles
	"S_HOST_PROFILE"=>			"主机配置",
	"S_DEVICE_TYPE"=>			"设备类型",
	"S_OS"=>				"操作系统",
	"S_SERIALNO"=>				"SerialNo",
	"S_TAG"=>				"标签",
	"S_HARDWARE"=>				"硬件环境",
	"S_SOFTWARE"=>				"软件环境",
	"S_CONTACT"=>				"联系人",
	"S_LOCATION"=>				"主机位置",
	"S_NOTES"=>				"备注",
	"S_MACADDRESS"=>			"MAC地址",
	"S_PROFILE_ADDED"=>			"资料已添加",
	"S_CANNOT_ADD_PROFILE"=>		"无法添加资料",
	"S_PROFILE_UPDATED"=>			"资料已更新",
	"S_CANNOT_UPDATE_PROFILE"=>		"无法更新资料",
	"S_PROFILE_DELETED"=>			"资料已删除",
	"S_CANNOT_DELETE_PROFILE"=>		"无法删除资料",
	"S_ADD_TO_GROUP"=>			"添加到组",
	"S_DELETE_FROM_GROUP"=>			"从组中删除",
	"S_UPDATE_IN_GROUP"=>			"更新组",
	"S_DELETE_SELECTED_HOSTS_Q"=>		"删除选中主机?",
	"S_DISABLE_SELECTED_HOSTS_Q"=>		"Disable selected hosts?",
	"S_ACTIVATE_SELECTED_HOSTS_Q"=>		"Activate selected hosts?",
	"S_SELECT_HOST_TEMPLATE_FIRST"=>	"Select host template first",

//	items.php
	"S_CONFIGURATION_OF_ITEMS"=>		"配置条目",
	"S_CONFIGURATION_OF_ITEMS_BIG"=>	"配置检测条目",
	"S_CANNOT_UPDATE_ITEM"=>		"Cannot update item",
	"S_STATUS_UPDATED"=>			"状态已更新",
	"S_CANNOT_UPDATE_STATUS"=>		"Cannot update status",
	"S_CANNOT_ADD_ITEM"=>			"Cannot add item",
	"S_ITEM_DELETED"=>			"Item deleted",
	"S_CANNOT_DELETE_ITEM"=>		"Cannot delete item",
	"S_ITEMS_DELETED"=>			"Items deleted",
	"S_CANNOT_DELETE_ITEMS"=>		"Cannot delete items",
	"S_ITEMS_ACTIVATED"=>			"Items activated",
	"S_CANNOT_ACTIVATE_ITEMS"=>		"Cannot activate items",
	"S_ITEMS_DISABLED"=>			"Items disabled",
	"S_CANNOT_DISABLE_ITEMS"=>		"Cannot disable items",
	"S_SERVERNAME"=>			"Server Name",
	"S_KEY"=>				"Key值",
	"S_DESCRIPTION"=>			"检测内容",
	"S_UPDATE_INTERVAL"=>			"更新间隔s",
	"S_HISTORY"=>				"保存记录d",
	"S_TRENDS"=>				"Trends",
	"S_SHORT_NAME"=>			"Short name",
	"S_ZABBIX_AGENT"=>			"ZABBIX agent",
	"S_ZABBIX_AGENT_ACTIVE"=>		"ZABBIX agent (active)",
	"S_SNMPV1_AGENT"=>			"SNMPv1 agent",
	"S_ZABBIX_TRAPPER"=>			"ZABBIX trapper",
	"S_SIMPLE_CHECK"=>			"Simple check",
	"S_SNMPV2_AGENT"=>			"SNMPv2 agent",
	"S_SNMPV3_AGENT"=>			"SNMPv3 agent",
	"S_ZABBIX_INTERNAL"=>			"ZABBIX internal",
	"S_ZABBIX_UNKNOWN"=>			"未知",
	"S_ACTIVE"=>				"活跃",
	"S_NOT_ACTIVE"=>			"不活跃",
	"S_NOT_SUPPORTED"=>			"不支持",
	"S_ACTIVATE_SELECTED_ITEMS_Q"=>		"激活选中的条目?",
	"S_DISABLE_SELECTED_ITEMS_Q"=>		"取消选中的条目?",
	"S_DELETE_SELECTED_ITEMS_Q"=>		"删除选中的条目?",
	"S_EMAIL"=>				"Email",
	"S_SCRIPT"=>				"Script",
	"S_UNITS"=>				"单位Units",
	"S_MULTIPLIER"=>			"Multiplier",
	"S_UPDATE_INTERVAL_IN_SEC"=>		"数据更新间隔(秒)",
	"S_KEEP_HISTORY_IN_DAYS"=>		"数据保存天数",
	"S_KEEP_TRENDS_IN_DAYS"=>		"Keep trends (in days)",
	"S_TYPE_OF_INFORMATION"=>		"数据类型",
	"S_STORE_VALUE"=>			"Store value",
	"S_NUMERIC"=>				"数值",
	"S_CHARACTER"=>				"字符串",
	"S_LOG"=>				"日志",
	"S_AS_IS"=>				"As is",
	"S_DELTA_SPEED_PER_SECOND"=>		"Delta (speed per second)",
	"S_DELTA_SIMPLE_CHANGE"=>		"Delta (simple change)",
	"S_ITEM"=>				"Item",
	"S_SNMP_COMMUNITY"=>			"SNMP community",
	"S_SNMP_OID"=>				"SNMP OID",
	"S_SNMP_PORT"=>				"SNMP port",
	"S_ALLOWED_HOSTS"=>			"Allowed hosts",
	"S_SNMPV3_SECURITY_NAME"=>		"SNMPv3 security name",
	"S_SNMPV3_SECURITY_LEVEL"=>		"SNMPv3 security level",
	"S_SNMPV3_AUTH_PASSPHRASE"=>		"SNMPv3 auth passphrase",
	"S_SNMPV3_PRIV_PASSPHRASE"=>		"SNMPv3 priv passphrase",
	"S_CUSTOM_MULTIPLIER"=>			"Custom multiplier",
	"S_DO_NOT_USE"=>			"Do not use",
	"S_USE_MULTIPLIER"=>			"使用乘法器",
	"S_SELECT_HOST_DOT_DOT_DOT"=>		"选择主机...",
	"S_LOG_TIME_FORMAT"=>			"Log time format",

//	latestalarms.php
	"S_LATEST_EVENTS"=>			"最新报警",
	"S_HISTORY_OF_EVENTS_BIG"=>		"系统警告信息",

//	latest.php
	"S_LAST_CHECK"=>			"最近检查记录",
	"S_LAST_CHECK_BIG"=>			"LAST CHECK",
	"S_LAST_VALUE"=>			"最新数据",

//	sysmap.php
	"S_LABEL"=>				"Label",
	"S_X"=>					"X",
	"S_Y"=>					"Y",
	"S_ICON"=>				"Icon",
	"S_HOST_1"=>				"Host 1",
	"S_HOST_2"=>				"Host 2",
	"S_LINK_STATUS_INDICATOR"=>		"Link status indicator",
	"S_CONFIGURATION_OF_NETWORK_MAPS"=>	"配置系统布局图",

//	map.php
	"S_OK_BIG"=>				"OK",
	"S_PROBLEMS_SMALL"=>			"problems",
	"S_ZABBIX_URL"=>			"http://www.zabbix.com",

//	maps.php
	"S_NETWORK_MAPS"=>			"网络结构图",
	"S_NETWORK_MAPS_BIG"=>			"网络结构",
	"S_NO_MAPS_TO_DISPLAY"=>		"没有结构图显示",
	"S_SELECT_MAP_TO_DISPLAY"=>		"请选择结构图",
	"S_SELECT_MAP_DOT_DOT_DOT"=>		"选择...",
	"S_BACKGROUND_IMAGE"=>			"背景图片",
	"S_ICON_LABEL_TYPE"=>			"Icon label type",
	"S_HOST_LABEL"=>			"Host label",
	"S_HOST_NAME"=>				"Host name",
	"S_STATUS_ONLY"=>			"Status only",
	"S_NOTHING"=>				"Nothing",

//	media.php
	"S_MEDIA"=>				"报警介质",
	"S_MEDIA_BIG"=>				"介质信息",
	"S_MEDIA_ACTIVATED"=>			"介质已激活",
	"S_CANNOT_ACTIVATE_MEDIA"=>		"无法激活介质",
	"S_MEDIA_DISABLED"=>			"介质已停用",
	"S_CANNOT_DISABLE_MEDIA"=>		"介质无法停用",
	"S_MEDIA_ADDED"=>			"介质已添加",
	"S_CANNOT_ADD_MEDIA"=>			"无法添加介质",
	"S_MEDIA_UPDATED"=>			"介质已升级",
	"S_CANNOT_UPDATE_MEDIA"=>		"无法升级介质",
	"S_MEDIA_DELETED"=>			"介质已删除",
	"S_CANNOT_DELETE_MEDIA"=>		"无法删除介质",
	"S_SEND_TO"=>				"收件人",
	"S_WHEN_ACTIVE"=>			"激活时间",
	"S_NO_MEDIA_DEFINED"=>			"没有定义介质",
	"S_NEW_MEDIA"=>				"New media",
	"S_USE_IF_SEVERITY"=>			"Use if severity",
	"S_DELETE_SELECTED_MEDIA_Q"=>		"Delete selected media?",

//	Menu
	"S_MENU_LATEST_VALUES"=>		"最新数据",
	"S_MENU_TRIGGERS"=>			"触发器",
	"S_MENU_QUEUE"=>			"队列",
	"S_MENU_ALARMS"=>			"ALARMS",
	"S_MENU_ALERTS"=>			"ALERTS",
	"S_MENU_NETWORK_MAPS"=>			"网络结构图",
	"S_MENU_GRAPHS"=>			"GRAPHS",
	"S_MENU_SCREENS"=>			"SCREENS",
	"S_MENU_IT_SERVICES"=>			"IT服务",
	"S_MENU_HOME"=>				"HOME",
	"S_MENU_ABOUT"=>			"ABOUT",
	"S_MENU_STATUS_OF_ZABBIX"=>		"ZABBIX系统状态",
	"S_MENU_AVAILABILITY_REPORT"=>		"AVAILABILITY REPORT",
	"S_MENU_CONFIG"=>			"CONFIG",
	"S_MENU_USERS"=>			"用户",
	"S_MENU_HOSTS"=>			"主机",
	"S_MENU_ITEMS"=>			"ITEMS",
	"S_MENU_AUDIT"=>			"AUDIT",

//	overview.php
	"S_SELECT_GROUP_DOT_DOT_DOT"=>		"Select group ...",
	"S_OVERVIEW"=>				"总览",
	"S_OVERVIEW_BIG"=>			"总览",
	"S_EXCL"=>				"!",
	"S_DATA"=>				"数据",

//	queue.php
	"S_QUEUE_BIG"=>				"QUEUE",
	"S_QUEUE_OF_ITEMS_TO_BE_UPDATED_BIG"=>	"已经更新的队列",
	"S_NEXT_CHECK"=>			"Next check",
	"S_THE_QUEUE_IS_EMPTY"=>		"The queue is empty",
	"S_TOTAL"=>				"合计",
	"S_COUNT"=>				"数量",
	"S_5_SECONDS"=>				"5秒",
	"S_10_SECONDS"=>			"10秒",
	"S_30_SECONDS"=>			"30秒",
	"S_1_MINUTE"=>				"1分",
	"S_5_MINUTES"=>				"5分",
	"S_MORE_THAN_5_MINUTES"=>		"多于5分",

//	report1.php
	"S_STATUS_OF_ZABBIX"=>			"ZABBIX状态",
	"S_STATUS_OF_ZABBIX_BIG"=>		"ZABBIX系统状态",
	"S_VALUE"=>				"值",
	"S_ZABBIX_SERVER_IS_RUNNING"=>		"ZABBIX server 的状态",
	"S_NUMBER_OF_VALUES_STORED"=>		"已保存的信息数",
	"S_NUMBER_OF_TRENDS_STORED"=>		"已保存的trends数",
	"S_NUMBER_OF_ALARMS"=>			"alarms示警数量",
	"S_NUMBER_OF_ALERTS"=>			"alerts数量",
	"S_NUMBER_OF_TRIGGERS_ENABLED_DISABLED"=>"触发器数量(活跃/不活跃)",
	"S_NUMBER_OF_ITEMS_ACTIVE_TRAPPER"=>	"统计的条目数量 (活跃/trapper/不活跃/不支持)",
	"S_NUMBER_OF_USERS"=>			"ZABBIX系统用户数量",
	"S_NUMBER_OF_HOSTS_MONITORED"=>		"主机数量(检测中/未检测/模板主机/已删除)",
	"S_YES"=>				"Yes",
	"S_NO"=>				"No",

//	report2.php
	"S_AVAILABILITY_REPORT"=>		"可用性统计",
	"S_AVAILABILITY_REPORT_BIG"=>		"系统可用性统计",
	"S_SHOW"=>				"显示",
	"S_TRUE"=>				"可用",
	"S_FALSE"=>				"不可用",

//	report3.php
	"S_IT_SERVICES_AVAILABILITY_REPORT_BIG"=>	"IT服务可用性报告",
	"S_FROM"=>				"From",
	"S_TILL"=>				"Till",
	"S_OK"=>				"Ok",
	"S_PROBLEMS"=>				"Problems",
	"S_PERCENTAGE"=>			"百分比",
	"S_SLA"=>				"SLA",
	"S_DAY"=>				"日",
	"S_MONTH"=>				"月",
	"S_YEAR"=>				"年",
	"S_DAILY"=>				"每日",
	"S_WEEKLY"=>				"每周",
	"S_MONTHLY"=>				"每月",
	"S_YEARLY"=>				"每年",

//	screenconf.php
	"S_SCREENS"=>				"配置界面",
	"S_SCREEN"=>				"界面",
	"S_CONFIGURATION_OF_SCREENS_BIG"=>	"用户显示界面配置",
	"S_CONFIGURATION_OF_SCREENS"=>		"显示界面配置",
	"S_SCREEN_ADDED"=>			"界面已添加",
	"S_CANNOT_ADD_SCREEN"=>			"无法添加界面",
	"S_SCREEN_UPDATED"=>			"界面已更新",
	"S_CANNOT_UPDATE_SCREEN"=>		"无法更新界面",
	"S_SCREEN_DELETED"=>			"界面已删除",
	"S_CANNOT_DELETE_SCREEN"=>		"无法删除界面",
	"S_COLUMNS"=>				"列数",
	"S_ROWS"=>				"函数",
	"S_NO_SCREENS_DEFINED"=>		"No screens defined",
	"S_DELETE_SCREEN_Q"=>			"Delete screen?",
	"S_CONFIGURATION_OF_SCREEN_BIG"=>	"CONFIGURATION OF SCREEN",
	"S_SCREEN_CELL_CONFIGURATION"=>		"Screen cell configuration",
	"S_RESOURCE"=>				"资源",
	"S_SIMPLE_GRAPH"=>			"Simple graph",
	"S_GRAPH_NAME"=>			"图表名",
	"S_WIDTH"=>				"宽",
	"S_HEIGHT"=>				"高",
	"S_EMPTY"=>				"Empty",

//	screenedit.php
	"S_MAP"=>				"Map",
	"S_PLAIN_TEXT"=>			"文本",
	"S_COLUMN_SPAN"=>			"Column span",
	"S_ROW_SPAN"=>				"Row span",

//	screens.php
	"S_CUSTOM_SCREENS"=>			"用户界面",
	"S_SCREENS_BIG"=>			"用户界面",
	"S_NO_SCREENS_TO_DISPLAY"=>		"No screens to display",
	"S_SELECT_SCREEN_TO_DISPLAY"=>		"请选择要显示的图表",
	"S_SELECT_SCREEN_DOT_DOT_DOT"=>		"选择界面...",

//	services.php
	"S_IT_SERVICES"=>			"IT状态",
	"S_SERVICE_UPDATED"=>			"Service updated",
	"S_CANNOT_UPDATE_SERVICE"=>		"Cannot update service",
	"S_SERVICE_ADDED"=>			"Service added",
	"S_CANNOT_ADD_SERVICE"=>		"Cannot add service",
	"S_LINK_ADDED"=>			"Link added",
	"S_CANNOT_ADD_LINK"=>			"Cannot add link",
	"S_SERVICE_DELETED"=>			"Service deleted",
	"S_CANNOT_DELETE_SERVICE"=>		"Cannot delete service",
	"S_LINK_DELETED"=>			"Link deleted",
	"S_CANNOT_DELETE_LINK"=>		"Cannot delete link",
	"S_STATUS_CALCULATION"=>		"状态统计",
	"S_STATUS_CALCULATION_ALGORITHM"=>	"Status calculation algorithm",
	"S_NONE"=>				"None",
	"S_MAX_OF_CHILDS"=>			"MAX of childs",
	"S_MIN_OF_CHILDS"=>			"MIN of childs",
	"S_SERVICE_1"=>				"Service 1",
	"S_SERVICE_2"=>				"Service 2",
	"S_SOFT_HARD_LINK"=>			"Soft/hard link",
	"S_SOFT"=>				"Soft",
	"S_HARD"=>				"Hard",
	"S_DO_NOT_CALCULATE"=>			"Do not calculate",
	"S_MAX_BIG"=>				"MAX",
	"S_MIN_BIG"=>				"MIN",
	"S_SHOW_SLA"=>				"Show SLA",
	"S_ACCEPTABLE_SLA_IN_PERCENT"=>		"Acceptabe SLA (in %)",
	"S_LINK_TO_TRIGGER_Q"=>			"Link to trigger?",
	"S_SORT_ORDER_0_999"=>			"Sort order (0->999)",
	"S_DELETE_SERVICE_Q"=>			"S_DELETE_SERVICE_Q",
	"S_LINK_TO"=>				"连接至",
	"S_SOFT_LINK_Q"=>			"软连接?",
	"S_ADD_SERVER_DETAILS"=>		"Add server details",
	"S_TRIGGER"=>				"Trigger",
	"S_SERVER"=>				"Server",
	"S_DELETE"=>				"Delete",
	"S_DELETE_SELECTED_SERVICES"=>		"Delete selected services?",
	"S_SERVICES_DELETED"=>			"Services deleted",
	"S_CANNOT_DELETE_SERVICES"=>		"Cannot delete services",

//	srv_status.php
	"S_IT_SERVICES_BIG"=>			"IT服务",
	"S_SERVICE"=>				"服务",
	"S_REASON"=>				"Reason",
	"S_SLA_LAST_7_DAYS"=>			"SLA (last 7 days)",
	"S_PLANNED_CURRENT_SLA"=>		"Planned/current SLA",
	"S_TRIGGER_BIG"=>			"TRIGGER",

//	triggers.php
	"S_CONFIGURATION_OF_TRIGGERS"=>		"配置触发器",
	"S_CONFIGURATION_OF_TRIGGERS_BIG"=>	"触发器配置",
	"S_DEPENDENCY_ADDED"=>			"Dependency added",
	"S_CANNOT_ADD_DEPENDENCY"=>		"Cannot add dependency",
	"S_TRIGGERS_UPDATED"=>			"触发器已更新",
	"S_CANNOT_UPDATE_TRIGGERS"=>		"无法更新触发器",
	"S_TRIGGERS_DISABLED"=>			"触发器已取消",
	"S_CANNOT_DISABLE_TRIGGERS"=>		"无法取消触发器",
	"S_TRIGGERS_DELETED"=>			"触发器已删除",
	"S_CANNOT_DELETE_TRIGGERS"=>		"无法删除触发器",
	"S_TRIGGER_DELETED"=>			"触发器已删除",
	"S_CANNOT_DELETE_TRIGGER"=>		"无法删除触发器",
	"S_INVALID_TRIGGER_EXPRESSION"=>	"触发器表达式无效",
	"S_TRIGGER_ADDED"=>			"触发器已添加",
	"S_CANNOT_ADD_TRIGGER"=>		"无法添加触发器",
	"S_SEVERITY"=>				"示警度",
	"S_EXPRESSION"=>			"表达式",
	"S_DISABLED"=>				"不活跃",
	"S_ENABLED"=>				"活跃",
	"S_ENABLE_SELECTED_TRIGGERS_Q"=>	"激活选中的触发器?",
	"S_DISABLE_SELECTED_TRIGGERS_Q"=>	"取消选中的触发器?",
	"S_CHANGE"=>				"更改",
	"S_TRIGGER_UPDATED"=>			"触发器已更新",
	"S_CANNOT_UPDATE_TRIGGER"=>		"无法更新触发器",
	"S_DEPENDS_ON"=>			"Depends on",

//	tr_comments.php
	"S_TRIGGER_COMMENTS"=>			"Trigger comments",
	"S_TRIGGER_COMMENTS_BIG"=>		"TRIGGER COMMENTS",
	"S_COMMENT_UPDATED"=>			"Comment updated",
	"S_CANNOT_UPDATE_COMMENT"=>		"Cannot update comment",
	"S_ADD"=>				"Add",

//	tr_status.php
	"S_STATUS_OF_TRIGGERS"=>		"触发器状态",
	"S_STATUS_OF_TRIGGERS_BIG"=>		"触发器的状态",
	"S_SHOW_ONLY_TRUE"=>			"Show only true",
	"S_HIDE_ACTIONS"=>			"隐藏操作",
	"S_SHOW_ACTIONS"=>			"显示操作",
	"S_SHOW_ALL_TRIGGERS"=>			"显示所有触发器",
	"S_HIDE_DETAILS"=>			"隐藏细节",
	"S_SHOW_DETAILS"=>			"显示细节",
	"S_SELECT"=>				"搜索",
	"S_HIDE_SELECT"=>			"隐藏搜索",
	"S_TRIGGERS_BIG"=>			"触发器",
	"S_DESCRIPTION_BIG"=>			"详细内容",
	"S_SEVERITY_BIG"=>			"示警度",
	"S_LAST_CHANGE_BIG"=>			"上次修改于",
	"S_LAST_CHANGE"=>			"上次修改于",
	"S_COMMENTS"=>				"备注",

//	users.php
	"S_USERS"=>				"用户",
	"S_USER_ADDED"=>			"添加用户",
	"S_CANNOT_ADD_USER"=>			"Cannot add user",
	"S_CANNOT_ADD_USER_BOTH_PASSWORDS_MUST"=>"Cannot add user. Both passwords must be equal.",
	"S_USER_DELETED"=>			"User deleted",
	"S_CANNOT_DELETE_USER"=>		"Cannot delete user",
	"S_PERMISSION_DELETED"=>		"Permission deleted",
	"S_CANNOT_DELETE_PERMISSION"=>		"Cannot delete permission",
	"S_PERMISSION_ADDED"=>			"Permission added",
	"S_CANNOT_ADD_PERMISSION"=>		"Cannot add permission",
	"S_USER_UPDATED"=>			"User updated",
	"S_CANNOT_UPDATE_USER"=>		"Cannot update user",
	"S_CANNOT_UPDATE_USER_BOTH_PASSWORDS"=>	"Cannot update user. Both passwords must be equal.",
	"S_GROUP_ADDED"=>			"Group added",
	"S_CANNOT_ADD_GROUP"=>			"Cannot add group",
	"S_GROUP_UPDATED"=>			"Group updated",
	"S_CANNOT_UPDATE_GROUP"=>		"Cannot update group",
	"S_GROUP_DELETED"=>			"Group deleted",
	"S_CANNOT_DELETE_GROUP"=>		"Cannot delete group",
	"S_CONFIGURATION_OF_USERS_AND_USER_GROUPS"=>"用户及用户组配置",
	"S_USER_GROUPS_BIG"=>			"用户组",
	"S_USERS_BIG"=>				"用户",
	"S_USER_GROUPS"=>			"用户组",
	"S_MEMBERS"=>				"成员",
	"S_TEMPLATES"=>				"Templates",
	"S_HOSTS_TEMPLATES_LINKAGE"=>		"Hosts/templates linkage",
	"S_CONFIGURATION_OF_TEMPLATES_LINKAGE"=>"CONFIGURATION OF TEMPLATES LINKAGE",
	"S_LINKED_TEMPLATES_BIG"=>		"LINKED TEMPLATES",
	"S_NO_USER_GROUPS_DEFINED"=>		"未定义用户组",
	"S_ALIAS"=>				"登陆名",
	"S_NAME"=>				"名称",
	"S_SURNAME"=>				"姓氏",
	"S_IS_ONLINE_Q"=>			"是否在线?",
	"S_NO_USERS_DEFINED"=>			"没有定义用户",
	"S_PERMISSION"=>			"Permission",
	"S_RIGHT"=>				"权限",
	"S_RESOURCE_NAME"=>			"Resource name",
	"S_READ_ONLY"=>				"只读",
	"S_READ_WRITE"=>			"读写",
	"S_HIDE"=>				"可隐身",
	"S_PASSWORD"=>				"密码",
	"S_PASSWORD_ONCE_AGAIN"=>		"确认密码",
	"S_URL_AFTER_LOGIN"=>			"登录后定向到",
	"S_AUTO_LOGOUT_IN_SEC"=>		"自动退出(0 永远)",
	"S_SCREEN_REFRESH"=>                    "自动刷行间隔(s)",

//	audit.php
	"S_AUDIT_LOG"=>				"审计日志",
	"S_AUDIT_LOG_BIG"=>			"审计日志",
	"S_ACTION"=>				"操作",
	"S_DETAILS"=>				"详细内容",
	"S_UNKNOWN_ACTION"=>			"未知操作",
	"S_ADDED"=>				"添加",
	"S_UPDATED"=>				"更新",
	"S_LOGGED_IN"=>				"登录/退出",
	"S_LOGGED_OUT"=>			"退出",
	"S_MEDIA_TYPE"=>			"介质类型",
	"S_GRAPH_ELEMENT"=>			"Graph element",
	"S_UNKNOWN_RESOURCE"=>			"未知资源",

//	profile.php
	"S_USER_PROFILE_BIG"=>			"USER PROFILE",
	"S_USER_PROFILE"=>			"User profile",
	"S_LANGUAGE"=>				"Language",
	"S_ENGLISH_GB"=>			"English (GB)",
	"S_JAPANESE_JP"=>			"Chinese (CN)",
	"S_FRENCH_FR"=>				"French (FR)",
	"S_GERMAN_DE"=>				"German (DE)",
	"S_ITALIAN_IT"=>			"Italian (IT)",
	"S_LATVIAN_LV"=>			"Latvian (LV)",
	"S_RUSSIAN_RU"=>			"Russian (RU)",
	"S_SPANISH_SP"=>			"Spanish (SP)",
	
//	index.php
	"S_ZABBIX_BIG"=>			"ZABBIX",

//	hostprofiles.php
	"S_HOST_PROFILES"=>			"Host profiles",
	"S_HOST_PROFILES_BIG"=>			"请选择主机所在位置=>",

//	bulkloader.php
	"S_MENU_BULKLOADER"=>			"批量导入",
	"S_BULKLOADER_MAIN"=>			"批量导入: Main Page",
	"S_BULKLOADER_HOSTS"=>			"批量导入: Hosts",
	"S_BULKLOADER_ITEMS"=>			"批量导入: Items",
	"S_BULKLOADER_USERS"=>			"批量导入: Users",
	"S_BULKLOADER_TRIGGERS"=>		"批量导入: Triggers",
	"S_BULKLOADER_ACTIONS"=>		"批量导入: Actions",
	"S_BULKLOADER_ITSERVICES"=>		"批量导入: IT Services",

	"S_BULKLOADER_IMPORT_HOSTS"=>		"导入主机",
	"S_BULKLOADER_IMPORT_ITEMS"=>		"Import Items",
	"S_BULKLOADER_IMPORT_USERS"=>		"Import Users",
	"S_BULKLOADER_IMPORT_TRIGGERS"=>	"Import Triggers",
	"S_BULKLOADER_IMPORT_ACTIONS"=>		"Import Actions",
	"S_BULKLOADER_IMPORT_ITSERVICES"=>	"Import IT Services",

//	Menu

	"S_HELP"=>				"帮助",
	"S_PROFILE"=>				"配置",
	"S_MONITORING"=>			"状态统计",
	"S_CONFIGURATION_MANAGEMENT"=>		"主机资料",
	"S_QUEUE"=>				"队列",
	"S_EVENTS"=>				"事件",
	"S_MAPS"=>				"系统布局",
	"S_REPORTS"=>				"系统评估",
	"S_GENERAL"=>				"摘要",
	"S_AUDIT"=>				"审计",
	"S_LOGIN"=>				"登录/退出",
	"S_LATEST_DATA"=>			"最新数据",
	);
?>
