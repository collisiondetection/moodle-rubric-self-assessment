<?php
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/config.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');
$cmd = optional_param('cmd', 'self_assess', PARAM_TEXT);

function fail($reason) {
	$result = array('reason' => $reason, 'success' => false);
	die(json_encode($result));
}

switch($cmd) {
  case 'get':
  $result = array('success' => true);
  $cmid = optional_param('cmid', -1, PARAM_INT);
  if($cmid < 0)
  	fail('Invalid cmid');
	
  if(!$mod = $DB->get_record('course_modules', array('id' => $cmid))) 
	fail('Invalid cmid');

  // check current user has grading capability
  $context = context_module::instance($cmid);
  if(!has_capability('mod/assign:grade', $context, $USER->id, false))
  	fail('Access denied');
	
  $userid = optional_param('userid', -1, PARAM_INT);
  if($userid < 0)
  	fail('Invalid userid');
	
  // get assignment grading area
  if(!$area = $DB->get_record('grading_areas', array('contextid' => $context->id, 'activemethod' => 'rubric')))
  	fail('Could not find grading area');
	
  // get rubric definition
  if(!$definition = $DB->get_record('grading_definitions', array('areaid' => $area->id, 'method' => 'rubric')))
  	fail('Could not find rubric definition');
	
  $options = json_decode($definition->options);
  if(!$options->allowselfassessment)
  	fail('Self assessment not enabled');
	
  // get grading instance
  if(!$instance = $DB->get_record('grading_instances', array('definitionid' => $definition->id, 'raterid' => $userid, 'itemid' => $userid)))
  	fail('Could not get grading instance');
  
  // get self assessments
  if(!$records = $DB->get_records('gradingform_rubric_self', array('instanceid' => $instance->id, 'userid' => $userid)))
  	fail('Could not find any self assessments');
	
  $grades = array();
  foreach($records as $r) {
	  $i = array('level' => $r->levelid, 'criterion' => $r->criterionid);
	  $grades[] = $i;
  }
  $result['grades'] = $grades;
 
  

  echo(json_encode($result));

  break;

	case 'self_assess':
		$definitionid = required_param('definitionid', PARAM_INT);
		$criterionid = required_param('criterionid', PARAM_INT);
		$levelid = required_param('levelid', PARAM_INT);

		// user must be logged in
		require_login();

		// check criterion is valid
		$criteria = $DB->get_field('gradingform_rubric_criteria', 'description', array('definitionid' => $definitionid, 'id' => $criterionid), MUST_EXIST);

		// check level is valid
		$level = $DB->get_field('gradingform_rubric_levels', 'definition', array('criterionid' => $criterionid, 'id' => $levelid), MUST_EXIST);

		// get existing self assessment instance
		if($instance = $DB->get_record('grading_instances', array('definitionid' => $definitionid, 'raterid' => $USER->id, 'itemid' => $USER->id))) {
			$instance->timemodified = time();
			$DB->update_record('grading_instances', $instance);
		} else {
		// or create new
			$instance = json_decode(json_encode(array('definitionid' => $definitionid, 'raterid' => $USER->id, 'itemid' => $USER->id, 'timemodified' => time())));
			$instance->id = $DB->insert_record('grading_instances', $instance);
		}

		// get existing self assessment rating
		if($self = $DB->get_record('gradingform_rubric_self', array('instanceid' => $instance->id, 'criterionid' => $criterionid, 'userid' => $USER->id))){
			$self->levelid = $levelid;
			$DB->update_record('gradingform_rubric_self', $self);

		} else {
			$self = json_decode(json_encode(array('instanceid' => $instance->id, 'criterionid' => $criterionid, 'levelid' => $levelid, 'userid' => $USER->id)));
			$self->id = $DB->insert_record('gradingform_rubric_self', $self);
		}
		
		// get associated assignment
		$sql = "SELECT * FROM {assign} WHERE id=(SELECT instance FROM {course_modules} WHERE id=(SELECT instanceid FROM {context} WHERE id=(SELECT contextid FROM {grading_areas} WHERE component='mod_assign' AND id = (SELECT areaid FROM {grading_definitions} WHERE id=" . $definitionid . "))))";
		$assign = $DB->get_record_sql($sql);
		
		$grade_item = $DB->get_record('grade_items', array('itemname'=>$assign->name . ' (S/A)', 'itemtype'=>'mod', 'itemmodule'=>'assign'));
		
		$score = $DB->get_field_sql("SELECT SUM(score) FROM {gradingform_rubric_levels} WHERE id IN (SELECT levelid FROM {gradingform_rubric_self} WHERE userid=" . $USER->id . " AND instanceid = " . $instance->id . ")");
		
		$sql = "SELECT SUM(score) AS total FROM (SELECT MAX(l.score) AS score FROM {gradingform_rubric_criteria} AS c 
LEFT JOIN {gradingform_rubric_levels} AS l ON c.id=l.criterionid WHERE c.definitionid=$definitionid GROUP BY c.id) scores";
		$maxscore = $DB->get_field_sql($sql);
		
		
		$rawgrade = round($score * 100 / $maxscore, 2);
		
		//echo("score: $rawgrade, max: $maxscore, raw: $score");
		$grade_data = array('itemid'=>$grade_item->id, 'userid'=>$USER->id);
		if($DB->record_exists('grade_grades', $grade_data)) {
			$grade = $DB->get_record('grade_grades', $grade_data);
			$grade->timemodified = time();
			$grade->rawgrade = $rawgrade;
			$grade->finalgrade = $rawgrade;
			$DB->update_record('grade_grades', $grade);
		} else {
			$grade = new stdClass();
			$grade->itemid = $grade_item->id;
			$grade->userid = $USER->id;
			$garde->timemodified = time();
			$grade->rawgrade = $rawgrade;
			$grade->finalgrade = $rawgrade;
			$grade->rawgrademax = 100.0;
			$grade->rawgrademin = 0.0;
			$grade->rawscaleid = NULL;
			$grade->usermodified = $USER->id;
			$grade->aggregationstatus = 'used';
			$grade->aggregationweight = 1.0;
			$grade->id = $DB->insert_record('grade_grades', $grade);
		}
		
		

	//echo($sql);
	break;
}

?>
