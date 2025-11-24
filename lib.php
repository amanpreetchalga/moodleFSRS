<?php


defined('MOODLE_INTERNAL') || die();

function fsrs_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Add a new FSRS instance.
 */
function fsrs_add_instance($data, $mform) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = time();

    // Insert into main table 'fsrs'.
    $id = $DB->insert_record('fsrs', $data);

    return $id;
}

/**
 * Update FSRS instance.
 */
function fsrs_update_instance($data, $mform) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    return $DB->update_record('fsrs', $data);
}

/**
 * Delete FSRS instance.
 */
function fsrs_delete_instance($id) {
    global $DB;

    if (!$DB->record_exists('fsrs', ['id' => $id])) {
        return false;
    }

    // Delete child tables.
    $DB->delete_records('fsrs_topic', ['fsrsid' => $id]);
    $DB->delete_records('fsrs_flashcard', ['fsrsid' => $id]);

    // Delete main table.
    $DB->delete_records('fsrs', ['id' => $id]);

    return true;
}