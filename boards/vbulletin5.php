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

class VBULLETIN5_Converter extends Converter
{

    /**
     * String of the bulletin board name
     *
     * @var string
     */
    var string $bbname = "vBulletin 5";

    /**
     * String of the plain bulletin board name
     *
     * @var string
     */
    var string $plain_bbname = "vBulletin 5";

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
//						 "import_polls" => array("name" => "Polls", "dependencies" => "db_configuration,import_threads"),
//						 "import_pollvotes" => array("name" => "Poll Votes", "dependencies" => "db_configuration,import_polls"),
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
        "import_events" => ["name" => "Calendar Events", "dependencies" => "db_configuration,import_users"],
        "import_avatars" => ["name" => "Avatars", "dependencies" => "db_configuration,import_users"],
        "import_attachments" => ["name" => "Attachments", "dependencies" => "db_configuration,import_posts"],
    ];

    /**
     * The table we check to verify it's "our" database
     *
     * @var String
     */
    var string $check_table = "forumpermission";

    /**
     * The table prefix we suggest to use
     *
     * @var String
     */
    var string $prefix_suggestion = "";

    /**
     * An array of vb4 -> mybb groups
     *
     * @var array
     */
    var array $groups = [
        1 => MYBB_GUESTS, // Guests
        2 => MYBB_REGISTERED, // Registered
        3 => MYBB_AWAITING, // Awaiting activation
        4 => MYBB_REGISTERED, // Registered coppa
        5 => MYBB_SMODS, // Super Moderators
        6 => MYBB_ADMINS, // Administrators
        7 => MYBB_MODS, // Moderators
        8 => MYBB_BANNED, // Banned
    ];

    /**
     * An array of supported databases
     * vB only supports MySQL
     */
    var array $supported_databases = ["mysql"];

    var array $column_length_to_check = [
        'post' => [
            'posts' => [
                'pagetext' => 'message'
            ]
        ],
        'user' => [
            'users' => [
                'icq' => 'icq'
            ]
        ]
    ];
}


