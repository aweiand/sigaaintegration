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
 * @package    local_sigaaintegration
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_sigaaintegration\form\test_mail_form;
use local_sigaaintegration\task\test_mail_adhoc_task;
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

function setTaskData($task, $cpf) {

    $task->set_custom_data((object) [
        'cpf' => $cpf,
    ]);
}


$returnurl = new moodle_url('/local/sigaaintegration/testmail.php');

admin_externalpage_setup('local_sigaaintegration_testmail');

$form = new test_mail_form();

if ($data = $form->get_data()) {

    if(isset($data->submitbutton)){
        if (isset($data->cpf)) {

            $message = "Teste de email adicionado na fila para processamento.";
            $task = new test_mail_adhoc_task();
            setTaskData($task, $data->cpf);

        }

        if (!empty($task)) {
            \core\task\manager::queue_adhoc_task($task);
        }

        if (!empty($message)) {
            \core\notification::add($message, \core\output\notification::NOTIFY_INFO);
            redirect($returnurl);
        }
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('testmail', 'local_sigaaintegration'));
echo $form->render();
echo $OUTPUT->footer();
