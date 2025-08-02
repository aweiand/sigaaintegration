<?php
namespace local_sigaaintegration;

use core\context;
use core_user;
use dml_exception;
use Exception;
use local_sigaaintegration\utils\sigaa_utils;
use moodle_exception;

class sigaa_courses_sync extends sigaa_base_sync{

    private string $year;
    private string $period;

    private $course_discipline_mapper;

    public function __construct(string $year, string $period) {
        parent::__construct();
        $this->year = $year;
        $this->period = $period;
        $this->course_discipline_mapper = new course_discipline_mapper();
    }

    protected function get_records(campus $campus): array
    {
        mtrace("CONFIG: Criar turmas com valor null: " . ($campus->createcourseifturmanull ? "Ativada" : "DESATIVADA"));
        mtrace("CONFIG: Criar turmas individualizadas: " . ($campus->create_turmaindividualizada ? "Ativada" : "DESATIVADA"));
        mtrace('INFO: Importando disciplinas...');

        $academic_period = sigaa_academic_period::buildFromParameters($this->year, $this->period);
        $enrollments = $this->api_client->get_enrollments($campus, $academic_period);
        return $this->get_all_course_discipline($campus, $enrollments);
    }

    protected function process_records(campus $campus, array $records): void
    {
        try {
            foreach ($records as $course_discipline){
               $this->create_course_for_discipline($campus, $course_discipline);
            }
        } catch (Exception $e) {
            mtrace(sprintf(
                'ERROR: Falha ao criar categorias, erro: %s',
                $e->getMessage()
            ));
        }
    }

    private function create_course_for_discipline(campus $campus, course_discipline $course_discipline) {
        global $CFG;
        require_once($CFG->dirroot . '/course/lib.php');

        try {
            mtrace("Processando ID Disciplina: {$course_discipline->discipline_id}");
            $course_idnumber = $course_discipline->generate_course_idnumber($campus);
            if($course_idnumber) {
                mtrace("idnumber: " . $course_idnumber);
                $class_group = $course_discipline->generate_class_group($campus);
                $period = sigaa_utils::remove_zero_in_the_period($course_discipline->period);

                $semester = $course_discipline->current_enrollment_semester;
                $has_semester = isset($semester) && $semester !== '';
                $suffix = sigaa_utils::get_year_or_semester_suffix($course_discipline->period);

                // Fullname
                $fullname = "{$course_discipline->discipline_name} / {$course_discipline->course_name}";
                $fullname .= $has_semester ? " / {$semester}{$suffix}" : "";
                $fullname .= " / {$period}";

                // Shortname
                $shortname = "{$course_discipline->discipline_code} / {$course_discipline->course_id} / {$class_group}";
                $shortname .= $has_semester ? " / {$semester}{$suffix}" : "";
                $shortname .= " / {$course_discipline->period}";

                if (!$this->course_exists($course_idnumber)) {
                    $category_idnumber = $this->generate_category_level_three_id($campus, $course_discipline);
                    $category = $this->get_category_for_discipline($category_idnumber);

                    if ($category) {
                        $newCourse = (object)[
                            'fullname' => $fullname,
                            'shortname' => $shortname,
                            'category' => $category->id,
                            'idnumber' => $course_idnumber,
                            'summary' => $course_discipline->discipline_name,
                            'summaryformat' => FORMAT_PLAIN,
                            'format' => 'topics',
                            'visible' => $campus->coursevisibility,
                            'numsections' => 10,
                            'startdate' => time()
                        ];

                        $new_course = create_course($newCourse);

                        mtrace(sprintf(
                            'INFO: Disciplina criada. idnumber: %s, fullname: %s',
                            $new_course->idnumber,
                            $new_course->fullname
                        ));
                    } else {
                        mtrace("ERROR: Falha ao importar disciplina. Categoria não cadastrada: " . $category_idnumber);
                    }
                }
            }
        } catch (Exception $exception) {
            mtrace('ERROR: Falha ao importar disciplina. erro:' . $exception->getMessage());
        }

    }

    private function get_all_course_discipline(campus $campus, $enrollments): ?array {
        // Inicializando um array para armazenar objetos course_discipline únicos
        $disciplines = [];

        // Percorrendo os dados dos alunos
        foreach ($enrollments as $enrollment => $student) {
            // Percorrendo as disciplinas do aluno
            foreach ($student["disciplinas"] as $discipline) {

                // Adiciona o nível do curso à disciplina (necessário para a lógica de validação)
                $discipline['curso_nivel'] = $student['curso_nivel'] ?? null;

                // Valida a disciplina
                if (sigaa_utils::validate_discipline($campus, $discipline)) {
                    // Mapeia os dados da disciplina para o objeto course_discipline
                    $discipline_obj = $this->course_discipline_mapper->map_to_course_discipline($student, $discipline);

                    // Filtra pelo idnumber da disciplina
                    $id = $discipline_obj->generate_course_idnumber($campus);
                    if (!isset($disciplines[$id])) {
                        $disciplines[$id] = $discipline_obj;
                    }
                } else {
                    mtrace('A disciplina contém dados inválidos: ' . json_encode($discipline));
                }
            }
        }
        mtrace("INFO: Total de disciplinas únicas geradas para processamento: " . count($disciplines));
        return $disciplines;
    }

    public function get_category_for_discipline($idnumber) {
        global $DB;
        return $DB->get_record('course_categories', ['idnumber' => $idnumber]);
    }

    public function course_exists($idnumber) {
        global $DB;
        return $DB->record_exists('course', ['idnumber' => $idnumber]);
    }

    private function generate_category_level_three_id(campus $campus, course_discipline $course_discipline) {
        $base = "{$campus->id_campus}.{$course_discipline->course_id}.{$course_discipline->period}";

        if (property_exists($course_discipline, 'current_enrollment_semester') &&
            $course_discipline->current_enrollment_semester !== null &&
            $course_discipline->current_enrollment_semester !== '') {

            return $base . '.' . $course_discipline->current_enrollment_semester;
        }

        return $base;
    }


}
