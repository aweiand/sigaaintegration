<?php
namespace local_sigaaintegration;

use core\context;
use core_user;
use dml_exception;
use Exception;
use local_sigaaintegration\utils\sigaa_utils;
use moodle_exception;
use context_course;

class sigaa_courses_sync extends sigaa_base_sync {

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
                'ERROR: Falha ao criar disciplinas, erro: %s',
                $e->getMessage()
            ));
        }
    }

    private function create_course_for_discipline(campus $campus, course_discipline $course_discipline)
    {
        global $CFG;
        require_once($CFG->dirroot . '/course/lib.php');

        try {
            mtrace("Processando ID Disciplina: {$course_discipline->discipline_id}");

            $course_idnumber = $course_discipline->generate_course_idnumber($campus);

            if (!$course_idnumber) {
                return;
            }

            mtrace("idnumber: " . $course_idnumber);

            $class_group = $course_discipline->generate_class_group($campus);
            $period = sigaa_utils::remove_zero_in_the_period($course_discipline->period);

            // REGRA CENTRALIZADA
            $semester = $course_discipline->getEffectiveSemester();
            $has_semester = $semester !== null;

            $suffix = sigaa_utils::get_year_or_semester_suffix($course_discipline->period);

            // =========================
            // FULLNAME
            // =========================
            $fullname = "{$course_discipline->discipline_name} / {$course_discipline->course_name}";
            $fullname .= $has_semester ? " / {$semester}{$suffix}" : "";
            $fullname .= " / {$period}";

            // =========================
            // SHORTNAME
            // =========================
            $shortname = "{$course_discipline->discipline_code} / {$course_discipline->course_id} / {$class_group}";
            $shortname .= $has_semester ? " / {$semester}{$suffix}" : "";
            $shortname .= " / {$course_discipline->period}";

            $course = $this->get_course_by_idnumber($course_idnumber);
            if (!$course) {

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

                    // 🔹 Habilita inscrição manual no curso criado
                    $manual_enrol = enrol_get_plugin('manual');
                    if ($manual_enrol) {
                        $instances = enrol_get_instances($new_course->id, true);
                        $manual_instance = null;
                        foreach ($instances as $instance) {
                            if ($instance->enrol === 'manual') {
                                $manual_instance = $instance;
                                break;
                            }
                        }

                        if (!$manual_instance) {
                            $student_roleid = configuration::getIdPapelAluno();

                            $manual_instance = $manual_enrol->add_instance($new_course, [
                                'status' => ENROL_INSTANCE_ENABLED,
                                'enrolperiod' => 0,
                                'roleid' => $student_roleid
                            ]);

                            mtrace("INFO: Inscrição manual ativada no curso {$new_course->id} com roleid {$student_roleid}");
                        }
                    }


                } else {
                    mtrace("ERROR: Categoria não encontrada: " . $category_idnumber);
                }
            } else {
                // 🔹 Curso já existe, vamos verificar inscrição manual
                $manual_enrol = enrol_get_plugin('manual');

                if ($manual_enrol) {
                    // pega todas as instâncias de inscrição do curso
                    $instances = enrol_get_instances($course->id, true); // true = somente habilitadas
                    $manual_instance = null;

                    foreach ($instances as $instance) {
                        if ($instance->enrol === 'manual') {
                            $manual_instance = $instance;
                            break;
                        }
                    }

                    if (!$manual_instance) {
                        mtrace("🚨 INSCRIÇÃO MANUAL NÃO EXISTE: Disciplina {$course->idnumber} / {$course->fullname}");
                    } else {
                        // verificar se a inscrição está habilitada
                        if ($manual_instance->status != ENROL_INSTANCE_ENABLED) {
                            mtrace("🚨 INSCRIÇÃO MANUAL DESATIVADA: Disciplina {$course->idnumber} / {$course->fullname}");
                        }
                    }
                } else {
                    mtrace("ERRO: Plugin de inscrição manual não encontrado!");
                }
            }

        } catch (Exception $exception) {
            mtrace('ERROR: Falha ao importar disciplina: ' . $exception->getMessage());
        }
    }

    private function get_all_course_discipline(campus $campus, $enrollments): ?array
    {
        $disciplines = [];

        foreach ($enrollments as $student) {

            foreach ($student["disciplinas"] as $discipline) {

                $discipline['curso_nivel'] = $student['curso_nivel'] ?? null;

                if (sigaa_utils::validate_discipline($campus, $discipline)) {

                    $discipline_obj = $this->course_discipline_mapper
                        ->map_to_course_discipline($student, $discipline);

                    $id = $discipline_obj->generate_course_idnumber($campus);

                    if ($id && !isset($disciplines[$id])) {
                        $disciplines[$id] = $discipline_obj;
                    }

                } else {
                    mtrace('Disciplina inválida: ' . json_encode($discipline));
                }
            }
        }

        mtrace("INFO: Total de disciplinas únicas geradas: " . count($disciplines));

        return $disciplines;
    }

    public function get_category_for_discipline($idnumber)
    {
        global $DB;
        return $DB->get_record('course_categories', ['idnumber' => $idnumber]);
    }

    public function course_exists($idnumber)
    {
        global $DB;
        $record = $DB->record_exists('course', ['idnumber' => $idnumber]);
        return $record ?: null;
    }

    private function get_course_by_idnumber(string $idnumber): ?object {
        global $DB;
        $record = $DB->get_record('course', ['idnumber' => $idnumber]);
        return $record ?: null;
    }

    private function generate_category_level_three_id(campus $campus, course_discipline $course_discipline)
    {
        $base = "{$campus->id_campus}.{$course_discipline->course_id}.{$course_discipline->period}";

        // 🔹 Mesma regra do idnumber
        $semester = $course_discipline->getEffectiveSemester();

        if ($semester !== null) {
            return $base . '.' . $semester;
        }

        return $base;
    }
}