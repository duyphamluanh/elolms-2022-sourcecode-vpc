<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains functions used by the eloprogress report
 *
 * @package    report
 * @subpackage eloprogress
 * @copyright  2009 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_eloprogress_extend_navigation_course($navigation, $course, $context) {
    global $CFG, $OUTPUT;

    require_once($CFG->libdir.'/completionlib.php');

    $showonnavigation = has_capability('report/eloprogress:view', $context);
    $group = groups_get_course_group($course,true); // Supposed to verify group
    if($group===0 && $course->groupmode==SEPARATEGROUPS) {
        $showonnavigation = ($showonnavigation && has_capability('moodle/site:accessallgroups', $context));
    }

    $completion = new completion_info($course);
    $showonnavigation = ($showonnavigation && $completion->is_enabled() && $completion->has_activities());
    if ($showonnavigation) {
        $url = new moodle_url('/report/eloprogress/index.php', array('course'=>$course->id));
        $navigation->add(get_string('pluginname','report_eloprogress'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array
 */
function report_eloprogress_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        '*'                     => get_string('page-x', 'pagetype'),
        'report-*'              => get_string('page-report-x', 'pagetype'),
        'report-eloprogress-*'     => get_string('page-report-eloprogress-x',  'report_eloprogress'),
        'report-eloprogress-index' => get_string('page-report-eloprogress-index',  'report_eloprogress'),
    );
    return $array;
}
