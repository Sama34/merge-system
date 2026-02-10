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

class XENFORO_Converter extends Converter
{

    /**
     * String of the bulletin board name
     *
     * @var string
     */
    var $bbname = "Xenforo 1";

    /**
     * String of the plain bulletin board name
     *
     * @var string
     */
    var $plain_bbname = "Xenforo 1";

    /**
     * Whether or not this module requires the loginconvert.php plugin
     *
     * @var boolean
     */
    var $requires_loginconvert = true;

    /**
     * Array of all of the modules
     *
     * @var array
     */
    var $modules = [
        "db_configuration" => ["name" => "Database Configuration", "dependencies" => ""],
        "import_usergroups" => ["name" => "Usergroups", "dependencies" => "db_configuration"],
        "import_users" => ["name" => "Users", "dependencies" => "db_configuration,import_usergroups"],
        "import_forums" => ["name" => "Forums", "dependencies" => "db_configuration,import_users"],
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
    var $check_table = "ip";

    /**
     * The table prefix we suggest to use
     *
     * @var String
     */
    var $prefix_suggestion = "xf_";

    /**
     * An array of xenforo -> mybb groups
     *
     * @var array
     */
    var $groups = [
        1 => MYBB_GUESTS, // Guests
        2 => MYBB_REGISTERED, // Registered
        3 => MYBB_ADMINS, // Administrators
        4 => MYBB_MODS, // Moderators
    ];

    /**
     * An array of supported databases
     * XenForo only supports MySQL
     */
    var $supported_databases = ["mysql"];
}

