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

class XENFORO2_Converter extends Converter
{
    /**
     * String of the bulletin board name
     *
     * @var string
     */
    var string $bbname = "Xenforo 2";

    /**
     * String of the plain bulletin board name
     *
     * @var string
     */
    var string $plain_bbname = "Xenforo 2";

    /**
     * Whether or not this module requires the loginconvert.php plugin
     *
     * @var boolean
     */
    var bool $requires_loginconvert = true;

    /**
     * Array of all of the modules
     *
     * @var array
     */
    var array $modules = [
        "db_configuration" => ["name" => "Database Configuration", "dependencies" => ""],
        "import_settings" => ["name" => "Settings", "dependencies" => "db_configuration"],
        "import_usergroups" => ["name" => "Usergroups", "dependencies" => "db_configuration"],
        "import_users" => ["name" => "Users", "dependencies" => "db_configuration,import_usergroups"],
        "import_forums" => ["name" => "Forums", "dependencies" => "db_configuration,import_users"],
        "import_moderators" => [
            "name" => "Moderators",
            "dependencies" => "db_configuration,import_forums,import_users"
        ],
        "import_threads" => ["name" => "Threads", "dependencies" => "db_configuration,import_forums"],
        "import_polls" => ["name" => "Polls", "dependencies" => "db_configuration,import_threads"],
        "import_pollvotes" => ["name" => "Poll Votes", "dependencies" => "db_configuration,import_polls"],
        "import_posts" => ["name" => "Posts", "dependencies" => "db_configuration,import_threads"],
        "import_privatemessages" => [
            "name" => "Private Messages",
            "dependencies" => "db_configuration,import_users"
        ],
        "import_avatars" => ["name" => "Avatars", "dependencies" => "db_configuration,import_users"],
        "import_attachments" => ["name" => "Attachments", "dependencies" => "db_configuration,import_posts"],
    ];

    /**
     * The table we check to verify it's "our" database
     *
     * @var String
     */
    var string $check_table = "ip";

    /**
     * The table prefix we suggest to use
     *
     * @var String
     */
    var string $prefix_suggestion = "xf_";

    /**
     * An array of xenforo -> mybb groups
     *
     * @var array
     */
    var array $groups = [
        1 => MYBB_GUESTS, // Guests
        2 => MYBB_REGISTERED, // Registered
        3 => MYBB_ADMINS, // Administrators
        4 => MYBB_MODS, // Moderators
    ];

    /**
     * An array of supported databases
     * XenForo only supports MySQL
     */
    var array $supported_databases = ["mysql"];

    var array $column_length_to_check = [
        "user_group" => [
            "usergroups" => [
                "title" => "title",
                "username_css" => "namestyle",
            ],
        ],
        "user" => [
            "users" => [
                "username" => "username",
                "email" => "email",
            ],
        ],
        "user_profile" => [
            "users" => [
                "website" => "website",
            ],
        ],
        "thread" => [
            "threads" => [
                "title" => "subject",
            ],
        ],
        "post" => [
            "posts" => [
                "message" => "message",
            ],
        ],
    ];

    /**
     * Get imported thread and cache it during script processing.
     */
    var array $cache_threads = [];

    function get_thread(int $tid): array
    {
        global $db;

        if (isset($this->cache_threads[$tid])) {
            return $this->cache_threads[$tid];
        }

        $query = $db->simple_select("threads", "fid,subject,dateline,visible", "tid='{$tid}'", ["limit" => 1]);
        $thread = $db->fetch_array($query);
        $db->free_result($query);

        $this->cache_threads[$tid] = $thread;
        return $this->cache_threads[$tid];
    }
}

