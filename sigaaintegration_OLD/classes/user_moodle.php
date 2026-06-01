<?php
namespace local_sigaaintegration;

use core\context;
use core_user;
use Exception;
use stdClass;

class user_moodle
{
    public function insert(array $record): string {
        global $DB, $CFG;

        if (!$this->is_user_registered_by_login($record['login'])) {

            try {
                // Validação dos dados do usuário
                $this->validate_user_data($record);

                // Cria o usuário
                $user = new stdClass();
                $user->username = $record['login'];
                $user->firstname = $this->get_first_name($record['nome_completo']);
                $user->lastname = $this->get_last_name($record['nome_completo']);
                $user->email = $record['email'];
                $user->password = hash_internal_user_password($this->generate_strong_password());
                $user->auth = 'manual';
                $user->confirmed = 1;
                $user->mnethostid = $CFG->mnet_localhost_id;

                $userid = $DB->insert_record('user', $user);
                mtrace("INFO: Usuário criado com sucesso. ID: $userid, Login: {$user->username}, Email: {$user->email}");
                return $userid;
            } catch (Exception $e) {
                mtrace("ERROR: Erro ao criar usuário {$record['login']}. Detalhes: " . $e->getMessage());
            }
        }
        return '';
    }

    public function update_email($user, string $newemail): bool {
        global $DB;

        if (!filter_var($newemail, FILTER_VALIDATE_EMAIL)) {
            mtrace("ERROR: E-mail fornecido não é válido: {$newemail}");
            return false;
        }
        try {
            // Atualiza o e-mail
            $user->email = $newemail;
            $DB->update_record('user', $user);
            mtrace("INFO: E-mail do usuário {$user->username} foi atualizado para {$newemail}.");
            return true;
        } catch (Exception $e) {
            mtrace("ERROR: Falha ao atualizar o e-mail do usuário {$user->username}: " . $e->getMessage());
            return false;
        }
    }

    public function is_institutional_email(string $email, string $institutional_domain): bool {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $email_domain = strtolower(substr(strrchr($email, "@"), 1));
        $institutional_domain = strtolower($institutional_domain);

        return substr_compare($email_domain, $institutional_domain, -strlen($institutional_domain)) === 0;
    }


    /**
     * Verifica se um usuário está cadastrado pelo login.
     *
     * @param string $login O login do usuário a ser verificado.
     * @return bool Retorna true se o usuário estiver cadastrado, false caso contrário.
     */
    public function is_user_registered_by_login($login): bool {
        global $DB;
        // Verifica se o login existe no banco de dados e  retorna ele
        return $DB->record_exists('user', ['username' => $login]);
    }

    public function get_user_by_login(?string $login): stdClass|false {
        global $DB;

        if (empty($login)) {
            mtrace('WARNING: get_user_by_login chamado com login vazio ou nulo.');
            return false;
        }

        return $DB->get_record('user', ['username' => $login]);
    }

    protected function validate_user_data(array $record): void {
        if (empty($record['login'])) {
            throw new Exception('O campo "login" é obrigatório.');
        }

        // Validação do email gerando um email se necessário
        /*
        if (empty($record['email']) || !filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {
            // Se o email estiver vazio ou não for válido, gerar um email aleatório
            $record['email'] = $this->generate_email($record);
            mtrace("INFO: Email gerado para o login {$record['login']}: {$record['email']}");
        }
        */

        // Verificação de email vazio
        if (empty($record['email'])) {
            throw new Exception("ERROR: O campo 'email' está vazio para o login {$record['login']}.");
        }
        // Verificação de email inválido
        elseif (!filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("ERROR: O e-mail fornecido para o login {$record['login']} não é válido: {$record['email']}");
        }
    }

    protected function generate_email(array $record): string {

        // Remove caracteres especiais do nome e sobrenome
        $nome = preg_replace('/[^a-zA-Z0-9]/', '', $this->get_first_name($record['nome_completo']));
        $sobrenome = preg_replace('/[^a-zA-Z0-9]/', '',  $this->get_last_name($record['nome_completo']));

        // Gera uma parte aleatória do email
        $parteAleatoria = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(5 / strlen($x)))), 1, 5);

        // Combina as partes para formar o email
        $email = strtolower("$nome.$sobrenome.$parteAleatoria@example.com");

        return $email;
    }

    function get_first_name($fullName) {
        return explode(' ', $fullName)[0];
    }

    function get_last_name($fullName) {
        // Verifica se o nome completo não está vazio e é uma string válida
        if (empty($fullName) || !is_string($fullName) || trim($fullName) === '') {
            return '';  // Return an empty string if the name is invalid or empty
        }

        // Encontra a posição do primeiro espaço no nome
        $spacePosition = strpos($fullName, ' ');

        // Se não houver espaço, significa que o nome completo não tem sobrenome
        if ($spacePosition === false) {
            return '';  // No surname (only a single name)
        }

        // Retorna tudo que vem após o primeiro espaço (resto do nome)
        return trim(substr($fullName, $spacePosition + 1));
    }

    function generate_strong_password($length = 8)
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%^&*()';

        // Garantindo que a senha terá pelo menos um de cada tipo
        $password = '';
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $special[rand(0, strlen($special) - 1)];

        // Preencher o restante da senha com caracteres aleatórios
        $all = $lowercase . $uppercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {  // Já temos 4 caracteres definidos acima
            $password .= $all[rand(0, strlen($all) - 1)];
        }

        // Embaralhar os caracteres para não deixar previsível
        $password = str_shuffle($password);

        return $password;
    }

    public function completarCPF($cpf)
    {
        return str_pad($cpf, 11, '0', STR_PAD_LEFT);
    }

}