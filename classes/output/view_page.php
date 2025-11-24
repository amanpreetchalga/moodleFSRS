<?php

namespace mod_fsrs\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;

class view_page implements renderable {
    public function render(renderer_base $output): string {
        return $output->heading('FSRS plugin loaded successfully.');
    }
}