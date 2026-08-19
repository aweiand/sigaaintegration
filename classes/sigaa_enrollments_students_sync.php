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
 * @package   local_sigaaintegration
 * @copyright 2024, Igor Ferreira Cemim
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sigaaintegration;

use core\context;
use core_course_category;
use Exception;
use local_sigaaintegration\utils\sigaa_utils;

class sigaa_enrollments_students_sync extends sigaa_base_sync
{

    private string $ano;

    private string $periodo;

    private array $courseNotFound = [];

    private int $studentroleid;

    private $course_discipline_mapper;

    public function __construct(string $year, string $period)
    {
        parent::__construct();
        $this->ano = $year;
        $this->periodo = $period;
        $this->studentroleid = configuration::getIdPapelAluno();
        $this->course_discipline_mapper = new course_discipline_mapper();
    }

    protected function get_records(campus $campus): array
    {
        $periodoletivo = sigaa_academic_period::buildFromParameters($this->ano, $this->periodo);
        return $this->api_client->get_enrollments($campus, $periodoletivo);
    }

    protected function process_records(campus $campus, array $records): void
    {
        try {
            $this->enroll_student_into_courses($campus, $records);
        } catch (Exception $e) {
            mtrace(sprintf(
                'ERRO: Falha ao processar todas as inscrições do estudante. erro: %s',
                $e->getMessage()
            ));
        }
        mtrace('INFO: Fim importação.');
    }

    /**
     * Tenta inscrever o estudante nas disciplinas retornadas pela API do SIGAA.
     */
    private function enroll_student_into_courses(campus $campus, array $enrollments): void
    {
        foreach ($enrollments as $enrollment) {
            // DTI OSÓRIO -- ALTERAMOS POIS NOSSO LDAP USA MATRÍCULA COMO LOGIN
            $user = $this->search_student($enrollment['matricula']);
            if (!$user) {
                mtrace(sprintf(
                    'ERRO: Usuário não encontrado. login: %s | nome: %s | matrícula: %s',
                    $enrollment['login'] ?? 'NULL',
                    $enrollment['nome_completo'] ?? 'NÃO INFORMADO',
                    $enrollment['matricula'] ?? 'NÃO INFORMADA'
                ));
            } else {
                // mtrace(sprintf('Processando o usuário: %s', $enrollment['login']));

                foreach ($enrollment['disciplinas'] as $course_enrollment) {
                    try {
                        // Adiciona o nível do curso à disciplina (necessário para a lógica de validação)
                        $course_enrollment['curso_nivel'] = $enrollment['curso_nivel'] ?? null;
                        if (sigaa_utils::validate_discipline($campus, $course_enrollment)) {
                            // generate_course_idnumber(campus $campus, $enrollment, $disciplina);
                            $course_discipline = $this->course_discipline_mapper->map_to_course_discipline($enrollment, $course_enrollment);

                            $courseidnumber = $course_discipline->generate_course_idnumber($campus);
                            if($courseidnumber){
                                $this->enroll_student_into_single_course($user, $courseidnumber);
                            } else {
                                mtraca("ERRO na geração do idnumber da disciplina");
                            }
                        } else {
                            mtrace('Disciplina não validada:');
                            mtrace(print_r($course_enrollment, true));
                        }
                    } catch (Exception $e) {
                        mtrace(sprintf(
                            'ERRO: Falha ao processar inscrição de estudante em uma disciplina. ' .
                            'matrícula: %s, usuário: %s, erro: %s',
                            $enrollment['matricula'],
                            $user->username,
                            $e->getMessage()
                        ));
                    }
                }
            }
        }
    }

    /**
     * Busca estudante pelo login/CPF.
     */
    private function search_student(?string $login): object|false
    {
        global $DB;

        if (empty($login)) {
            mtrace('WARNING: search_student chamado com login vazio ou nulo.');
            return false;
        }

        return $DB->get_record('user', ['username' => $login]);
    }



    /**
     * Busca disciplina pelo código de integração.
     */
    private function search_course(string $courseidnumber): ?object
    {
        /**
         * Evita busca repetida por disciplinas não encontradas.
         */
        if (array_search($courseidnumber, $this->courseNotFound)) {
            return null;
        }

        $results = core_course_category::search_courses(['search' => $courseidnumber]);
        if (count($results) > 0) {
            return current($results);
        }

        $this->courseNotFound[] = $courseidnumber;
        return null;
    }

    /**
     * Inscreve o estudante em uma disciplina.
     */
    private function enroll_student(object $course, object $user): void
    {
        global $CFG;
        require_once($CFG->dirroot . '/lib/enrollib.php');

        if (is_enrolled(context\course::instance($course->id), $user)) {
            mtrace(sprintf(
                "INFO: O estudante já está inscrito na disciplina. usuário: %s, disciplina: %s",
                $user->username,
                $course->idnumber
            ));
            return;
        }

        $enrolinstances = enrol_get_instances($course->id, true);
        $manualenrolinstance = current(array_filter($enrolinstances, function ($instance) {
            return $instance->enrol == 'manual';
        }));
        if (empty($manualenrolinstance)) {
            mtrace(
                'ERRO: o plugin Inscrição Manual ativado é um pré-requisito para o funcionamento da ' .
                'integração com o SIGAA. Ative o plugin Inscrição Manual e execute o processo de integração novamente.'
            );
            return;
        }

        $manualenrol = enrol_get_plugin('manual');
        $manualenrol->enrol_user($manualenrolinstance, $user->id, $this->studentroleid);
        mtrace(sprintf(
            "INFO: O estudante foi inscrito na disciplina com sucesso. usuário: %s, disciplina: %s",
            $user->username,
            $course->idnumber
        ));
    }

    /**
     * Tenta increver o estudante em uma determinada disciplina retornada pela API do SIGAA.
     */
    private function enroll_student_into_single_course(object $user, string $courseidnumber) :void
    {
        $course = $this->search_course($courseidnumber);
        if (!$course) {
            mtrace(sprintf(
                'ERRO: Disciplina não encontrada. Inscrição não realizada. usuário: %s, disciplina: %s',
                $user->username,
                $courseidnumber
            ));
            return;
        }

        $this->enroll_student($course, $user);
    }

}
