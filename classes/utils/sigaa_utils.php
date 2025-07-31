<?php
/**
 * Funções utilitárias para integração com o SIGAA.
 *
 * @package    local_sigaaintegration
 * @category   utils
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 ou posterior
 */

namespace local_sigaaintegration\utils;

use local_sigaaintegration\campus;

/**
 * Classe utilitária para integração com o SIGAA.
 */
class sigaa_utils {

    /**
     * Remove "/0" do final da string do período, se presente.
     *
     * @param string $period A string do período.
     * @return string A string do período sem o "/0".
     */
    public static function remove_zero_in_the_period($period): string {
        return (substr($period, -2) === '/0') ? substr($period, 0, -2) : $period;
    }

    /**
     * Obtém o sufixo apropriado para um período (ano ou semestre).
     *
     * @param string $periodo A string do período.
     * @return string O sufixo correspondente (por exemplo, "º ano" ou "º semestre").
     */
    public static function get_year_or_semester_suffix($period): string {
        return (substr($period, -1) === '0') ? 'º ano' : 'º semestre';
    }

    /**
     * Valida se um array de disciplina contém os campos obrigatórios.
     *
     * @param array $disciplina Os dados da disciplina.
     * @return bool True se for válida, False caso contrário.
     */
    public static function validate_discipline(campus $campus, array $discipline): bool {

        $sem_turma = $campus->createcourseifturmanull;
        $turma_individualizada = $campus->create_turmaindividualizada;

        // Adicione estes mtraces para depuração
        // mtrace("Valor de \$sem_turma: " . ($sem_turma ? 'true' : 'false'));
        // mtrace("Valor de \$turma_individualizada: " . ($turma_individualizada ? 'true' : 'false'));

//($discipline['semestre_oferta_cursando'] !== null && $discipline['semestre_oferta_cursando'] !== '')
        // Verifica os campos básicos da disciplina
        $discipline_valid = isset($discipline['periodo']) && isset($discipline['semestre_oferta_cursando']) &&
            ($discipline['semestre_oferta_cursando'] !== null && $discipline['semestre_oferta_cursando'] !== '');

        // Se não permitir disciplina sem turma, então a turma deve estar definida e não ser nula
        if (!$sem_turma) {
            return $discipline_valid && isset($discipline['turma']) && $discipline['turma'] !== null;
        }

        // Verificar se permite a criação de disciplina para turmas individualizadas
        if(!$turma_individualizada && $discipline['turma'] !== null){
            if (substr($discipline['turma'], -3) === 'IND') {
                mtrace("Turma individualizada {$discipline['turma']}");
                return false;
            }
        }

        return $discipline_valid;
    }

}
