<?php
namespace local_sigaaintegration;

use moodle_exception;

class sigaa_teachers_manager {

    // Variável para armazenar os servidores do SIGAA
    private array $servants_sigaa = [];  // Inicializando o array corretamente

    // Instância da classe que faz a consulta na API (por exemplo, $data_api)
    private sigaa_api_client $data_api;

    // Construtor da classe - você pode passar a instância da API aqui
    public function __construct(sigaa_api_client $data_api) {
        $this->data_api = $data_api;
    }

    /**
     * Obtém todos os servidores ativos por status.
     *
     * @param string $status O status dos servidores a ser filtrado.
     * @return array|null
     */
    private function get_all_servants_by_status(string $status): ?array {
        // Verifica se os servidores para o status já estão carregados
        if (empty($this->servants_sigaa)) {
            // Se não tiver, consulta a API para obter os servidores
            $this->servants_sigaa = $this->data_api->get_servants($status);
        }

        // Retorna os servidores
        return $this->servants_sigaa;
    }

    /**
     * Obtém o servidor com base no CPF.
     *
     * @param string $cpf O CPF do servidor.
     * @return array|null
     */
    public function get_servant_by_cpf($cpf) {
        // Filtra os servidores ativos
        $all_servants = $this->get_all_servants_by_status('ATIVO');

        // Itera sobre os servidores para encontrar o servidor com o CPF correspondente
        foreach ($all_servants as $servant) {
            // Verifica se o login do servidor existe e compara com o CPF
            if (isset($servant['login']) && $servant['login'] === $cpf) {
                //var_dump($servant);
                return $servant;  // Retorna os dados do servidor correspondente
            }
        }

        // Se o servidor não for encontrado, exibe uma mensagem de erro
        mtrace("CPF {$cpf} não encontrado");

        // Retorna null se o servidor não for encontrado
        return null;
    }
}
