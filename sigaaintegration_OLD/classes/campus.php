<?php
namespace local_sigaaintegration;

class campus
{
    public int $id_campus;
    public string $description;
    public bool $scheduled_sync;
    public int $modalidade_educacao;

    public bool $coursevisibility;

    public  bool $createcourseifturmanull;

    public bool $create_turmaindividualizada;

    public bool $syncemailwithsigaa;

    public bool $preserveinstitutionalemail;


    public const MODALIDADES = [
        1 => 'Presencial',
        2 => 'A Distância',
        3 => 'Semi-presencial',
        4 => 'Remoto'
    ];

    public function __construct(int $id_campus,
                                string $description,
                                bool $scheduled_sync,
                                int $modalidade_educacao,
                                bool $coursevisibility,
                                bool $createcourseifturmanull,
                                bool $create_turmaindividualizada,
                                bool $syncemailwithsigaa,
                                bool $preserveinstitutionalemail
    )
    {
        $this->id_campus = $id_campus;
        $this->description = $description;
        $this->scheduled_sync = $scheduled_sync;
        $this->modalidade_educacao = $modalidade_educacao;
        $this->coursevisibility = $coursevisibility;
        $this->createcourseifturmanull = $createcourseifturmanull;
        $this->create_turmaindividualizada = $create_turmaindividualizada;
        $this->syncemailwithsigaa = $syncemailwithsigaa;
        $this->preserveinstitutionalemail = $preserveinstitutionalemail;


    }

}
