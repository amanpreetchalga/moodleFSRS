<?php

namespace mod_fsrs\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for creating and editing flashcards.
 */
class flashcard_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        $topics = $this->_customdata['topics'] ?? [];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'fsrsid');
        $mform->setType('fsrsid', PARAM_INT);

        $mform->addElement('select', 'topicid', get_string('topic', 'mod_fsrs'), $topics);
        $mform->setType('topicid', PARAM_INT);
        $mform->addRule('topicid', null, 'required', null, 'client');

        $mform->addElement('textarea', 'front', get_string('flashcardfront', 'mod_fsrs'), ['rows' => 5, 'cols' => 60]);
        $mform->setType('front', PARAM_RAW);
        $mform->addRule('front', null, 'required', null, 'client');

        $mform->addElement('textarea', 'back', get_string('flashcardback', 'mod_fsrs'), ['rows' => 5, 'cols' => 60]);
        $mform->setType('back', PARAM_RAW);
        $mform->addRule('back', null, 'required', null, 'client');

        $mform->addActionButtons(true, get_string('saveflashcard', 'mod_fsrs'));
    }
}
