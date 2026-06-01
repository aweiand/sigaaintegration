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
 * Languages configuration for the local_sigaaintegration plugin.
 *
 * @package   local_sigaaintegration
 * @copyright 2024, Igor Ferreira Cemim
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Integração SIGAA';
$string['settings'] = 'Integração SIGAA - Configurações';
$string['apisettings'] = 'Configurações da API';
$string['apisettings_information'] = 'Configurações de URL e credenciais de acesso para a API do SIGAA.';
$string['userfields_settings'] = 'Campos de perfil do usuário';
$string['userfields_settings_information'] = 'Esses campos deverão ser criados manualmente antes da utilização do plug-in.';
$string['coursefields_settings'] = 'Campos personalizados de curso';
$string['coursefields_settings_information'] = 'Esses campos deverão ser criados manualmente antes da utilização do plug-in.';
$string['apibaseurl'] = 'URL base';
$string['apibaseurl_information'] = 'URL base da API do SIGAA.';
$string['apiclientid'] = 'Client ID';
$string['apiclientid_information'] = 'Client ID da API do SIGAA.';
$string['apiclientsecret'] = 'Client Secret';
$string['apiclientsecret_information'] = 'Client Secret da API do SIGAA.';

$string['othersettings'] = 'Outras Configurações';
$string['cpffieldname'] = 'Nome do Campo de CPF';
$string['cpffieldname_information'] = '<p>Nome breve do campo personalizado utilizado para armazenar o CPF do professor.<br/>
Configuração sugerida para o campo:
    <ul>
        <li><strong>Tipo:</strong> Campo de uma linha de texto</li>
        <li><strong>Limite de caracteres:</strong> 11</li>
        <li><strong>Este campo está trancado:</strong> Sim</li>
        <li><strong>A informação deve ser única:</strong> Sim</li>
        <li><strong>Quem pode ver este campo:</strong> Ninguém</li>
    </ul>
</p>';
$string['periodfieldname'] = 'Nome do Campo de Período Letivo';
$string['periodfieldname_information'] = '<p>Nome breve do campo personalizado utilizado para armazenar o período letivo do curso.<br/>
Configuração sugerida para o campo:
        <ul>
            <li><strong>Tipo:</strong> Texto curto</li>
            <li><strong>Número máximo de caracteres:</strong> 6</li>
            <li><strong>Bloqueado:</strong> Sim</li>
            <li><strong>Visível para:</strong> Ninguém</li>
        </ul>

</p>';
$string['metadatafieldname'] = 'Nome do Campo de Metadados';
$string['metadatafieldname_information'] = '<p>Nome breve do campo personalizado utilizado para armazenar os metadados do curso.<br/>
Configuração sugerida para o campo:
        <ul>
            <li><strong>Tipo:</strong> Texto curto</li>
            <li><strong>Número máximo de caracteres:</strong> 1333</li>
            <li><strong>Bloqueado:</strong> Sim</li>
            <li><strong>Visível para:</strong> Ninguém</li>
        </ul>
</p>';
$string['basecategory'] = 'Categoria Base';
$string['basecategory_information'] = 'Categoria onde serão inseridas as categorias e disciplinas importadas.';
$string['archivecategoryname'] = 'Nome da categoria de disciplinas arquivadas';
$string['archivecategoryname_information'] = 'Nome utilizado para criar a categoria de disciplinas arquivadas.';
$string['studentroleid'] = 'Papel de estudante';
$string['studentroleid_information'] = 'Papel utilizado para inscrever os estudantes nas disciplinas ao importar as matrículas.';
$string['teacherroleid'] = 'Papel de professor';
$string['teacherroleid_information'] = 'Papel utilizado para inscrever os professores nas disciplinas ao importar as disciplinas.';
$string['manageintegration'] = 'Integração SIGAA - Gerenciar Integração';
$string['period'] = 'Período Letivo (ano/período)';
$string['period_help'] = 'Informe o Período Letivo (ano/período) para qual o processamento será realizado.';
$string['importenrollments'] = '6. Importar matrículas de discentes';
$string['importservantenrollments'] = '4. Importar matrículas de docentes';
$string['importstudents'] = '5. Importar Estudantes';
$string['importservants'] = '3. Importar Professores';
$string['importcourses'] = '2. Importar disciplinas';
$string['importcategories'] = '1. Importar categorias';
$string['archivecourses'] = 'Arquivar disciplinas';
$string['import'] = 'Importar';
$string['archive'] = 'Arquivar';
$string['sync_task_name'] = 'Sync Task';
$string['error:no_enrol_instance'] = 'Manual enrol plugin is disabled.';
$string['error:user_already_enrolled'] = 'User "{$a->userid}" is already enrolled into course "{$a->courseid}"';
$string['error:course_already_exists'] = 'Course already exists.';

$string['clientlist'] = 'Lista de campus';
$string['clientlist_desc'] = 'Lista de campus a serem configurados, separados por vírgula. Exemplo: alvorada, osorio, poa. A lista deve conter apenas a primeira parte do domínio, como em "alvorada.ifrs.edu.br" onde o valor correto é "alvorada".';


$string['id_campus'] = 'id_campus';
$string['id_campus_information'] = 'id do campus na API do SIGAA';
$string['scheduled_sync_information'] = 'A tarefa de sincronização será agendada ou não.';
$string['scheduled_sync'] = 'scheduled_sync';
$string['current_term'] = 'current_term';
$string['current_term_information'] = 'current_term_information';
$string['error_current_term_format'] = 'O formato do período letivo "{$a}" está incorreto. Por favor, utilize o formato AAAA/N (onde AAAA é o ano e N é o número do semestre, 1 ou 2). Para mais informações, consulte a documentação.';

$string['presencial'] = 'Presencial';
$string['a_distancia'] = 'A Distância';
$string['semi_presencial'] = 'Semi-Presencial';
$string['remoto'] = 'Remoto';

$string['modalidade_educacao'] = 'Modalidade Educação';
$string['modalidade_educacao_information'] = 'Modalidade Educação';

$string['academic_period_information'] = 'Periodo letivo a ser sincronizado. Ex: 2025/1';

$string['coursevisibility'] = 'Visibilidade do curso';
$string['coursevisibility_desc'] = 'Defina se os cursos devem ser visíveis ou ocultos por padrão ao serem criados.';
$string['visible'] = 'Visível';
$string['hidden'] = 'Oculto';

$string['testmail'] = 'SIGAA Integration - Teste de E-mail';
$string['cpf'] = 'CPF';
$string['cpf_help'] = 'Digite seu CPF (Cadastro de Pessoa Física).';
$string['courseidnumber'] = 'Número de Identificação do Curso';
$string['courseidnumber_help'] = 'Digite o número de identificação do curso.';
$string['required'] = 'Este campo é obrigatório.';
$string['email_sent'] = 'E-mail enviado com os dados fornecidos.';
$string['invalidcpf'] = 'CPF ou username inválido.';

$string['createcourseifturmanull'] = 'Criar curso se a turma for nula';
$string['createcourseifturmanull_desc'] = 'Criar um novo curso quando o valor da turma for nulo.';

$string['syncemailwithsigaa'] = 'Sincronizar e-mail com o SIGAA';
$string['syncemailwithsigaa_desc'] = 'Se habilitado, o endereço de e-mail dos usuários será sincronizado com o valor fornecido pelo SIGAA durante os processos de integração.';

$string['preserveinstitutionalemail'] = 'Preservar e-mail institucional no Moodle durante a Sincronização.';
$string['preserveinstitutionalemail_desc'] = 'Se habilitado, o e-mail institucional do usuário não será sobrescrito pelo e-mail vindo do SIGAA durante a sincronização.';


$string['create_turmaindividualizada'] = 'Criar turmas individualizadas';
$string['create_turmaindividualizada_desc'] = 'Habilita a criação automática de disciplinas no Moodle para turmas individualizadas identificadas com o sufixo "IND".';

$string['campi_codes_title'] = 'Códigos dos campi';

$string['clientlist_info_desc'] = '11: Bento Gonçalves; 20: Canoas; 25: Erechim; 29: Osório; 31: Porto Alegre; 
35: Rio Grande; 40: Sertão; 46: Restinga; 47: Feliz; 48: Ibirubá; 49: Caxias do Sul; 50: Farroupilha; 52: Rolante; 
53: Alvorada; 54: Viamão; 55: Vacaria; 56: Veranópolis';