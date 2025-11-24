<?php

namespace mod_fsrs\output;

defined('MOODLE_INTERNAL') || die();

use context;
use moodle_url;
use renderable;
use renderer_base;
use templatable;

class flashcard implements renderable, templatable {
    private $id;
    private $front;
    private $back;
    private $topic;
    private $questiontype;
    private $reviewurl;
    private $pageurl;
    private $context;
    private $sesskey;

    public function __construct(\stdClass $record, context $context, moodle_url $reviewurl, moodle_url $pageurl, string $sesskey) {
        $this->id = $record->id;
        $this->front = $record->front;
        $this->back = $record->back;
        $this->topic = $record->topicname ?? '';
        $this->questiontype = $record->questiontype ?? 0;
        $this->reviewurl = $reviewurl;
        $this->pageurl = $pageurl;
        $this->context = $context;
        $this->sesskey = $sesskey;
    }

    public function export_for_template(renderer_base $output): array {
        return [
            'id' => $this->id,
            'front' => format_text($this->front, FORMAT_HTML, ['context' => $this->context]),
            'back' => format_text($this->back, FORMAT_HTML, ['context' => $this->context]),
            'topic' => format_string($this->topic, true, ['context' => $this->context]),
            'questiontype' => $this->questiontype,
            'reviewurl' => $this->reviewurl->out(false),
            'pageurl' => $this->pageurl->out(false),
            'sesskey' => $this->sesskey,
            'overlaytemplate' => get_string('nextreviewmessage', 'mod_fsrs'),
            'days' => 0,
            'showanswerlabel' => get_string('showanswer', 'mod_fsrs'),
            'buttons' => [
                ['rate' => 0, 'label' => get_string('again', 'mod_fsrs')],
                ['rate' => 1, 'label' => get_string('hard', 'mod_fsrs')],
                ['rate' => 2, 'label' => get_string('good', 'mod_fsrs')],
                ['rate' => 3, 'label' => get_string('easy', 'mod_fsrs')],
            ],
        ];
    }
}