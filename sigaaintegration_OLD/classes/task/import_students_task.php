<?php
namespace local_sigaaintegration\task;

use core\task\scheduled_task;
use local_sigaaintegration\sigaa_academic_period;
use local_sigaaintegration\sigaa_students_sync;

class import_students_task extends scheduled_task {

    /**
    * Get a descriptive name for this task (shown to admins).
    *
    * @return string
    */
    public function get_name() {
        return get_string('importstudents', 'local_sigaaintegration');
    }

    public function execute() {
        $period = sigaa_academic_period::getAcademicPeriod();
        $studentssync = new sigaa_students_sync($period->getAno(), $period->getPeriodo());
        $studentssync->sync();
    }

}

