<?php
namespace local_sigaaintegration;

class admin_setting_academic_period extends \admin_setting_configtext {

    /**
     * Valida o formato do valor (YYYY/N).
     *
     * @param string $data O valor fornecido pelo usuário.
     * @return string|bool Mensagem de erro ou true se for válido.
     */
    public function validate($data) {
        if (preg_match('/^\d{4}\/[12]$/', $data)) {
            return true; // Valor válido.
        }
        return get_string('error_current_term_format', 'local_sigaaintegration', $data);
    }
}