<?php

# This file is a part of RackTables, a datacenter and server room management
# framework. See accompanying file "COPYING" for the full copyright and
# licensing information.

/*
*
*  This file is a library of tab triggers for RackTables.
*
*/

// Triggers may be optionally referred by some tabs of a page.
// In case they are defined, the given tab is only displayed if
// the trigger returned CSS class name. In certain cases, a key is necessary
// to decide (the 'bypass' hint of a page), and in some cases,
// other data can be used.

// APC "switched rack PDU" stands for a number of part numbers:
// http://www.apc.com/products/family/index.cfm?id=70
$known_APC_SKUs = array
(
	// 100V input
	1151 => 'AP7902J',
	1152 => 'AP7930J',
	1153 => 'AP7932J',
	// 120V input
	1154 => 'AP7900',
	1155 => 'AP7901',
	1156 => 'AP7902',
	1157 => 'AP7930',
	1158 => 'AP7931',
	1159 => 'AP7932',
	// 208V input
	1160 => 'AP7911',
	1161 => 'AP7940',
	1162 => 'AP7941',
	// 208V 3 phases input
	1163 => 'AP7960',
	1164 => 'AP7961',
	1165 => 'AP7968',
	1166 => 'AP7990',
	1167 => 'AP7991',
	1168 => 'AP7998',
	// 230V input
	1137 => 'AP7920',
	1138 => 'AP7921',
	1139 => 'AP7922',
	1140 => 'AP7950',
	1141 => 'AP7951',
	1142 => 'AP7952',
	1143 => 'AP7953',
	1144 => 'AP7954',
	// 400V 3 phases input
	1154 => 'AP7957',
);

// Return 'std', if the object belongs to specified type and has
// specified attribute belonging to the given set of values.
function checkTypeAndAttribute ($object_id, $type_id, $attr_id, $values, $hit = 'std')
{
	$object = spotEntity ('object', $object_id);
	if ($object['objtype_id'] != $type_id)
		return '';
	foreach (getAttrValues ($object_id) as $record)
		if ($record['id'] == $attr_id and in_array ($record['key'], $values))
			return $hit;
	return '';
}

// This trigger filters out everything except switches with known-good
// software.
function trigger_livevlans ()
{
	return checkTypeAndAttribute
	(
		$_REQUEST['object_id'],
		8, // network switch
		4, // SW type
		// Cisco IOS 12.0
		// Cisco IOS 12.1
		// Cisco IOS 12.2
		array (244, 251, 252)
	);
}

// This trigger is on when any of the (get_mac_list, get_link_status) ops permitted
function trigger_liveports ()
{
	$breed = detectDeviceBreed (getBypassValue());
	foreach (array ('getportstatus', 'getmaclist') as $command)
		if
		(
			validBreedFunction ($breed, $command) and
			permitted (NULL, 'liveports', $command)
		)
			return 'std';
	return '';
}

// SNMP port finder tab trigger. At the moment we decide on showing it
// for pristine switches/PDUs only. Once a user has begun
// filling the data in, we stop showing the tab.
function trigger_snmpportfinder ()
{

	assertUIntArg ('object_id');
	$object = spotEntity ('object', $_REQUEST['object_id']);
	switch ($object['objtype_id'])
	{
	case 7: // any router
	case 8: // or switch would suffice
		return $object['nports'] ? '' : 'attn';
	case 2: // but only selected PDUs
		if ($object['nports'])
			return '';
		global $known_APC_SKUs;
		return checkTypeAndAttribute
		(
			$object['id'],
			2, // PDU
			2, // HW type
			array_keys ($known_APC_SKUs),
			'attn'
		);
	default:
		return '';
	}
}

function trigger_isloadbalancer ()
{
	assertUIntArg ('object_id');
	return considerConfiguredConstraint (spotEntity ('object', $_REQUEST['object_id']), 'IPV4LB_LISTSRC') ? 'std' : '';
}

function trigger_ip ()
{
	assertUIntArg ('object_id');
	if (count (getObjectIPAllocationList ($_REQUEST['object_id'])))
		return 'std';
	// Only hide the tab, if there are no addresses allocated.
	return considerConfiguredConstraint (spotEntity ('object', $_REQUEST['object_id']), 'IPV4OBJ_LISTSRC') ? 'std' : '';
}

function trigger_natv4 ()
{
	assertUIntArg ('object_id');
	if (!count (getObjectIPv4AllocationList ($_REQUEST['object_id'])))
		return '';
	return considerConfiguredConstraint (spotEntity ('object', $_REQUEST['object_id']), 'IPV4NAT_LISTSRC') ? 'std' : '';
}

function trigger_autoports ()
{
	assertUIntArg ('object_id');
	$object = spotEntity ('object', $_REQUEST['object_id']);
	amplifyCell ($object);
	if (count ($object['ports']))
		return '';
	return count (getAutoPorts ($object['objtype_id'])) ? 'attn' : '';
}

function trigger_tags ()
{
	global $taglist;
	return count ($taglist) ? 'std' : '';
}

function trigger_passwdchange ()
{
	global $user_auth_src;
	return $user_auth_src == 'database' ? 'std' : '';
}

function trigger_localreports ()
{
	global $localreports;
	return count ($localreports) ? 'std' : '';
}

function trigger_file_editText ()
{
	assertUIntArg ('file_id');
	$fileInfo = spotEntity ('file', $_REQUEST['file_id']);
	return ($fileInfo['type'] == 'text/plain') ? 'std' : '';
}

function trigger_rackspace ()
{
	global $virtual_obj_types;

	// Hide the tab if the object type is virtual
	assertUIntArg ('object_id');
	$object = spotEntity ('object', $_REQUEST['object_id']);
	if (in_array($object['objtype_id'], $virtual_obj_types))
		return '';

	$rackspace = getRackspaceStats();
	if ($rackspace['Racks'] > 0) return 'std';
	return '';
}

function trigger_ports ()
{
	assertUIntArg ('object_id');
	// Hide the tab if the object type exists in the exclusion config option
	if (considerConfiguredConstraint (spotEntity ('object', $_REQUEST['object_id']), 'PORT_EXCLUSION_LISTSRC')) 
		return '';

	return 'std';
}

// Offer the generic VLAN setup tab for every object, which already
// has a VLAN domain associated or at least can have one (in the latter
// case additionally heat the tab, if no domain is set.
function trigger_object_8021qorder ()
{
	if (NULL !== getVLANSwitchInfo ($_REQUEST['object_id']))
		return 'std';
	if (!count (getVLANDomainOptions()) or !count (getVSTOptions()))
		return '';
	if (considerConfiguredConstraint (spotEntity ('object', $_REQUEST['object_id']), 'VLANSWITCH_LISTSRC'))
		return 'attn';
	return '';
}

function trigger_8021q_configured ()
{
	if (!count (getVLANDomainOptions()) or !count (getVSTOptions()))
		return '';
	return 'std';
}

// implement similar logic for IPv4 networks
function trigger_ipv4net_vlanconfig ()
{
	if (!count (getVLANDomainOptions())) // no domains -- no VLANs to bind with
		return '';
	$netinfo = spotEntity ('ipv4net', $_REQUEST['id']);
	if ($netinfo['vlanc'])
		return 'std';
	elseif (considerConfiguredConstraint ($netinfo, 'VLANIPV4NET_LISTSRC'))
		return 'attn';
	else
		return '';
}

// implement similar logic for IPv6 networks
function trigger_ipv6net_vlanconfig ()
{
	if (!count (getVLANDomainOptions())) // no domains -- no VLANs to bind with
		return '';
	$netinfo = spotEntity ('ipv6net', $_REQUEST['id']);
	if ($netinfo['vlanc'])
		return 'std';
	elseif (considerConfiguredConstraint ($netinfo, 'VLANIPV4NET_LISTSRC'))
		return 'attn';
	else
		return '';
}

function trigger_vlan_ipv4net ()
{
	$vlan_info = getVLANInfo ($_REQUEST['vlan_ck']);
	return count ($vlan_info['ipv4nets']) ? 'std' : 'attn';
}

function trigger_vlan_ipv6net ()
{
	$vlan_info = getVLANInfo ($_REQUEST['vlan_ck']);
	return count ($vlan_info['ipv6nets']) ? 'std' : 'attn';
}

function trigger_object_8021qports ()
{
	global $sic;
	if (NULL === getVLANSwitchInfo ($sic['object_id']))
		return '';
	return count (getStored8021QConfig ($sic['object_id'], 'desired')) ? 'std' : '';
}

function trigger_object_8021qsync ()
{
	global $sic;
	if (NULL === $vswitch = getVLANSwitchInfo ($sic['object_id']))
		return '';
	return $vswitch['out_of_sync'] == 'yes' ? 'attn' : 'std';
}

function trigger_LiveCDP ()
{
	return trigger_anyDP ('getcdpstatus', 'CDP_RUNNERS_LISTSRC');
}

function trigger_LiveLLDP ()
{
	return trigger_anyDP ('getlldpstatus', 'LLDP_RUNNERS_LISTSRC');
}

function trigger_anyDP ($command, $constraint)
{
	if
	(
		validBreedFunction (detectDeviceBreed (getBypassValue()), $command) and
		considerConfiguredConstraint (spotEntity ('object', getBypassValue()), $constraint)
	)
		return 'std';
	return '';
}

// tease rules editor tab, when the VST has no rules
function trigger_vst_editrules()
{
	$vst = spotEntity ('vst', getBypassValue());
	return $vst['rulec'] ? 'std' : 'attn';
}

function triggerIPAddressLog ()
{
	$ip_bin = assertIPArg ('ip');
	switch (strlen ($ip_bin))
	{
		case 4:
			$result = usePreparedSelectBlade ("SELECT COUNT(id) FROM IPv4Log WHERE ip = ?", array (ip4_bin2db ($ip_bin)));
			break;
		case 16:
			$result = usePreparedSelectBlade ("SELECT COUNT(id) FROM IPv6Log WHERE ip = ?", array ($ip_bin));
			break;
	}
	if ($row = $result->fetch(PDO::FETCH_NUM))
		if ($row[0] > 0)
			return 'std';
	return '';
}

function triggerCactiGraphs ()
{
	if ('' == getConfigVar ('CACTI_URL'))
		return '';
	if
	(
		count (getCactiGraphsForObject (getBypassValue())) or
		considerConfiguredConstraint (spotEntity ('object', getBypassValue()), 'CACTI_LISTSRC')
	)
		return 'std';
	else
		return '';
}

?>
