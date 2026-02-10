<?php
/**
 * MyBB 1.8 Merge System
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/download/merge-system/license/
 */

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class WBB4_Converter_Module_Attachments extends Converter_Module_Attachments
{

    var array $settings = [
        'friendly_name' => 'attachments',
        'progress_column' => 'attachmentID',
        'default_per_screen' => 20,
    ];

    var int $objectID;

    public string $path_column = "attachmentID,fileHash";

    function pre_setup(): void
    {
        $this->test_table = WCF_PREFIX . "attachment";

        parent::pre_setup();

        // Don't ask - wbb...
        $class = $this->old_db->escape_string("wbb\system\attachment\PostAttachmentObjectType");
        $query = $this->old_db->simple_select(WCF_PREFIX . "object_type", "objectTypeID", "className='{$class}'");
        $this->objectID = $this->old_db->fetch_field($query, "objectTypeID");
        $this->old_db->free_result($query);
    }

    function get_upload_path(): string
    {
        $query = $this->old_db->simple_select(WCF_PREFIX . "application", "domainName,domainPath", "isPrimary='1'");
        $data = $this->old_db->fetch_array($query);
        return "http://" . $data['domainName'] . $data['domainPath'] . "wcf/attachments/";
    }

    function import(): void
    {
        global $import_session;

        $query = $this->old_db->simple_select(
            WCF_PREFIX . "attachment",
            "*",
            "objectTypeID='{$this->objectID}'",
            [
                'limit_start' => $this->trackers['start_attachments'],
                'limit' => $import_session['attachments_per_screen']
            ]
        );
        while ($attachment = $this->old_db->fetch_array($query)) {
            $this->insert($attachment);
        }
    }

    function convert_data(array $data): array
    {
        global $db;

        $insert_data = [];

        // WBB 4 values
        $insert_data['import_aid'] = $data['attachmentID'];
        $insert_data['uid'] = $this->get_import->uid($data['userID']);
        $insert_data['filename'] = $data['filename'];
        $insert_data['filetype'] = $data['fileType'];
        $insert_data['filesize'] = $data['filesize'];
        $insert_data['downloads'] = $data['downloads'];

        $attach_details = $this->get_import->post_attachment_details($data['objectID']);

        $insert_data['pid'] = $attach_details['pid'];
        $insert_data['posthash'] = md5($attach_details['tid'] . $attach_details['uid'] . random_str());

        // Check if this is an image
        if ($data['isImage']) {
            $insert_data['thumbnail'] = 'SMALL';
        } else {
            $insert_data['thumbnail'] = '';
        }

        // Build name and check whether it's already in use
        $insert_data['attachname'] = "post_" . $insert_data['uid'] . "_" . $data['uploadTime'] . ".attach";
        $query = $db->simple_select(
            "attachments",
            "aid",
            "attachname='" . $db->escape_string($insert_data['attachname']) . "'"
        );
        if ($db->num_rows($query) > 0) {
            $insert_data['attachname'] = "post_" . $insert_data['uid'] . "_" . $data['uploadTime'] . "_" . $data['attachmentID'] . ".attach";
        }

        return $insert_data;
    }

    function generate_raw_filename(array $attach): string
    {
        $dir = substr($attach['fileHash'], 0, 2);
        return "{$dir}/{$attach['attachmentID']}-{$attach['fileHash']}";
    }

    function fetch_total(): int
    {
        global $import_session;

        // Get number of attachments
        if (!isset($import_session['total_attachments'])) {
            // We need the ID...
            $this->pre_setup();

            $query = $this->old_db->simple_select(
                WCF_PREFIX . "attachment",
                "COUNT(*) as count",
                "objectTypeID='{$this->objectID}'"
            );
            $import_session['total_attachments'] = $this->old_db->fetch_field($query, 'count');
            $this->old_db->free_result($query);
        }

        return $import_session['total_attachments'];
    }
}


