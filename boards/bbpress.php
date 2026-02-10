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

class BBPRESS_Converter extends Converter
{

    /**
     * String of the bulletin board name
     *
     * @var string
     */
    var string $bbname = "BBPress 2.5";

    /**
     * String of the plain bulletin board name
     *
     * @var string
     */
    var string $plain_bbname = "BBPress 2.5";

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
        "import_users" => ["name" => "Users", "dependencies" => "db_configuration"],
        "import_forums" => ["name" => "Forums", "dependencies" => "db_configuration,import_users"],
        "import_threads" => ["name" => "Threads", "dependencies" => "db_configuration,import_forums"],
        "import_posts" => ["name" => "Posts", "dependencies" => "db_configuration,import_threads"],
        "import_avatars" => ["name" => "Avatars", "dependencies" => "db_configuration,import_users"],
    ];

    /**
     * The table we check to verify it's "our" database
     *
     * @var String
     */
    var string $check_table = "usermeta";

    /**
     * The table prefix we suggest to use
     *
     * @var String
     */
    var string $prefix_suggestion = "wp_";

    /**
     * An array of bbpress -> mybb groups
     * bbpress doesn't use id's but names
     *
     * @var array
     */
    var array $groups = [
        "bbp_blocked" => MYBB_BANNED, // Banned
        "bbp_spectator" => MYBB_REGISTERED, // Registered
        "bbp_participant" => MYBB_REGISTERED, // Registered
        "bbp_moderator" => MYBB_SMODS, // Super Moderators
        "bbp_keymaster" => MYBB_ADMINS, // Administrators
    ];

    /**
     * What BBCode Parser we're using
     *
     * @var String
     */
    var string $parser_class = "html";

    /**
     * An array of supported databases
     * WordPress (and therefor bbPress) only support MySQL
     */
    var array $supported_databases = ["mysql"];

    /**
     * Convert a serialized list of original roes in one of mybb
     *
     * @param string $gids
     * @param int|array $remove
     * @return string group id(s)
     */
    function get_group_id(string $gids, int|array $remove = []): string
    {
        // bbPress saves roles as ["name" => true]
        $roles = array_keys(unserialize($gids));

        // A user can have multiple roles but only one for bbPress
        foreach ($roles as $role) {
            // We found our role so return it
            if (isset($this->groups[$role])) {
                return $this->groups[$role];
            }
        }

        // The user hadn't a role but he was registered
        return MYBB_REGISTERED;
    }
}

