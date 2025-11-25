<?php

namespace mod_fsrs;

defined('MOODLE_INTERNAL') || die();

class manager {
    /**
     * Get a single topic ensuring it belongs to the FSRS instance.
     *
     * @param int $id
     * @param int $fsrsid
     * @return \stdClass|null
     */
    public function get_topic(int $id, int $fsrsid): ?\stdClass {
        global $DB;

        return $DB->get_record('fsrs_topic', ['id' => $id, 'fsrsid' => $fsrsid]);
    }

    /**
     * Get topics for an FSRS instance ordered by sortorder then id.
     *
     * @param int $fsrsid
     * @return array
     */
    public function get_topics(int $fsrsid): array {
        global $DB;

        return $DB->get_records('fsrs_topic', ['fsrsid' => $fsrsid], 'sortorder ASC, id ASC');
    }

    /**
     * Get flashcards for a topic ordered by sortorder then id.
     *
     * @param int $topicid
     * @return array
     */
    public function get_flashcards(int $topicid): array {
        global $DB;

        return $DB->get_records('fsrs_flashcard', ['topicid' => $topicid], 'sortorder ASC, id ASC');
    }

    /**
     * Get a flashcard ensuring it belongs to the FSRS instance.
     *
     * @param int $id
     * @param int $fsrsid
     * @return \stdClass|null
     */
    public function get_flashcard(int $id, int $fsrsid): ?\stdClass {
        global $DB;

        return $DB->get_record('fsrs_flashcard', ['id' => $id, 'fsrsid' => $fsrsid]);
    }

    /**
     * Add a topic.
     *
     * @param \stdClass $data
     * @return int
     */
    public function add_topic(\stdClass $data): int {
        global $DB;

        $data->timecreated = $data->timecreated ?? time();
        $data->timemodified = $data->timemodified ?? time();

        return $DB->insert_record('fsrs_topic', $data);
    }

    /**
     * Update a topic.
     *
     * @param int $id
     * @param \stdClass $data
     * @return bool
     */
    public function update_topic(int $id, \stdClass $data): bool {
        global $DB;

        $data->id = $id;
        $data->timemodified = time();

        return (bool) $DB->update_record('fsrs_topic', $data);
    }

    /**
     * Delete a topic.
     *
     * @param int $id
     * @return bool
     */
    public function delete_topic(int $id): bool {
        global $DB;

        $flashcardids = $DB->get_fieldset_select('fsrs_flashcard', 'id', 'topicid = :topicid', ['topicid' => $id]);

        if (!empty($flashcardids)) {
            list($flashcardsql, $params) = $DB->get_in_or_equal($flashcardids, SQL_PARAMS_NAMED);
            $DB->delete_records_select('fsrs_review_log', "flashcardid {$flashcardsql}", $params);
            $DB->delete_records_select('fsrs_user_card', "flashcardid {$flashcardsql}", $params);
            $DB->delete_records_select('fsrs_flashcard', "id {$flashcardsql}", $params);
        }

        return (bool) $DB->delete_records('fsrs_topic', ['id' => $id]);
    }

    /**
     * Add a flashcard.
     *
     * @param \stdClass $data
     * @return int
     */
    public function add_flashcard(\stdClass $data): int {
        global $DB;

        $data->timecreated = $data->timecreated ?? time();
        $data->timemodified = $data->timemodified ?? time();

        return $DB->insert_record('fsrs_flashcard', $data);
    }

    /**
     * Update a flashcard.
     *
     * @param int $id
     * @param \stdClass $data
     * @return bool
     */
    public function update_flashcard(int $id, \stdClass $data): bool {
        global $DB;

        $data->id = $id;
        $data->timemodified = time();

        return (bool) $DB->update_record('fsrs_flashcard', $data);
    }

    /**
     * Delete a flashcard.
     *
     * @param int $id
     * @return bool
     */
    public function delete_flashcard(int $id): bool {
        global $DB;

        $DB->delete_records('fsrs_review_log', ['flashcardid' => $id]);
        $DB->delete_records('fsrs_user_card', ['flashcardid' => $id]);

        return (bool) $DB->delete_records('fsrs_flashcard', ['id' => $id]);
    }
}
