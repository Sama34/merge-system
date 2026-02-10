<?php
/**
 * MyBB 1.8 Merge System
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/download/merge-system/license/
 */

declare(strict_types=1);

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class WBB4_Converter_Module_Posts extends Converter_Module_Posts
{

    var array $settings = [
        'friendly_name' => 'posts',
        'progress_column' => 'postID',
        'default_per_screen' => 1000,
        'check_table_type' => 'post',
    ];

    function pre_setup(): void
    {
        // We need to do that as WBB uses different prefixes and we cant set it above
        $this->settings['check_table_type'] = WBB_PREFIX . $this->settings['check_table_type'];
    }

    function import(): void
    {
        global $import_session;

        $query = $this->old_db->query(
            "SELECT p.*, t.boardID AS fid
				FROM " . WBB_PREFIX . "post p
				LEFT JOIN " . WBB_PREFIX . "thread t ON(t.threadID=p.threadID)
				LIMIT {$this->trackers['start_posts']}, {$import_session['posts_per_screen']}"
        );

        while ($post = $this->old_db->fetch_array($query)) {
            $this->insert($post);
        }
    }

    function convert_data(array $data): array
    {
        $insert_data = [];

        // WBB 4 values
        $insert_data['import_pid'] = $data['postID'];
        $insert_data['tid'] = $this->get_import->tid($data['threadID']);

        $insert_data['fid'] = $this->get_import->fid($data['fid']);
        $insert_data['subject'] = encode_to_utf8($data['subject'], WBB_PREFIX . "post", "posts");
        $insert_data['uid'] = $this->get_import->uid($data['userID']);
        $insert_data['import_uid'] = $data['userID'];
        $insert_data['username'] = $this->get_import->username($data['userID'], $data['username']);
        $insert_data['dateline'] = $data['time'];
        $insert_data['message'] = encode_to_utf8(
            $this->bbcode_parser->convert($data['message']),
            WBB_PREFIX . "post",
            "posts"
        );
        $insert_data['ipaddress'] = my_inet_pton($data['ipAddress']);
        $insert_data['includesig'] = $data['showSignature'];

        return $insert_data;
    }

    function after_insert(array $data, array $insert_data, int $pid): void
    {
        global $db;

        // Restore first post connections
        $db->update_query(
            "threads",
            ['firstpost' => $pid],
            "tid = '{$insert_data['tid']}' AND import_firstpost = '{$insert_data['import_pid']}'"
        );
        if ($db->affected_rows() == 0) {
            $query = $db->simple_select("threads", "firstpost", "tid = '{$insert_data['tid']}'");
            $first_post = $db->fetch_field($query, "firstpost");
            $db->free_result($query);
            $db->update_query("posts", ['replyto' => $first_post], "pid = '{$pid}'");
        }
    }

    function fetch_total(): int
    {
        global $import_session;

        // Get number of posts
        if (!isset($import_session['total_posts'])) {
            $query = $this->old_db->simple_select(WBB_PREFIX . "post", "COUNT(*) as count");
            $import_session['total_posts'] = $this->old_db->fetch_field($query, 'count');
            $this->old_db->free_result($query);
        }

        return $import_session['total_posts'];
    }
}

