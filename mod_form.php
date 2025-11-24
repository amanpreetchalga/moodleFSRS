<?php


require_once(dirname(__DIR__, 1) . '/config.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_fsrs_mod_form extends moodleform_mod {

    function definition() {
        $mform = $this->_form;

        // Activity name.
        $mform->addElement('text', 'name', get_string('fsrsname', 'mod_fsrs'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Standard intro / description fields.
        $this->standard_intro_elements();

        // Standard course module settings.
        $this->standard_coursemodule_elements();

        // Buttons.
        $this->add_action_buttons();
    }
}
