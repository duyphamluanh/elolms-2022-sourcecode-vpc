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
 *
 *
 * @package    format_eloflexsections
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');
    
    function elo_get_assign_dates_text($assignid)
    {
        global $DB;
        $params = array('id' => $assignid);
        $assigninstance = $DB->get_record('assign', $params, '*');
        if(!$assigninstance)
            return;        
        //$result->starttimetext = $assigninstance->allowsubmissionsfromdate;
        $result->endtimetext = $assigninstance->duedate;
        return $result;
    }
    function elo_get_quiz_dates_text($quizid)
    {
        global $DB;
        $params = array('id' => $quizid);
        $quizinstance = $DB->get_record('quiz', $params, '*');
        if(!$quizinstance)
            return;        
        //$result->starttimetext = $quizinstance->timeopen;
        $result->endtimetext = $quizinstance->timeclose;
        return $result;
    }    
     function elo_get_forum_dates_text($forumid)
    {
        global $DB;
        $params = array('id' => $forumid);
        $foruminstance = $DB->get_record('forum', $params, '*');
        if(!$foruminstance)
            return;        
        //$forumresult->starttimetext = $foruminstance->timeopen;
        $result->endtimetext= $foruminstance->assesstimefinish;
        return $result;
    } 
    function elo_course_activitive_completion_statistic($course,$userid = null) {
        global $USER,$OUTPUT,$CFG,$DB;
        // Make sure we continue with a valid userid.
        if (empty($userid)) {
            $userid = $USER->id;
        }

        $elo_act_col = get_string('elo_activitive_col','course');
        //$elo_start_col= get_string('elo_activitive_startdate_col','course');
        $elo_end_col= get_string('elo_activitive_enddate_col','course');
        $elo_completion_col= get_string('elo_activitive_completion_col','course');
        
        $completion = new \completion_info($course);
        $progresses = $completion->get_progress_all('(u.id = ' . $userid . ')' );
        // First, let's make sure completion is enabled.
        $activities = $completion->get_activities();
        /*<th onclick="w3.sortHTML(\'#elo_course_activitive_table_id\', \'.item\', \'td:nth-child(2)\',1)" style="cursor:pointer">
                        ' . $elo_start_col . ' <i class="fa fa-sort" style="font-size:15px;"></i></th>*/
                    
        if($activities){
        $elo_course_activitive_html = 
            '<script src="elo_w3.js"></script>	
            <script type="text/javascript">
                window.onload=function(){	
                        document.getElementById("elo_default_th").click();
            };
            </script>
            <div class="table-responsive">
            <table id = "elo_course_activitive_table_id" class="elo_course_activitive_table" style="overflow-y:scroll">
              <thead>
                <tr>
                  <th id = "elo_default_th" onclick="w3.sortHTML(\'#elo_course_activitive_table_id\', \'.item\', \'td:nth-child(1)\')" style="cursor:pointer">
                        ' . $elo_act_col . ' <i class="fa fa-sort" style="font-size:15px;"></i></th>
                  <th onclick="w3.sortHTML(\'#elo_course_activitive_table_id\', \'.item\', \'td:nth-child(2)\',1)" style="cursor:pointer">
                        ' . $elo_end_col . ' <i class="fa fa-sort" style="font-size:15px;"></i></th>
                  <th  onclick="w3.sortHTML(\'#elo_course_activitive_table_id\', \'.item\', \'td:nth-child(3)\')" style="cursor:pointer">
                        ' . $elo_completion_col . ' <i class="fa fa-sort" style="font-size:15px;"></i></th>
                </tr>
              </thead>
              <tbody>';
        
        foreach($activities as $activity) {
            if ($activity->modname == 'quiz' || $activity->modname == 'assign' || $activity->modname == 'forum') {
                $datepassed = $activity->completionexpected && $activity->completionexpected <= time();
                $datepassedclass = $datepassed ? 'completion-expired' : '';

                if ($activity->modname == 'forum') {// Fix forum khong cham diem nhung van hien thi thoi gian  completionexpected
                    if ($activity->completionexpected > 0) {
                        //$enddatetext=userdate($activity->completionexpected,get_string('strftimedate','langconfig'));
                        $enddatetext = userdate($activity->completionexpected, get_string('strftimerecent'));
                        $endYYMMDDHHIISS = date('Y-m-d H:i:s', $activity->completionexpected);
                    } else {
                        $forumtimetext = elo_get_forum_dates_text($activity->instance);
                        $forumtimetext->endtimetext > 0 ? $enddatetext = userdate($forumtimetext->endtimetext, get_string('strftimerecent')) : $enddatetext = 'N/A';
                        $forumtimetext->endtimetext > 0 ? $endYYMMDDHHIISS = date('Y-m-d H:i:s', $forumtimetext->endtimetext) :
                                        $endYYMMDDHHIISS = date('Y-m-d H:i:s', $course->enddate);
                    }
                }//update 10_05_2019
                // quiz
                if ($activity->modname == 'quiz') {
                        $quiztimetext = elo_get_quiz_dates_text($activity->instance);
                    /*if($assigntimetext->starttimetext){
                        $startdatetext = userdate($assigntimetext->starttimetext,get_string('strftimedate','langconfig'));
                        $startYYMMDDHHIISS = date('Y-m-d H:i:s',$assigntimetext->starttimetext);
                    }*/
                    $quiztimetext->endtimetext > 0 ?
                        //$enddatetext = userdate($assigntimetext->endtimetext,get_string('strftimedate','langconfig'));
                        $enddatetext = userdate($quiztimetext->endtimetext,get_string('strftimerecent')): $enddatetext = 'N/A';
                        $endYYMMDDHHIISS = date('Y-m-d H:i:s',$quiztimetext->endtimetext);             
                }
                 // assign
                if ($activity->modname == 'assign') {
                        $assigntimetext = elo_get_assign_dates_text($activity->instance);
                    /*if($assigntimetext->starttimetext){
                        $startdatetext = userdate($assigntimetext->starttimetext,get_string('strftimedate','langconfig'));
                        $startYYMMDDHHIISS = date('Y-m-d H:i:s', $assigntimetext->starttimetext);
                    }*/
                    $assigntimetext->endtimetext > 0 ? 
    //              $enddatetext = userdate($assigntimetext->endtimetext,get_string('strftimedate','langconfig'));
                    $enddatetext = userdate($assigntimetext->endtimetext,get_string('strftimerecent')):$enddatetext = 'N/A';
                    $endYYMMDDHHIISS = date('Y-m-d H:i:s',$assigntimetext->endtimetext);       
                }   
            $displayname = format_string($activity->name, true, array('context' => $activity->context));
            //$shortenedname = shorten_text($displayname);
            $elo_course_activitive_html .= '<tr class="item">';
            /*if(!$enddatetext){
                $enddatetext = 'N/A';
            }*/
//            $classdimmed = $assigntimetext->endtimetext < time() || !$assigntimetext->endtimetext = 0 ? " class=\"dimmed\" ": "";
            
            $elo_course_activitive_html .= '<td>' .
                $OUTPUT->image_icon('icon', get_string('modulename', $activity->modname), $activity->modname) .
                '<a target="_blank" href="'.$CFG->wwwroot.'/mod/'.$activity->modname.
                '/view.php?id='.$activity->id.'" title="' . s($displayname) . '">'.
                '<span class="rotated-text">'.$displayname.'</span>'.
                '</a></td>';
//            if(!$startdatetext){
//                $startdatetext = 'N/A';
//            }
                       
            //$elo_course_activitive_html .= '<td><span style = "display:none">'.$startYYMMDDHHIISS.'</span><span class="elo-startdate">'.$startdatetext.'</span></td>';
            $elo_course_activitive_html .= '<td><span style = "display:none">'.$endYYMMDDHHIISS.'</span><span class="elo-enddate">'.$enddatetext.'</span></td>';
            

            $formattedactivities[$activity->id] = (object)array(
                'datepassedclass' => $datepassedclass,
                'displayname' => $displayname,
            );
            
            // Get progress information and state
            if (array_key_exists($activity->id, $progresses[$USER->id]->progress)) {
                $thisprogress = $progresses[$USER->id]->progress[$activity->id];
                $state = $thisprogress->completionstate;
                $overrideby = $thisprogress->overrideby;
                $date = userdate($thisprogress->timemodified);
            } else {
                $state = COMPLETION_INCOMPLETE;
                $overrideby = 0;
                $date = '';
            }
            // Work out how it corresponds to an icon
            switch($state) {
                case COMPLETION_INCOMPLETE :
                    $completiontype = 'n'.($overrideby ? '-override' : '');
                    break;
                case COMPLETION_COMPLETE :
                    $completiontype = 'y'.($overrideby ? '-override' : '');
                    break;
                case COMPLETION_COMPLETE_PASS :
                    $completiontype = 'pass';
                    break;
                case COMPLETION_COMPLETE_FAIL :
                    $completiontype = 'fail';
                    break;
            }
           
            $completiontrackingstring = $activity->completion == COMPLETION_TRACKING_AUTOMATIC ? 'auto' : 'manual';
            $completionicon = 'completion-' . $completiontrackingstring. '-' . $completiontype;

            if ($overrideby) {
                $overridebyuser = \core_user::get_user($overrideby, '*', MUST_EXIST);
                $describe = get_string('completion-' . $completiontype, 'completion', fullname($overridebyuser));
            } else {
                $describe = get_string('completion-' . $completiontype, 'completion');
            }
            $a=new StdClass;
            $a->state=$describe;
            $a->date=$date;
            $a->user=fullname($USER);
            $a->activity = $formattedactivities[$activity->id]->displayname;
            $fulldescribe=get_string('progress-title','completion',$a);
            $celltext = $OUTPUT->pix_icon('i/' . $completionicon, s($fulldescribe));
            $elo_course_activitive_html .= '<td><span style = "display:none">'.$completiontype.'</span><div>'.
                $celltext . '</div></td>';
            
            $elo_course_activitive_html .= '</tr>';
            }
            unset($enddatetext); // update 10_05_2019
        }
        $elo_course_activitive_html.= '</tbody></table></div>';
        }
        return $elo_course_activitive_html;
    }
function elo_export_course_completed_html($course = null,$userid = null) {
    //$completedhtml;
    global $COURSE;
    if ($course == null)
        $elocompletornotactivitives = elo_course_activitive_completion_statistic($COURSE,$userid);
    else 
        $elocompletornotactivitives = elo_course_activitive_completion_statistic($course,$userid);

    return $elocompletornotactivitives;
}
    
$context = context_course::instance($course->id);

if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// make sure section 0 is created
course_create_sections_if_missing($course, 0);

$renderer = $PAGE->get_renderer('format_eloflexsections');
if (($deletesection = optional_param('deletesection', 0, PARAM_INT)) && confirm_sesskey()) {
    $renderer->confirm_delete_section($course, $displaysection, $deletesection);
} else {
    
            //Nhien create tab content
            $summary = get_string('tongquan', 'course');
            $content = get_string('noidung', 'course');
            $learningplanweek = get_string('kehoachhoctaptheotuan', 'course');
            $learningschedule = get_string('lichtrinhhoctap', 'course');
            $nameobjectfor = get_string('tenmonhocho', 'course');
            $level = get_string('bacdaotao', 'course');
            $duration = get_string('thoiluong', 'course');
            $conditionfirst = get_string('dieukientienquyet', 'course');
            $descriptionobject = get_string('motamonhoc', 'course');
            $downloadcontentobject=get_string('tainoidungmonhoc', 'course');
            $seeforum=get_string('xemdiendan', 'course');
           
            //----------------------------------------------start ul 
            print '<div class="block-elo-content" data-region="myoverview">';
            echo html_writer::start_tag('ul', array('id'=>'eloflexsectionsTab','class' => 'nav nav-tabs mb-2','role'=>'tablist'));
            //--------------------------------
            print '<li class="nav-item" role="presentation">';
            print '<a href="#tabsummary" class="nav-link" aria-controls="tabsummary" title="Tổng quan môn học"  data-toggle="tab" role="tab"><h4>'.$summary.'</h4></a>';
            print '</li">';
            //--------------------------------
            print '<li class="nav-item" role="presentation">';
            print '<a href="#tabcontent" class="nav-link" aria-controls="tabcontent" title="Nội dung môn học" data-toggle="tab" role="tab"><h4>'.$content.'</h4></a>';
            print '</li">';
            //--------------------------------
            print '<li class="nav-item" role="presentation">';
            print '<a href="#tablearningschedule" class="nav-link" aria-controls="tablearningschedule"  title="Lịch trình học tập" data-toggle="tab" role="tab"><h4>'.$learningschedule.'</h4></a>';
            print '</li">';
            echo html_writer::end_tag('ul');
            //------------------------------------------
            print '<div class="tab-content">'; // open div
            //*******************************TAB TONG QUAN MON HOC**********************
            $elocoursesummaryhtml = '
            <div id="tabsummary" role="tabpanel" class="tab-pane fade">
            <div class="tab-pane p-1" id="elosummary">
            <ul>
            <li><span>' . $nameobjectfor . '</span> ' .$course->fullname. '</li>';
            if($course->educationlevel) {
                $elocoursesummaryhtml .= '<li><span>'.$level.'</span> '.$course->educationlevel.'</li>';
            }
            if ($course->time) {
                $elocoursesummaryhtml .='<li><span>'.$duration.'</span> '.$course->time.'</li>';
            }
            if ($course->firstrequired) {
                $elocoursesummaryhtml .='<li><span>'.$conditionfirst.'</span> '.$course->firstrequired.'</li>';
            }
            $elocoursesummaryhtml.= '<li><span>'.$descriptionobject.'</span><br>'.$course->summary.'</li>';
            if($course->file) {
                $elocoursesummaryhtml .= '
                    <li><span>'.$downloadcontentobject.'</span>&nbsp;&nbsp;<a download="'.$course->file.'"'
                    . 'href="'.$course->file.'">'
                    . '<i style="font-size:20px" class="fa fa-cloud-download"></i></a></li>';
            }
            $elocoursesummaryhtml .= '</ul></div></div>';
            print $elocoursesummaryhtml;

        //******************************Nhien Tab NOI DUNG MON HOC******************
            print '<div id="tabcontent" role="tabpanel" class="tab-pane fade in active">';
            echo html_writer::start_tag('div', array('class'=>'course-content'));            
            $renderer->display_section($course, $displaysection, $displaysection);//$this->display_section($course,$section,$sr,$level);
            echo html_writer::end_tag('div');
            print '</div>'; 

        //*******************************NHien Tab lich trinh hoc tap*******************

        require_once($CFG->dirroot.'/course/lib.php');
        require_once($CFG->dirroot.'/calendar/lib.php');

        $categoryid = optional_param('category', null, PARAM_INT);
        $time = optional_param('time', 0, PARAM_INT);
        $view = optional_param('view', 'month', PARAM_ALPHA);

        $url = new moodle_url('/calendar/view.php');

        if (empty($time)) {
            $time = time();
        }

        $url->param('course', $course->id);


        if ($categoryid) {
            $url->param('categoryid', $categoryid);
        }

        if ($view !== 'upcoming') {
            $time = usergetmidnight($time);
            $url->param('view', $view);
        }

        $calendar = calendar_information::create($time, $course->id, $categoryid);

        $renderer = $PAGE->get_renderer('core_calendar');
        //$calendar->add_sidecalendar_blocks($renderer, true, $view);

        list($data, $template) = calendar_get_view($calendar, $view);
        list($dataupcoming, $templateupcoming) = calendar_get_view($calendar, 'upcoming');

        $elocalendarhtml .= $renderer->start_layout();
        $elocalendarhtml .= html_writer::start_tag('div', array('class'=>' p-4 eloheightcontainer'));
        $elocalendarhtml .= $renderer->render_from_template($template, $data);
        list($data, $template) = calendar_get_footer_options($calendar);
        $elocalendarhtml .= $renderer->render_from_template($template, $data);
        $elocalendarhtml .= html_writer::end_tag('div');

        $elocalendarhtml .= '<div class = "p-3 elo_upcomming_calendar_block d-none"> ';
        $elocalendarhtml .= $renderer->render_from_template($templateupcoming, $dataupcoming);
        $elocalendarhtml .= $renderer->render_from_template($template, $data);
        $elocalendarhtml .= '</div> ';
        $elocalendarhtml .= $renderer->complete_layout();

            print '<div id="tablearningschedule" role="tabpanel" class="tab-pane fade" >';
            print $elocalendarhtml;

            $elocomplettionhtlm = elo_export_course_completed_html($course);

            print $elocomplettionhtlm;

        //    print $course->studyplan;
        //    if ($course->linkstudyplan){
        //        print '<br />';
        //        print '<a href="' . $course->linkstudyplan . '">Xem lịch trình</a>';
        //    }
            print '</div>';   
            print '</div>'; 
            print '</div>'; //End div myoverview

        //***************************Nhien create tab content end*****************************************    

// Nhien said : oginrincode only 1 call            
//$renderer->display_section($course, $displaysection, $displaysection);
}

// Include course format js module
$PAGE->requires->js('/course/format/eloflexsections/format.js');
$PAGE->requires->string_for_js('confirmdelete', 'format_eloflexsections');
$PAGE->requires->js_init_call('M.course.format.init_eloflexsections');

$PAGE->requires->js('/course/format/eloflexsections/js/savetabcourse.js'); //save tab for client side update_10_05_2019
$PAGE->requires->js('/course/format/eloflexsections/js/bootstrap.min.js'); // include library bootstrap, delete some features avoid confliction update_10_05_2019