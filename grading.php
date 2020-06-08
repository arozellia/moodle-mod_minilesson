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
 * Reports for poodlltime
 *
 *
 * @package    mod_poodlltime
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

use \mod_poodlltime\constants;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // poodlltime instance ID
$format = optional_param('format', 'html', PARAM_TEXT); //export format csv or html
$action = optional_param('action', 'grading', PARAM_TEXT); // report type
$userid = optional_param('userid', 0, PARAM_INT); // user id
$attemptid = optional_param('attemptid', 0, PARAM_INT); // attemptid
$returnurl = optional_param('returnurl', false, PARAM_URL); //returnurl
$debug  = optional_param('debug', 0, PARAM_INT);


//paging details
$paging = new stdClass();
$paging->perpage = optional_param('perpage',-1, PARAM_INT);
$paging->pageno = optional_param('pageno',0, PARAM_INT);
$paging->sort  = optional_param('sort','user', PARAM_TEXT);


if ($id) {
    $cm         = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance  = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $moduleinstance  = $DB->get_record(constants::M_TABLE, array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

require_capability('mod/poodlltime:evaluate', $modulecontext);

//Get an admin settings 
$config = get_config(constants::M_COMPONENT);

//set per page according to admin setting
if($paging->perpage==-1){
	$paging->perpage = $config->itemsperpage;
}

// Trigger module viewed event.
$event = \mod_poodlltime\event\course_module_viewed::create(array(
   'objectid' => $moduleinstance->id,
   'context' => $modulecontext
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot(constants::M_MODNAME, $moduleinstance);
$event->trigger();


//process form submission
switch($action){
	case 'gradenowsubmit':
		$mform = new \mod_poodlltime\gradenowform();
		if($mform->is_cancelled()) {
		    if($returnurl){
                redirect(urldecode($returnurl));
		    }else{
                $action='grading';
            }
			break;
		}else{
			$data = $mform->get_data();
			$gradenow = new \mod_poodlltime\gradenow($attemptid,$modulecontext->id);
			$gradenow->update($data);
			
			//update gradebook
            poodlltime_update_grades($moduleinstance, $gradenow->attemptdetails('userid'));

			//stay on this page(always), or (possibly) go to returnurl or go to this activity grading
            $stay_on_this_page=true;
			if($stay_on_this_page) {
                $action = 'gradenow';
                //redirect to clear out form data so we can gradenow on next attempt
                $url = new \moodle_url(constants::M_URL . '/grading.php',
                        array('id' => $cm->id, 'format' => $format,
                                'action' => $action, 'userid' => $userid,
                                'attemptid' => $attemptid, 'returnurl' => $returnurl));
                redirect($url);
            }elseif($returnurl===false){
                $action='grading';
			}else{
                redirect(urldecode($returnurl));
			}
		}
		break;
}


$PAGE->set_url(constants::M_URL . '/grading.php',
    array('id' => $cm->id,'format'=>$format,'action'=>$action,'userid'=>$userid,'attemptid'=>$attemptid,'returnurl'=>$returnurl));

/// Set up the page header
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

//$PAGE->set_pagelayout('course');
if(has_capability('mod/' . constants::M_MODNAME . ':' . 'manage',$modulecontext)) {
    $PAGE->set_pagelayout('course');
}else{
    $PAGE->set_pagelayout('embedded');
}




$PAGE->requires->jquery();

//This puts all our display logic into the renderer.php files in this plugin
$renderer = $PAGE->get_renderer(constants::M_COMPONENT);
$reportrenderer = $PAGE->get_renderer(constants::M_COMPONENT,'report');
$gradenowrenderer = $PAGE->get_renderer(constants::M_COMPONENT,'gradenow');

//From here we actually display the page.
$mode = "grading";
$extraheader="";
switch ($action){

    //load individual attempt page with most recent(human or machine) eval and action buttons
	case 'gradenow':

		$gradenow = new \mod_poodlltime\gradenow($attemptid,$modulecontext->id);
		$force_aidata=false;//ai data could still be used if not human grading. we just do not force it
        $reviewmode=$reviewmode=constants::REVIEWMODE_NONE;
        $nextid = $gradenow->get_next_ungraded_id();
		$setdata=array(
			'action'=>'gradenowsubmit',
			'attemptid'=>$attemptid,
			'n'=>$moduleinstance->id,
			'shownext'=>$nextid,
			'sessiontime'=>$gradenow->formdetails('sessiontime',$force_aidata),
			'sessionscore'=>$gradenow->formdetails('sessionscore',$force_aidata),
			'sessionendword'=>$gradenow->formdetails('sessionendword',$force_aidata),
			'sessionerrors'=>$gradenow->formdetails('sessionerrors',$force_aidata),
            'selfcorrections'=>$gradenow->formdetails('selfcorrections',$force_aidata),
            'returnurl'=>$returnurl,
            'notes'=>$gradenow->formdetails('notes',$force_aidata)
            );

		$gradenowform = new \mod_poodlltime\gradenowform(null,array('n'=>$moduleinstance->id,'attemptid'=>$attemptid));
		$gradenowform->set_data($setdata);
		//no tabs because we want to keep the user nav. tight
		//echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('grading', constants::M_COMPONENT));
        echo $renderer->simpleheader($moduleinstance, $cm, get_string('grading', constants::M_COMPONENT));
        echo $gradenow->prepare_javascript($reviewmode,$force_aidata);
		echo $gradenowrenderer->render_gradenow($gradenow);
		$gradenowform->display();

		//the return to grading page
        //lets not show this
        //echo $reportrenderer->show_grading_footer($moduleinstance,$cm,$mode);

        //backtotop
        //lets not show this
        //echo $renderer->backtotopbutton($course->id);


        //show footer
        echo $renderer->footer();
		return;


    //load individual attempt page with machine eval and action buttons   (BUT rerun the AI auto grade code on it first)
    case 'regradenow':

        $mode = "machinegrading";

        //this forces the regrade using any changes in the diff algorythm, or alternatives
        //must be done before instant. $gradenow which also  aigrade object internally
        $aigrade = new \mod_poodlltime\aigrade($attemptid,$modulecontext->id);
        if($debug) {
            $debugsequences = $aigrade->do_diff($debug);
        }else{
            $aigrade->do_diff();
        }

        //fetch attempt and ai data
        $gradenow = new \mod_poodlltime\gradenow($attemptid,$modulecontext->id);
        $force_aidata=true;//in this case we are just interested in ai data
        $reviewmode = $reviewmode=constants::REVIEWMODE_MACHINE;

        echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('grading', constants::M_COMPONENT));
        echo $gradenow->prepare_javascript($reviewmode,$force_aidata);
        echo $gradenowrenderer->render_machinereview($gradenow,$debug);
        //if we can grade and manage attempts show the gradenow button
        if(has_capability('mod/poodlltime:canmanageattempts',$modulecontext )) {
            echo $gradenowrenderer->render_machinereview_buttons($gradenow);
            if($debug) {
                echo $gradenowrenderer->render_debuginfo($debugsequences, $aigrade->aidetails('transcript'), $aigrade->aidetails('fulltranscript'));
            }
        }
        echo $reportrenderer->show_grading_footer($moduleinstance,$cm,$mode);

        //backtotop
        echo $renderer->backtotopbutton($course->id);

        echo $renderer->footer();
        return;

    //load individual attempt page with machine eval (NO action buttons )
    case 'machinereview':

        $mode = "machinegrading";
        $gradenow = new \mod_poodlltime\gradenow($attemptid,$modulecontext->id);
        $force_aidata=true;//in this case we are just interested in ai data
        $reviewmode=constants::REVIEWMODE_MACHINE;

        echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('grading', constants::M_COMPONENT));

        echo $gradenow->prepare_javascript($reviewmode,$force_aidata);
        echo $gradenowrenderer->render_machinereview($gradenow);
        //if we can grade and manage attempts show the gradenow button
        if(has_capability('mod/poodlltime:canmanageattempts',$modulecontext )) {
            echo $gradenowrenderer->render_machinereview_buttons($gradenow);
        }
        echo $reportrenderer->show_grading_footer($moduleinstance,$cm,$mode);
        //backtotop
        echo $renderer->backtotopbutton($course->id);

        echo $renderer->footer();
        return;

     //load individual attempt page with machine eval and action buttons
    case 'aigradenow':

        $mode = "machinegrading";
        $gradenow = new \mod_poodlltime\gradenow($attemptid,$modulecontext->id);
        $force_aidata=true;//in this case we are just interested in ai data
        $reviewmode=$reviewmode=constants::REVIEWMODE_NONE;

        //$aigrade = new \mod_poodlltime\aigrade($attemptid,$modulecontext->id);

        $setdata=array(
            'action'=>'gradenowsubmit',
            'attemptid'=>$attemptid,
            'n'=>$moduleinstance->id,
            'sessiontime'=>$gradenow->formdetails('sessiontime',$force_aidata),
            'sessionscore'=>$gradenow->formdetails('sessionscore',$force_aidata),
            'sessionendword'=>$gradenow->formdetails('sessionendword',$force_aidata),
            'sessionerrors'=>$gradenow->formdetails('sessionerrors',$force_aidata));
        $nextid = $gradenow->get_next_ungraded_id();
        $gradenowform = new \mod_poodlltime\gradenowform(null,array('shownext'=>$nextid !== false));
        $gradenowform->set_data($setdata);
        echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('grading', constants::M_COMPONENT));
        echo $gradenow->prepare_javascript($reviewmode,$force_aidata);
        echo $gradenowrenderer->render_gradenow($gradenow);
        $gradenowform->display();
        echo $reportrenderer->show_grading_footer($moduleinstance,$cm,$mode);
        //backtotop
        echo $renderer->backtotopbutton($course->id);

        echo $renderer->footer();
        return;

    //list view of attempts and grades and action links
	case 'grading':
		$report = new \mod_poodlltime\report\grading();
		//formdata should only have simple values, not objects
		//later it gets turned into urls for the export buttons
		$formdata = new stdClass();
		$formdata->poodlltimeid = $moduleinstance->id;
		$formdata->modulecontextid = $modulecontext->id;
		break;

    //list view of attempts and grades and action links for a particular user
	case 'gradingbyuser':
		$report = new \mod_poodlltime\report\gradingbyuser();
		//formdata should only have simple values, not objects
		//later it gets turned into urls for the export buttons
		$formdata = new stdClass();
		$formdata->poodlltimeid = $moduleinstance->id;
		$formdata->userid = $userid;
		$formdata->modulecontextid = $modulecontext->id;
		break;

    //list view of attempts and machine grades and action links
    case 'machinegrading':
        $mode="machinegrading";
        $report = new \mod_poodlltime\report\machinegrading();
        //formdata should only have simple values, not objects
        //later it gets turned into urls for the export buttons
        $formdata = new stdClass();
        $formdata->poodlltimeid = $moduleinstance->id;
        $formdata->modulecontextid = $modulecontext->id;
        $formddata->moduleinstance = $moduleinstance;
        switch($moduleinstance->accadjustmethod){
            case constants::ACCMETHOD_NONE:
                $accadjust=0;
                break;
            case constants::ACCMETHOD_AUTO:
                $accadjust = \mod_poodlltime\utils::estimate_errors($moduleinstance->id);
                break;
            case constants::ACCMETHOD_FIXED:
                $accadjust = $moduleinstance->accadjust;
                break;
            case constants::ACCMETHOD_NOERRORS:
                $accadjust=9999;
                break;
        }
        $formdata->accadjust=$accadjust;
        $formdata->targetwpm=$moduleinstance->targetwpm;
        break;

    //list view of machine  attempts and grades and action links for a particular user
    case 'machinegradingbyuser':
        $mode = "machinegrading";
        $report = new \mod_poodlltime\report\machinegradingbyuser();
        //formdata should only have simple values, not objects
        //later it gets turned into urls for the export buttons
        $formdata = new stdClass();
        $formdata->poodlltimeid = $moduleinstance->id;
        $formdata->userid = $userid;
        $formdata->modulecontextid = $modulecontext->id;
        break;

	default:
		echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('grading', constants::M_COMPONENT));
		echo "unknown action.";
        //backtotop
        echo $renderer->backtotopbutton($course->id);
		echo $renderer->footer();
		return;
}

//if we got to here we are loading the report on screen
//so we need our audio player loaded
//here we set up any info we need to pass into javascript
$aph_opts =Array();

//this inits the js for the audio players on the list of submissions
$PAGE->requires->js_call_amd("mod_poodlltime/hiddenplayerhelper", 'init', array($aph_opts));


/*
1) load the class
2) call report->process_raw_data
3) call $rows=report->fetch_formatted_records($withlinks=true(html) false(print/excel))
5) call $reportrenderer->render_section_html($sectiontitle, $report->name, $report->get_head, $rows, $report->fields);
*/

$report->process_raw_data($formdata, $moduleinstance);
$reportheading = $report->fetch_formatted_heading();

switch($format){
    case 'csv':
        $reportrows = $report->fetch_formatted_rows(false);
        $reportrenderer->render_section_csv($reportheading, $report->fetch_name(), $report->fetch_head(), $reportrows, $report->fetch_fields());
        exit;
	case 'html':
	default:
        $reportrows = $report->fetch_formatted_rows(true,$paging);
        $allrowscount = $report->fetch_all_rows_count();
	    $pagingbar = $reportrenderer->show_paging_bar($allrowscount, $paging,$PAGE->url);
        $perpage_selector = $reportrenderer->show_perpage_selector($PAGE->url,$paging);


		echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('grading', constants::M_COMPONENT));
		echo $gradenowrenderer->render_hiddenaudioplayer();
		echo $extraheader;
		echo $pagingbar;
		echo $perpage_selector;
		echo $reportrenderer->render_section_html($reportheading, $report->fetch_name(), $report->fetch_head(), $reportrows, $report->fetch_fields());
		echo $pagingbar;
		echo $reportrenderer->show_grading_footer($moduleinstance,$cm,$mode);
        echo $reportrenderer->show_export_buttons($cm,$formdata,$action);
        //backtotop
        echo $renderer->backtotopbutton($course->id);
		echo $renderer->footer();
}