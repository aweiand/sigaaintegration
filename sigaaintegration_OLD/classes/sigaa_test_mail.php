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
 * @copyright 2024, DTI Alvorada
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace local_sigaaintegration;

 use core\context;
 use core_course_category;
 use Exception;

class sigaa_test_mail {
    private string $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    public function sync(): void
    {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        try {
            $this->send_mail_test();
            $transaction->allow_commit();
        } catch (Exception $e) {
            $transaction->rollback($e);
            mtrace('ERROR: Transação falhou. ' . $e->getMessage());
        }

        gc_collect_cycles(); // Liberar memória após cada lote

    }

    private function send_mail_test(): void
    {
        try {
            // Buscar o usuário no banco
            $user = $this->search_user(trim($this->username));
            if (!$user) {
                mtrace(sprintf('ERRO: Usuário não encontrado. usuário: %s', $this->username));
            } else {
                mtrace(sprintf('INFO: Usuário encontrado. usuário: %s', $this->username));
                $this->send_notification($user);
            }

        } catch (Exception $e) {
            mtrace(sprintf(
                'ERRO: Falha ao processar inscrição de professor em uma disciplina. ' .
                'cpf: %s, erro: %s',
                $this->username,
                $e->getMessage()
            ));
        }
    }

    /**
     * Busca professor pelo login/CPF.
     */
    private function search_user(string $username): object|false
    {
        global $DB;
        return $DB->get_record('user', ['username' => $username]);
    }


    /**
     * Envia notificação por e-mail para o professor informando sobre a inscrição na disciplina.
     */
    private function send_notification(object $user): void
    {
        global $CFG, $SITE;
        require_once($CFG->libdir . '/moodlelib.php'); // Importação para `email_to_user()`

        $subject = "E-mail de teste '{$CFG->wwwroot}'";
        $messagehtml = "<p>Olá <strong>{$user->firstname}</strong>,</p>";
        $messagehtml .= "<p>Teste de envio de e-mail.</p>";
        $messagehtml .= "<p>Atenciosamente,<br>Equipe {$SITE->fullname}</p>";

        $messagetext = strip_tags(str_replace('<br>', "\n", $messagehtml));

        $admin = get_admin(); // Usuário remetente (administrador do Moodle)

        // Enviar e-mail
        $email_sent = email_to_user($user, $admin, $subject, $messagetext, $messagehtml);

        if ($email_sent) {
            mtrace("INFO: E-mail de notificação enviado para {$user->email}");
        } else {
            mtrace("ERRO: Falha ao enviar e-mail para {$user->email}");
        }
    }

}
