<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Adds settings links to admin tree.
 *
 * @package   local_sigaaintegration
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use local_sigaaintegration\admin_setting_academic_period;

$settings = new admin_settingpage(
    'local_sigaaintegration',
     new lang_string('settings', 'local_sigaaintegration')
);
$ADMIN->add('root', $settings);

$manageintegration = new admin_externalpage(
    'local_sigaaintegration_manageintegration',
    new lang_string('manageintegration', 'local_sigaaintegration'),
    new moodle_url('/local/sigaaintegration/manageintegration.php')
);
$ADMIN->add('root', $manageintegration);

$manageintegration = new admin_externalpage(
    'local_sigaaintegration_testmail',
    new lang_string('testmail', 'local_sigaaintegration'),
    new moodle_url('/local/sigaaintegration/testmail.php')
);
$ADMIN->add('root', $manageintegration);

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'apisettings',
        new lang_string('apisettings', 'local_sigaaintegration'),
        new lang_string('apisettings_information', 'local_sigaaintegration')
    ));

    $apibaseurl = new admin_setting_configtext(
        'local_sigaaintegration/apibaseurl',
        new lang_string('apibaseurl', 'local_sigaaintegration'),
        new lang_string('apibaseurl_information', 'local_sigaaintegration'),
        '',
        PARAM_URL
    );
    $settings->add($apibaseurl);

    $apiclientid = new admin_setting_configtext(
        'local_sigaaintegration/apiclientid',
        new lang_string('apiclientid', 'local_sigaaintegration'),
        new lang_string('apiclientid_information', 'local_sigaaintegration'),
        ''
    );
    $settings->add($apiclientid);

    $apiclientsecret = new admin_setting_configpasswordunmask(
        'local_sigaaintegration/apiclientsecret',
        new lang_string('apiclientsecret', 'local_sigaaintegration'),
        new lang_string('apiclientsecret_information', 'local_sigaaintegration'),
        ''
    );
    $settings->add($apiclientsecret);

    $clientlist = new admin_setting_configtextarea(
        'local_sigaaintegration/clientlist',
        new lang_string('clientlist', 'local_sigaaintegration'),
        new lang_string('clientlist_desc', 'local_sigaaintegration'),
        '',
        PARAM_TEXT,
        null, // Não especifica largura (número de colunas).
        '3'  // Define o número de linhas.
    );
    // Adiciona o ícone de ajuda (tooltip)
    //$clientlist->set_help('clientlist', 'clientlist_help', 'local_sigaaintegration');

    $settings->add($clientlist);

    // Recupera a lista de clientes cadastrados
    $clients = get_config('local_sigaaintegration', 'clientlist');
    if ($clients) {
        // Divide os nomes por linhas ou vírgulas
        $clientnames = preg_split("/[\r\n,]+/", $clients, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($clientnames as $client) {
            $client = trim($client);
            $client = iconv('UTF-8', 'ASCII//TRANSLIT', $client);
            // Remover espaços e caracteres especiais
            $client = preg_replace('/[^a-zA-Z0-9]/', '', $client);
            $client = strtolower($client);
            $settings->add(new admin_setting_heading(
                'api_config_' . $client,
                new lang_string('client_config', 'local_sigaaintegration') . ': ' . $client,
                ''
            ));

            // Configuração para o id do campus
            $settings->add(new admin_setting_configtext(
                "local_sigaaintegration/id_campus_{$client}",
                new lang_string('id_campus', 'local_sigaaintegration') . " ({$client})",
                new lang_string('id_campus_information', 'local_sigaaintegration'),
                '',
                PARAM_TEXT
            ));

            $settings->add(new admin_setting_configcheckbox(
                "local_sigaaintegration/scheduled_sync_{$client}",
                new lang_string('scheduled_sync', 'local_sigaaintegration') . " ({$client})",
                new lang_string('scheduled_sync_information', 'local_sigaaintegration'),
                0
            ));

            $settings->add(new admin_setting_configselect(
                "local_sigaaintegration/modalidade_educacao_{$client}",
                new lang_string('modalidade_educacao', 'local_sigaaintegration') . " ({$client})",
                new lang_string('modalidade_educacao_information', 'local_sigaaintegration'),
                'Nao definido', // Valor padrão
                [
                    1 => new lang_string('presencial', 'local_sigaaintegration'),
                    2 => new lang_string('a_distancia', 'local_sigaaintegration'),
                    3 => new lang_string('semi_presencial', 'local_sigaaintegration'),
                    4 => new lang_string('remoto', 'local_sigaaintegration')
                ]
            ));

            $settings->add(new admin_setting_configselect(
                "local_sigaaintegration/coursevisibility_{$client}",
                new lang_string('coursevisibility', 'local_sigaaintegration'),
                new lang_string('coursevisibility_desc', 'local_sigaaintegration'),
                1, // Valor padrão: curso visível
                [
                    1 => new lang_string('visible', 'local_sigaaintegration'),
                    0 => new lang_string('hidden', 'local_sigaaintegration')
                ]
            ));

            $settings->add(new admin_setting_configcheckbox(
                "local_sigaaintegration/createcourseifturmanull_{$client}",
                new lang_string('createcourseifturmanull', 'local_sigaaintegration'),
                new lang_string('createcourseifturmanull_desc', 'local_sigaaintegration'),
                0 // Valor padrão: desmarcado (0)
            ));

            // Habilita a criação de Disciplinas que são Turmas Individualizas
            $settings->add(new admin_setting_configcheckbox(
                "local_sigaaintegration/create_turmaindividualizada_{$client}",
                new lang_string('create_turmaindividualizada', 'local_sigaaintegration'),
                new lang_string('create_turmaindividualizada_desc', 'local_sigaaintegration'),
                0 // Valor padrão: desmarcado (0)
            ));

            $settings->add(new admin_setting_configcheckbox(
                "local_sigaaintegration/syncemailwithsigaa_{$client}",
                new lang_string('syncemailwithsigaa', 'local_sigaaintegration'),
                new lang_string('syncemailwithsigaa_desc', 'local_sigaaintegration'),
                0 // Valor padrão: desmarcado (0 = desativado)
            ));

            $settings->add(new admin_setting_configcheckbox(
                "local_sigaaintegration/preserveinstitutionalemail_{$client}",
                new lang_string('preserveinstitutionalemail', 'local_sigaaintegration'),
                new lang_string('preserveinstitutionalemail_desc', 'local_sigaaintegration'),
                1 // Valor padrão: marcado (1 = preservar por padrão)
            ));

        }
    }

    $settings->add(new admin_setting_heading(
        'othersettings',
        new lang_string('othersettings', 'local_sigaaintegration'),
        ''
    ));

    $academic_period = new admin_setting_academic_period(
        'local_sigaaintegration/academic_period',
        new lang_string('academic_period', 'local_sigaaintegration'),
        new lang_string('academic_period_information', 'local_sigaaintegration'),
        '', // Valor padrão.
        PARAM_TEXT
    );

    $settings->add($academic_period);

    $basecategory = new admin_settings_coursecat_select(
        'local_sigaaintegration/basecategory',
        new lang_string('basecategory', 'local_sigaaintegration'),
        new lang_string('basecategory_information', 'local_sigaaintegration')
    );
    $settings->add($basecategory);

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());

        $student = get_archetype_roles('student');
        $student = reset($student);
        $studentroleid = new admin_setting_configselect(
            'local_sigaaintegration/studentroleid',
            new lang_string('studentroleid', 'local_sigaaintegration'),
            new lang_string('studentroleid_information', 'local_sigaaintegration'),
            $student->id ?? null,
            $options
        );

        $editingteacher = get_archetype_roles('editingteacher');
        $editingteacher = reset($editingteacher);
        $teacherroleid = new admin_setting_configselect(
            'local_sigaaintegration/teacherroleid',
            new lang_string('teacherroleid', 'local_sigaaintegration'),
            new lang_string('teacherroleid_information', 'local_sigaaintegration'),
            $editingteacher->id ?? null,
            $options
        );

        $settings->add($studentroleid);
        $settings->add($teacherroleid);
    }

    $settings->add(new admin_setting_heading(
        'local_sigaaintegration/clientlist_info',
        new lang_string('campi_codes_title', 'local_sigaaintegration'), // Deixe o título em branco se quiser só o texto
        new lang_string('clientlist_info_desc', 'local_sigaaintegration') // Texto de ajuda/instrução
    ));


}
