<?php
class block_course_module_list extends block_base {
    public function init() {
        $this->title = get_string('course_module_list', 'block_course_module_list');
    }

    public function applicable_formats() {
        return array('course' => true);
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $course = $this->page->course;
        $this->content = new stdClass();

        if ($course) {
            $module_list = $this->generate_module_list($course);
            $this->content->text = $module_list;
            $this->content->footer = '';
        }

        return $this->content;
    }

    private function generate_module_list($course) {
        global $USER, $DB, $OUTPUT, $COURSE, $PAGE;

        $module_list = '';

        $modinfo = get_fast_modinfo($COURSE);
        $course_modules = $modinfo->get_cms();
        // var_dump($USER->id,$COURSE);

        foreach ($course_modules as $cm_item) {
            $module = $DB->get_record('modules', ['id' => $cm_item->module], '*', MUST_EXIST);
            $instance = $DB->get_record($module->name, ['id' => $cm_item->instance], '*', MUST_EXIST);

            // Retrieve relevant information for display.
            $cmid = $cm_item->id;
            $activity_name = $instance->name;
            $creation_date = date('d-m-Y', $cm_item->added);
            $completion_status = '';
            $userId = $USER->id;

            // Check completion status.
            // $completion = new completion_info($COURSE);
            // $completion_state = $completion->get_completions($USER->id,[$cm_item->id]);
            $completion_state = $DB->record_exists('course_modules_completion', array(
                'coursemoduleid' => $cmid,
                'completionstate' => COMPLETION_COMPLETE,
                'userid' => $userId
            ));
            // var_dump("cool");
            // var_dump($completion_state);
            if ($completion_state) {
                $completion_status = 'Completed';
            }

            // Generate module entry.
            $module_list .= html_writer::link($cm_item->url, "$cmid-$activity_name-$creation_date");
            $module_list .= $completion_status ? "-$completion_status" : '';
            $module_list .= "<br>";
        }

        return $module_list;
    }

}