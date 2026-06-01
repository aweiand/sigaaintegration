<?php

namespace local_sigaaintegration;

use core\context;
use core_course_category;
use dml_exception;
use Exception;
use moodle_exception;
use stdClass;

class delete_users_sync {
    // Função para verificar se o usuário é administrador
    private function is_user_admin($user_id) {
        global $DB;

        // Obtém o contexto do sistema (onde as permissões de administrador são aplicadas)
        //$system_context = context_system::instance();

        // Verifica se o usuário tem a função de administrador no contexto do sistema
        return is_siteadmin($user_id);
    }

    // Função para deletar todos os usuários, exceto administradores
    private function delete_all_users_except_admins() {
        global $DB;

        // Pega todos os usuários, exceto o usuário com ID 1 (geralmente o primeiro admin criado)
        $users = $DB->get_records_sql("SELECT * FROM {user} WHERE id > 1");

        // Se não houver usuários para deletar
        if (empty($users)) {
            echo "Não há usuários para deletar.\n";
            return;
        }

        // Contador para mostrar o progresso
        $counter = 0;
        $total = count($users);

        // Percorre cada usuário e tenta deletá-lo, exceto administradores
        foreach ($users as $user) {
            try {
                // Verifica se o usuário é administrador
                if ($this->is_user_admin($user->id)) {
                    echo "Usuário ignorado (Administrador): {$user->username} (ID: {$user->id})\n";
                    continue;
                }

                // Deleta o usuário, se não for administrador
                delete_user($user);
                $counter++;
                echo "Usuário deletado: {$user->username} (ID: {$user->id})\n";
            } catch (Exception $e) {
                echo "Erro ao deletar o usuário {$user->username} (ID: {$user->id}): " . $e->getMessage() . "\n";
            }
        }

        // Exibe um resumo final
        echo "\nTotal de usuários deletados: $counter de $total\n";
    }

    // Função principal para execução do script
    public function execute_script() {
        try {
            // Executa a função de deletar todos os usuários, exceto administradores
            $this->delete_all_users_except_admins();

            echo "\nProcesso de deleção concluído com sucesso.\n";
        } catch (Exception $e) {
            echo "Erro durante a execução do script: " . $e->getMessage() . "\n";
        }
    }
}