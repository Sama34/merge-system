<?php
/**
 * MyBB 1.8 Merge System
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/download/merge-system/license/
 */

abstract class Converter_Module_Events extends Converter_Module
{
    public array $default_values = [
        'cid' => 0,
        'uid' => 0,
        'name' => '',
        'description' => '',
        'visible' => 0,
        'private' => 0,
        'dateline' => 0,
        'starttime' => 0,
        'endtime' => 0,
        'timezone' => '',
        'ignoretimezone' => 0,
        'usingtime' => 0,
        'repeats' => ''
    ];

    public array $integer_fields = [
        'cid',
        'uid',
        'visible',
        'private',
        'dateline',
        'starttime',
        'endtime',
        'ignoretimezone',
        'usingtime',
    ];

    /**
     * Insert an event into database
     *
     * @param array $data The insert array going into the MyBB database
     * @return int The new id
     */
    public function insert(array $data): bool|int
    {
        global $db, $output;

        $output->print_progress("start", $data[$this->settings['progress_column']]);

        $this->debug->log->datatrace('$data', $data);

        // Call our currently module's process function
        $data = $this->convert_data($data);

        // Should loop through and fill in any values that aren't set based on the MyBB db schema or other standard default values and escape them properly
        $insert_array = $this->prepare_insert_array($data, 'events');

        $this->debug->log->datatrace('$insert_array', $insert_array);

        $db->insert_query("events", $insert_array);
        $eid = $db->insert_id();

        $this->increment_tracker('events');

        $output->print_progress("end");

        return $eid;
    }
}


