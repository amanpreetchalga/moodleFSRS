<?php

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('fsrs', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$fsrs = $DB->get_record('fsrs', ['id' => $cm->instance], '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/fsrs/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($cm->name ?: $fsrs->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->css(new moodle_url('/mod/fsrs/styles.css'));
$PAGE->requires->js_call_amd('mod_fsrs/flashcard', 'init');

$renderer = $PAGE->get_renderer('mod_fsrs');

if (has_capability('mod/fsrs:manage', $context)) {
    $manageurl = new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id]);
}

$now = time();
$flashcard = $DB->get_record_sql(
    "SELECT fc.id, fc.front, fc.back, fc.questiontype, fc.topicid, t.name AS topicname,
            fuc.id AS usercardid, fuc.interval_days, fuc.due_at
       FROM {fsrs_user_card} fuc
       JOIN {fsrs_flashcard} fc ON fc.id = fuc.flashcardid
  LEFT JOIN {fsrs_topic} t ON t.id = fc.topicid
      WHERE fuc.userid = :userid AND fc.fsrsid = :fsrsid AND fuc.due_at <= :now
   ORDER BY fuc.due_at ASC, fuc.id ASC",
    [
        'userid' => $USER->id,
        'fsrsid' => $fsrs->id,
        'now' => $now,
    ]
);

echo $OUTPUT->header();

if (!empty($manageurl)) {
    echo $OUTPUT->single_button($manageurl, get_string('managedeck', 'mod_fsrs'), 'get');
}

if ($flashcard) {
    $flashcarddata = new \mod_fsrs\output\flashcard(
        $flashcard,
        $context,
        new moodle_url('/mod/fsrs/review.php'),
        $PAGE->url,
        sesskey()
    );
    echo $renderer->render_flashcard($flashcarddata);
} else {
    echo $OUTPUT->notification(get_string('nocards', 'mod_fsrs'), \core\output\notification::NOTIFY_INFO);
}

echo $OUTPUT->footer();
