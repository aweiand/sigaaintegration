<?php
namespace local_sigaaintegration;

use moodle_exception;

class sigaa_courses_manager {

    // Variável para armazenar os cursos de cada campus
    private $courses_sigaa = [];

    // Instância da classe que faz a consulta na API (por exemplo, $data_api)
    private sigaa_api_client $data_api;

    // Construtor da classe - você pode passar a instância da API aqui
    public function __construct(sigaa_api_client $data_api) {
        $this->data_api = $data_api;
    }

    /**
     * Verifica se os cursos do campus já estão disponíveis e, caso contrário, consulta a API.
     *
     * @param int $id_campus - O ID do campus para buscar os cursos
     * @return array - Lista de cursos do campus
     */
    private function get_all_courses_by_campus(campus $campus): array {
        // Verifica se os cursos para o campus já estão carregados
        if (!isset($this->courses_sigaa[$campus->id_campus])) {
            // Se não tiver, consulta a API para obter os cursos
            $this->courses_sigaa[$campus->id_campus] = $this->data_api->get_courses_sigaa($campus->id_campus, $campus->modalidade_educacao);
        }
        // Retorna os cursos do campus
        return $this->courses_sigaa[$campus->id_campus];
    }

    /**
     * Método para limpar os cursos carregados (caso precise, por exemplo, ao resetar o sistema).
     */
    public function clear_courses() {
        $this->courses_sigaa = [];
    }

    /**
     * Método que recebe o id_campus e retorna as informações do campus correspondente.
     *
     * @param int $id_campus - O ID do campus a ser consultado.
     * @return array|null - Retorna os dados do campus ou null caso não encontre.
     */
    public function get_courses_by_campus(campus $campus): ?array {
        // Filtra o array de cursos para encontrar o campus com o ID fornecido
        $all_courses = $this->get_all_courses_by_campus($campus);
        foreach ($all_courses as $course) {
            if ($course['id_campus'] == $campus->id_campus) {
                return $course;  // Retorna os dados do campus correspondente
            }
        }
        // Retorna null se o campus não for encontrado
        return null;
    }

    public function get_courses_by_id_course(campus $campus, $id_curso): ?array {
        // Filtra o array de cursos para encontrar o campus com o ID fornecido
        $all_courses = $this->get_all_courses_by_campus($campus);
        foreach ($all_courses as $course) {
            if ($course['id_campus'] == $campus->id_campus) {
                if ($course['id_curso'] == $id_curso) {
                    // Retorna os dados do curso correspondente
                    return $course;
                }

            }
        }
        // Retorna null se o campus não for encontrado
        return null;
    }
}
