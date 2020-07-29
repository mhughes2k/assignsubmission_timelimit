<?php
$observers = [
    [
        'eventname' => '\mod_assign\event\course_module_viewed',
        'callback' => '\assignsubmission_timelimit\event\event_observer::assign_viewed'
    ]

];