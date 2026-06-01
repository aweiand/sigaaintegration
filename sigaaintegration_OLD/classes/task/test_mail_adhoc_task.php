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
 * A scheduled task.
 *
 * @package   local_sigaaintegration
 * @copyright 2024, Igor Ferreira Cemim
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_sigaaintegration\task;

use core\task\adhoc_task;
use local_sigaaintegration\sigaa_enrollments_test_mail_sync;
use local_sigaaintegration\sigaa_test_mail;

class test_mail_adhoc_task extends adhoc_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('testmail', 'local_sigaaintegration');
    }

    public function retry_until_success(): bool {
        return false;
    }

    public function execute() {
        $data = $this->get_custom_data();

        if (!isset($data->cpf)) {
            mtrace("Erro: Dados do formulário ausentes.");
            return;
        }

        mtrace("Iniciando a execução da tarefa ad-hoc...");
        mtrace("CPF: " . $data->cpf);
        mtrace("Tarefa concluída com sucesso.");

        $test_mail_sync = new sigaa_test_mail($data->cpf);
        $test_mail_sync->sync();
    }

}
