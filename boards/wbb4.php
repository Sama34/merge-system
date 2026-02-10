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

class WBB4_Converter extends Converter
{
    /**
     * The installationnumber which can be set during configuration
     * It should be detected automatically, however if you experience problems, you can set it here directly
     *
     * @var int
     */
    var int $installationnumber;

    /**
     * String of the bulletin board name
     *
     * @var string
     */
    var string $bbname = "WoltLab Burning Board 4";

    /**
     * String of the plain bulletin board name
     *
     * @var string
     */
    var string $plain_bbname = "WBB 4";

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
        "import_forumperms" => [
            "name" => "Forum Permissions",
            "dependencies" => "db_configuration,import_forums,import_usergroups"
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
    var string $check_table = "sitemap";

    /**
     * The table prefix we suggest to use
     *
     * @var String
     */
    var string $prefix_suggestion = "";
    var bool $hide_table_prefix = true;

    /**
     * An array of wbb -> mybb groups
     *
     * @var array
     */
    var array $groups = [
        1 => MYBB_GUESTS, // All
        2 => MYBB_GUESTS, // Guests
        3 => MYBB_REGISTERED, // Registered
        4 => MYBB_ADMINS, // Administrators
        5 => MYBB_MODS, // Moderators
        6 => MYBB_SMODS, // Super Moderators
    ];

    /**
     * An array of supported databases
     * WBB only supports MySQL
     */
    var array $supported_databases = ["mysql"];

    function __construct()
    {
        global $import_session;

        parent::__construct();

        // The number was set during the configuration and saved as prefix
        if (empty($this->installationnumber) && !empty($import_session['old_tbl_prefix'])) {
            $this->installationnumber = (int)substr($import_session['old_tbl_prefix'], 3, 1);
            unset($import_session['old_tbl_prefix']);
            $import_session['wbb_number'] = $this->installationnumber;
        } else {
            if (empty($this->installationnumber) && !empty($import_session['wbb_number'])) {
                $this->installationnumber = (int)$import_session['wbb_number'];
            }
        }

        define("WCF_PREFIX", "wcf{$this->installationnumber}_");
        define("WBB_PREFIX", "wbb{$this->installationnumber}_");
    }

    function db_extra(): string
    {
        global $mybb, $lang;

        if (!empty($this->installationnumber)) {
            return '';
        }

        if (!isset($mybb->input['installationnumber'])) {
            $mybb->input['installationnumber'] = 1;
        }

        // This is a hack to fix the table prefix. It's always wbb{num}_1.
        echo '<script type="text/javascript">
			$(function() {
				$("#next_button > .submit_button").click(function(e) {
					var dbengine = $("#dbengine").val();
					$("#config_"+dbengine+"_tableprefix").val("wcf"+$("#installationnumber").val()+"_");
				});
			});
		</script>';

        return "<tbody>
		<tr>
			<tr>
				<th colspan=\"2\" class=\"first last\">{$lang->wbb_installationnumber}</th>
			</tr>
			<tr class=\"last\">
				<td class=\"first\"><label for=\"installationnumber\">{$lang->wbb_installationnumber_desc}</label></td>
				<td class=\"last alt_col\"><input type=\"text\" class=\"text_input\" name=\"installationnumber\" id=\"installationnumber\" value=\"" . htmlspecialchars_uni(
                $mybb->input['installationnumber']
            ) . "\" /></td>
			</td>
		</tr>
		</tbody>";
    }
}


