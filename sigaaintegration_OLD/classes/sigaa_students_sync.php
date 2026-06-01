<?php
/**
 *
 * @package   local_sigaaintegration
 * @copyright 2024, Cassiano Doneda
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sigaaintegration;

use core_course_category;

set_time_limit(5000);

class sigaa_students_sync extends sigaa_base_sync
{
    private string $ano;
    private string $periodo;
    private array $courseNotFound = [];
    private $course_discipline_mapper;
    private user_moodle $user_moodle;

    public function __construct(string $year, string $period)
    {
        parent::__construct();
        $this->ano = $year;
        $this->periodo = $period;
        $this->user_moodle = new user_moodle();
        $this->course_discipline_mapper = new course_discipline_mapper();
    }

    protected function get_records($campus): array
    {
        mtrace("Sincronização de e-mail está: " . ($campus->syncemailwithsigaa ? "Ativada" : "DESATIVADA"));
        if($campus->syncemailwithsigaa) {
            mtrace("Preservação do e-mail institucional: " . ($campus->preserveinstitutionalemail ? "Ativada" : "DESATIVADA"));
        }
        $periodoletivo = sigaa_academic_period::buildFromParameters($this->ano, $this->periodo);
        return $this->api_client->get_enrollments($campus, $periodoletivo);
    }

    protected function process_records(campus $campus, array $records): void
    {

        mtrace("Processando dados: ". $campus->description);
        foreach ($records as $key => $enrollment) {
            try {
                $this->sync_user($campus, $enrollment);
            } catch (Exception $e) {
                mtrace(sprintf(
                    'ERRO: Falha ao processar o estudante. Matrícula: %s, erro: %s',
                    $key,
                    $e->getMessage()
                ));
            }
        }
    }

    private function sync_user(campus $campus, array $enrollment)
    {
        // Processa o usuário somente se o curso existir no banco de dados do Moodle.
        if($this->course_exists_in_moodle($campus, $enrollment)){
            $current_user = $this->user_moodle->get_user_by_login($enrollment['login']);
            if($current_user){
                // Etapa de Sincronização de E-mail entre o SIGAA e Moodle
                $this->maybe_sync_email($campus, $current_user, $enrollment);
            } else {
                // Cadastra o novo usuário no Moodle
                $this->user_moodle->insert($enrollment);
            }
        }

    }

    private function maybe_sync_email(campus $campus, $current_user, array $enrollment): void {
        if (!$campus->syncemailwithsigaa) {
            return;
        }

        mtrace("{$enrollment['login']}: Email moodle: {$current_user->email}");

        if (strtolower($current_user->email) === strtolower($enrollment['email'])) {
            return; // Os e-mails são iguais, nada a fazer
        }

        $domain = 'ifrs.edu.br';

        if ($campus->preserveinstitutionalemail) {
            if ($this->user_moodle->is_institutional_email($current_user->email, $domain)) {
                mtrace("INFO: E-mail do usuário {$current_user->username} pertence ao domínio '{$domain}'. Não será atualizado.");
                return;
            }
        }

        $this->user_moodle->update_email($current_user, $enrollment['email']);
    }


    private function course_exists_in_moodle($campus, array $enrollment): bool
    {
        //ex: 53.44973, procura pela existência do curso.
        $courseidnumber = $campus->id_campus.'.'.$enrollment['id_curso'];
        if($this->search_course_by_id($courseidnumber)) {
            return true;
        }
        return false;
    }

    private function insert_student_if_course_exists($campus, array $enrollment): void
    {
        try {
            // generate_course_idnumber(campus $campus, $enrollment, $disciplina);
            $courseidnumber = $campus->id_campus.'.'.$enrollment['id_curso'];//ex: 53.44973, procura pela existência do curso.
            if($this->search_course_by_id($courseidnumber)) {
                //inserir usuario no moodle
                $this->user_moodle->insert($enrollment);
                //continue;//inserindo a primeira vez, não preciso olhar o restante das disciplinas para esse usuario.
            }
        } catch (Exception $e) {
            mtrace(sprintf(
                'ERRO: Falha ao processar criação de estudante no moodle. ' .
                'Usuário: %s, usuário: %s, disciplina: %s, erro: %s',
                $enrollment['matricula'],
                $enrollment['nome_completo'],
                $courseidnumber,
                $e->getMessage()
            ));
        }

    }

    //procura pela existencia do curso no moodle
    private function search_course_by_id(string $courseidnumber): ?object
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
}
