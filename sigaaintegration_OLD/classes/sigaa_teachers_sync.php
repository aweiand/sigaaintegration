<?php

/**
 *
 * @package   local_sigaaintegration
 * @copyright 2024, Cassiano Doneda
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sigaaintegration;

class sigaa_teachers_sync extends sigaa_base_sync
{
    private user_moodle $user_moodle;

    private sigaa_teachers_manager $sigaa_servants;

    public function __construct(string $year, string $period)
    {
        parent::__construct();
        $this->ano = $year;
        $this->periodo = $period;
        $this->user_moodle = new user_moodle();
        $this->sigaa_servants = new sigaa_teachers_manager($this->api_client);

    }

    protected function get_records(campus $campus): array
    {
        try {
            $periodoletivo = sigaa_academic_period::buildFromParameters($this->ano, $this->periodo);
            $enrollments =  $this->api_client->get_enrollments($campus, $periodoletivo);
            $docentes = $this->get_records_docentes($enrollments);
            $novos = $this->teachres_to_register($docentes);
        } catch (Exception $e) {
            mtrace(sprintf('ERRO:: %s', $e->getMessage()
            ));
        }
        return $novos;
    }

    protected function process_records(campus $campus, array $records): void
    {
        mtrace("Processando dados: ". $campus->description);
        foreach ($records as $key => $record) {
            try {
                $this->user_moodle->insert($record);
            } catch (Exception $e) {
                mtrace(sprintf(
                    'ERRO: Falha ao processar o servidor. CPF: %s, erro: %s',
                    $key,
                    $e->getMessage()
                ));
            }
        }
    }

    protected function get_records_docentes(array $enrollments): array {
        mtrace("Colentando os docentes das disciplinas");
        $docentes = [];
        foreach ($enrollments as $enrollment) {
            if(isset($enrollment['disciplinas'])){
                foreach ($enrollment['disciplinas'] as $disciplina) {
                    if(isset($disciplina['docentes'])) {
                        foreach ($disciplina['docentes'] as $docente) {
                            // Converter o CPF para string
                            $cpf_docente_str = strval($docente['cpf_docente']);

                            // Garantir que o CPF tenha 11 dígitos, completando com zeros à esquerda, se necessário
                            $cpf_docente_str = str_pad($cpf_docente_str, 11, "0", STR_PAD_LEFT);

                            if (!in_array($cpf_docente_str, $docentes)) {
                                $docentes[] = $cpf_docente_str; //$this->user_moodle->completarCPF($docente['cpf_docente']);
                            }
                        }
                    }

                }

            }
        }
        return $docentes;

    }

    /**
     * Verifica se os docentes precisam ser registrados e os retorna.
     *
     * @param array $docentes
     * @return array
     */
    protected function teachres_to_register(array $docentes): array
    {
        $novos_docentes = [];

        foreach ($docentes as $cpf) {
            // Verifica se o docente já está registrado pelo CPF
            $cpf_str = strval($cpf); // Certifique-se de que $cpf seja uma string
            if (!$this->user_moodle->is_user_registered_by_login($cpf_str)) {
                // Busca o docente na plataforma SIGAA
                $novo = $this->sigaa_servants->get_servant_by_cpf($cpf_str);
                // Se o docente for encontrado, adiciona ao array de novos docentes
                if ($novo) {
                    $novos_docentes[] = $novo;
                } else {
                    mtrace("CPF $cpf_str não encontrado na plataforma SIGAA em Servidores ATIVOS.");
                }
            }
        }

        // Retorna os novos docentes
        return $novos_docentes;
    }
}