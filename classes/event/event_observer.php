<?php


namespace assignsubmission_timelimit\event;

defined('MOODLE_INTERNAL') || die();

use \mod_assign\event\course_module_viewed;

class event_observer
{
    /**
     * An assignment activity is viewed.
     * @param $event course_module_viewed
     */
    public static function assign_viewed($event) {
        global $DB;
        $timelimited = null;

        if ($timelimited) {
            // Has this user already viewed this page?
            $assignid = $event->objectid;
            $assign = $event->get_record_snapshot('assign', $assignid);
            if (!$DB->record_exists('assignsubmission_timelimit', [
                'assign' => $assignid,
                'userid' => $event->userid
            ])) {
                // First time user has viewed page.
                $viewtime = $event->timecreated;
                $duedate = $viewtime + (24 * HOURSECS);
                $cutoffdate = $duedate + (10 * MINSECS);
                list($course, $cm) = get_course_and_cm_from_instance($assign, 'assign');

                $assign = new \assign($event->get_context(), $cm, $course);
                $assign->update_effective_access($event->userid);
                $overridedata = new stdClass();
                $overridedata->assignid = $assignid;
                $overridedata->sortorder = null;
                $overridedata->duedate = $duedate;
                $overridedata->cutoffdate = $cutoffdate;

                if ($override = $assign->override_exists($event->userid)) {
                    // TODO. What to do if there's already an override...
                } else {
                    $tx = $DB->start_delegated_transaction();
                    $DB->insert_record('assignsubmission_timelimit', (object)[
                       'assign' => $assignid,
                       'userid' => $event->userid
                    ]);
                    $overrideid = $DB->insert_record('assign_overrides', $overridedata);
                    $tx->allow_commit();
                }

            }
        }
    }
}