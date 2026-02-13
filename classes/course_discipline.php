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

    public $semester_offered;              // Semestre da oferta
    public $current_enrollment_semester;   // Semestre que o aluno está cursando

    public $period;
    public $enrollment_status;
    public $class_group;
    public $education_mode;
    public $shift;

    // Construtor da classe
    public function __construct(
        $course_id,
        $course_code,
        $course_name,
        $course_level,
        $status,
        $discipline_name,
        $discipline_code,
        $discipline_id,
        $semester_offered,
        $current_enrollment_semester,
        $period,
        $enrollment_status,
        $class_group,
        $education_mode,
        $shift
    ) {
        $this->course_id = $course_id;
        $this->course_code = $course_code;
        $this->course_name = $course_name;
        $this->course_level = $course_level;
        $this->status = $status;
        $this->discipline_name = $discipline_name;
        $this->discipline_code = $discipline_code;
        $this->discipline_id = $discipline_id;

        $this->semester_offered = $semester_offered;
        $this->current_enrollment_semester = $current_enrollment_semester;

        $this->period = $period;
        $this->enrollment_status = $enrollment_status;
        $this->class_group = $class_group;
        $this->education_mode = $education_mode;
        $this->shift = $shift;
    }

    public function isEqual(course_discipline $other)
    {
        return $this->course_id === $other->course_id &&
            $this->course_code === $other->course_code &&
            $this->course_name === $other->course_name &&
            $this->course_level === $other->course_level &&
            $this->status === $other->status &&
            $this->discipline_name === $other->discipline_name &&
            $this->discipline_code === $other->discipline_code &&
            $this->discipline_id === $other->discipline_id &&
            $this->semester_offered === $other->semester_offered &&
            $this->current_enrollment_semester === $other->current_enrollment_semester &&
            $this->period === $other->period &&
            $this->enrollment_status === $other->enrollment_status &&
            $this->class_group === $other->class_group &&
            $this->education_mode === $other->education_mode &&
            $this->shift === $other->shift;
    }

    /**
     * Retorna o semestre efetivo seguindo a regra:
     * 1) current_enrollment_semester (prioridade)
     * 2) semester_offered (fallback)
     * Considera válido o valor 0 (disciplina optativa).
     */
    public function getEffectiveSemester(): ?string
    {
        // 1️⃣ prioridade: semestre que está cursando
        if ($this->current_enrollment_semester !== null &&
            trim((string)$this->current_enrollment_semester) !== '') {
            return (string)$this->current_enrollment_semester;
        }

        // 2️⃣ se semester_offered for array (caso da API nova)
        if (is_array($this->semester_offered) && !empty($this->semester_offered)) {
            return (string) reset($this->semester_offered);
        }

        /*/ 3️⃣ se ainda for string (compatibilidade antiga)
        if ($this->semester_offered !== null &&
            !is_array($this->semester_offered) &&
            trim((string)$this->semester_offered) !== '') {
            return (string)$this->semester_offered;
        }
        */
        return null;
    }

    // Função que cria o class_group
    public function generate_class_group(campus $campus): ?string
    {
        $class_group_null = "SemTurma";

        if ($this->class_group !== null && trim($this->class_group) !== '') {
            return str_replace(' ', '', $this->class_group);
        } elseif ($campus->createcourseifturmanull) {
            return $class_group_null;
        } else {
            mtrace("ERROR: Revisar a criação do class_group");
            return false;
        }
    }
    // Função que cria o class_group


    public function generate_course_idnumber(campus $campus)
    {
        $class_group = $this->generate_class_group($campus);
        if ($class_group === false) { return false; }
        $semester = $this->getEffectiveSemester();
        $idnumber = "{$campus->id_campus}.{$this->course_id}.{$this->discipline_id}.{$class_group}.{$this->period}";
        if ($semester !== null) { $idnumber .= '.' . $semester; }
        return $idnumber;
    }

}
