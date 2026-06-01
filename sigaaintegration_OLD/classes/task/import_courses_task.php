<?php
namespace local_sigaaintegration\task;

use core\task\scheduled_task;
use local_sigaaintegration\sigaa_courses_sync;
use local_sigaaintegration\sigaa_academic_period;

class import_courses_task extends scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('importcourses', 'local_sigaaintegration');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        try {
            $period = sigaa_academic_period::getAcademicPeriod();

            if (!$period || !$period->getAno() || !$period->getPeriodo()) {
                throw new \moodle_exception('Invalid period generated.');
            }

            mtrace("INFO: Iniciando a sincrinização de disciplinas para o ano: {$period->getAno()}, periodo: {$period->getPeriodo()}");

            $coursessync = new sigaa_courses_sync($period->getAno(), $period->getPeriodo());
            $coursessync->sync();

            mtrace("INFO: Sincronização de disciplina concluída.");
        } catch (\Exception $e) {
            mtrace("ERROR: Erro durante a sincronização: " . $e->getMessage());
        }
    }
}

