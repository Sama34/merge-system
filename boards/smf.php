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

class SMF_Converter extends Converter
{

    /**
     * String of the bulletin board name
     *
     * @var string
     */
    var $bbname = "SMF 1.1";

    /**
     * String of the plain bulletin board name
     *
     * @var string
     */
    var $plain_bbname = "SMF 1";

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
        "import_categories" => ["name" => "Categories", "dependencies" => "db_configuration,import_users"],
        "import_forums" => ["name" => "Forums", "dependencies" => "db_configuration,import_categories"],
        "import_forumperms" => ["name" => "Forum Permissions", "dependencies" => "db_configuration,import_forums"],
        "import_threads" => ["name" => "Threads", "dependencies" => "db_configuration,import_forums"],
        "import_polls" => ["name" => "Polls", "dependencies" => "db_configuration,import_threads"],
        "import_pollvotes" => ["name" => "Poll Votes", "dependencies" => "db_configuration,import_polls"],
        "import_posts" => ["name" => "Posts", "dependencies" => "db_configuration,import_threads"],
        "import_privatemessages" => [
            "name" => "Private Messages",
            "dependencies" => "db_configuration,import_users"
        ],
        "import_moderators" => [
            "name" => "Moderators",
            "dependencies" => "db_configuration,import_forums,import_users"
        ],
        "import_settings" => ["name" => "Settings", "dependencies" => "db_configuration"],
        "import_events" => ["name" => "Calendar Events", "dependencies" => "db_configuration,import_posts"],
        "import_avatars" => ["name" => "Avatars", "dependencies" => "db_configuration,import_users"],
        "import_attachments" => ["name" => "Attachments", "dependencies" => "db_configuration,import_posts"],
    ];

    /**
     * The table we check to verify it's "our" database
     *
     * @var String
     */
    var $check_table = "boards";

    /**
     * The table prefix we suggest to use
     *
     * @var String
     */
    var $prefix_suggestion = "smf_";

    /**
     * An array of smf -> mybb groups
     *
     * @var array
     */
    var $groups = [
        -1 => MYBB_GUESTS, // Guests
        0 => MYBB_REGISTERED, // Registered
        1 => MYBB_ADMINS, // Administrators
        2 => MYBB_SMODS, // Super Moderators
        3 => MYBB_MODS, // Moderators
    ];

    var $get_post_cache = [];

    /**
     * Get a post from the SMF database
     *
     * @param int $pid Post ID
     * @return array The post
     */
    function get_post($pid)
    {
        if (array_key_exists($pid, $this->get_post_cache)) {
            return $this->get_post_cache[$pid];
        }

        $pid = intval($pid);

        $query = $this->old_db->simple_select("messages", "*", "ID_MSG = '{$pid}'", ['limit' => 1]);
        $results = $this->old_db->fetch_array($query);
        $this->old_db->free_result($query);

        $this->get_post_cache[$pid] = $results;

        return $results;
    }
}


