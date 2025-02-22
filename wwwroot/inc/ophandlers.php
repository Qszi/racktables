<?php

# This file is a part of RackTables, a datacenter and server room management
# framework. See accompanying file "COPYING" for the full copyright and
# licensing information.

/*

"Ophandler" in RackTables stands for "operation handler", or a function
that handles execution of "operation" (in the meaning explained in
navigation.php). Most of the ophandlers are meant to perform one specific
action, for example, to set a name of an object. Each such action often
requires a set of parameters (e. g. ID of the object and the new name),
and it is responsibility of each ophandler function to verify that all
necessary parameters are provided by the user and have proper values. There
is a number of helper functions to make such verification simpler.

Errors occurring in ophandlers are typically indicated with exceptions of
assorted classes. Namely, an "InvalidRequestArgException" class means that
at least one of the parameters provided by the user is not acceptable. This
is a "soft" error, which gets displayed in the standard message area of
otherwise usual interface. A different case is "InvalidArgException", which
means that one of the internal functions detected its argument(s) invalid
or corrupted, and that argument(s) did not come from user's input (and thus
cannot be fixed without fixing a bug in the code). Such "hard" errors don't
get special early handling and end up in the default catching block. The
latter may print a detailed stack trace instead of the interface HTML to
help a developer debug the issue.

As long as an ophandler makes through its request (extracting arguments,
performing validation and actually updating records in the database), it
may queue up messages (often referred to as "green" and "red" bars) by
means of showError() and showSuccess() functions. The messages are not
displayed immediately, because successful ophandlers are expected to
return only the new URL, where the user will be immediately redirected to
(it is also possible to return an empty string to mean that the current
logical location remains the same). The page at the "next" location is
supposed to translate message buffer into the standard message area.

A very special case of an ophandler is tableHandler(). This generic
function handles the most trivial actions, which map to a single INSERT,
UPDATE or DELETE SQL statement with a fixed number of arguments. The rules
of argument validation and mapping are listed in $opspec_list (operation
specifications list) array.

*/

// This array is deprecated. Please do not add new message constants to it.
// use the new showError, showWarning, showSuccess functions instead
global $msgcode;
$msgcode = array();

global $opspec_list;
$opspec_list = array();

$opspec_list['object-edit-unlinkObjects'] = array
(
	'table' => 'EntityLink',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'link_id', 'table_colname' => 'id', 'assertion' => 'natural'),
	),
);
$opspec_list['object-ports-useup'] = array
(
	'table' => 'Port',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('fix_argname' => 'reservation_comment', 'fix_argvalue' => NULL),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'port_id', 'table_colname' => 'id', 'assertion' => 'natural'),
		array ('url_argname' => 'object_id', 'assertion' => 'natural'), # preserve context
	),
);
$opspec_list['object-ports-delPort'] = array
(
	'table' => 'Port',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'port_id', 'table_colname' => 'id', 'assertion' => 'natural'),
		array ('url_argname' => 'object_id', 'assertion' => 'natural'),
	),
);
$opspec_list['object-ports-deleteAll'] = array
(
	'table' => 'Port',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'object_id', 'assertion' => 'natural'),
	),
);
$opspec_list['location-log-del'] = array
(
	'table' => 'ObjectLog',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'log_id', 'table_colname' => 'id', 'assertion' => 'natural'),
		array ('url_argname' => 'location_id', 'table_colname' => 'object_id', 'assertion' => 'natural'),
	),
);
$opspec_list['object-log-del'] = array
(
	'table' => 'ObjectLog',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'log_id', 'table_colname' => 'id', 'assertion' => 'natural'),
		array ('url_argname' => 'object_id', 'assertion' => 'natural'),
	),
);
$opspec_list['rack-log-del'] = array
(
	'table' => 'ObjectLog',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'log_id', 'table_colname' => 'id', 'assertion' => 'natural'),
		array ('url_argname' => 'rack_id', 'table_colname' => 'object_id', 'assertion' => 'natural'),
	),
);
$opspec_list['row-log-del'] = array
(
	'table' => 'ObjectLog',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'log_id', 'table_colname' => 'id', 'assertion' => 'natural'),
		array ('url_argname' => 'row_id', 'table_colname' => 'object_id', 'assertion' => 'natural'),
	),
);
$opspec_list['ipv4vs-editlblist-delLB'] =
$opspec_list['ipv4rspool-editlblist-delLB'] =
$opspec_list['object-editrspvs-delLB'] = array
(
	'table' => 'IPv4LB',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'object_id', 'assertion' => 'natural'),
		array ('url_argname' => 'pool_id', 'table_colname' => 'rspool_id', 'assertion' => 'natural'),
		array ('url_argname' => 'vs_id', 'assertion' => 'natural'),
	),
);
$opspec_list['ipv4vs-editlblist-updLB'] =
$opspec_list['ipv4rspool-editlblist-updLB'] =
$opspec_list['object-editrspvs-updLB'] = array
(
	'table' => 'IPv4LB',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'vsconfig', 'assertion' => 'string0', 'translator' => 'nullIfEmptyStr'),
		array ('url_argname' => 'rsconfig', 'assertion' => 'string0', 'translator' => 'nullIfEmptyStr'),
		array ('url_argname' => 'prio', 'assertion' => 'string0', 'translator' => 'nullIfEmptyStr'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'object_id', 'assertion' => 'natural'),
		array ('url_argname' => 'pool_id', 'table_colname' => 'rspool_id', 'assertion' => 'natural'),
		array ('url_argname' => 'vs_id', 'assertion' => 'natural'),
	),
);
$opspec_list['ipv4rspool-editrslist-delRS'] = array
(
	'table' => 'IPv4RS',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'natural'),
	),
);
$opspec_list['parentmap-edit-add'] = array
(
	'table' => 'ObjectParentCompat',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'parent_objtype_id', 'assertion' => 'natural'),
		array ('url_argname' => 'child_objtype_id', 'assertion' => 'natural'),
	),
);
$opspec_list['parentmap-edit-del'] = array
(
	'table' => 'ObjectParentCompat',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'parent_objtype_id', 'assertion' => 'natural'),
		array ('url_argname' => 'child_objtype_id', 'assertion' => 'natural'),
	),
);
$opspec_list['portifcompat-edit-add'] = array
(
	'table' => 'PortInterfaceCompat',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'iif_id', 'assertion' => 'natural'),
		array ('url_argname' => 'oif_id', 'assertion' => 'natural'),
	),
);
$opspec_list['portifcompat-edit-del'] = array
(
	'table' => 'PortInterfaceCompat',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'iif_id', 'assertion' => 'natural'),
		array ('url_argname' => 'oif_id', 'assertion' => 'natural'),
	),
);
$opspec_list['portoifs-edit-add'] = array
(
	'table' => 'PortOuterInterface',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'oif_name', 'assertion' => 'string'),
	),
);
$opspec_list['portoifs-edit-del'] = array
(
	'table' => 'PortOuterInterface',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'natural'),
	),
);
$opspec_list['portoifs-edit-upd'] = array
(
	'table' => 'PortOuterInterface',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'oif_name', 'assertion' => 'string'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'natural'),
	),
);
$opspec_list['attrs-editmap-del'] = array
(
	'table' => 'AttributeMap',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'attr_id', 'assertion' => 'natural'),
		array ('url_argname' => 'objtype_id', 'assertion' => 'natural'),
	),
);
$opspec_list['attrs-editattrs-add'] = array
(
	'table' => 'Attribute',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'attr_type', 'table_colname' => 'type', 'assertion' => 'enum/attr_type'),
		array ('url_argname' => 'attr_name', 'table_colname' => 'name', 'assertion' => 'string'),
	),
);
$opspec_list['attrs-editattrs-del'] = array
(
	'table' => 'Attribute',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'attr_id', 'table_colname' => 'id', 'assertion' => 'natural'),
	),
);
$opspec_list['attrs-editattrs-upd'] = array
(
	'table' => 'Attribute',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'attr_name', 'table_colname' => 'name', 'assertion' => 'string'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'attr_id', 'table_colname' => 'id', 'assertion' => 'natural'),
	),
);
$opspec_list['dict-chapters-add'] = array
(
	'table' => 'Chapter',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'chapter_name', 'table_colname' => 'name', 'assertion' => 'string')
	),
);
$opspec_list['chapter-edit-add'] = array
(
	'table' => 'Dictionary',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'chapter_no', 'table_colname' => 'chapter_id', 'assertion' => 'natural'),
		array ('url_argname' => 'dict_value', 'assertion' => 'string'),
	),
);
$opspec_list['chapter-edit-del'] = array
(
	'table' => 'Dictionary',
	'action' => 'DELETE',
	'arglist' => array
	(
		// Technically dict_key is enough to delete, but including chapter_id into
		// WHERE clause makes sure that the action actually happends for the same
		// chapter that authorization was granted for.
		array ('url_argname' => 'chapter_no', 'table_colname' => 'chapter_id', 'assertion' => 'natural'),
		array ('url_argname' => 'dict_key', 'assertion' => 'natural'),
		array ('fix_argname' => 'dict_sticky', 'fix_argvalue' => 'no'), # protect system rows
	),
);
$opspec_list['chapter-edit-upd'] = array
(
	'table' => 'Dictionary',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'dict_value', 'assertion' => 'string'),
	),
	'where_arglist' => array
	(
		# same as above for listing chapter_no
		array ('url_argname' => 'chapter_no', 'table_colname' => 'chapter_id', 'assertion' => 'natural'),
		array ('url_argname' => 'dict_key', 'assertion' => 'natural'),
		array ('fix_argname' => 'dict_sticky', 'fix_argvalue' => 'no'), # protect system rows
	),
);
$opspec_list['tagtree-edit-createTag'] = array
(
	'table' => 'TagTree',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'tag_name', 'table_colname' => 'tag', 'assertion' => 'tag'),
		array ('url_argname' => 'parent_id', 'assertion' => 'unsigned', 'translator' => 'nullIfZero'),
		array ('url_argname' => 'is_assignable', 'assertion' => 'enum/yesno'),
		array ('url_argname' => 'color', 'assertion' => 'htmlcolor0', 'translator' => 'HTMLColorForDatabase'),
	),
);
$opspec_list['tagtree-edit-destroyTag'] = array
(
	'table' => 'TagTree',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'tag_id', 'table_colname' => 'id', 'assertion' => 'natural'),
	),
);
$opspec_list['tagtree-descriptions-updTagDescr'] = array
(
	'table' => 'TagTree',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'description', 'assertion' => 'string0', 'translator' => 'nullIfEmptyStr'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'natural'),
	),
);
$opspec_list['8021q-vstlist-add'] = array
(
	'table' => 'VLANSwitchTemplate',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'vst_descr', 'table_colname' => 'description', 'assertion' => 'string'),
		// workaround SQL_STRICT
		array ('fix_argname' => 'mutex_rev', 'fix_argvalue' => 0),
		array ('fix_argname' => 'saved_by', 'fix_argvalue' => ""),
	),
);
$opspec_list['8021q-vstlist-del'] = array
(
	'table' => 'VLANSwitchTemplate',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'vst_id', 'table_colname' => 'id', 'assertion' => 'natural'),
	),
);
$opspec_list['8021q-vstlist-upd'] = array
(
	'table' => 'VLANSwitchTemplate',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'vst_descr', 'table_colname' => 'description', 'assertion' => 'string'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'vst_id', 'table_colname' => 'id', 'assertion' => 'natural'),
	),
);
$opspec_list['8021q-vdlist-del'] = array
(
	'table' => 'VLANDomain',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'vdom_id', 'table_colname' => 'id', 'assertion' => 'natural'),
	),
);
$opspec_list['vlandomain-vlanlist-add'] = array
(
	'table' => 'VLANDescription',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'vdom_id', 'table_colname' => 'domain_id', 'assertion' => 'natural'),
		array ('url_argname' => 'vlan_id', 'assertion' => 'vlan'),
		array ('url_argname' => 'vlan_type', 'assertion' => 'enum/vlan_type'),
		array ('url_argname' => 'vlan_descr', 'assertion' => 'string0', 'translator' => 'nullIfEmptyStr'),
	),
);
$opspec_list['vlandomain-vlanlist-del'] = array
(
	'table' => 'VLANDescription',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'vdom_id', 'table_colname' => 'domain_id', 'assertion' => 'natural'),
		array ('url_argname' => 'vlan_id', 'assertion' => 'vlan'),
	),
);
$opspec_list['vlan-edit-upd'] = // both locations are using the same tableHandler op
$opspec_list['vlandomain-vlanlist-upd'] = array
(
	'table' => 'VLANDescription',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'vlan_type', 'assertion' => 'enum/vlan_type'),
		array ('url_argname' => 'vlan_descr', 'assertion' => 'string0', 'translator' => 'nullIfEmptyStr'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'vdom_id', 'table_colname' => 'domain_id', 'assertion' => 'natural'),
		array ('url_argname' => 'vlan_id', 'assertion' => 'vlan'),
	),
);
$opspec_list['dict-chapters-upd'] = array
(
	'table' => 'Chapter',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'chapter_name', 'table_colname' => 'name', 'assertion' => 'string'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'chapter_no', 'table_colname' => 'id', 'assertion' => 'natural'),
		array ('fix_argname' => 'sticky', 'fix_argvalue' => 'no'), # protect system chapters
	),
);
$opspec_list['dict-chapters-del'] = array
(
	'table' => 'Chapter',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'chapter_no', 'table_colname' => 'id', 'assertion' => 'natural'),
		array ('fix_argname' => 'sticky', 'fix_argvalue' => 'no'), # protect system chapters
	),
);
$opspec_list['cables-heaps-add'] = array
(
	'table' => 'PatchCableHeap',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'end1_conn_id', 'assertion' => 'natural'),
		array ('url_argname' => 'pctype_id', 'assertion' => 'natural'),
		array ('url_argname' => 'end2_conn_id', 'assertion' => 'natural'),
		array ('fix_argname' => 'amount', 'fix_argvalue' => 0),
		array ('url_argname' => 'length', 'assertion' => 'decimal'),
		array ('url_argname' => 'description', 'assertion' => 'string0'),
	),
);
$opspec_list['cables-heaps-del'] = array
(
	'table' => 'PatchCableHeap',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'natural'),
	),
);
$opspec_list['cables-heaps-upd'] = array
(
	'table' => 'PatchCableHeap',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'end1_conn_id', 'assertion' => 'natural'),
		array ('url_argname' => 'pctype_id', 'assertion' => 'natural'),
		array ('url_argname' => 'end2_conn_id', 'assertion' => 'natural'),
		array ('url_argname' => 'length', 'assertion' => 'decimal'),
		array ('url_argname' => 'description', 'assertion' => 'string0'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'natural'),
	),
);
$opspec_list['cableconf-connectors-add'] = array
(
	'table' => 'PatchCableConnector',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'connector', 'assertion' => 'string'),
		array ('fix_argname' => 'origin', 'fix_argvalue' => 'custom'),
	),
);
$opspec_list['cableconf-connectors-del'] = array
(
	'table' => 'PatchCableConnector',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'natural'),
		array ('fix_argname' => 'origin', 'fix_argvalue' => 'custom'),
	),
);
$opspec_list['cableconf-connectors-upd'] = array
(
	'table' => 'PatchCableConnector',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'connector', 'assertion' => 'string'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'natural'),
		array ('fix_argname' => 'origin', 'fix_argvalue' => 'custom'),
	),
);
$opspec_list['cableconf-cabletypes-add'] = array
(
	'table' => 'PatchCableType',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'pctype', 'assertion' => 'string'),
		array ('fix_argname' => 'origin', 'fix_argvalue' => 'custom'),
	),
);
$opspec_list['cableconf-cabletypes-del'] = array
(
	'table' => 'PatchCableType',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'natural'),
		array ('fix_argname' => 'origin', 'fix_argvalue' => 'custom'),
	),
);
$opspec_list['cableconf-cabletypes-upd'] = array
(
	'table' => 'PatchCableType',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'pctype', 'assertion' => 'string'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'natural'),
		array ('fix_argname' => 'origin', 'fix_argvalue' => 'custom'),
	),
);
$opspec_list['cableconf-conncompat-add'] = array
(
	'table' => 'PatchCableConnectorCompat',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'pctype_id', 'assertion' => 'natural'),
		array ('url_argname' => 'connector_id', 'assertion' => 'natural'),
	),
);
$opspec_list['cableconf-conncompat-del'] = array
(
	'table' => 'PatchCableConnectorCompat',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'pctype_id', 'assertion' => 'natural'),
		array ('url_argname' => 'connector_id', 'assertion' => 'natural'),
	),
);
$opspec_list['cableconf-oifcompat-add'] = array
(
	'table' => 'PatchCableOIFCompat',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'pctype_id', 'assertion' => 'natural'),
		array ('url_argname' => 'oif_id', 'assertion' => 'natural'),
	),
);
$opspec_list['cableconf-oifcompat-del'] = array
(
	'table' => 'PatchCableOIFCompat',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'pctype_id', 'assertion' => 'natural'),
		array ('url_argname' => 'oif_id', 'assertion' => 'natural'),
	),
);
$opspec_list['plugins-edit-disable'] = array
(
	'table' => 'Plugin',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('fix_argname' => 'state', 'fix_argvalue' => 'disabled'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'name', 'assertion' => 'string'),
	),
);
$opspec_list['plugins-edit-enable'] = array
(
	'table' => 'Plugin',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('fix_argname' => 'state', 'fix_argvalue' => 'enabled'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'name', 'assertion' => 'string'),
	),
);

function setFuncMessages ($funcname, $messages)
{
	global $msgcode;
	foreach ($messages as $symbol => $code)
		$msgcode[$funcname][$symbol] = $code;
}

function addPortForwarding ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 48));
	$proto = genericAssertion ('proto', 'enum/natv4proto');
	if ($proto != 'ALL')
	{
		genericAssertion ('localport', 'natural');
		genericAssertion ('remoteport', 'natural');
	}
	assertStringArg ('description', TRUE);
	$remoteport = isset ($_REQUEST['remoteport']) ? $_REQUEST['remoteport'] : '';
	if ($remoteport == '')
		$remoteport = $_REQUEST['localport'];

	try
	{
		newPortForwarding
		(
			getBypassValue(),
			genericAssertion ('localip', 'inet4'),
			$_REQUEST['localport'],
			genericAssertion ('remoteip', 'inet4'),
			$remoteport,
			$proto,
			$_REQUEST['description']
		);
	}
	catch (InvalidArgException $iae)
	{
		throw $iae->newIRAE();
	}
	showFuncMessage (__FUNCTION__, 'OK');
}

function delPortForwarding ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 49));
	$proto = genericAssertion ('proto', 'enum/natv4proto');
	if ($proto != 'ALL')
	{
		genericAssertion ('localport', 'natural');
		genericAssertion ('remoteport', 'natural');
	}

	deletePortForwarding
	(
		getBypassValue(),
		genericAssertion ('localip', 'inet4'),
		$_REQUEST['localport'],
		genericAssertion ('remoteip', 'inet4'),
		$_REQUEST['remoteport'],
		$proto
	);
	showFuncMessage (__FUNCTION__, 'OK');
}

function updPortForwarding ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 51));
	$proto = genericAssertion ('proto', 'enum/natv4proto');
	if ($proto != 'ALL')
	{
		genericAssertion ('localport', 'natural');
		genericAssertion ('remoteport', 'natural');
	}
	assertStringArg ('description', TRUE);

	updatePortForwarding
	(
		getBypassValue(),
		genericAssertion ('localip', 'inet4'),
		$_REQUEST['localport'],
		genericAssertion ('remoteip', 'inet4'),
		$_REQUEST['remoteport'],
		$proto,
		$_REQUEST['description']
	);
	showFuncMessage (__FUNCTION__, 'OK');
}

function addPortForObject ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 48));
	genericAssertion ('port_name', 'string');
	try
	{
		commitAddPort
		(
			getBypassValue(),
			trim ($_REQUEST['port_name']),
			genericAssertion ('port_type_id', 'string'),
			trim ($_REQUEST['port_label']),
			trim (genericAssertion ('port_l2address', 'l2address0'))
		);
	}
	catch (InvalidRequestArgException $irae)
	{
		throw $irae;
	}
	catch (InvalidArgException $iae)
	{
		throw $iae->newIRAE();
	}
	showFuncMessage (__FUNCTION__, 'OK', array ($_REQUEST['port_name']));
}

function editPortForObject ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 6));
	global $sic;
	$port_id = genericAssertion ('port_id', 'natural');
	try
	{
		commitUpdatePort
		(
			getBypassValue(),
			$port_id,
			genericAssertion ('name', 'string'),
			assertStringArg ('port_type_id'),
			genericAssertion ('label', 'string0'),
			genericAssertion ('l2address', 'l2address0'),
			assertStringArg ('reservation_comment', TRUE)
		);
	}
	catch (InvalidRequestArgException $irae)
	{
		throw $irae;
	}
	catch (InvalidArgException $iae)
	{
		throw $iae->newIRAE();
	}
	if (array_key_exists ('cable', $_REQUEST))
		commitUpdatePortLink ($port_id, $sic['cable']);
	showFuncMessage (__FUNCTION__, 'OK', array ($_REQUEST['name']));
}

function addMultiPorts ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 10));
	assertStringArg ('input');
	$format = genericAssertion ('format', 'string');
	$port_type = genericAssertion ('port_type', 'string');
	$object_id = getBypassValue();
	$ports = array();
	foreach (textareaCooked ($_REQUEST['input']) as $line)
	{
		switch ($format)
		{
			case 'ssv1':
				$words = explode (' ', $line);
				if ($words[0] == '') // empty L2 address is OK
					continue 2;
				$ports[] = array
				(
					'name' => $words[0],
					'l2address' => array_fetch ($words, 1, ''),
					'label' => ''
				);
				break;
			default:
				throw new RackTablesError ("unknown data format '${format}'", RackTablesError::INTERNAL);
		}
	}
	// Create ports, if they don't exist.
	$added_count = $updated_count = $error_count = 0;
	foreach ($ports as $port)
	{
		$port_ids = getPortIDs ($object_id, $port['name']);
		try
		{
			if (!count ($port_ids))
			{
				commitAddPort ($object_id, $port['name'], $port_type, $port['label'], $port['l2address']);
				$added_count++;
			}
			elseif (count ($port_ids) == 1) // update only single-socket ports
			{
				$rsvc = getPortReservationComment (array_first ($port_ids));
				commitUpdatePort ($object_id, $port_ids[0], $port['name'], $port_type, $port['label'], $port['l2address'], $rsvc);
				$updated_count++;
			}
		}
		catch (InvalidArgException $iae)
		{
			showError ($iae->getMessage());
		}
	}
	showFuncMessage (__FUNCTION__, 'OK', array ($added_count, $updated_count, $error_count));
}

function addBulkPorts ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 82));
	assertStringArg ('port_name', TRUE);
	assertStringArg ('port_label', TRUE);

	$object_id = getBypassValue();
	$port_name = $_REQUEST['port_name'];
	$port_type_id = genericAssertion ('port_type_id', 'string');
	$port_label = $_REQUEST['port_label'];
	$port_numbering_start = genericAssertion ('port_numbering_start', 'unsigned');
	$port_numbering_count = genericAssertion ('port_numbering_count', 'natural');

	$added_count = $error_count = 0;
	if (strrpos ($port_name, '%u') === FALSE)
		$port_name .= '%u';
	if (strrpos ($port_label, '%u') === FALSE)
		$port_label .= '%u';
	for ($i = 0, $c = $port_numbering_start; $i < $port_numbering_count; $i++, $c++)
	{
		commitAddPort ($object_id, @sprintf ($port_name, $c), $port_type_id, @sprintf ($port_label, $c), '');
		$added_count++;
	}
	showFuncMessage (__FUNCTION__, 'OK', array ($added_count, $error_count));
}

function updIPAllocation ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 51));
	$ip_bin = assertIPArg ('ip');
	assertStringArg ('bond_name', TRUE);
	updateIPBond
	(
		$ip_bin,
		genericAssertion ('object_id', 'natural'),
		$_REQUEST['bond_name'],
		genericAssertion ('bond_type', 'enum/alloc_type')
	);
	showFuncMessage (__FUNCTION__, 'OK');
	return buildRedirectURL (NULL, NULL, array ('hl_ip' => ip_format ($ip_bin)));
}

function delIPAllocation ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 49));
	unbindIPFromObject (genericAssertion ('ip', 'inet'), genericAssertion ('object_id', 'natural'));
	showFuncMessage (__FUNCTION__, 'OK');
}

function addIPAllocation ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 48, 'ERR1' => 170));
	$ip_bin = assertIPArg ('ip');
	$alloc_type = genericAssertion ('bond_type', 'enum/alloc_type');

	// check if address is alread allocated
	$address = getIPAddress($ip_bin);

	if(!empty($address['allocs']) && ( ($address['allocs'][0]['type'] != 'shared') || ($alloc_type != 'shared') ) )
		showWarning("IP ".ip_format($ip_bin)." already in use by ".$address['allocs'][0]['object_name']." - ".$address['allocs'][0]['name']);

	if (getConfigVar ('IPV4_JAYWALK') != 'yes' && NULL === getIPAddressNetworkId ($ip_bin))
	{
		showFuncMessage (__FUNCTION__, 'ERR1', array (ip_format ($ip_bin)));
		return;
	}

	if($address['reserved'] && $address['name'] != '')
	{
		showWarning("IP ".ip_format($ip_bin)." reservation \"".$address['name']."\" is removed");
		//TODO ask to take reserved IP or not !
	}

	bindIPToObject
	(
		$ip_bin,
		genericAssertion ('object_id', 'natural'),
		genericAssertion ('bond_name', 'string0'),
		$alloc_type
	);

	showFuncMessage (__FUNCTION__, 'OK');
	return buildRedirectURL (NULL, NULL, array ('hl_ip' => ip_format ($ip_bin)));
}

function addIPv4Prefix ()
{
	global $sic;
	$vlan_ck = empty ($sic['vlan_ck']) ? NULL : genericAssertion ('vlan_ck', 'uint-vlan1');
	try
	{
		$net_id = createIPv4Prefix
		(
			genericAssertion ('range', 'string'),
			genericAssertion ('name', 'string0'),
			isCheckSet ('is_connected'),
			genericAssertion ('taglist', 'array0')
		);
	}
	catch (InvalidRequestArgException $irae)
	{
		throw $irae;
	}
	catch (InvalidArgException $iae)
	{
		throw $iae->newIRAE();
	}
	$net_cell = spotEntity ('ipv4net', $net_id);
	if (isset ($vlan_ck))
	{
		if (considerConfiguredConstraint ($net_cell, 'VLANNET_LISTSRC'))
			commitSupplementVLANIPv4 ($vlan_ck, $net_id);
		else
			showError ("VLAN binding to network " . mkCellA ($net_cell) . " is restricted in config");
	}
	showSuccess ('IP network ' . mkCellA ($net_cell) . ' has been created');
}

function addIPv6Prefix ()
{
	global $sic;
	$vlan_ck = empty ($sic['vlan_ck']) ? NULL : genericAssertion ('vlan_ck', 'uint-vlan1');
	try
	{
		$net_id = createIPv6Prefix
		(
			genericAssertion ('range', 'string'),
			genericAssertion ('name', 'string0'),
			isCheckSet ('is_connected'),
			genericAssertion ('taglist', 'array0')
		);
	}
	catch (InvalidRequestArgException $irae)
	{
		throw $irae;
	}
	catch (InvalidArgException $iae)
	{
		throw $iae->newIRAE();
	}
	$net_cell = spotEntity ('ipv6net', $net_id);
	if (isset ($vlan_ck))
	{
		if (considerConfiguredConstraint ($net_cell, 'VLANNET_LISTSRC'))
			commitSupplementVLANIPv6 ($vlan_ck, $net_id);
		else
			showError ("VLAN binding to network " . mkCellA ($net_cell) . " is restricted in config");
	}
	showSuccess ('IP network ' . mkCellA ($net_cell) . ' has been created');
}

function delIPv4Prefix ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 49));
	$netinfo = spotEntity ('ipv4net', genericAssertion ('id', 'natural'));
	loadIPAddrList ($netinfo);
	if (! isIPNetworkEmpty ($netinfo))
	{
		showError ("There are allocations within prefix, delete forbidden");
		return;
	}
	if (array_key_exists ($netinfo['ip_bin'], $netinfo['addrlist']))
		updateV4Address ($netinfo['ip_bin'], '', 'no');
	$last_ip = ip_last ($netinfo);
	if (array_key_exists ($last_ip, $netinfo['addrlist']))
		updateV4Address ($last_ip, '', 'no');
	destroyIPv4Prefix ($netinfo['id']);
	showFuncMessage (__FUNCTION__, 'OK');
	global $pageno;
	if ($pageno == 'ipv4net')
		return buildRedirectURL ('index', 'default');
}

function delIPv6Prefix ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 49));
	$netinfo = spotEntity ('ipv6net', genericAssertion ('id', 'natural'));
	loadIPAddrList ($netinfo);
	if (! isIPNetworkEmpty ($netinfo))
	{
		showError ("There are allocations within prefix, delete forbidden");
		return;
	}
	if (array_key_exists ($netinfo['ip_bin'], $netinfo['addrlist']))
		updateV6Address ($netinfo['ip_bin'], '', 'no');
	destroyIPv6Prefix ($netinfo['id']);
	showFuncMessage (__FUNCTION__, 'OK');
	global $pageno;
	if ($pageno == 'ipv6net')
		return buildRedirectURL ('index', 'default');
}

function editAddress ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 51));
	assertStringArg ('name', TRUE);
	assertStringArg ('comment', TRUE);
	updateAddress
	(
		genericAssertion ('ip', 'inet'),
		$_REQUEST['name'],
		isCheckSet ('reserved', 'yesno'),
		$_REQUEST['comment']
	);
	showFuncMessage (__FUNCTION__, 'OK');
}

function createUser ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 5));
	assertStringArg ('username');
	assertStringArg ('realname', TRUE);
	assertStringArg ('password');
	$username = $_REQUEST['username'];
	$password = sha1 ($_REQUEST['password']);
	$user_id = commitCreateUserAccount ($username, $_REQUEST['realname'], $password);
	if (isset ($_REQUEST['taglist']))
		produceTagsForNewRecord ('user', $_REQUEST['taglist'], $user_id);
	showFuncMessage (__FUNCTION__, 'OK', array ($username));
}

function updateUser ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 6));
	$username = genericAssertion ('username', 'string');
	commitUpdateUserAccount
	(
		genericAssertion ('user_id', 'natural'),
		$username,
		genericAssertion ('realname', 'string0'),
		sha1 (genericAssertion ('password', 'string0'))
	);
	showFuncMessage (__FUNCTION__, 'OK', array ($username));
}

function deleteUser ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 7));
	$user_id = genericAssertion ('id', 'natural');
	$userinfo = spotEntity ('user', $user_id);
	commitDeleteUserAccount ($user_id);
	showFuncMessage (__FUNCTION__, 'OK', array ($userinfo['user_name']));
}

function supplementAttrMap ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 48, 'ERR1' => 154));
	$attr_id = genericAssertion ('attr_id', 'natural');
	if (getAttrType ($attr_id) != 'dict')
		$chapter_id = NULL;
	else
	{
		try
		{
			$chapter_id = genericAssertion ('chapter_no', 'natural');
		}
		catch (InvalidRequestArgException $e)
		{
			showFuncMessage (__FUNCTION__, 'ERR1', array ('chapter not selected'));
			return;
		}
	}
	commitSupplementAttrMap ($attr_id, genericAssertion ('objtype_id', 'natural'), $chapter_id);
	showFuncMessage (__FUNCTION__, 'OK');
}

function clearSticker ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 49));
	$attr_id = genericAssertion ('attr_id', 'natural');
	if (permitted (NULL, NULL, NULL, array (array ('tag' => '$attr_' . $attr_id))))
		commitUpdateAttrValue (getBypassValue(), $attr_id);
	else
	{
		$oldvalues = getAttrValues (getBypassValue());
		showError ('Permission denied, "' . $oldvalues[$attr_id]['name'] . '" left unchanged');
	}
}

// This function accepts rack data returned by amplifyCell(), validates and applies changes
// supplied in $_REQUEST and returns resulting array. Only those changes are examined that
// correspond to current rack ID.
// 1st arg is rackdata, 2nd arg is unchecked state, 3rd arg is checked state.
// If 4th arg is present, object_id fields will be updated accordingly to the new state.
// The function returns TRUE if the DB was successfully changed, FALSE otherwise
function processGridForm (&$rackData, $unchecked_state, $checked_state, $object_id = 0)
{
	global $loclist, $dbxlink;
	$rack_id = $rackData['id'];
	$rack_name = $rackData['name'];
	$rackchanged = FALSE;
	$dbxlink->beginTransaction();
	for ($unit_no = $rackData['height']; $unit_no > 0; $unit_no--)
	{
		for ($locidx = 0; $locidx < 3; $locidx++)
		{
			if ($rackData[$unit_no][$locidx]['enabled'] != TRUE)
				continue;
			// detect a change
			$state = $rackData[$unit_no][$locidx]['state'];
			$newstate = isCheckSet ("atom_${rack_id}_${unit_no}_${locidx}") ? $checked_state : $unchecked_state;
			if ($state == $newstate)
				continue;
			$rackchanged = TRUE;
			// and validate
			$atom = $loclist[$locidx];
			// The only changes allowed are those introduced by checkbox grid.
			if
			(
				!($state == $checked_state && $newstate == $unchecked_state) &&
				!($state == $unchecked_state && $newstate == $checked_state)
			)
			{
				showError ("${rack_name}: Rack ID ${rack_id}, unit ${unit_no}, 'atom ${atom}', cannot change state from '${state}' to '${newstate}'");
				$dbxlink->rollBack();
				return FALSE;
			}
			// This code uses an unconditional DELETE followed by a conditional INSERT
			// rather than ON DUPLICATE KEY UPDATE.
			usePreparedDeleteBlade ('RackSpace', array ('rack_id' => $rack_id, 'unit_no' => $unit_no, 'atom' => $atom));
			if ($newstate != 'F')
				usePreparedInsertBlade ('RackSpace', array ('rack_id' => $rack_id, 'unit_no' => $unit_no, 'atom' => $atom, 'state' => $newstate));
			if ($newstate == 'T' && $object_id != 0)
			{
				// At this point respective row exists in RackSpace and has state set to "T".
				usePreparedUpdateBlade
				(
					'RackSpace',
					array ('object_id' => $object_id),
					array
					(
						'rack_id' => $rack_id,
						'unit_no' => $unit_no,
						'atom' => $atom,
					)
				);
				$rackData[$unit_no][$locidx]['object_id'] = $object_id;
			}
		}
	}
	if ($rackchanged)
	{
		usePreparedDeleteBlade ('RackThumbnail', array ('rack_id' => $rack_id));
		$dbxlink->commit();
		return TRUE;
	}
	$dbxlink->rollBack();
	return FALSE;
}

function updateObjectAllocation ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 63));
	global $remote_username;
	global $op;
	if (!isset ($_REQUEST['got_atoms']))
	{
		unset($_GET['page']);
		unset($_GET['tab']);
		unset($_GET['op']);
		unset($_POST['page']);
		unset($_POST['tab']);
		unset($_POST['op']);
		return buildRedirectURL (NULL, NULL, $_REQUEST);
	}
	$object_id = getBypassValue();
	$object = spotEntity ('object', $object_id);
	$changecnt = 0;
	// Get a list of rack ids that are parents of the object
	$parentRacks = reduceSubarraysToColumn (getParents ($object, 'rack'), 'id');
	$workingRacksData = array();
	foreach (genericAssertion ('rackmulti', 'array0') as $cand_id)
	{
		if (!isset ($workingRacksData[$cand_id]))
		{
			$rackData = spotEntity ('rack', $cand_id);
			amplifyCell ($rackData);
			$workingRacksData[$cand_id] = $rackData;
		}
		else
			$rackData = $workingRacksData[$cand_id];
		$is_ro = ! rackModificationPermitted ($rackData, $op, FALSE);
		// It's zero-U mounted to this rack on the form, but not in the DB.  Mount it.
		if (isset($_REQUEST["zerou_${cand_id}"]) && !in_array($cand_id, $parentRacks))
		{
			if ($is_ro)
				continue;
			$changecnt++;
			commitLinkEntities ('rack', $cand_id, 'object', $object_id);
		}
		// It's not zero-U mounted to this rack on the form, but it is in the DB.  Unmount it.
		if (!isset($_REQUEST["zerou_${cand_id}"]) && in_array($cand_id, $parentRacks))
		{
			if ($is_ro)
				continue;
			$changecnt++;
			commitUnlinkEntities ('rack', $cand_id, 'object', $object_id);
		}
	}

	foreach (array_keys ($workingRacksData) as $key)
		applyObjectMountMask ($workingRacksData[$key], $object_id);

	$oldMolecule = getMoleculeForObject ($object_id);
	foreach ($workingRacksData as $rack_id => $rackData)
	{
		$is_ro = ! rackModificationPermitted ($rackData, $op, FALSE);
		if ($is_ro || !processGridForm ($rackData, 'F', 'T', $object_id))
			continue;
		$changecnt++;
		// Reload our working copy after form processing.
		$rackData = spotEntity ('rack', $cand_id);
		amplifyCell ($rackData);
		applyObjectMountMask ($rackData, $object_id);
		$workingRacksData[$rack_id] = $rackData;
	}
	if ($changecnt)
	{
		// Log a record.
		$newMolecule = getMoleculeForObject ($object_id);
		usePreparedInsertBlade
		(
			'MountOperation',
			array
			(
				'object_id' => $object_id,
				'old_molecule_id' => count ($oldMolecule) ? createMolecule ($oldMolecule) : NULL,
				'new_molecule_id' => count ($newMolecule) ? createMolecule ($newMolecule) : NULL,
				'user_name' => $remote_username,
				'comment' => nullIfEmptyStr (genericAssertion ('comment', 'string0')),
			)
		);
	}
	showFuncMessage (__FUNCTION__, 'OK', array ($changecnt));
}

function updateObject ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 51));
	$taglist = genericAssertion ('taglist', 'array0');
	genericAssertion ('num_attrs', 'unsigned');
	genericAssertion ('object_name', 'string0');
	genericAssertion ('object_label', 'string0');
	genericAssertion ('object_asset_no', 'string0');
	genericAssertion ('object_comment', 'string0');
	genericAssertion ('object_serial_no', 'string0');
    genericAssertion ('object_ci_id', 'string0');
	$object_type_id = genericAssertion ('object_type_id', 'natural');
	$object_id = getBypassValue();

	global $dbxlink;
	$dbxlink->beginTransaction();
	commitUpdateObject
	(
		$object_id,
		$_REQUEST['object_name'],
		$_REQUEST['object_label'],
		isCheckSet ('object_has_problems', 'yesno'),
		$_REQUEST['object_asset_no'],
		$_REQUEST['object_serial_no'],
		$_REQUEST['object_ci_id'],
		$_REQUEST['object_comment']
	);
	updateObjectAttributes ($object_id);
	$object = spotEntity ('object', $object_id);
	if ($object_type_id != $object['objtype_id'])
	{
		if (! array_key_exists ($object_type_id, getObjectTypeChangeOptions ($object_id)))
			throw new InvalidRequestArgException ('new type_id', $object_type_id, 'incompatible with requested attribute values');
		usePreparedUpdateBlade ('Object', array ('objtype_id' => $object_type_id), array ('id' => $object_id));
	}
	// Invalidate thumb cache of all racks objects could occupy.
	foreach (getResidentRackIDs ($object_id) as $rack_id)
		usePreparedDeleteBlade ('RackThumbnail', array ('rack_id' => $rack_id));
	$dbxlink->commit();
	rebuildTagChainForEntity ('object', $object_id, buildTagChainFromIds ($taglist), TRUE);
	showFuncMessage (__FUNCTION__, 'OK');
}

// Used when updating an object, location or rack
function updateObjectAttributes ($object_id)
{
	$type_id = getObjectType ($object_id);
	$oldvalues = getAttrValues ($object_id);
	$num_attrs = genericAssertion ('num_attrs', 'unsigned');
	for ($i = 0; $i < $num_attrs; $i++)
	{
		$attr_id = genericAssertion ("${i}_attr_id", 'natural');
		if (! array_key_exists ($attr_id, $oldvalues))
			throw new InvalidRequestArgException ('attr_id', $attr_id, 'malformed request');
		$value = genericAssertion ("${i}_value", 'string0');

		// If the object is a rack, certain attributes (height, sort_order) never normally
		// appear in this subset of the request arguments as they are processed elsewhere.
		if ($type_id == 1560 && ($attr_id == 27 || $attr_id == 29))
			throw new RackTablesError ('unexpected special meaning attr_id', RackTablesError::INTERNAL);

		// Delete attribute and move on, when the field is empty or if the field
		// type is a dictionary and it is the "--NOT SET--" value of 0.
		if ($value == '' || ($oldvalues[$attr_id]['type'] == 'dict' && $value == 0))
		{
			if (permitted (NULL, NULL, NULL, array (array ('tag' => '$attr_' . $attr_id))))
				commitUpdateAttrValue ($object_id, $attr_id);
			else
				showError ('Permission denied, "' . $oldvalues[$attr_id]['name'] . '" left unchanged');
			continue;
		}

		try
		{
			switch ($oldvalues[$attr_id]['type'])
			{
			case 'uint':
				genericAssertion ("${i}_value", 'unsigned');
				$oldvalue = $oldvalues[$attr_id]['value'];
				break;
			case 'float':
				genericAssertion ("${i}_value", 'decimal0');
				$oldvalue = $oldvalues[$attr_id]['value'];
				break;
			case 'string':
				// already checked above
				$oldvalue = $oldvalues[$attr_id]['value'];
				break;
			case 'date':
				$value = timestampFromDatetimestr (genericAssertion ("${i}_value", 'datetime'));
				$oldvalue = $oldvalues[$attr_id]['value'];
				break;
			case 'dict':
				// Not 'unsigned' as 0 is handled above.
				genericAssertion ("${i}_value", 'natural');
				$oldvalue = $oldvalues[$attr_id]['key'];
				break;
			default:
				throw new RackTablesError ('Unexpected attribute type', RackTablesError::INTERNAL);
			}
		}
		catch (InvalidRequestArgException $irae)
		{
			// The submitted form may include a number of changes hence the error message
			// must use same term as the form label (before the conversion it is the input name).
			throw new InvalidRequestArgException ($oldvalues[$attr_id]['name'], $irae->getValue(), $irae->getReason());
		}
		if ($value === $oldvalue) // ('' == 0), but ('' !== 0)
			continue;
		if (permitted (NULL, NULL, NULL, array (array ('tag' => '$attr_' . $attr_id))))
			commitUpdateAttrValue ($object_id, $attr_id, $value);
		else
			showError ('Permission denied, "' . $oldvalues[$attr_id]['name'] . '" left unchanged');
	}
}

function addMultipleObjects()
{
	$taglist = genericAssertion ('taglist', 'array0');
	$max = genericAssertion ('num_records', 'natural');
	for ($i = 0; $i < $max; $i++)
	{
		$tid = genericAssertion ("${i}_object_type_id", 'unsigned'); // 0 by default in the SELECT
		assertStringArg ("${i}_object_name", TRUE);
		assertStringArg ("${i}_object_label", TRUE);
		assertStringArg ("${i}_object_asset_no", TRUE);
		assertStringArg ("${i}_object_serial_no", TRUE);
		assertStringArg ("${i}_object_ci_id", TRUE);
		$name = $_REQUEST["${i}_object_name"];

		if ($tid == 0)
			continue; // Just skip on intact SELECT.
		try
		{
			$object_id = commitAddObject
			(
				$name,
				$_REQUEST["${i}_object_label"],
				$tid,
				$_REQUEST["${i}_object_asset_no"],
				$_REQUEST["${i}_object_serial_no"],
				$_REQUEST["${i}_object_ci_id"],
				$taglist
			);
			showSuccess ('added object ' . mkCellA (spotEntity ('object', $object_id)));
		}
		catch (RTDatabaseError $e)
		{
			showError ("Error creating object '$name': " . $e->getMessage());
		}
	}
}

function addLotOfObjects()
{
	$taglist = genericAssertion ('taglist', 'array0');
	assertStringArg ('namelist', TRUE);
	$global_type_id = genericAssertion ('global_type_id', 'unsigned'); // 0 by default in the SELECT
	if ($global_type_id == 0 || $_REQUEST['namelist'] == '')
	{
		showError ('Incomplete form has been ignored. Cheers.');
		return;
	}
	foreach (textareaCooked ($_REQUEST['namelist']) as $name)
		try
		{
			$object_id = commitAddObject ($name, NULL, $global_type_id, '', $taglist);
			showSuccess ('added object ' . mkCellA (spotEntity ('object', $object_id)));
		}
		catch (RackTablesError $e)
		{
			showError ("Failed to add object '$name': " . $e->getMessage());
		}
}

function linkObjects ()
{
	commitLinkEntities
	(
		genericAssertion ('parent_entity_type', 'string'),
		genericAssertion ('parent_entity_id', 'natural'),
		genericAssertion ('child_entity_type', 'string'),
		genericAssertion ('child_entity_id', 'natural')
	);
	showSuccess ('Container set successfully');
}

function deleteObject ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 7));
	$oinfo = spotEntity ('object', genericAssertion ('object_id', 'natural'));

	$racklist = getResidentRackIDs ($oinfo['id']);
	commitDeleteObject ($oinfo['id']);
	foreach ($racklist as $rack_id)
		usePreparedDeleteBlade ('RackThumbnail', array ('rack_id' => $rack_id));
	showFuncMessage (__FUNCTION__, 'OK', array ($oinfo['dname']));
	return buildRedirectURL ('depot', 'addmore');
}

function resetObject ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 57));
	$racklist = getResidentRackIDs (getBypassValue());
	commitResetObject (getBypassValue());
	foreach ($racklist as $rack_id)
		usePreparedDeleteBlade ('RackThumbnail', array ('rack_id' => $rack_id));
	showFuncMessage (__FUNCTION__, 'OK');
}

function updateUI ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 43));
	$num_vars = genericAssertion ('num_vars', 'natural');
	try
	{
		for ($i = 0; $i < $num_vars; $i++)
		{
			assertStringArg ("${i}_varvalue", TRUE);
			$varname = genericAssertion ("${i}_varname", 'string');
			$varvalue = $_REQUEST["${i}_varvalue"];
			// If form value = value in DB, don't bother updating DB.
			if (isConfigVarChanged ($varname, $varvalue))
				setConfigVar ($varname, $varvalue);
		}
	}
	catch (InvalidArgException $iae)
	{
		throw $iae->newIRAE();
	}
	showFuncMessage (__FUNCTION__, 'OK');
}

function saveMyPreferences ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 51));
	$num_vars = genericAssertion ('num_vars', 'natural');

	for ($i = 0; $i < $num_vars; $i++)
	{
		assertStringArg ("${i}_varvalue", TRUE);
		$varname = genericAssertion ("${i}_varname", 'string');
		$varvalue = $_REQUEST["${i}_varvalue"];

		// If form value = value in DB, don't bother updating DB
		if (!isConfigVarChanged($varname, $varvalue))
			continue;
		try
		{
			setUserConfigVar ($varname, $varvalue);
		}
		catch (InvalidArgException $iae)
		{
			throw $iae->newIRAE();
		}
	}
	showFuncMessage (__FUNCTION__, 'OK');
}

function resetMyPreference ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 51));
	try
	{
		resetUserConfigVar (genericAssertion ('varname', 'string'));
	}
	catch (InvalidArgException $iae)
	{
		throw $iae->newIRAE();
	}
	showFuncMessage (__FUNCTION__, 'OK');
}

function resetUIConfig()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 57));
	foreach (getConfigDefaults() as $name => $value)
		setConfigVar ($name, $value);
	callHook ('resetUIConfig_hook');
	showFuncMessage (__FUNCTION__, 'OK');
}

// Add single record.
function addRealServer ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 48));
	addRStoRSPool
	(
		getBypassValue(),
		genericAssertion ('rsip', 'inet'),
		genericAssertion ('rsport', 'string0'),
		isCheckSet ('inservice', 'yesno'),
		genericAssertion ('rsconfig', 'string0'),
		genericAssertion ('comment', 'string0')
	);
	showFuncMessage (__FUNCTION__, 'OK');
}

// Parse textarea submitted and try adding a real server for each line.
function addRealServers ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 37, 'ERR1' => 131));
	$format = genericAssertion ('format', 'string');
	$ngood = 0;
	// Keep in mind that the text will have HTML entities (namely '>') escaped.
	foreach (explode ("\n", dos2unix (genericAssertion ('rawtext', 'string'))) as $line)
	{
		if ($line == '')
			continue;
		$match = array ();
		switch ($format)
		{
			case 'ipvs_2': // address and port only
				if (!preg_match ('/^  -> ([0-9\.]+):([0-9]+) /', $line, $match))
					if (!preg_match ('/^  -> \[([0-9a-fA-F:]+)\]:([0-9]+) /', $line, $match))
						continue 2;
				addRStoRSPool (getBypassValue(), ip_parse ($match[1]), $match[2], getConfigVar ('DEFAULT_IPV4_RS_INSERVICE'), '');
				break;
			case 'ipvs_3': // address, port and weight
				if (!preg_match ('/^  -> ([0-9\.]+):([0-9]+) +[a-zA-Z]+ +([0-9]+) /', $line, $match))
					if (!preg_match ('/^  -> \[([0-9a-fA-F:]+)\]:([0-9]+) +[a-zA-Z]+ +([0-9]+) /', $line, $match))
						continue 2;
				addRStoRSPool (getBypassValue(), ip_parse ($match[1]), $match[2], getConfigVar ('DEFAULT_IPV4_RS_INSERVICE'), 'weight ' . $match[3]);
				break;
			case 'ssv_2': // IP address and port
				if (!preg_match ('/^([0-9\.a-fA-F:]+) ([0-9]+)$/', $line, $match))
					continue 2;
				addRStoRSPool (getBypassValue(), ip_parse ($match[1]), $match[2], getConfigVar ('DEFAULT_IPV4_RS_INSERVICE'), '');
				break;
			case 'ssv_1': // IP address
				if (! $ip_bin = ip_checkparse ($line))
					continue 2;
				addRStoRSPool (getBypassValue(), $ip_bin, 0, getConfigVar ('DEFAULT_IPV4_RS_INSERVICE'), '');
				break;
			default:
				showFuncMessage (__FUNCTION__, 'ERR1');
				return;
		}
		$ngood++;
	}
	showFuncMessage (__FUNCTION__, 'OK', array ($ngood));
}

function addVService ()
{
	assertStringArg ('name', TRUE);
	$proto = genericAssertion ('proto', 'enum/ipproto');
	usePreparedInsertBlade
	(
		'IPv4VS',
		array
		(
			'vip' => genericAssertion ('vip', 'inet'),
			'vport' => $proto == 'MARK' ? NULL : genericAssertion ('vport', 'natural'),
			'proto' => $proto,
			'name' => nullIfEmptyStr ($_REQUEST['name']),
			'vsconfig' => nullIfEmptyStr (genericAssertion ('vsconfig', 'string0')),
			'rsconfig' => nullIfEmptyStr (genericAssertion ('rsconfig', 'string0')),
		)
	);
	$vs_id = lastInsertID();
	lastCreated ('ipv4vs', $vs_id);
	if (isset ($_REQUEST['taglist']))
		produceTagsForNewRecord ('ipv4vs', genericAssertion ('taglist', 'array0'), $vs_id);
	$vsinfo = spotEntity ('ipv4vs', $vs_id);
	showSuccess (mkCellA ($vsinfo) . ' created successfully');
}

function addVSG ()
{
	$name = assertStringArg ('name');
	usePreparedInsertBlade ('VS', array ('name' => $name));
	$vs_id = lastInsertID();
	lastCreated ('ipvs', $vs_id);
	if (isset ($_REQUEST['taglist']))
		produceTagsForNewRecord ('ipvs', $_REQUEST['taglist'], $vs_id);
	$vsinfo = spotEntity ('ipvs', $vs_id);
	showSuccess (mkCellA ($vsinfo) . ' created successfully');
}

function deleteVService ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 49));
	$vsinfo = spotEntity ('ipv4vs', genericAssertion ('vs_id', 'natural'));
	if ($vsinfo['refcnt'] != 0)
	{
		showError ("Could not delete linked virtual service");
		return;
	}
	commitDeleteVS ($vsinfo['id']);
	showFuncMessage (__FUNCTION__, 'OK');
	return buildRedirectURL ('ipv4slb', 'default');
}

function deleteVS()
{
	$vsinfo = spotEntity ('ipvs', genericAssertion ('vs_id', 'natural'));
	if (count (getTriplets ($vsinfo)) != 0)
	{
		showError ("Could not delete linked virtual service group");
		return;
	}
	commitDeleteVSG ($vsinfo['id']);
	showSuccess (formatEntityName ($vsinfo) . ' deleted');
	return buildRedirectURL ('ipv4slb', 'vs');
}

function updateSLBDefConfig ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 43));
	commitUpdateSLBDefConf
	(
		array
		(
			'vs' => genericAssertion ('vsconfig', 'string0'),
			'rs' => genericAssertion ('rsconfig', 'string0'),
		)
	);
	showFuncMessage (__FUNCTION__, 'OK');
}

function updateRealServer ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 51));
	commitUpdateRS (
		genericAssertion ('rs_id', 'natural'),
		genericAssertion ('rsip', 'inet'),
		genericAssertion ('rsport', 'string0'),
		isCheckSet ('inservice', 'yesno'),
		genericAssertion ('rsconfig', 'string0'),
		genericAssertion ('comment', 'string0')
	);
	showFuncMessage (__FUNCTION__, 'OK');
}

function updateVService ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 51));
	$vs_id = getBypassValue();
	$taglist = genericAssertion ('taglist', 'array0');
	$proto = genericAssertion ('proto', 'enum/ipproto');
	genericAssertion ('vport', $proto == 'MARK' ? 'string0' : 'natural');
	assertStringArg ('name', TRUE);
	commitUpdateVS (
		$vs_id,
		genericAssertion ('vip', 'inet'),
		$_REQUEST['vport'],
		$proto,
		$_REQUEST['name'],
		genericAssertion ('vsconfig', 'string0'),
		genericAssertion ('rsconfig', 'string0')
	);
	rebuildTagChainForEntity ('ipvs', $vs_id, buildTagChainFromIds ($taglist), TRUE);
	showFuncMessage (__FUNCTION__, 'OK');
}

function updateVS ()
{
	$taglist = genericAssertion ('taglist', 'array0');
	$vs_id = genericAssertion ('vs_id', 'natural');
	$name = assertStringArg ('name');
	$vsconfig = nullIfEmptyStr (assertStringArg ('vsconfig', TRUE));
	$rsconfig = nullIfEmptyStr (assertStringArg ('rsconfig', TRUE));

	usePreparedUpdateBlade ('VS', array ('name' => $name, 'vsconfig' => $vsconfig, 'rsconfig' => $rsconfig), array ('id' => $vs_id));
	rebuildTagChainForEntity ('ipvs', $vs_id, buildTagChainFromIds ($taglist), TRUE);
	showSuccess ("Service updated successfully");
}

function addIPToVS()
{
	$ip_bin = assertIPArg ('ip');
	$vsinfo = spotEntity ('ipvs', getBypassValue());
	amplifyCell ($vsinfo);
	$row = array ('vs_id' => $vsinfo['id'], 'vip' => $ip_bin, 'vsconfig' => NULL, 'rsconfig' => NULL);
	if ($vip = isVIPEnabled ($row, $vsinfo['vips']))
	{
		showError ("Service already contains IP " . formatVSIP ($vip));
		return;
	}
	usePreparedInsertBlade ('VSIPs', $row);
	showSuccess ("IP addded");
}

function addPortToVS()
{
	$proto = genericAssertion ('proto', 'enum/ipproto');
	$vport = genericAssertion ('port', 'unsigned');
	if ($proto == 'MARK')
	{
		if ($vport > 0xFFFFFFFF)
		{
			showError ("fwmark value is too large");
			return;
		}
	}
	else
		if ($vport == 0 || $vport >= 0xFFFF)
		{
			showError ("Invalid $proto port value");
			return;
		}

	$vsinfo = spotEntity ('ipvs', getBypassValue());
	amplifyCell ($vsinfo);
	$row = array ('vs_id' => $vsinfo['id'], 'proto' => $proto, 'vport' => $vport, 'vsconfig' => NULL, 'rsconfig' => NULL);
	if ($port = isPortEnabled ($row, $vsinfo['ports']))
	{
		showError ("Service already contains port " . $port['proto'] . ' ' . $port['vport']);
		return;
	}
	usePreparedInsertBlade ('VSPorts', $row);
	showSuccess ("port addded");
}

function updateIPInVS()
{
	$vs_id = getBypassValue();
	$ip_bin = assertIPArg ('ip');
	$vsconfig = nullIfEmptyStr (assertStringArg ('vsconfig', TRUE));
	$rsconfig = nullIfEmptyStr (assertStringArg ('rsconfig', TRUE));
	if (usePreparedUpdateBlade ('VSIPs', array ('vsconfig' => $vsconfig, 'rsconfig' => $rsconfig), array ('vs_id' => $vs_id, 'vip' => $ip_bin)))
		showSuccess ("IP configuration updated");
	else
		showNotice ("Nothing changed");
}

function updatePortInVS()
{
	$vs_id = getBypassValue();
	$proto = genericAssertion ('proto', 'enum/ipproto');
	$vport = genericAssertion ('port', 'unsigned');
	$vsconfig = nullIfEmptyStr (assertStringArg ('vsconfig', TRUE));
	$rsconfig = nullIfEmptyStr (assertStringArg ('rsconfig', TRUE));
	if (usePreparedUpdateBlade ('VSPorts', array ('vsconfig' => $vsconfig, 'rsconfig' => $rsconfig), array ('vs_id' => $vs_id, 'proto' => $proto, 'vport' => $vport)))
		showSuccess ("Port configuration updated");
	else
		showNotice ("Nothing changed");
}

function removeIPFromVS()
{
	$vip = array ('vip' => assertIPArg ('ip'));
	$vsinfo = spotEntity ('ipvs', getBypassValue());
	amplifyCell ($vsinfo);
	$used = 0;
	foreach (getTriplets ($vsinfo) as $triplet)
		if (isVIPEnabled ($vip, $triplet['vips']))
			$used++;
	if (usePreparedDeleteBlade ('VSIPs', array ('vs_id' => $vsinfo['id']) + $vip))
		showSuccess ("IP removed" . ($used ? ", it was binded with $used SLBs" : ''));
	else
		showNotice ("Nothing changed");
}

function removePortFromVS()
{
	$port = array ('proto' => genericAssertion ('proto', 'enum/ipproto'), 'vport' => genericAssertion ('port', 'unsigned'));
	$vsinfo = spotEntity ('ipvs', getBypassValue());
	amplifyCell ($vsinfo);
	$used = 0;
	foreach (getTriplets ($vsinfo) as $triplet)
		if (isPortEnabled ($port, $triplet['ports']))
			$used++;
	if (usePreparedDeleteBlade ('VSPorts', array ('vs_id' => $vsinfo['id']) + $port))
		showSuccess ("Port removed" . ($used ? ", it was binded with $used SLBs" : ''));
	else
		showNotice ("Nothing changed");
}

function updateTripletConfig()
{
	global $op;
	$key_fields = array
	(
		'object_id' => genericAssertion ('object_id', 'natural'),
		'vs_id' => genericAssertion ('vs_id', 'natural'),
		'rspool_id' => genericAssertion ('rspool_id', 'natural'),
	);
	$config_fields = array
	(
		'vsconfig' => nullIfEmptyStr (assertStringArg ('vsconfig', TRUE)),
		'rsconfig' => nullIfEmptyStr (assertStringArg ('rsconfig', TRUE)),
	);

	$vsinfo = spotEntity ('ipvs', $key_fields['vs_id']);
	amplifyCell ($vsinfo);
	$found = FALSE;

	if ($op == 'updPort')
	{
		$table = 'VSEnabledPorts';
		$proto = assertStringArg ('proto');
		$vport = genericAssertion ('port', 'unsigned');
		$key_fields['proto'] = $proto;
		$key_fields['vport'] = $vport;
		$key = "Port $proto-$vport";
		// check if such port exists in VS
		foreach ($vsinfo['ports'] as $vs_port)
			if ($vs_port['proto'] == $proto && $vs_port['vport'] == $vport)
			{
				$found = TRUE;
				break;
			}
	}
	else
	{
		$table = 'VSEnabledIPs';
		$vip = assertIPArg ('vip');
		$config_fields['prio'] = nullIfEmptyStr (assertStringArg ('prio', TRUE));
		$key_fields['vip'] = $vip;
		$key = "IP " . ip_format ($vip);
		// check if such VIP exists in VS
		foreach ($vsinfo['vips'] as $vs_vip)
			if ($vs_vip['vip'] === $vip)
			{
				$found = TRUE;
				break;
			}
	}
	if (! $found)
	{
		showError ("$key not found in VS");
		return;
	}

	$nchanged = 0;
	if (! isCheckSet ('enabled'))
	{
		if ($nchanged += usePreparedDeleteBlade ($table, $key_fields))
		{
			showSuccess ("$key disabled");
			return;
		}
	}
	else
	{
		global $dbxlink;
		$dbxlink->beginTransaction();
		$q = "SELECT * FROM $table WHERE";
		$sep = '';
		$params = array();
		foreach ($key_fields as $field => $value)
		{
			$q .= " $sep $field = ?";
			$params[] = $value;
			$sep = 'AND';
		}
		$result = usePreparedSelectBlade ("$q FOR UPDATE", $params);
		$row = $result->fetch (PDO::FETCH_ASSOC);
		unset ($result);
		if ($row)
		{
			if ($nchanged += usePreparedUpdateBlade ($table, $config_fields, $key_fields))
				showSuccess ("$key config updated");
		}
		else
		{
			if (
				$nchanged += ($table == 'VSEnabledIPs' ?
					addSLBIPLink ($key_fields + $config_fields) :
					addSLBPortLink ($key_fields + $config_fields)
				)
			)
				showSuccess ("$key enabled");
		}
		$dbxlink->commit();
	}
	if (! $nchanged)
		showNotice ("No changes made");
}

function removeTriplet()
{
	$key_fields = array
	(
		'object_id' => genericAssertion ('object_id', 'natural'),
		'vs_id' => genericAssertion ('vs_id', 'natural'),
		'rspool_id' => genericAssertion ('rspool_id', 'natural'),
	);

	global $dbxlink;
	$dbxlink->beginTransaction();
	usePreparedDeleteBlade ('VSEnabledIPs', $key_fields);
	usePreparedDeleteBlade ('VSEnabledPorts', $key_fields);
	$dbxlink->commit();
	showSuccess ('Triplet deleted');
}

function createTriplet()
{
	global $dbxlink;
	$object_id = genericAssertion ('object_id', 'natural');
	$vs_id = genericAssertion ('vs_id', 'natural');
	$rspool_id = genericAssertion ('rspool_id', 'natural');
	$vips = genericAssertion ('enabled_vips', 'array0');
	$ports = genericAssertion ('enabled_ports', 'array0');

	$vsinfo = spotEntity ('ipvs', $vs_id);
	amplifyCell ($vsinfo);
	try
	{
		$dbxlink->beginTransaction();
		foreach ($vsinfo['vips'] as $vip)
			if (in_array (ip_format ($vip['vip']), $vips))
				addSLBIPLink (array ('object_id' => $object_id, 'vs_id' => $vs_id, 'rspool_id' => $rspool_id, 'vip' => $vip['vip']));
		foreach ($vsinfo['ports'] as $port)
			if (in_array($port['proto'] . '-' . $port['vport'], $ports))
				addSLBPortLink (array ('object_id' => $object_id, 'vs_id' => $vs_id, 'rspool_id' => $rspool_id, 'proto' => $port['proto'], 'vport' => $port['vport']));
		$dbxlink->commit();
	}
	catch (RTDatabaseError $e)
	{
		$dbxlink->rollBack();
		throw $e;
	}
	showSuccess ("SLB triplet created");
}

function addLoadBalancer ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 48));
	assertStringArg ('prio', TRUE);

	addLBtoRSPool (
		genericAssertion ('pool_id', 'natural'),
		genericAssertion ('object_id', 'natural'),
		genericAssertion ('vs_id', 'natural'),
		genericAssertion ('vsconfig', 'string0'),
		genericAssertion ('rsconfig', 'string0'),
		$_REQUEST['prio']
	);
	showFuncMessage (__FUNCTION__, 'OK');
}

function addRSPool ()
{
	assertStringArg ('name');
	$pool_id = commitCreateRSPool
	(
		$_REQUEST['name'],
		genericAssertion ('vsconfig', 'string0'),
		genericAssertion ('rsconfig', 'string0'),
		isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array()
	);
	showSuccess ('RS pool ' . mkA ($_REQUEST['name'], 'ipv4rspool', $pool_id) . ' created successfully');
}

function deleteRSPool ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 49));
	$poolinfo = spotEntity ('ipv4rspool', genericAssertion ('pool_id', 'natural'));
	if ($poolinfo['refcnt'] != 0)
	{
		showError ("Could not delete linked RS pool");
		return;
	}
	commitDeleteRSPool ($poolinfo['id']);
	showFuncMessage (__FUNCTION__, 'OK');
	return buildRedirectURL ('ipv4slb', 'rspools');
}

function importPTRData ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 26, 'ERR' => 141));
	$net = spotEntity ('ipv4net', getBypassValue());
	$addrcount = genericAssertion ('addrcount', 'natural');
	$nbad = $ngood = 0;
	for ($i = 1; $i <= $addrcount; $i++)
	{
		$inputname = "import_${i}";
		if (! isCheckSet ($inputname))
			continue;
		$ip_bin = assertIPv4Arg ("addr_${i}");
		assertStringArg ("descr_${i}", TRUE);
		assertStringArg ("rsvd_${i}");
		// Non-existent addresses will not have this argument set in request.
		$rsvd = 'no';
		if ($_REQUEST["rsvd_${i}"] == 'yes')
			$rsvd = 'yes';
		try
		{
			if (! ip_in_range ($ip_bin, $net))
				throw new InvalidArgException ('ip_bin', $ip_bin);
			updateAddress ($ip_bin, $_REQUEST["descr_${i}"], $rsvd);
			$ngood++;
		}
		catch (RackTablesError $e)
		{
			$nbad++;
		}
	}
	if (!$nbad)
		showFuncMessage (__FUNCTION__, 'OK', array ($ngood));
	else
		showFuncMessage (__FUNCTION__, 'ERR', array ($nbad, $ngood));
}

function generateAutoPorts ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 21));
	$object = spotEntity ('object', getBypassValue());
	executeAutoPorts ($object['id']);
	showFuncMessage (__FUNCTION__, 'OK');
	return buildRedirectURL (NULL, 'ports');
}

function updateTag ()
{
	try
	{
		commitUpdateTag
		(
			genericAssertion ('tag_id', 'natural'),
			genericAssertion ('tag_name', 'tag'),
			genericAssertion ('parent_id', 'unsigned'),
			genericAssertion ('is_assignable', 'enum/yesno'),
			genericAssertion ('color', 'htmlcolor0')
		);
	}
	catch (InvalidArgException $iae)
	{
		throw $iae->newIRAE();
	}
	showSuccess ('Tag updated successfully');
}

function saveEntityTags ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 43));
	$realm = etypeByPageno();
	$entity_id = getBypassValue();
	$taglist = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
	rebuildTagChainForEntity ($realm, $entity_id, buildTagChainFromIds ($taglist), TRUE);
	showFuncMessage (__FUNCTION__, 'OK');
}

function rollTags ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 67, 'ERR' => 149));
	if (genericAssertion ('sum', 'string0') != genericAssertion ('realsum', 'natural'))
	{
		showFuncMessage (__FUNCTION__, 'ERR');
		return;
	}
	// Even if the user requested an empty tag list, don't bail out, but process existing
	// tag chains with "zero" extra. This will make sure that the stuff processed will
	// have its chains refined to "normal" form.
	$extratags = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
	$n_ok = 0;
	// Minimizing the extra chain early, so that tag rebuilder doesn't have to
	// filter out the same tag again and again. It will have own noise to cancel.
	$extrachain = getExplicitTagsOnly (buildTagChainFromIds ($extratags));
	foreach (listCells ('rack', getBypassValue()) as $rack)
	{
		if (rebuildTagChainForEntity ('rack', $rack['id'], $extrachain))
			$n_ok++;
		amplifyCell ($rack);
		foreach ($rack['mountedObjects'] as $object_id)
			if (rebuildTagChainForEntity ('object', $object_id, $extrachain))
				$n_ok++;
	}
	showFuncMessage (__FUNCTION__, 'OK', array ($n_ok));
}

function changeMyPassword ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 51, 'ERR1' => 150, 'ERR2' => 151, 'ERR3' => 152));
	global $remote_username, $user_auth_src;
	if ($user_auth_src != 'database')
	{
		showFuncMessage (__FUNCTION__, 'ERR1');
		return;
	}
	assertStringArg ('oldpassword');
	assertStringArg ('newpassword1');
	assertStringArg ('newpassword2');
	$remote_userid = getUserIDByUsername ($remote_username);
	$userinfo = spotEntity ('user', $remote_userid);
	if ($userinfo['user_password_hash'] != sha1 ($_REQUEST['oldpassword']))
	{
		showFuncMessage (__FUNCTION__, 'ERR2');
		return;
	}
	if ($_REQUEST['newpassword1'] != $_REQUEST['newpassword2'])
	{
		showFuncMessage (__FUNCTION__, 'ERR3');
		return;
	}
	commitUpdateUserAccount ($remote_userid, $userinfo['user_name'], $userinfo['user_realname'], sha1 ($_REQUEST['newpassword1']));
	showFuncMessage (__FUNCTION__, 'OK');
}

function saveRackCode ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 43, 'ERR1' => 154));
	assertStringArg ('rackcode');
	// For the test to succeed, unescape LFs, strip CRs.
	$newcode = dos2unix ($_REQUEST['rackcode']);
	$parseTree = getRackCode ($newcode);
	if ($parseTree['result'] != 'ACK')
	{
		showFuncMessage (__FUNCTION__, 'ERR1', array ($parseTree['load']));
		return;
	}
	saveScript ('RackCode', $newcode);
	saveScript ('RackCodeCache', base64_encode (serialize ($parseTree)));
	showFuncMessage (__FUNCTION__, 'OK');
}

function submitSLBConfig ()
{
	showNotice ("You should redefine submitSLBConfig ophandler in your local extension to install SLB config");
}

function addLocation ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 5));
	assertStringArg ('name');

	$location_id = commitAddObject ($_REQUEST['name'], NULL, 1562, NULL);
	if (0 != $parent_id = genericAssertion ('parent_id', 'unsigned'))
		commitLinkEntities ('location', $parent_id, 'location', $location_id);
	showSuccess ('added location ' . mkA ($_REQUEST['name'], 'location', $location_id));
}

// This function is used by two forms:
//  - renderEditLocationForm - all attributes may be modified
//  - renderRackspaceLocationEditor - only the name and parent may be modified
function updateLocation ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 6));
	global $pageno;
	$location_id = genericAssertion ('location_id', 'natural');
	$parent_id = genericAssertion ('parent_id', 'unsigned');
	assertStringArg ('name');

	if ($pageno == 'location')
	{
		$taglist = genericAssertion ('taglist', 'array0');
		$has_problems = isCheckSet ('has_problems', 'yesno');
		assertStringArg ('comment', TRUE);
		commitUpdateObject ($location_id, $_REQUEST['name'], NULL, $has_problems, NULL, $_REQUEST['comment']);
		updateObjectAttributes ($location_id);
		rebuildTagChainForEntity ('location', $location_id, buildTagChainFromIds ($taglist), TRUE);
	}
	else
		commitRenameObject ($location_id, $_REQUEST['name']);

	$locationData = spotEntity ('location', $location_id);

	// parent_id was submitted, but no link exists - create it
	if ($parent_id > 0 && !$locationData['parent_id'])
		commitLinkEntities ('location', $parent_id, 'location', $location_id);

	// parent_id was submitted, but it doesn't match the existing link - update it
	if ($parent_id > 0 && $parent_id != $locationData['parent_id'])
		commitUpdateEntityLink
		(
			'location', $locationData['parent_id'], 'location', $location_id,
			'location', $parent_id, 'location', $location_id
		);

	// no parent_id was submitted, but a link exists - delete it
	if ($parent_id == 0 && $locationData['parent_id'])
		commitUnlinkEntities ('location', $locationData['parent_id'], 'location', $location_id);

	showFuncMessage (__FUNCTION__, 'OK', array ($_REQUEST['name']));
}

function deleteLocation ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 7, 'ERR1' => 206));
	$location_id = genericAssertion ('location_id', 'natural');
	$locationData = spotEntity ('location', $location_id);
	amplifyCell ($locationData);
	if (count ($locationData['locations']) || count ($locationData['rows']))
	{
		showFuncMessage (__FUNCTION__, 'ERR1', array ($locationData['name']));
		return;
	}
	releaseFiles ('location', $location_id);
	destroyTagsForEntity ('location', $location_id);
	commitDeleteObject ($location_id);
	showFuncMessage (__FUNCTION__, 'OK', array ($locationData['name']));
	return buildRedirectURL ('rackspace', 'editlocations');
}

function addRow ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 5));
	$location_id = genericAssertion ('location_id', 'unsigned');
	assertStringArg ('name');
	$row_id = commitAddObject ($_REQUEST['name'], NULL, 1561, NULL);
	if ($location_id)
		commitLinkEntities ('location', $location_id, 'row', $row_id);
	showSuccess ('added row ' . mkA ($_REQUEST['name'], 'row', $row_id));
}

// This function is used by two forms:
//  - renderEditRowForm - all attributes may be modified
//  - renderRackspaceRowEditor - only the name and location may be modified
function updateRow ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 6));
	$row_id = genericAssertion ('row_id', 'natural');
	$location_id = genericAssertion ('location_id', 'unsigned');
	assertStringArg ('name');

	commitUpdateObject ($row_id, $_REQUEST['name'], NULL, 'no', NULL, NULL);

	global $pageno;
	if ($pageno == 'row')
		updateObjectAttributes ($row_id);

	$rowData = spotEntity ('row', $row_id);

	// location_id was submitted, but no link exists - create it
	if ($location_id > 0 && !$rowData['location_id'])
		commitLinkEntities ('location', $location_id, 'row', $row_id);

	// location_id was submitted, but it doesn't match the existing link - update it
	if ($location_id > 0 && $location_id != $rowData['location_id'])
		commitUpdateEntityLink
		(
			'location', $rowData['location_id'], 'row', $row_id,
			'location', $location_id, 'row', $row_id
		);

	// no parent_id was submitted, but a link exists - delete it
	if ($location_id == 0 && $rowData['location_id'])
		commitUnlinkEntities ('location', $rowData['location_id'], 'row', $row_id);

	showFuncMessage (__FUNCTION__, 'OK', array ($_REQUEST['name']));
}

function deleteRow ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 7, 'UMOUNT' => 58));
	$row_id = genericAssertion ('row_id', 'natural');
	$rowData = spotEntity ('row', $row_id);
	$unmounted = getRowMountsCount ($row_id);
	commitDeleteRow ($row_id);
	if ($unmounted)
		showFuncMessage (__FUNCTION__, 'UMOUNT', array ($unmounted));
	showFuncMessage (__FUNCTION__, 'OK', array ($rowData['name']));
	return buildRedirectURL ('rackspace', 'editrows');
}

function addRack ()
{
	setFuncMessages (__FUNCTION__, array ('ERR2' => 172));
	$taglist = genericAssertion ('taglist', 'array0');
	$row_id = getBypassValue();

	// The new rack(s) should be placed on the bottom of the list, sort-wise
	$rowInfo = getRowInfo ($row_id);
	$sort_order = $rowInfo['count']+1;

	switch (genericAssertion ('mode', 'string'))
	{
	case 'one':
		assertStringArg ('name');
		$height = genericAssertion ('height1', 'natural');
		assertStringArg ('asset_no', TRUE);
		$rack_id = commitAddObject ($_REQUEST['name'], NULL, 1560, $_REQUEST['asset_no'], $taglist);

		// Set the height and sort order
		commitUpdateAttrValue ($rack_id, 27, $height);
		commitUpdateAttrValue ($rack_id, 29, $sort_order);

		// Link it to the row
		commitLinkEntities ('row', $row_id, 'rack', $rack_id);
		showSuccess ('added ' . mkCellA (spotEntity ('rack', $rack_id)));
		break;
	case 'many':
		$height = genericAssertion ('height2', 'natural');
		assertStringArg ('names', TRUE);
		foreach (textareaCooked ($_REQUEST['names']) as $cname)
		{
			$rack_id = commitAddObject ($cname, NULL, 1560, NULL, $taglist);

			// Set the height and sort order
			commitUpdateAttrValue ($rack_id, 27, $height);
			commitUpdateAttrValue ($rack_id, 29, $sort_order);
			$sort_order++;

			// Link it to the row
			commitLinkEntities ('row', $row_id, 'rack', $rack_id);
			showSuccess ('added ' . mkCellA (spotEntity ('rack', $rack_id)));
		}
		break;
	default:
		showFuncMessage (__FUNCTION__, 'ERR2');
	}
}

function updateRack ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 6));
	$row_id = genericAssertion ('row_id', 'natural');
	assertStringArg ('name');
	$height = genericAssertion ('height', 'natural');
	assertStringArg ('asset_no', TRUE);
	assertStringArg ('serial_no', TRUE);
	assertStringArg ('ci_id', TRUE);
	assertStringArg ('comment', TRUE);
	$taglist = genericAssertion ('taglist', 'array0');
	$rack_id = getBypassValue();
	usePreparedDeleteBlade ('RackThumbnail', array ('rack_id' => $rack_id));
	commitUpdateRack
	(
		$rack_id,
		$row_id,
		$_REQUEST['name'],
		$height,
		isCheckSet ('has_problems', 'yesno'),
		$_REQUEST['asset_no'],
		$_REQUEST['serial_no'],
		$_REQUEST['comment'],
		$_REQUEST['position'],
		$_REQUEST['ci_id']
	);
	updateObjectAttributes ($rack_id);
	rebuildTagChainForEntity ('rack', $rack_id, buildTagChainFromIds ($taglist), TRUE);
	showFuncMessage (__FUNCTION__, 'OK', array ($_REQUEST['name']));
}

function deleteRack ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 7, 'ERR1' => 206));
	$rackData = spotEntity ('rack', getBypassValue());
	amplifyCell ($rackData);
	if (!$rackData['isDeletable'])
	{
		showFuncMessage (__FUNCTION__, 'ERR1');
		return;
	}
	commitDeleteRack ($rackData['id']);
	showFuncMessage (__FUNCTION__, 'OK', array (formatEntityName ($rackData)));
	return buildRedirectURL ('rackspace', 'default');
}

function cleanRack ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 58));
	$rack_id = getBypassValue();
	$unmounted = getRackMountsCount ($rack_id);
	commitCleanRack ($rack_id);
	showFuncMessage (__FUNCTION__, 'OK', array ($unmounted));
}

function updateRackDesign ()
{
	$rackData = spotEntity ('rack', getBypassValue());
	amplifyCell ($rackData);
	applyRackDesignMask($rackData);
	if (processGridForm ($rackData, 'A', 'F'))
		showSuccess ("Saved successfully");
	else
		showNotice ("Nothing saved");
}

function updateRackProblems ()
{
	$rackData = spotEntity ('rack', getBypassValue());
	amplifyCell ($rackData);
	applyRackProblemMask($rackData);
	if (processGridForm ($rackData, 'F', 'U'))
		showSuccess ("Saved successfully");
	else
		showNotice ("Nothing saved");
}

function querySNMPData ()
{
	$ver = genericAssertion ('ver', 'natural');
	$snmpsetup = array ();
	switch ($ver)
	{
	case 1:
	case 2:
		genericAssertion ('community', 'string');
		$snmpsetup['community'] = $_REQUEST['community'];
		break;
	case 3:
		assertStringArg ('sec_name');
		assertStringArg ('sec_level');
		assertStringArg ('auth_protocol');
		assertStringArg ('auth_passphrase', TRUE);
		assertStringArg ('priv_protocol');
		assertStringArg ('priv_passphrase', TRUE);

		$snmpsetup['sec_name'] = $_REQUEST['sec_name'];
		$snmpsetup['sec_level'] = $_REQUEST['sec_level'];
		$snmpsetup['auth_protocol'] = $_REQUEST['auth_protocol'];
		$snmpsetup['auth_passphrase'] = $_REQUEST['auth_passphrase'];
		$snmpsetup['priv_protocol'] = $_REQUEST['priv_protocol'];
		$snmpsetup['priv_passphrase'] = $_REQUEST['priv_passphrase'];
		break;
	default:
		throw new InvalidRequestArgException ('ver', $ver);
	}
	$snmpsetup['version'] = $ver;
	doSNMPmining (getBypassValue(), $snmpsetup); // shows message by itself
}

// File-related functions
function addFileWithoutLink ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 5, 'ERR1' => 207));
	assertStringArg ('comment', TRUE);

	// Make sure the file can be uploaded
	if (get_cfg_var('file_uploads') != 1)
		throw new RackTablesError ('file uploads not allowed, change "file_uploads" parameter in php.ini', RackTablesError::MISCONFIGURED);

	// Exit if the upload failed
	if ($_FILES['file']['error'])
	{
		showFuncMessage (__FUNCTION__, 'ERR1', array ($_FILES['file']['error']));
		return;
	}
	if (FALSE === $fp = fopen($_FILES['file']['tmp_name'], 'rb'))
	{
		showFuncMessage (__FUNCTION__, 'ERR1', array ('failed to access the temporary file'));
		return;
	}

	$file_id = commitAddFile ($_FILES['file']['name'], $_FILES['file']['type'], $fp, genericAssertion ('comment', 'string0'));
	if (isset ($_REQUEST['taglist']))
		produceTagsForNewRecord ('file', $_REQUEST['taglist'], $file_id);
	showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($_FILES['file']['name'])));
}

function addFileToEntity ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 5, 'ERR1' => 207));
	$realm = etypeByPageno();
	assertStringArg ('comment', TRUE);

	// Make sure the file can be uploaded
	if (get_cfg_var('file_uploads') != 1)
		throw new RackTablesError ('file uploads not allowed, change "file_uploads" parameter in php.ini', RackTablesError::MISCONFIGURED);

	// Exit if the upload failed
	if ($_FILES['file']['error'])
	{
		showFuncMessage (__FUNCTION__, 'ERR1', array ($_FILES['file']['error']));
		return;
	}
	if (FALSE === $fp = fopen($_FILES['file']['tmp_name'], 'rb'))
	{
		showFuncMessage (__FUNCTION__, 'ERR1', array ('failed to access the temporary file'));
		return;
	}

	commitAddFile ($_FILES['file']['name'], $_FILES['file']['type'], $fp, genericAssertion ('comment', 'string0'));
	usePreparedInsertBlade
	(
		'FileLink',
		array
		(
			'file_id' => lastInsertID(),
			'entity_type' => $realm,
			'entity_id' => getBypassValue(),
		)
	);
	showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($_FILES['file']['name'])));
}

function linkFileToEntity ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 71));
	$fi = spotEntity ('file', genericAssertion ('file_id', 'natural'));
	usePreparedInsertBlade
	(
		'FileLink',
		array
		(
			'file_id' => $fi['id'],
			'entity_type' => etypeByPageno(),
			'entity_id' => getBypassValue(),
		)
	);
	showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($fi['name'])));
}

function replaceFile ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 6, 'ERR2' => 201));
	// Make sure the file can be uploaded
	if (get_cfg_var('file_uploads') != 1)
		throw new RackTablesError ('file uploads not allowed, change "file_uploads" parameter in php.ini', RackTablesError::MISCONFIGURED);
	$shortInfo = spotEntity ('file', getBypassValue());

	if (FALSE === $fp = fopen ($_FILES['file']['tmp_name'], 'rb'))
	{
		showFuncMessage (__FUNCTION__, 'ERR2');
		return;
	}
	commitReplaceFile ($shortInfo['id'], $fp);

	showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($shortInfo['name'])));
}

function unlinkFile ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 72));
	commitUnlinkFile (genericAssertion ('link_id', 'natural'));
	showFuncMessage (__FUNCTION__, 'OK');
}

function deleteFile ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 7));
	$file_id = genericAssertion ('file_id', 'natural');
	$shortInfo = spotEntity ('file', $file_id);
	commitDeleteFile ($file_id);
	showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($shortInfo['name'])));
}

function updateFileText ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 6, 'ERR1' => 179, 'ERR2' => 155));
	$shortInfo = spotEntity ('file', getBypassValue());
	if ($shortInfo['mtime'] != genericAssertion ('mtime_copy', 'string'))
	{
		showFuncMessage (__FUNCTION__, 'ERR1');
		return;
	}
	commitReplaceFile ($shortInfo['id'], genericAssertion ('file_text', 'string0'));
	showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($shortInfo['name'])));
}

function addIIFOIFCompatPack ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 37));
	$standard = genericAssertion ('standard', 'enum/wdmstd');
	$iif_id = genericAssertion ('iif_id', 'iif');
	global $wdm_packs;
	$ngood = 0;
	foreach ($wdm_packs[$standard]['oif_ids'] as $oif_id)
	{
		commitSupplementPIC ($iif_id, $oif_id);
		$ngood++;
	}
	showFuncMessage (__FUNCTION__, 'OK', array ($ngood));
}

function addOIFCompat ()
{
	$type1 = genericAssertion ('type1', 'natural');
	$type2 = genericAssertion ('type2', 'natural');
	$n_changed = addPortOIFCompat ($type1, $type2);
	showSuccess ("$n_changed row(s) added");
}

function delOIFCompat ()
{
	$type1 = genericAssertion ('type1', 'natural');
	$type2 = genericAssertion ('type2', 'natural');
	$n_changed = deletePortOIFCompat ($type1, $type2);
	showSuccess ("$n_changed row(s) deleted");
}

function delIIFOIFCompatPack ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 38));
	$standard = genericAssertion ('standard', 'enum/wdmstd');
	$iif_id = genericAssertion ('iif_id', 'iif');
	global $wdm_packs;
	$ngood = 0;
	foreach ($wdm_packs[$standard]['oif_ids'] as $oif_id)
	{
		usePreparedDeleteBlade ('PortInterfaceCompat', array ('iif_id' => $iif_id, 'oif_id' => $oif_id));
		$ngood++;
	}
	showFuncMessage (__FUNCTION__, 'OK', array ($ngood));
}

function addOIFCompatPack ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 21));
	global $wdm_packs;
	$oifs = $wdm_packs[genericAssertion ('standard', 'enum/wdmstd')]['oif_ids'];
	foreach ($oifs as $oif_id_1)
	{
		$args = $qmarks = array();
		$query = 'REPLACE INTO PortCompat (type1, type2) VALUES ';
		foreach ($oifs as $oif_id_2)
		{
			$qmarks[] = '(?, ?)';
			$args[] = $oif_id_1;
			$args[] = $oif_id_2;
		}
		$query .= implode (', ', $qmarks);
		usePreparedExecuteBlade ($query, $args);
	}
	showFuncMessage (__FUNCTION__, 'OK');
}

function delOIFCompatPack ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 21));
	global $wdm_packs;
	$oifs = $wdm_packs[genericAssertion ('standard', 'enum/wdmstd')]['oif_ids'];
	foreach ($oifs as $oif_id_1)
		foreach ($oifs as $oif_id_2)
			if ($oif_id_1 != $oif_id_2) # leave narrow-band mapping intact
				usePreparedDeleteBlade ('PortCompat', array ('type1' => $oif_id_1, 'type2' => $oif_id_2));
	showFuncMessage (__FUNCTION__, 'OK');
}

function add8021QOrder ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 48));
	$vdom_id = genericAssertion ('vdom_id', 'natural');
	$object_id = genericAssertion ('object_id', 'natural');
	$vst_id = genericAssertion ('vst_id', 'natural');
	global $pageno;
	fixContext();
	if ($pageno != 'object')
		spreadContext (spotEntity ('object', $object_id));
	if ($pageno != 'vst')
		spreadContext (spotEntity ('vst', $vst_id));
	assertPermission();
	usePreparedExecuteBlade
	(
		'INSERT INTO VLANSwitch (domain_id, object_id, template_id, last_change, out_of_sync) ' .
		'VALUES (?, ?, ?, NOW(), "yes")',
		array ($vdom_id, $object_id, $vst_id)
	);
	showFuncMessage (__FUNCTION__, 'OK');
}

function del8021QOrder ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 49));
	$object_id = genericAssertion ('object_id', 'natural');
	$vdom_id = genericAssertion ('vdom_id', 'natural');
	$vst_id = genericAssertion ('vst_id', 'natural');
	global $pageno;
	fixContext();
	if ($pageno != 'object')
		spreadContext (spotEntity ('object', $object_id));
	if ($pageno != 'vst')
		spreadContext (spotEntity ('vst', $vst_id));
	assertPermission();
	usePreparedDeleteBlade ('VLANSwitch', array ('object_id' => $object_id));
	$focus_hints = array
	(
		'prev_objid' => $object_id,
		'prev_vstid' => $vst_id,
		'prev_vdid' => $vdom_id,
	);
	showFuncMessage (__FUNCTION__, 'OK');
	return buildRedirectURL (NULL, NULL, $focus_hints);
}

function createVLANDomain ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 48));
	usePreparedInsertBlade
	(
		'VLANDomain',
		array
		(
			'description' => genericAssertion ('vdom_descr', 'string'),
		)
	);
	$domain_id = lastInsertID();
	lastCreated ('vdom', $domain_id);
	usePreparedInsertBlade
	(
		'VLANDescription',
		array
		(
			'domain_id' => $domain_id,
			'vlan_id' => VLAN_DFL_ID,
			'vlan_type' => 'compulsory',
			'vlan_descr' => 'default',
		)
	);
	showFuncMessage (__FUNCTION__, 'OK');
}

function save8021QPorts ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 21));
	global $sic;
	$object_id = getBypassValue();
	$form_mode = genericAssertion ('form_mode', 'string');
	$extra = array();

	// prepare the $changes array
	$changes = array();
	switch ($form_mode)
	{
	case 'save':
		$nports = genericAssertion ('nports', 'natural');
		if ($nports == 1)
			$extra = array ('port_name' => genericAssertion ('pn_0', 'string'));
		for ($i = 0; $i < $nports; $i++)
		{
			$portname = assertStringArg ('pn_' . $i);
			$portmode = assertStringArg ('pm_' . $i);
			$native = isset ($sic['pnv_' . $i]) ? $sic['pnv_' . $i] : 0;
			switch ($portmode)
			{
			case 'trunk':
#				assertArrayArg ('pav_' . $i);
				if ($native != 0)
					genericAssertion ('pnv_' . $i, 'vlan1');
				$allowed = isset ($sic['pav_' . $i]) ? $sic['pav_' . $i] : array();
				break;
			case 'access':
				if ($native == 'same')
					continue 2;
				genericAssertion ('pnv_' . $i, 'vlan1');
				// An access port only generates a form input for its native VLAN,
				// the allowed VLAN list has to be derived.
				$allowed = array ($native);
				break;
			default:
				throw new InvalidRequestArgException ("pm_${i}", $portmode, 'unknown port mode');
			}
			$changes[$portname] = array
			(
				'mode' => $portmode,
				'allowed' => $allowed,
				'native' => $native,
			);
		}
		break;
	case 'duplicate':
		$from_port = genericAssertion ('from_port', 'string');
		$before = getStored8021QConfig ($object_id, 'desired');
		if (!array_key_exists ($from_port, $before))
			throw new InvalidArgException ('from_port', $from_port, 'this port does not exist');
		foreach (genericAssertion ('to_ports', 'array0') as $tpn)
			if (!array_key_exists ($tpn, $before))
				throw new InvalidArgException ('to_ports[]', $tpn, 'this port does not exist');
			elseif ($tpn != $from_port)
				$changes[$tpn] = $before[$from_port];
		break;
	default:
		throw new InvalidRequestArgException ('form_mode', $form_mode);
	}
	apply8021qChangeRequest ($object_id, $changes, TRUE, genericAssertion ('mutex_rev', 'unsigned'));
	return buildRedirectURL (NULL, NULL, $extra);
}

function bindVLANtoIPv4 ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 48));
	commitSupplementVLANIPv4 (genericAssertion ('vlan_ck', 'uint-vlan1'), genericAssertion ('id', 'natural'));
	showFuncMessage (__FUNCTION__, 'OK');
}

function bindVLANtoIPv6 ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 48));
	commitSupplementVLANIPv6 (genericAssertion ('vlan_ck', 'uint-vlan1'), genericAssertion ('id', 'natural'));
	showFuncMessage (__FUNCTION__, 'OK');
}

function unbindVLANfromIPv4 ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 49));
	commitReduceVLANIPv4 (genericAssertion ('vlan_ck', 'uint-vlan1'), genericAssertion ('id', 'natural'));
	showFuncMessage (__FUNCTION__, 'OK');
}

function unbindVLANfromIPv6 ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 49));
	commitReduceVLANIPv6 (genericAssertion ('vlan_ck', 'uint-vlan1'), genericAssertion ('id', 'natural'));
	showFuncMessage (__FUNCTION__, 'OK');
}

function process8021QSyncRequest ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 63, 'ERR' => 191));
	// behave depending on current operation: exec8021QPull or exec8021QPush
	global $op;
	if (FALSE === $done = exec8021QDeploy (getBypassValue(), $op == 'exec8021QPush'))
		showFuncMessage (__FUNCTION__, 'ERR');
	else
		showFuncMessage (__FUNCTION__, 'OK', array ($done));
}

function process8021QRecalcRequest ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 87));
	assertPermission (NULL, NULL, NULL, array (array ('tag' => '$op_recalc8021Q')));
	$counters = recalc8021QPorts (getBypassValue());
	if ($counters['ports'])
		showFuncMessage (__FUNCTION__, 'OK', array ($counters['ports'], $counters['switches']));
	else
		showNotice ('No changes were made');
}

function resolve8021QConflicts ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 63, 'ERR1' => 179, 'ERR2' => 109));
	global $sic, $dbxlink;
	$mutex_rev = genericAssertion ('mutex_rev', 'unsigned');
	$nrows = genericAssertion ('nrows', 'natural');
	$object_id = getBypassValue();
	// Divide submitted radio buttons into 3 groups:
	// left (saved version wins)
	// asis (ignore)
	// right (running version wins)
	$F = array();
	for ($i = 0; $i < $nrows; $i++)
	{
		if (!array_key_exists ("i_${i}", $sic))
			continue;
		// let's hope other inputs are in place
		switch ($sic["i_${i}"])
		{
		case 'left':
		case 'right':
			$F[$sic["pn_${i}"]] = array
			(
				'mode' => $sic["rm_${i}"],
				'allowed' => array_fetch ($sic, "ra_${i}", array()),
				'native' => $sic["rn_${i}"],
				'decision' => $sic["i_${i}"],
			);
			break;
		default:
			// don't care
		}
	}
	$dbxlink->beginTransaction();
	try
	{
		if (NULL === $vswitch = getVLANSwitchInfo ($object_id, 'FOR UPDATE'))
			throw new InvalidArgException ('object_id', $object_id, 'VLAN domain is not set for this object');
		if ($vswitch['mutex_rev'] != $mutex_rev)
			throw new InvalidRequestArgException ('mutex_rev', $mutex_rev, 'expired form (table data has changed)');
		$D = getStored8021QConfig ($vswitch['object_id'], 'desired');
		$C = getStored8021QConfig ($vswitch['object_id'], 'cached');
		$R = getRunning8021QConfig ($vswitch['object_id']);
		$plan = get8021QSyncOptions ($vswitch, $D, $C, $R['portdata']);
		$ndone = 0;
		foreach ($F as $port_name => $port)
		{
			if (!array_key_exists ($port_name, $plan))
				continue;
			elseif ($plan[$port_name]['status'] == 'merge_conflict')
			{
				// for R neither mutex nor revisions can be emulated, but revision change can be
				if (!same8021QConfigs ($port, $R['portdata'][$port_name]))
					throw new InvalidRequestArgException ("port ${port_name}", '(hidden)', 'expired form (switch data has changed)');
				if ($port['decision'] == 'right') // D wins, frame R by writing value of R to C
					$ndone += upd8021QPort ('cached', $vswitch['object_id'], $port_name, $port, $C[$port_name]);
				elseif ($port['decision'] == 'left') // R wins, cross D up
					$ndone += upd8021QPort ('cached', $vswitch['object_id'], $port_name, $D[$port_name], $C[$port_name]);
				// otherwise there was no decision made
			}
			elseif
			(
				$plan[$port_name]['status'] == 'delete_conflict' or
				$plan[$port_name]['status'] == 'martian_conflict'
			)
				if ($port['decision'] == 'left')
					// confirm deletion of local copy
					$ndone += del8021QPort ($vswitch['object_id'], $port_name);
				// otherwise ignore a decision that doesn't address a conflict
		}
	}
	catch (InvalidRequestArgException $e)
	{
		$dbxlink->rollBack();
		showFuncMessage (__FUNCTION__, 'ERR1');
		return;
	}
	catch (Exception $e)
	{
		$dbxlink->rollBack();
		showFuncMessage (__FUNCTION__, 'ERR2');
		return;
	}
	$dbxlink->commit();
	showFuncMessage (__FUNCTION__, 'OK', array ($ndone));
}

function update8021QPortList()
{
	genericAssertion ('ports', 'array');
	$enabled = $disabled = 0;
	$default_port = array
	(
		'mode' => 'access',
		'allowed' => array (VLAN_DFL_ID),
		'native' => VLAN_DFL_ID,
	);
	foreach (genericAssertion ('ports', 'array') as $line)
		if (preg_match ('/^enable (.+)$/', $line, $m))
			$enabled += add8021QPort (getBypassValue(), $m[1], $default_port);
		elseif (preg_match ('/^disable (.+)$/', $line, $m))
			$disabled += del8021QPort (getBypassValue(), $m[1]);
		else
			throw new InvalidRequestArgException ('ports[]', $line, 'malformed array item');
	# $enabled + $disabled > 0
	if ($enabled)
		showSuccess ("enabled 802.1Q for ${enabled} port(s)");
	if ($disabled)
		showSuccess ("disabled 802.1Q for ${disabled} port(s)");
}

function cloneVST()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 48));
	$src_vst = spotEntity ('vst', genericAssertion ('from_id', 'natural'));
	amplifyCell ($src_vst);
	commitUpdateVSTRules (getBypassValue(), genericAssertion ('mutex_rev', 'unsigned'), $src_vst['rules']);
	showFuncMessage (__FUNCTION__, 'OK');
}

function updVSTRule()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 43));
	// this is used for making throwing an invalid argument exception easier.
	function updVSTRule_get_named_param ($name, $haystack, &$last_used_name)
	{
		$last_used_name = $name;
		return isset ($haystack[$name]) ? $haystack[$name] : NULL;
	}

	global $port_role_options;
	$vst_id = getBypassValue();
	$taglist = genericAssertion ('taglist', 'array0');
	$mutex_rev = genericAssertion ('mutex_rev', 'unsigned');
	$data = genericAssertion ('template_json', 'json');
	$rule_no = 0;
	try
	{
		$last_field = '';
		foreach ($data as $rule)
		{
			$rule_no++;
			if
			(
				! isUnsignedInteger (updVSTRule_get_named_param ('rule_no', $rule, $last_field)) ||
				! isPCRE (updVSTRule_get_named_param ('port_pcre', $rule, $last_field)) ||
				NULL === updVSTRule_get_named_param ('port_role', $rule, $last_field) ||
				! array_key_exists (updVSTRule_get_named_param ('port_role', $rule, $last_field), $port_role_options) ||
				NULL ===  updVSTRule_get_named_param ('wrt_vlans', $rule, $last_field) ||
				! preg_match ('/^[ 0-9\-,]*$/',  updVSTRule_get_named_param ('wrt_vlans', $rule, $last_field)) ||
				NULL ===  updVSTRule_get_named_param ('description', $rule, $last_field)
			)
				throw new InvalidRequestArgException ($last_field, $rule[$last_field], "rule #$rule_no");
		}
		commitUpdateVSTRules ($vst_id, $mutex_rev, $data);
	}
	catch (Exception $e)
	{
		// Every case that is soft-processed in index.php?module=redirect will have
		// the working copy available for a retry.
		if ($e instanceof InvalidRequestArgException || $e instanceof RTDatabaseError)
		{
			startSession();
			$_SESSION['vst_edited'] = $data;
			session_commit();
		}
		throw $e;
	}
	rebuildTagChainForEntity ('vst', $vst_id, buildTagChainFromIds ($taglist), TRUE);
	showFuncMessage (__FUNCTION__, 'OK');
}

function importDPData()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 44));
	global $sic, $dbxlink;
	$nports = genericAssertion ('nports', 'natural');
	$object_id = getBypassValue();
	$nignored = $ndone = 0;
	for ($i = 0; $i < $nports; $i++)
		if (array_key_exists ("do_${i}", $sic))
		{
			$params = array();
			assertStringArg ("ports_${i}");
			foreach (explode (',', $_REQUEST["ports_${i}"]) as $item)
			{
				$pair = explode (':', $item);
				if (count ($pair) != 2)
					continue;
				$params[$pair[0]] = $pair[1];
			}
			if (! isset ($params['a_id']) || ! isset ($params['b_id']) ||
				! intval ($params['a_id']) || ! intval ($params['b_id']))
				throw new InvalidRequestArgException ("ports_${i}", $_REQUEST["ports_${i}"], "can not unpack port ids");

			$porta = getPortInfo ($params['a_id']);
			$portb = getPortInfo ($params['b_id']);
			if
			(
				$porta['linked'] ||
				$portb['linked'] ||
				($porta['object_id'] != $object_id && $portb['object_id'] != $object_id)
			)
			{
				$nignored++;
				continue;
			}
			$oif_a = intval (@$params['a_oif']); // these parameters are optional
			$oif_b = intval (@$params['b_oif']);

			$dbxlink->beginTransaction();
			try
			{
				if ($oif_a)
				{
					commitUpdatePortOIF ($params['a_id'], $oif_a);
					$porta['oif_id'] = $oif_a;
				}
				if ($oif_b)
				{
					commitUpdatePortOIF ($params['b_id'], $oif_b);
					$portb['oif_id'] = $oif_b;
				}

				if (arePortsCompatible ($porta, $portb))
				{
					linkPorts ($params['a_id'], $params['b_id']);
					$ndone++;
					$dbxlink->commit();
				}
				else
				{
					$dbxlink->rollBack();
					$nignored++;
				}
			}
			catch (RTDatabaseError $e)
			{
				$dbxlink->rollBack();
				$nignored++;
			}
		}
	showFuncMessage (__FUNCTION__, 'OK', array ($nignored, $ndone));
}

function addObjectlog ()
{
	global $remote_username, $sic;
	if (isset ($sic['rack_id']))
		$object_id = $sic['rack_id'];
	elseif (isset ($sic['row_id']))
		$object_id = $sic['row_id'];
	elseif (isset ($sic['location_id']))
		$object_id = $sic['location_id'];
	else
		$object_id = $sic['object_id'];

	usePreparedExecuteBlade
	(
		'INSERT INTO ObjectLog SET object_id=?, user=?, date=NOW(), content=?',
		array ($object_id, $remote_username, genericAssertion ('logentry', 'string'))
	);
	showSuccess ('Log entry added');
}

function saveQuickLinks()
{
	try
	{
		setUserConfigVar ('QUICK_LINK_PAGES', implode(',', genericAssertion ('page_list', 'array0')));
	}
	catch (InvalidArgException $iae)
	{
		throw $iae->newIRAE();
	}
	showSuccess ('Quick links list is saved');
}

$ucsproductmap = array
(
	// UCS Fabric Interconnects/switches
	'UCS-FI-6248UP' => 1757, # 6248 FI
	'UCS-FI-6296UP' => 1758, # 6296 FI
	'N10-S6100' => 1755, # 6120 FI
	'N10-S6200' => 1756, # 6140 FI
	'UCS-FI-M-6324' => 2576, # UCS-Mini 6324 FI
	'UCS-FI-6332-U' => 2578, # 6332 FI
	'UCS-FI-6332-16UP-U' => 2579, # 6332-16 FI
	// UCS Blade Chassis
	'N20-C6508' => 1735, # 5108 chassis
	'UCSB-5108-AC2' => 2220, # 5108-AC2 chassis
	'UCSB-5108-DC2' => 2221, # 5108-DC2 chassis
	'UCSB-5108-HVDC' => 2222, # 5108-HVDC chassis
	// UCS 'B-series' Blade Servers
	'N20-B6620-1'  => 1736,  # Cisco UCS B200 M1 2 Socket Blade Server
	'N20-B6625-1'  => 1737,  # Cisco UCS B200 M2 2 Socket Blade Server
	'UCSB-B200-M3' => 1738,  # Cisco UCS B200 M3 2 Socket Blade Server
	'UCSB-B200-M3' => 1738,  # Cisco UCS B200 M3 2 Socket Blade Server
	'N20-B6730-1'  => 1739,  # Cisco UCS B440-M2 4 Socket, Extended Memory Blade Server
	'B230-BASE-M2' => 1740,  # Cisco UCS B420 M3 4 Socket Blade Server
	'N20-B6620-2'  => 1741,  # Cisco UCS B420 M3 4 Socket Blade Server
	'N20-B6625-2'  => 1742,  # Cisco UCS B22 M3 2 Socket Half Width Blade Server
	'B440-BASE-M2' => 1743,  # Cisco UCS C240 M3 High-Density Rack-Mount Server
	'UCSB-B420-M3' => 1744,  # Cisco UCS B22 M3 2 Socket Half Width Blade Server
	'UCSB-B420-M3' => 1744,  # Cisco UCS C22 M3 High-Density Rack-Mount Server
	'UCSB-B22-M3'  => 1745,  # Cisco UCS B250 M1 2 Socket, Extended Memory Blade Server
	'UCSB-B22-M3'  => 1745,  # Cisco UCS B250 M2 2 Socket, Extended Memory Blade Server
	'UCSB-B200-M4' => 2225,  # Cisco UCS B230-M1 2 Socket Blade Server
	'UCSB-B200-M4' => 2225,  # Cisco UCS B230-M2 2 Socket Blade Server
	'UCSB-B420-M4' => 2558,  # Cisco UCS C220 M3 High-Density Rack-Mount Server
	'N20-B6740-2'  => 2559,  # Cisco UCS C24 M3 High-Density Rack-Mount Server
	// UCS 'C-Series' Rackmount Servers
	'UCSC-C22-M3S'   => 1751,  # Cisco UCS B420 M4 4 Socket Blade Server
	'UCSC-C220-M3S'  => 1752,  # Cisco UCS B440 M1 4 Socket, Extended Memory Blade Server
	'UCSC-C24-M3S'   => 1753,  # Cisco UCS C220 M3 High-Density Rack-Mount Server
	'UCSC-C240-M3S'  => 1754,  # Cisco UCS C240 M3 High-Density Rack-Mount Server
	'UCSC-C22-M3L'   => 2562,  # Cisco UCS C260 M2 High-Density Rack-Mount Server
	'UCSC-C220-M3L'  => 2563,  # Cisco UCS C200 M2 High-Density Rack-Mount Server
	'UCSC-C24-M3L'   => 2564,  # Cisco UCS C24 M3 High-Density Rack-Mount Server
	'UCSC-C240-M3S2' => 2565,  # Cisco UCS C220 M4 High-Density Rack-Mount Server
	'UCSC-C220-M4L'  => 2566,  # Cisco UCS C210 M2 General-Purpose Rack-Mount Server
	'UCSC-C220-M4S'  => 2567,  # Cisco UCS C22 M3 High-Density Rack-Mount Server
	'UCSC-C240-M4SX' => 2568,  # Cisco UCS C220 M4 High-Density Rack-Mount Server
	'UCSC-C240-M4S2' => 2569,  # Cisco UCS C240 M4 High-Density Rack-Mount Server
	'UCSC-C240-M4L'  => 2570,  # Cisco UCS C240 M4 High-Density Rack-Mount Server
	'UCSC-C240-M4S'  => 2571,  # Cisco UCS C240 M4 High-Density Rack-Mount Server
	'UCSC-C420-M3'   => 2573,  # Cisco UCS C420 M3 High-Density Rack-Mount Server
	'UCSC-C460-M4'   => 2575,  # Cisco UCS C460 M4 High-Density Rack-Mount Server
	'UCSC-C240-M3L'  => 2579,  # Cisco UCS C240 M3 High-Density Rack-Mount Server
);

function autoPopulateUCS()
{
	global $ucsproductmap;
	$ucsm_id = getBypassValue();
	$oinfo = spotEntity ('object', $ucsm_id);
	$chassis_id = array();
	$done = 0;
	# There are three request parameters (use_terminal_settings, ucs_login and
	# ucs_password) not processed here. These are asserted and used inside
	# queryTerminal().

	try
	{
		$contents = queryDevice ($ucsm_id, 'getinventory');
	}
	catch (RTGatewayError $e)
	{
		showError ($e->getMessage());
		return;
	}
	foreach ($contents as $item)
	{
		$mname = preg_replace ('#^sys/(.+)$#', $oinfo['name'] . '/\\1', $item['DN']);
		switch ($item['type'])
		{
		case 'NetworkElement':
			$new_object_id = commitAddObject ($mname, NULL, 8, NULL);
			#    Set H/W Type for Network Switch
			if (array_key_exists ($item['model'], $ucsproductmap))
				commitUpdateAttrValue ($new_object_id, 2, $ucsproductmap[$item['model']]);
			#  	 Set Serial#
			commitUpdateAttrValue ($new_object_id, 1, $item['serial']);
			commitLinkEntities ('object', $ucsm_id, 'object', $new_object_id);
			bindIPToObject (ip_parse ($item['OOB']), $new_object_id, 'mgmt0', 'regular');
			$done++;
			break;
		case 'EquipmentChassis':
			$chassis_id[$item['DN']] = $new_object_id = commitAddObject ($mname, NULL, 1502, NULL);
			#    Set H/W Type for Server Chassis
			if (array_key_exists ($item['model'], $ucsproductmap))
				commitUpdateAttrValue ($new_object_id, 2, $ucsproductmap[$item['model']]);
			#  	 Set Serial#
			commitUpdateAttrValue ($new_object_id, 1, $item['serial']);
			commitLinkEntities ('object', $ucsm_id, 'object', $new_object_id);
			$done++;
			break;
		case 'ComputeBlade':
			if ($item['assigned'] == '')
				$new_object_id = commitAddObject ($mname, NULL, 4, NULL);
			else
			{
				$spname = preg_replace ('#.+/ls-(.+)#i', '${1}', $item['assigned']) . "(" . $oinfo['name'] . ")";
				$spname_id[$spname] = $new_object_id = commitAddObject ($spname, NULL, 4, NULL);
			}
			#    Set H/W Type for Blade Server
			if (array_key_exists ($item['model'], $ucsproductmap))
				commitUpdateAttrValue ($new_object_id, 2, $ucsproductmap[$item['model']]);
			#  	 Set Serial#
			commitUpdateAttrValue ($new_object_id, 1, $item['serial']);
			#  	 Set Slot#
			commitUpdateAttrValue ($new_object_id, 28, $item['slot']);
			$parent_name = preg_replace ('#^([^/]+)/([^/]+)/([^/]+)$#', '${1}/${2}', $item['DN']);
			if (array_key_exists ($parent_name, $chassis_id))
				commitLinkEntities ('object', $chassis_id[$parent_name], 'object', $new_object_id);
			$done++;
			break;
		case 'VnicPort':
			$spname = preg_replace ('#^([^/]+)/ls-([^/]+)/([^/]+)$#', '${2}', $item['DN']) . "(" . $oinfo['name'] . ")";
			$porttype = preg_replace ('#^([^/]+)/([^/]+)/([^-/]+)-.+$#', '${3}', $item['DN']);
			try
			{
				// Add "virtual" (1469) ports for associated blades only. The attempt may fail
				// due to incorrect port type or MAC address.
				if ($spid = $spname_id[$spname])
					commitAddPort ($spid, $item['name'], 1469, $porttype, $item['addr']);
			}
			catch (InvalidArgException $iae)
			{
				showError ($iae->getMessage());
			}
			break;
		case 'ComputeRackUnit':
			if ($item['assigned'] == '')
				$new_object_id = commitAddObject ($mname, NULL, 4, NULL);
			else
			{
				$spname = preg_replace ('#.+/ls-(.+)#i', '${1}', $item['assigned']) . "(" . $oinfo['name'] . ")";
				$new_object_id = commitAddObject ($spname, NULL, 4, NULL);
			}
			# Set H/W Type for RackmountServer
			if (array_key_exists ($item['model'], $ucsproductmap))
				commitUpdateAttrValue ($new_object_id, 2, $ucsproductmap[$item['model']]);
			# Set Serial#
			commitUpdateAttrValue ($new_object_id, 1, $item['serial']);
			$parent_name = preg_replace ('#^([^/]+)/([^/]+)/([^/]+)$#', '${1}/${2}', $item['DN']);
			commitLinkEntities ('object', $ucsm_id, 'object', $new_object_id);
			$done++;
			break;
		}
	} # endfor
	showSuccess ("Auto-populated UCS Domain '${oinfo['name']}' with ${done} items");
}

function cleanupUCS()
{
	global $ucsproductmap;
	$oinfo = spotEntity ('object', getBypassValue());
	$contents = getObjectContentsList ($oinfo['id']);

	$clear = TRUE;
	foreach ($contents as $item_id)
	{
		$o = spotEntity ('object', $item_id);
		$attrs = getAttrValues ($item_id);
		# use HW type to decide if the object was produced by autoPopulateUCS()
		if (! array_key_exists (2, $attrs) || ! in_array ($attrs[2]['key'], $ucsproductmap))
		{
			showWarning ('Contained object ' . mkA ($o['dname'], 'object', $item_id) . ' is not an automatic UCS object');
			$clear = FALSE;
		}
	}
	if (! $clear)
	{
		showNotice ('nothing was deleted');
		return;
	}

	$done = 0;
	foreach ($contents as $item_id)
	{
		commitDeleteObject ($item_id);
		$done++;
	}
	showSuccess ("Removed ${done} items from UCS Domain '${oinfo['name']}'");
}

function getOpspec()
{
	global $pageno, $tabno, $op, $opspec_list;
	if (!array_key_exists ($pageno . '-' . $tabno . '-' . $op, $opspec_list))
		throw new RackTablesError ('key not found in opspec_list', RackTablesError::INTERNAL);
	$ret = $opspec_list[$pageno . '-' . $tabno . '-' . $op];
	if
	(
		! array_key_exists ('table', $ret) ||
		! array_key_exists ('action', $ret)
		// add further checks here
	)
		throw new RackTablesError ('malformed array structure in opspec_list', RackTablesError::INTERNAL);
	return $ret;
}

function unlinkPort ()
{
	commitUnlinkPort (genericAssertion ('port_id', 'natural'));
	showSuccess ("Port unlinked successfully");
}

function clearVlan()
{
	list ($vdom_id, $vlan_id) = decodeVLANCK (genericAssertion ('vlan_ck', 'uint-vlan1'));

	$n_cleared = pinpointDeleteVlan ($vdom_id, $vlan_id);
	if ($n_cleared > 0)
		showSuccess ("VLAN $vlan_id removed from $n_cleared port(s)");
}

function deleteVlan()
{
	list ($vdom_id, $vlan_id) = decodeVLANCK (genericAssertion ('vlan_ck', 'uint-vlan'));
	$n_cleared = pinpointDeleteVlan ($vdom_id, $vlan_id);
	if ($n_cleared > 0)
		showSuccess ("VLAN $vlan_id removed from $n_cleared port(s)");
	// Since there are no strict foreign keys referring VLANDescription, it is safe just to delete the row.
	usePreparedDeleteBlade ('VLANDescription', array ('domain_id' => $vdom_id, 'vlan_id' => $vlan_id));
	showSuccess ("VLAN $vlan_id has been deleted");
	return buildRedirectURL ('vlandomain', 'default', array ('vdom_id' => $vdom_id));
}

function cloneRSPool()
{
	$pool = spotEntity ('ipv4rspool', getBypassValue());
	$rs_list = getRSListInPool ($pool['id']);
	$tagidlist = array();
	foreach ($pool['etags'] as $taginfo)
		$tagidlist[] = $taginfo['id'];
	$new_id = commitCreateRSPool ($pool['name'] . ' (copy)', $pool['vsconfig'], $pool['rsconfig'], $tagidlist);
	foreach ($rs_list as $rs)
		addRStoRSPool ($new_id, $rs['rsip_bin'], $rs['rsport'], $rs['inservice'], $rs['rsconfig'], $rs['comment']);
	showSuccess ('Created a copy of pool  ' . mkCellA ($pool));
	return buildRedirectURL ('ipv4rspool', 'default', array ('pool_id' => $new_id));
}

function doVSMigrate()
{
	global $dbxlink;
	$vs_id = getBypassValue();
	$vs_cell = spotEntity ('ipvs', $vs_id);
	amplifyCell ($vs_cell);
	$tag_ids = genericAssertion ('taglist', 'array0');
	$old_vs_list = genericAssertion ('vs_list', 'array');
	$plan = callHook ('buildVSMigratePlan', $vs_id, $old_vs_list);

	$dbxlink->beginTransaction();

	// remove all triplets
	usePreparedDeleteBlade ('VSEnabledIPs', array ('vs_id' => $vs_id));
	usePreparedDeleteBlade ('VSEnabledPorts', array ('vs_id' => $vs_id));

	// remove all VIPs and ports that are in $plan and create new ones
	foreach ($plan['vips'] as $vip)
	{
		usePreparedDeleteBlade ('VSIPs', array ('vs_id' => $vs_id, 'vip' => $vip['vip']));
		usePreparedInsertBlade ('VSIPs', array ('vs_id' => $vs_id) + $vip);
	}
	foreach ($plan['ports'] as $port)
	{
		usePreparedDeleteBlade ('VSPorts', array ('vs_id' => $vs_id, 'proto' => $port['proto'], 'vport' => $port['vport']));
		usePreparedInsertBlade ('VSPorts', array ('vs_id' => $vs_id) + $port);
	}

	// create triplets
	foreach ($plan['triplets'] as $triplet)
	{
		$tr_key = array
		(
			'vs_id' => $triplet['vs_id'],
			'object_id' => $triplet['object_id'],
			'rspool_id' => $triplet['rspool_id'],
		);

		foreach ($triplet['ports'] as $port)
			addSLBPortLink ($tr_key + $port);
		foreach ($triplet['vips'] as $vip)
			addSLBIPLink ($tr_key + $vip);
	}

	// update configs
	usePreparedUpdateBlade ('VS', $plan['properties'], array ('id' => $vs_id));

	// replace tags
	global $taglist;
	$chain = array();
	foreach ($tag_ids as $tid)
		if (! isset ($taglist[$tid]))
		{
			$dbxlink->rollBack();
			showError ("Unknown tag id $tid");
		}
		else
			$chain[] = $taglist[$tid];
	rebuildTagChainForEntity ('ipvs', $vs_id, $chain, TRUE);

	$dbxlink->commit();
	showSuccess ("old VS configs were copied to VS group");
	return buildRedirectURL (NULL, 'default');
}

# validate user input and produce SQL columns per the opspec descriptor
function buildOpspecColumns ($opspec, $listname)
{
	$columns = array();
	if (! array_key_exists ($listname, $opspec))
		throw new InvalidArgException ('opspec', '(malformed structure)', "missing '${listname}'");
	foreach ($opspec[$listname] as $argspec)
		switch (TRUE)
		{
		case array_key_exists ('url_argname', $argspec): # HTTP input
			$arg_value = genericAssertion ($argspec['url_argname'], $argspec['assertion']);
			// "table_colname" is normally used for an override, if it is not
			// set, use the URL argument name
			$table_colname = array_fetch ($argspec, 'table_colname', $argspec['url_argname']);
			if (array_key_exists ('translator', $argspec))
			{
				if (! is_callable ($argspec['translator']))
					throw new RackTablesError ('opspec translator function is not callable', RackTablesError::INTERNAL);
				$arg_value = $argspec['translator'] ($arg_value);
			}
			$columns[$table_colname] = $arg_value;
			break;
		case array_key_exists ('fix_argname', $argspec): # fixed column
			if (! array_key_exists ('fix_argvalue', $argspec))
				throw new InvalidArgException ('opspec', '(malformed structure)', 'missing "fix_argvalue"');
			$columns[$argspec['fix_argname']] = $argspec['fix_argvalue'];
			break;
		default:
			throw new InvalidArgException ('opspec', '(malformed structure)', 'unknown argument source');
		} // switch (TRUE)
	return $columns;
}

# execute a single SQL statement defined by an opspec descriptor
function tableHandler()
{
	$opspec = getOpspec();
	switch ($opspec['action'])
	{
	case 'INSERT':
		switch ($opspec['table'])
		{
			case 'Attribute':
				$realm = 'attr';
				break;
			case 'Chapter':
				$realm = 'chapter';
				break;
			case 'Dictionary':
				$realm = 'dict';
				break;
			case 'TagTree':
				$realm = 'tag';
				break;
			case 'VLANSwitchTemplate':
				$realm = 'vst';
				break;
			default:
				$realm = NULL;
		}
		usePreparedInsertBlade ($opspec['table'], buildOpspecColumns ($opspec, 'arglist'));
		if (isset ($realm))
			lastCreated ($realm, lastInsertID());

		$retcode = 48;
		break;
	case 'DELETE':
		usePreparedDeleteBlade
		(
			$opspec['table'],
			buildOpspecColumns ($opspec, 'arglist'),
			array_fetch ($opspec, 'conjunction', 'AND')
		);
		$retcode = 49;
		break;
	case 'UPDATE':
		usePreparedUpdateBlade
		(
			$opspec['table'],
			buildOpspecColumns ($opspec, 'set_arglist'),
			buildOpspecColumns ($opspec, 'where_arglist'),
			array_fetch ($opspec, 'conjunction', 'AND')
		);
		$retcode = 51;
		break;
	default:
		throw new InvalidArgException ('opspec/action', $opspec['action']);
	}
	showOneLiner ($retcode);
}

function updateFile ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 6));
	$file_id = getBypassValue();
	$file_name = genericAssertion ('file_name', 'string');
	$file_type = genericAssertion ('file_type', 'string');
	$file_comment = genericAssertion ('file_comment', 'string0');
	$taglist = genericAssertion ('taglist', 'array0');
	usePreparedUpdateBlade
	(
		'File',
		array ('name' => $file_name, 'type' => $file_type, 'comment' => $file_comment),
		array ('id' => $file_id)
	);
	rebuildTagChainForEntity ('file', $file_id, buildTagChainFromIds ($taglist), TRUE);
	showFuncMessage (__FUNCTION__, 'OK', array ($file_name));
}

function editIPv4Net ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 6));
	$net_id = getBypassValue();
	$name = genericAssertion ('name', 'string0');
	$comment = genericAssertion ('comment', 'string0');
	$taglist = genericAssertion ('taglist', 'array0');
	usePreparedUpdateBlade
	(
		'IPv4Network',
		array ('name' => $name, 'comment' => $comment),
		array ('id' => $net_id)
	);
	rebuildTagChainForEntity ('ipv4net', $net_id, buildTagChainFromIds ($taglist), TRUE);
	$netdata = spotEntity ('ipv4net', $net_id);
	showFuncMessage (__FUNCTION__, 'OK', array ("${netdata['ip']}/${netdata['mask']}"));
}

function editIPv6Net ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 6));
	$net_id = getBypassValue();
	$name = genericAssertion ('name', 'string0');
	$comment = genericAssertion ('comment', 'string0');
	$taglist = genericAssertion ('taglist', 'array0');
	usePreparedUpdateBlade
	(
		'IPv6Network',
		array ('name' => $name, 'comment' => $comment),
		array ('id' => $net_id)
	);
	rebuildTagChainForEntity ('ipv6net', $net_id, buildTagChainFromIds ($taglist), TRUE);
	$netdata = spotEntity ('ipv6net', $net_id);
	showFuncMessage (__FUNCTION__, 'OK', array ("${netdata['ip']}/${netdata['mask']}"));
}

function updIPv4RSP ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 6));
	$rspool_id = getBypassValue();
	$name = genericAssertion ('name', 'string0');
	$vsconfig = genericAssertion ('vsconfig', 'string0');
	$rsconfig = genericAssertion ('rsconfig', 'string0');
	$taglist = genericAssertion ('taglist', 'array0');
	usePreparedUpdateBlade
	(
		"IPv4RSPool",
		array ('name' => $name, 'vsconfig' => $vsconfig, 'rsconfig' => $rsconfig),
		array ('id' => $rspool_id)
	);
	rebuildTagChainForEntity ('ipv4rspool', $rspool_id, buildTagChainFromIds ($taglist), TRUE);
	showFuncMessage (__FUNCTION__, 'OK', array($_REQUEST['name']));
}

function editUserProperties ()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 6));
	$taglist = genericAssertion ('taglist', 'array0');
	$user_id = getBypassValue();
	rebuildTagChainForEntity ('user', $user_id, buildTagChainFromIds ($taglist), TRUE);
	$user = spotEntity ('user', $user_id);
	showFuncMessage (__FUNCTION__, 'OK', array($user['user_name']));
}

function renameObjectPorts()
{
	$object_id = getBypassValue();
	$n = 0;
	foreach (getObjectPortsAndLinks ($object_id, FALSE) as $port)
	{
		$canon_pn = shortenPortName ($port['name'], $port['object_id']);
		if ($canon_pn != $port['name'])
		{
			try
			{
				commitUpdatePort ($object_id, $port['id'], $canon_pn, $port['oif_id'], $port['label'], $port['l2address'], $port['reservation_comment']);
				$n++;
			}
			catch (InvalidArgException $iae)
			{
				showError ($iae->getMessage());
			}
		}
	}
	if ($n)
		showSuccess ("Renamed $n port(s)");
	else
		showNotice ("Nothing renamed");
}

function consumePatchCable()
{
	if (commitModifyPatchCableAmount (genericAssertion ('id', 'natural'), -1))
		showSuccess ('consumed OK');
	else
		showError ('could not consume');
}

function replenishPatchCable()
{
	if (commitModifyPatchCableAmount (genericAssertion ('id', 'natural'), 1))
		showSuccess ('replenished OK');
	else
		showError ('could not replenish');
}

function setPatchCableAmount()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 51));
	commitSetPatchCableAmount (genericAssertion ('id', 'natural'), genericAssertion ('amount', 'unsigned'));
	showFuncMessage (__FUNCTION__, 'OK');
}

function updateVLANDomain()
{
	$domain_id = genericAssertion ('vdom_id', 'natural');
	$group_id = genericAssertion ('group_id', 'unsigned');
	$description = assertStringArg ('vdom_descr');

	if (! $group_id)
		$group_id = NULL;
	else
	{
		$dominfo = getVLANDomain ($domain_id);
		$parent_dominfo = getVLANDomain ($group_id);
		if ($group_id == $domain_id)
			throw new InvalidRequestArgException ('group_id', $group_id, "domains should not be the same");
		if ($parent_dominfo['group_id'] || $dominfo['subdomc'])
			throw new InvalidRequestArgException ('group_id', $group_id, "Multi-level domain groups are not allowed");
	}

	usePreparedUpdateBlade ('VLANDomain', array ('group_id' => $group_id, 'description' => $description), array ('id' => $domain_id));
	showSuccess ("VLAN domain updated successfully");
}

function installPlugin ()
{
	$name = assertStringArg ('name');
	try
	{
		if (! is_callable ("plugin_${name}_install"))
			throw new RackTablesError ("The ${name} plugin is missing or cannot be installed", RackTablesError::MISCONFIGURED);
		$plugin = getPlugin ($name);
		call_user_func ("plugin_${name}_install");
		commitInstallPlugin ($plugin['name'], $plugin['longname'], $plugin['code_version'], $plugin['home_url']);
	}
	catch (Exception $e)
	{
		showError ('Install failed: ' . $e->getMessage());
		return;
	}
	showSuccess ('Installed plugin: ' . $name);
}

function uninstallPlugin ()
{
	$name = assertStringArg ('name');
	try
	{
		if (! is_callable ("plugin_${name}_uninstall"))
			throw new RackTablesError ("The ${name} plugin is missing or cannot be uninstalled", RackTablesError::MISCONFIGURED);
		call_user_func ("plugin_${name}_uninstall");
		commitUninstallPlugin ($name);
	}
	catch (Exception $e)
	{
		showError ('Uninstall failed: ' . $e->getMessage());
		return;
	}
	showSuccess ('Uninstalled plugin: ' . $name);
}

function upgradePlugin ()
{
	$name = assertStringArg ('name');
	try
	{
		if (! is_callable ("plugin_${name}_upgrade"))
			throw new RackTablesError ("The ${name} plugin is missing or cannot be upgraded", RackTablesError::MISCONFIGURED);
		$plugin = getPlugin ($name);
		call_user_func ("plugin_${name}_upgrade");
		// get details from the plugin code itself
		$code_plugin = call_user_func ("plugin_${name}_info");
		usePreparedUpdateBlade
		(
			'Plugin',
			array ('longname' => $code_plugin['longname'], 'version' => $code_plugin['version'], 'home_url' => $code_plugin['home_url']),
			array ('name' => $name)
		);
	}
	catch (Exception $e)
	{
		showError ('Upgrade failed: ' . $e->getMessage());
		return;
	}
	showSuccess ('Upgraded plugin: ' . $name);
}
