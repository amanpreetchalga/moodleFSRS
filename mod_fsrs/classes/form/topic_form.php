<?php

namespace mod_fsrs\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for creating and editing topics.
 */
class topic_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'fsrsid');
        $mform->setType('fsrsid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('topicname', 'mod_fsrs'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('textarea', 'description', get_string('topicdescription', 'mod_fsrs'), ['rows' => 5, 'cols' => 60]);
        $mform->setType('description', PARAM_RAW);

        $mform->addActionButtons(true, get_string('savetopic', 'mod_fsrs'));
    }
}
