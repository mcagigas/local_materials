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
 * Cohort related management functions, this file needs to be included manually.
 *
 * @package    core_cohort
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require($CFG->dirroot.'/local/materials/edit_form.php');

require_login();

$id        = optional_param('id', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 1, PARAM_INT);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);

require_login();

$category = null;

if ($id) {
    $material = $DB->get_record('local_materials', array('id'=>$id));
} else {
    $material = new stdClass();
    $material->id = '';
    $material->courseid = '';
    $material->path = '';
}

$returnurl = new moodle_url('/local/materials/index.php');

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/local/materials/edit.php', array('id'=>$id, 'delete'=>$delete, 'confirm'=>$confirm));
$PAGE->set_context($context);


/*
if ($delete and $cohort->id) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        cohort_delete_cohort($cohort);
        redirect($returnurl);
    }
    $strheading = get_string('delcohort', 'cohort');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($COURSE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/cohort/edit.php', array('id'=>$cohort->id, 'delete'=>1, 'confirm'=>1,'sesskey'=>sesskey()));
    $message = get_string('delconfirm', 'cohort', format_string($cohort->name));
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}*/

if ($categoryid) {
    $courses = $DB->get_records('course', array('category'=>$categoryid));
} else {
    $courses = $DB->get_records('course', array());
}

$courseselect = array();
foreach ($courses as $course) {
    $courseselect[$course->id] = $course->fullname;
}

if ($material->id) {
    // Edit existing.
    $strheading = get_string('edit');

} else {
    // Add new.
    $strheading = get_string('add');
}

$PAGE->set_title($strheading);
$PAGE->set_heading($COURSE->fullname);
$PAGE->navbar->add(get_string('plugin_pluginname', 'local_materials'));
$PAGE->navbar->add($strheading, new moodle_url('/local/materials/edit.php', array('id'=>$id, 'delete'=>$delete, 'confirm'=>$confirm)));

$editform = new material_edit_form(null, array('data'=>$material, 'categoryid'=>$categoryid, 'courses'=>$courseselect));

if ($editform->is_cancelled()) {
    redirect($returnurl);

} else if ($data = $editform->get_data()) {

    if ($data->id) {
        $material->courseid = $data->courseid;
        $material->path = $data->path;
        $DB->update_record('local_materials', $material);
    } else {
        $material->courseid = $data->courseid;
        $material->path = $data->path;
        $DB->insert_record('local_materials', $material);
    }
    redirect(new moodle_url('/local/materials/index.php', array()));
}


echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);

if (!$material->id) {
    $list = coursecat::make_categories_list();
    $select = new single_select(new moodle_url('./edit.php', array()), 'categoryid', $list, $categoryid, null, 0);
    $select->nothing = array();
    $select->set_label(get_string('isactive', 'filters'), array('class' => 'accesshide'));
    echo $OUTPUT->render($select);
}

echo $editform->display();
echo $OUTPUT->footer();

