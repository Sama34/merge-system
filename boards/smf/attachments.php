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

class SMF_Converter_Module_Attachments extends Converter_Module_Attachments {

	var $settings = array(
		'friendly_name' => 'attachments',
		'progress_column' => 'ID_ATTACH',
		'default_per_screen' => 20,
	);

	var $thumbs = array();

	var $cache_attach_filenames = array();

	function pre_setup()
	{
		global $import_session, $mybb;

		// Set uploads path
		if(!isset($import_session['uploadspath']))
		{
			$query = $this->old_db->simple_select("settings", "value", "variable = 'attachmentUploadDir'", array('limit' => 1));
			$import_session['uploadspath'] = $this->old_db->fetch_field($query, 'value');
			$this->old_db->free_result($query);

			if(empty($import_session['uploadspath']))
			{
				$query = $this->old_db->simple_select("settings", "value", "variable = 'avatar_url'", array('limit' => 1));
				$import_session['uploadspath'] = str_replace('avatars', 'attachments', $this->old_db->fetch_field($query, 'value'));
				$this->old_db->free_result($query);
			}

			if(my_substr($import_session['uploadspath'], -1) != '/') {
				$import_session['uploadspath'] .= '/';
			}
		}

		$this->check_attachments_dir_perms();

		if($mybb->input['uploadspath'])
		{
			// Test our ability to read attachment files from the forum software
			if($this->old_db->field_exists("file_hash", "attachments"))
			{
				$this->path_column = "ID_ATTACH,file_hash";
			}
			else
			{
				$this->path_column = "ID_ATTACH,filename";
			}
			$this->test_readability("attachments");
		}
	}

	function import()
	{
		global $import_session;

		$query = $this->old_db->simple_select("attachments", "*", "attachmentType != '3' AND ID_MSG != '0'", array('limit_start' => $this->trackers['start_attachments'], 'limit' => $import_session['attachments_per_screen']));
		while($attachment = $this->old_db->fetch_array($query))
		{
			if(in_array($attachment['ID_ATTACH'], $this->thumbs))
			{
				continue;
			}

			$this->insert($attachment);
		}
	}

	function convert_data($data)
	{
		global $import_session;

		$insert_data = array();

		// SMF values
		$insert_data['import_aid'] = $data['ID_ATTACH'];

		$insert_data['uid'] = $this->get_import->uid($data['ID_MEMBER']);
		$insert_data['filename'] = $data['filename'];
		$insert_data['attachname'] = "post_".$insert_data['uid']."_".TIME_NOW."_".$data['ID_ATTACH'].".attach";

		if(function_exists("finfo_open"))
		{
			$file_info = finfo_open(FILEINFO_MIME);
			list($insert_data['filetype'], ) = explode(';', finfo_file($file_info, $import_session['uploadspath'].$this->generate_raw_filename($data)), 1);
			finfo_close($file_info);
		}
		else if(function_exists("mime_content_type"))
		{
			$insert_data['filetype'] = mime_content_type(get_extension($data['filename']));
		}
		else
		{
			$insert_data['filetype'] = '';
		}

		// Check if this is an image
		switch(strtolower($insert_data['filetype']))
		{
			case "image/gif":
			case "image/jpeg":
			case "image/x-jpg":
			case "image/x-jpeg":
			case "image/pjpeg":
			case "image/jpg":
			case "image/png":
			case "image/x-png":
				$is_image = 1;
				break;
			default:
				$is_image = 0;
				break;
		}

		// Check if this is an image
		if($is_image == 1)
		{
			$insert_data['thumbnail'] = 'SMALL';
		}
		else
		{
			$insert_data['thumbnail'] = '';
		}

		$insert_data['filesize'] = $data['size'];
		$insert_data['downloads'] = $data['downloads'];

		$attach_details = $this->get_import->post_attachment_details($data['ID_MSG']);
		$insert_data['pid'] = $attach_details['pid'];
		$insert_data['posthash'] = md5($attach_details['tid'].$attach_details['uid'].random_str());

		if($data['ID_THUMB'] != 0)
		{
			$this->thumbs[] = $data['ID_THUMB'];
			$thumbnail = $this->get_import_attach_filename($data['ID_THUMB']);
			$ext = get_extension($thumbnail['filename']);

			$insert_data['thumbnail'] = str_replace(".attach", "_thumb.$ext", $insert_data['attachname']);
		}

		return $insert_data;
	}

	function after_insert($data, $insert_data, $aid)
	{
		global $import_session, $mybb, $db, $lang;

		// Transfer attachment thumbnail
		if($data['ID_THUMB'] != 0)
		{
			// Transfer attachment thumbnail
			$attachment_thumbnail_file = merge_fetch_remote_file($import_session['uploadspath'].$this->generate_raw_filename($data));

			if(!empty($attachment_thumbnail_file))
			{
				$attachrs = @fopen($mybb->settings['uploadspath'].'/'.$insert_data['thumbnail'], 'w');
				if($attachrs)
				{
					@fwrite($attachrs, $attachment_thumbnail_file);
				}
				else
				{
					$this->board->set_error_notice_in_progress($lang->sprintf($lang->module_attachment_thumbnail_error, $aid));
				}
				@fclose($attachrs);
				@my_chmod($mybb->settings['uploadspath'].'/'.$insert_data['thumbnail'], '0777');
			}
			else
			{
				$this->board->set_error_notice_in_progress($lang->sprintf($lang->module_attachment_thumbnail_found, $aid));
			}
		}

		parent::after_insert($data, $insert_data, $aid);
	}

	function get_import_attach_filename($aid)
	{
		if(array_key_exists($aid, $this->cache_attach_filenames))
		{
			return $this->cache_attach_filenames[$aid];
		}

		$query = $this->old_db->simple_select("attachments", "filename", "ID_ATTACH = '{$aid}'");
		$thumbnail = $this->old_db->fetch_array($query, "filename");
		$this->old_db->free_result($query);

		$this->cache_attach_filenames[$aid] = $thumbnail;

		return $thumbnail;
	}

	function generate_raw_filename($attachment)
	{
		// If we're using the newer model, return it here.
		if(isset($attachment['file_hash']) && !empty($attachment['file_hash']))
		{
			return $attachment['ID_ATTACH']."_".$attachment['file_hash'];
		}

		// Welp, this is the way SMF does it, looks like - Legacy Method
		$attachment['filename'] = str_replace(array('????????????????????????????????????????????????????????????',
				'?',
				'?',
				'?',
				'?',
				'?',
				'?',
				'?',
				'?',
				'?',
				'?'),
			array('SZszYAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy',
				'TH',
				'th',
				'DH',
				'dh',
				'ss',
				'OE',
				'oe',
				'AE',
				'ae',
				'u'),
			$attachment['filename']);

		$attachment['filename'] = preg_replace(array('#\s#', '#[^\w_\.\-]#'), array('_', ''), $attachment['filename']);

		return $attachment['ID_ATTACH']."_".str_replace('.', '_', $attachment['filename']).md5($attachment['filename']);
	}

	function fetch_total()
	{
		global $import_session;

		// Get number of attachments
		if(!isset($import_session['total_attachments']))
		{
			$query = $this->old_db->simple_select("attachments", "COUNT(*) as count", "attachmentType != '3' AND ID_MSG != '0'");
			$import_session['total_attachments'] = $this->old_db->fetch_field($query, 'count');
			$this->old_db->free_result($query);
		}

		return $import_session['total_attachments'];
	}
}


