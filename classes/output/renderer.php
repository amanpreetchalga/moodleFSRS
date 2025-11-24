<?php

namespace mod_fsrs\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

class renderer extends plugin_renderer_base {
    public function render_flashcard(flashcard $flashcard): string {
        return $this->render_from_template('mod_fsrs/flashcard', $flashcard->export_for_template($this));
    }
}