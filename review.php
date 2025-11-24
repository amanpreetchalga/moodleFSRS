<?php


require_once(dirname(__DIR__, 1) . '/config.php');


$flashcardid = required_param('flashcardid', PARAM_INT);
$rating = required_param('rating', PARAM_INT);

$flashcard = $DB->get_record_sql(
    "SELECT fc.*, f.course, f.id AS fsrsid
       FROM {fsrs_flashcard} fc
       JOIN {fsrs} f ON f.id = fc.fsrsid
      WHERE fc.id = ?",
    [$flashcardid],
    MUST_EXIST
);

$course = get_course($flashcard->course);
$cm = get_coursemodule_from_instance('fsrs', $flashcard->fsrsid, $course->id, false, MUST_EXIST);
require_course_login($course, false, $cm);
require_sesskey();

$usercard = $DB->get_record('fsrs_user_card', ['userid' => $USER->id, 'flashcardid' => $flashcardid], '*', MUST_EXIST);

$rating = max(0, min(3, $rating));
$now = time();

$intervalmap = [1, 3, 5, 7];
$nextinterval = $intervalmap[$rating] ?? 1;
$stabilitybefore = (float)$usercard->stability;
$difficultybefore = (float)$usercard->difficulty;
$intervalbefore = (int)$usercard->interval_days;

$newstability = max(0, $stabilitybefore + 0.1 * ($rating + 1));
$newdifficulty = max(0, $difficultybefore + (1 - $rating * 0.1));
$nextdue = $now + ($nextinterval * DAYSECS);

$usercard->state = 1;
$usercard->stability = $newstability;
$usercard->difficulty = $newdifficulty;
$usercard->interval_days = $nextinterval;
$usercard->due_at = $nextdue;
$usercard->reps = $usercard->reps + 1;
$usercard->last_rating = $rating;
$usercard->last_reviewed_at = $now;
$usercard->timemodified = $now;

$DB->update_record('fsrs_user_card', $usercard);

$log = new stdClass();
$log->userid = $USER->id;
$log->flashcardid = $flashcardid;
$log->usercardid = $usercard->id;
$log->rating = $rating;
$log->was_correct = $rating >= 2 ? 1 : 0;
$log->stability_before = $stabilitybefore;
$log->stability_after = $newstability;
$log->interval_before = $intervalbefore;
$log->interval_after = $nextinterval;
$log->reviewed_at = $now;
$DB->insert_record('fsrs_review_log', $log);

header('Content-Type: application/json');
echo json_encode(['next_interval_days' => $nextinterval]);
die();