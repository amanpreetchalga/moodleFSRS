<?php


require_once(dirname(__DIR__, 1) . '/config.php');



$id = required_param('id', PARAM_INT);

$course = get_course($id);
require_course_login($course);

redirect(new moodle_url('/course/view.php', ['id' => $course->id]), 'This is the FSRS index page.');