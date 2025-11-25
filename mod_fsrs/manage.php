<?php

require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/fsrs/lib.php');

$id = required_param('id', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$topicid = optional_param('topicid', 0, PARAM_INT);
$flashcardid = optional_param('flashcardid', 0, PARAM_INT);

$cm = get_coursemodule_from_id('fsrs', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$fsrs = $DB->get_record('fsrs', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/fsrs:manage', $context);

$PAGE->set_url('/mod/fsrs/manage.php', ['id' => $cm->id, 'action' => $action, 'topicid' => $topicid, 'flashcardid' => $flashcardid]);
$PAGE->set_title(format_string($fsrs->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$manager = new \mod_fsrs\manager();

if ($action === 'addtopic' || $action === 'edittopic') {
    $form = new \mod_fsrs\form\topic_form(null, ['fsrsid' => $fsrs->id]);

    if ($action === 'edittopic') {
        $topic = $manager->get_topic($topicid, $fsrs->id);
        if (!$topic) {
            print_error('invalidtopicid', 'mod_fsrs');
        }
        $form->set_data($topic);
    } else {
        $form->set_data(['fsrsid' => $fsrs->id]);
    }

    if ($form->is_cancelled()) {
        redirect(new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id]));
    } else if ($data = $form->get_data()) {
        $data->fsrsid = $fsrs->id;
        if (!empty($data->id)) {
            $manager->update_topic($data->id, $data);
            redirect(new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id]), get_string('topicupdated', 'mod_fsrs'));
        } else {
            $manager->add_topic($data);
            redirect(new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id]), get_string('topiccreated', 'mod_fsrs'));
        }
    }

    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
    exit;
}

if ($action === 'deletetopic') {
    require_sesskey();
    $topic = $manager->get_topic($topicid, $fsrs->id);
    if (!$topic) {
        print_error('invalidtopicid', 'mod_fsrs');
    }
    $manager->delete_topic($topicid);
    redirect(new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id]), get_string('topicdeleted', 'mod_fsrs'));
}

if ($action === 'addflashcard' || $action === 'editflashcard') {
    $topics = $manager->get_topics($fsrs->id);
    $topicoptions = [];
    foreach ($topics as $topic) {
        $topicoptions[$topic->id] = format_string($topic->name);
    }

    if (empty($topicoptions)) {
        redirect(new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id]), get_string('notopicsmanage', 'mod_fsrs'));
    }

    $form = new \mod_fsrs\form\flashcard_form(null, ['topics' => $topicoptions]);

    if ($action === 'editflashcard') {
        $flashcard = $manager->get_flashcard($flashcardid, $fsrs->id);
        if (!$flashcard) {
            print_error('invalidflashcardid', 'mod_fsrs');
        }
        $form->set_data($flashcard);
    } else {
        $form->set_data(['fsrsid' => $fsrs->id, 'topicid' => $topicid]);
    }

    if ($form->is_cancelled()) {
        redirect(new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id]));
    } else if ($data = $form->get_data()) {
        $data->fsrsid = $fsrs->id;
        if (!empty($data->id)) {
            $manager->update_flashcard($data->id, $data);
            redirect(new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id]), get_string('flashcardupdated', 'mod_fsrs'));
        } else {
            $manager->add_flashcard($data);
            redirect(new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id]), get_string('flashcardcreated', 'mod_fsrs'));
        }
    }

    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
    exit;
}

if ($action === 'deleteflashcard') {
    require_sesskey();
    $flashcard = $manager->get_flashcard($flashcardid, $fsrs->id);
    if (!$flashcard) {
        print_error('invalidflashcardid', 'mod_fsrs');
    }
    $manager->delete_flashcard($flashcardid);
    redirect(new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id]), get_string('flashcarddeleted', 'mod_fsrs'));
}

$topics = $manager->get_topics($fsrs->id);
$topicdata = [];

foreach ($topics as $topic) {
    $flashcards = $manager->get_flashcards($topic->id);
    $flashcarddata = [];

    foreach ($flashcards as $flashcard) {
        $flashcarddata[] = [
            'id' => $flashcard->id,
            'front' => format_text($flashcard->front, FORMAT_HTML, ['context' => $context]),
            'back' => format_text($flashcard->back, FORMAT_HTML, ['context' => $context]),
            'editflashcardurl' => (new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id, 'action' => 'editflashcard', 'flashcardid' => $flashcard->id]))->out(false),
            'deleteflashcardurl' => (new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id, 'action' => 'deleteflashcard', 'flashcardid' => $flashcard->id, 'sesskey' => sesskey()]))->out(false),
        ];
    }

    $topicdata[] = [
        'id' => $topic->id,
        'name' => format_string($topic->name),
        'description' => format_text($topic->description ?? '', FORMAT_HTML, ['context' => $context]),
        'flashcards' => $flashcarddata,
        'edittopicurl' => (new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id, 'action' => 'edittopic', 'topicid' => $topic->id]))->out(false),
        'deletetopicurl' => (new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id, 'action' => 'deletetopic', 'topicid' => $topic->id, 'sesskey' => sesskey()]))->out(false),
        'addflashcardurl' => (new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id, 'action' => 'addflashcard', 'topicid' => $topic->id]))->out(false),
        'manageflashcardsurl' => (new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id, 'topicid' => $topic->id]))->out(false),
    ];
}

$templatecontext = [
    'fsrsname' => format_string($fsrs->name),
    'addtopicurl' => (new moodle_url('/mod/fsrs/manage.php', ['id' => $cm->id, 'action' => 'addtopic']))->out(false),
    'topics' => $topicdata,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_fsrs/manage', $templatecontext);
echo $OUTPUT->footer();
