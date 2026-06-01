<?php
namespace local_sigaaintegration;

use core\context;
use core_course_category;
use dml_exception;
use Exception;
use moodle_exception;
use stdClass;

class delete_categories_sync {
    // Função para deletar todas as categorias exceto a categoria raiz (ID: 1)
    private function delete_all_categories_except_root() {
        global $DB;

        // Pega todas as categorias, exceto a raiz (ID: 1)
        $categories = $DB->get_records_sql("SELECT * FROM {course_categories} WHERE id > 1");

        // Se não houver categorias para deletar
        if (empty($categories)) {
            echo "Não há categorias para deletar.\n";
            return;
        }

        // Contador para exibir o progresso
        $counter = 0;
        $total = count($categories);

        // Percorre cada categoria e tenta deletá-la
        foreach ($categories as $category) {
            try {
                // Carrega a categoria
                $category_object = core_course_category::get($category->id);

                // Função do Moodle que deleta uma categoria e seus cursos
                $category_object->delete_full(false); // 'false' significa que os cursos também serão deletados

                $counter++;
                echo "Categoria deletada: {$category->name} (ID: {$category->id})\n";
            } catch (Exception $e) {
                echo "Erro ao deletar a categoria {$category->name} (ID: {$category->id}): " . $e->getMessage() . "\n";
            }
        }

        // Exibe um resumo final
        echo "\nTotal de categorias deletadas: $counter de $total\n";
    }

    // Função principal para execução do script
    public function execute_category_deletion_script() {
        try {
            // Executa a função de deletar todas as categorias
            $this->delete_all_categories_except_root();

            echo "\nProcesso de deleção de categorias concluído com sucesso.\n";
        } catch (Exception $e) {
            echo "Erro durante a execução do script: " . $e->getMessage() . "\n";
        }
    }
}