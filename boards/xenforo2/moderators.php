<?php
/**
 * MyBB 1.8 Merge System
 * Copyright 2019 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/download/merge-system/license/
 */

declare(strict_types=1);

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class XENFORO2_Converter_Module_Moderators extends Converter_Module_Moderators
{
    var array $settings = [
        'friendly_name' => 'moderators',
        'progress_column' => 'moderator_id',
        'default_per_screen' => 1000,
    ];

    function import(): void
    {
        global $import_session;

        $query = $this->old_db->simple_select(
            "moderator_content",
            "*",
            "content_type='node'",
            [
                'limit_start' => $this->trackers['start_moderators'],
                'limit' => $import_session['moderators_per_screen']
            ]
        );
        while ($moderator = $this->old_db->fetch_array($query)) {
            // Not a standard forum.
            if (!$this->get_import->fid($moderator['content_id'])) {
                $this->increment_tracker('moderators');
                continue;
            }
            $this->insert($moderator);
        }
    }

    function convert_data(array $data): array
    {
        $insert_data = [];

        // Xenforo 2 values
        $insert_data['fid'] = $this->get_import->fid($data['content_id']);
        $insert_data['id'] = $this->get_import->uid($data['user_id']);

        return $insert_data;
    }

    function fetch_total(): int
    {
        global $import_session;

        // Get number of moderators
        if (!isset($import_session['total_moderators'])) {
            $query = $this->old_db->simple_select("moderator_content", "COUNT(*) as count", "content_type='node'");
            $import_session['total_moderators'] = $this->old_db->fetch_field($query, 'count');
            $this->old_db->free_result($query);
        }

        return $import_session['total_moderators'];
    }
}

