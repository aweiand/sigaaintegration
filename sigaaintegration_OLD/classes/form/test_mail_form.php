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
 * @package   local_sigaaintegration
 * @copyright 2024, Igor Ferreira Cemim
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sigaaintegration\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Manage integration.
 */
class test_mail_form extends \moodleform
{

    public function definition()
    {
        $mform = $this->_form;
        $mform->addElement('text', 'cpf', get_string('cpf', 'local_sigaaintegration'));
        $mform->setType('cpf', PARAM_RAW);
        $mform->addHelpButton('cpf', 'cpf', 'local_sigaaintegration');
        $mform->addRule('cpf', get_string('required'), 'required');

        // Botão de envio.
        $mform->addElement('submit', 'submitbutton', get_string('submit'));
    }

    public function validation($data, $files)
    {
        $errors = [];

        // Validação do CPF (formato simples, pode ser aprimorado com regex ou algoritmo de validação de CPF).
        if (empty($data['cpf'])) {
            $errors['cpf'] = get_string('invalidcpf', 'local_sigaaintegration');
        }

        return $errors;
    }
}
