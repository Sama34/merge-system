<?php
/**
 * MyBB 1.8 Merge System
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/download/merge-system/license/
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class VBULLETIN4_Converter_Module_Moderators extends Converter_Module_Moderators {

	var $settings = array(
		'friendly_name' => 'moderators',
		'progress_column' => 'userid',
		'default_per_screen' => 1000,
	);

	function import()
	{
		global $import_session;

		$query_options = array();

		if(isset($this->trackers['start_mods']))
		{
			$query_options['limit_start'] = $this->trackers['start_mods'];
		}

		if(isset($import_session['mods_per_screen']))
		{
			$query_options['limit'] = $import_session['mods_per_screen'];
		}

		$query = $this->old_db->simple_select("moderator", "*", "forumid != '-1'", $query_options);

		while($moderator = $this->old_db->fetch_array($query))
		{
			$this->insert($moderator);
		}
	}

	function convert_data($data)
	{
		$insert_data = array();

		// vBulletin 4 values
		$insert_data['fid'] = $this->get_import->fid($data['forumid']);
		$insert_data['id'] = $this->get_import->uid($data['userid']);

		return $insert_data;
	}

	function fetch_total()
	{
		global $import_session;

		// Get number of moderators
		if(!isset($import_session['total_moderators']))
		{
			$query = $this->old_db->simple_select("moderator", "COUNT(*) as count", "forumid != '-1'");
			$import_session['total_moderators'] = $this->old_db->fetch_field($query, 'count');
			$this->old_db->free_result($query);
		}

		return $import_session['total_moderators'];
	}
}

