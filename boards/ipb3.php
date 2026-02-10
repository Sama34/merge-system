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

class IPB3_Converter extends Converter
{

    /**
     * String of the bulletin board name
     *
     * @var string
     */
    var string $bbname = "Invision Power Board 3";

    /**
     * String of the plain bulletin board name
     *
     * @var string
     */
    var string $plain_bbname = "Invision Power Board 3";

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
        "import_usergroups" => ["name" => "Usergroups", "dependencies" => "db_configuration"],
        "import_users" => ["name" => "Users", "dependencies" => "db_configuration,import_usergroups"],
        "import_forums" => ["name" => "Forums", "dependencies" => "db_configuration,import_users"],
        "import_forumperms" => ["name" => "Forum Permissions", "dependencies" => "db_configuration,import_forums"],
        "import_threads" => ["name" => "Threads", "dependencies" => "db_configuration,import_forums"],
        "import_polls" => ["name" => "Polls", "dependencies" => "db_configuration,import_threads"],
        "import_pollvotes" => ["name" => "Poll Votes", "dependencies" => "db_configuration,import_polls"],
        "import_posts" => ["name" => "Posts", "dependencies" => "db_configuration,import_threads"],
        "import_moderators" => [
            "name" => "Moderators",
            "dependencies" => "db_configuration,import_forums,import_users"
        ],
        "import_privatemessages" => [
            "name" => "Private Messages",
            "dependencies" => "db_configuration,import_users"
        ],
        "import_settings" => ["name" => "Settings", "dependencies" => "db_configuration"],
        "import_avatars" => ["name" => "Avatars", "dependencies" => "db_configuration,import_users"],
        "import_attachments" => ["name" => "Attachments", "dependencies" => "db_configuration,import_posts"],
    ];

    /**
     * The table we check to verify it's "our" database
     *
     * @var String
     */
    var string $check_table = "forum_perms";

    /**
     * The table prefix we suggest to use
     *
     * @var String
     */
    var string $prefix_suggestion = "";

    /**
     * An array of ipb3 -> mybb groups
     *
     * @var array
     */
    var array $groups = [
        1 => MYBB_AWAITING, // Awaiting Activation
        2 => MYBB_GUESTS, // Guests
        3 => MYBB_REGISTERED, // Registered
        4 => MYBB_ADMINS, // Root Admin
        5 => MYBB_BANNED, // Banned
        6 => MYBB_MODS, // Moderators
    ];

    /**
     * An array of supported databases
     * IPB only supports MySQL
     */
    var array $supported_databases = ["mysql"];

    var array $column_length_to_check = [
        'topics' => [
            'threads' => [
                'title' => 'subject'
            ],
            'posts' => [
                'title' => 'subject'
            ]
        ]
    ];

    function db_connect(): void
    {
        parent::db_connect();

        // The calendar is optional so test it directly after the db is connected
        if ($this->old_db->table_exists("cal_events")) {
            $this->modules["import_events"] = [
                "name" => "Calendar Events",
                "dependencies" => "db_configuration,import_users"
            ];
        }
    }
}

