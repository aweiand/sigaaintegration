<?php
namespace local_sigaaintegration;
class course_discipline
{
    public $course_id;
    public $course_code;
    public $course_name;
    public $course_level;
    public $status;
    public $discipline_name;
    public $discipline_code;
    public $discipline_id;
    public $semester_offered;

    public $current_enrollment_semester;

    public $period;
    public $enrollment_status;
    public $class_group;
    public $education_mode;
    public $shift;

    // Construtor da classe
    public function __construct($course_id, $course_code, $course_name, $course_level, $status,
                                $discipline_name, $discipline_code, $discipline_id,
                                $current_enrollment_semester, $period,
                                $enrollment_status, $class_group, $education_mode, $shift)
    {
        $this->course_id = $course_id;
        $this->course_code = $course_code;
        $this->course_name = $course_name;
        $this->course_level = $course_level;
        $this->status = $status;
        $this->discipline_name = $discipline_name;
        $this->discipline_code = $discipline_code;
        $this->discipline_id = $discipline_id;

        $this->current_enrollment_semester = $current_enrollment_semester;

        $this->period = $period;
        $this->enrollment_status = $enrollment_status;
        $this->class_group = $class_group;
        $this->education_mode = $education_mode;
        $this->shift = $shift;
    }

    public function isEqual(course_discipline $other) {
        return $this->course_id === $other->course_id &&
            $this->course_code === $other->course_code &&
            $this->course_name === $other->course_name &&
            $this->course_level === $other->course_level &&
            $this->status === $other->status &&
            $this->discipline_name === $other->discipline_name &&
            $this->discipline_code === $other->discipline_code &&
            $this->discipline_id === $other->discipline_id &&
            $this->current_enrollment_semester === $other->current_enrollment_semester &&
            $this->period === $other->period &&
            $this->enrollment_status === $other->enrollment_status &&
            $this->class_group === $other->class_group &&
            $this->education_mode === $other->education_mode;
    }

    // Função que cria o class_group
    public function generate_class_group(campus $campus): ?string {
        $class_group_null = "SemTurma";

        if ($this->class_group !== null && !empty($this->class_group)) {
            // Remove espaços se class_group não for nulo/vazio
            return str_replace(' ', '', $this->class_group);
        } elseif ($campus->createcourseifturmanull) {
            return $class_group_null;
        } else {
            mtrace("ERROR: Revisar a criação do class_group");
            return false;
        }
    }

    public function generate_course_idnumber(campus $campus) {
        $class_group = $this->generate_class_group($campus);

        // Verifica se a criação do class_group falhou (retorno false)
        if ($class_group === false) {
            return false;
        }

        $idnumber = "{$campus->id_campus}.{$this->course_id}.{$this->discipline_id}.{$class_group}.{$this->period}";

        if (isset($this->current_enrollment_semester) && $this->current_enrollment_semester !== '') {
            $idnumber .= '.' . $this->current_enrollment_semester;
        }

        return $idnumber;
    }

}
