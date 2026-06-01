<?php
namespace local_sigaaintegration;

class course_discipline_mapper
{
    /**
     * Mapeia os dados do aluno e da disciplina para um objeto `course_discipline`.
     *
     * Regra:
     * - Se semestre_oferta_cursando estiver vazio, null ou inválido,
     *   usa semestre_oferta (fallback).
     * - Ignora valores como "Não Informado".
     */
    public function map_to_course_discipline(array $student, array $discipline): course_discipline
    {
        $course_data = [
            "course_id" => $student["id_curso"] ?? null,
            "course_code" => $student["cod_curso"] ?? null,
            "course_name" => $student["curso"] ?? null,
            "course_level" => $student["curso_nivel"] ?? null,
            "status" => $student["status"] ?? null
        ];

        // ==========================
        // Trata semestres_oferta
        // ==========================

        $semester_offered = null;

        if (!empty($discipline["semestres_oferta"])) {

            if (is_array($discipline["semestres_oferta"])) {

                $valid_semesters = array_filter(
                    $discipline["semestres_oferta"],
                    function ($value) {

                        if ($value === null) return false;

                        $value = trim((string)$value);

                        if ($value === '' || mb_strtolower($value) === 'não informado') {
                            return false;
                        }

                        // Remove caracteres não numéricos (ex: "1º")
                        $numeric = preg_replace('/[^0-9]/', '', $value);

                        return $numeric !== '';
                    }
                );

                if (!empty($valid_semesters)) {
                    $last = end($valid_semesters);
                    $semester_offered = preg_replace('/[^0-9]/', '', (string)$last);
                }

            } else {
                $value = trim((string)$discipline["semestres_oferta"]);

                if ($value !== '' && mb_strtolower($value) !== 'não informado') {
                    $semester_offered = preg_replace('/[^0-9]/', '', $value);
                }
            }
        }

        // ==========================
        // Trata semestre_oferta_cursando
        // ==========================

        $current_enrollment_semester = null;

        if (isset($discipline["semestre_oferta_cursando"])) {

            $value = trim((string)$discipline["semestre_oferta_cursando"]);

            if ($value !== '' && mb_strtolower($value) !== 'não informado') {
                $current_enrollment_semester = preg_replace('/[^0-9]/', '', $value);
            }
        }

        // Fallback final
        if ($current_enrollment_semester === null) {
            $current_enrollment_semester = $semester_offered;
        }

        return new course_discipline(
            $course_data["course_id"],
            $course_data["course_code"],
            $course_data["course_name"],
            $course_data["course_level"],
            $course_data["status"],
            $discipline["disciplina"] ?? null,
            $discipline["cod_disciplina"] ?? null,
            $discipline["id_disciplina"] ?? null,
            $semester_offered,
            $current_enrollment_semester,
            $discipline["periodo"] ?? null,
            $discipline["situacao_matricula"] ?? null,
            $discipline["turma"] ?? null,
            $discipline["modalidade_educacao_turma"] ?? null,
            $discipline["turno_turma"] ?? null
        );
    }
}
