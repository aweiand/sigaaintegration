<?php
namespace local_sigaaintegration;

class course_discipline_mapper
{
    /**
     * Mapeia os dados do aluno e da disciplina para um objeto `course_discipline`.
     *
     * Regra:
     * - Se semestre_oferta_cursando estiver vazio ou null,
     *   usa semestre_oferta (fallback).
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

        if (isset($discipline["semestres_oferta"])) {

            if (is_array($discipline["semestres_oferta"])) {

                $valid_semesters = array_filter(
                    $discipline["semestres_oferta"],
                    fn($value) => is_numeric($value) // inclui 0
                );

                if (!empty($valid_semesters)) {
                    $semester_offered = end($valid_semesters);
                }

            } else {
                $semester_offered = $discipline["semestres_oferta"];
            }
        }

        // ==========================
        // ALTERADO: Regra de fallback
        // ==========================

        // Preserva 0 como valor válido
        $current_enrollment_semester = (
            isset($discipline["semestre_oferta_cursando"]) &&
            $discipline["semestre_oferta_cursando"] !== ''
        )
            ? $discipline["semestre_oferta_cursando"]
            : $semester_offered;

        return new course_discipline(
            $course_data["course_id"],
            $course_data["course_code"],
            $course_data["course_name"],
            $course_data["course_level"],
            $course_data["status"],
            $discipline["disciplina"] ?? null,
            $discipline["cod_disciplina"] ?? null,
            $discipline["id_disciplina"] ?? null,
            $semester_offered, // semestre da matriz
            $current_enrollment_semester, // ALTERADO: agora com fallback
            $discipline["periodo"] ?? null,
            $discipline["situacao_matricula"] ?? null,
            $discipline["turma"] ?? null,
            $discipline["modalidade_educacao_turma"] ?? null,
            $discipline["turno_turma"] ?? null
        );
    }
}