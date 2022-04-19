<?php

    // REPORTAR ERROS
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // CONFIGURA CHARSET UTF-8 E SETA FORTALEZA COMO FUSO HORARIO
    header("Content-type: text/html; charset=utf-8");
    date_default_timezone_set('America/Fortaleza');

    // CONEXAO COM BANCO DE DADOS
    require_once ("./conn/conexao.php");

    // Conecta ao Banco de dados usando a API do Moodle
    require_once ("../config.php");
    global $CFG;
    global $DB;

    // Pega a data atual
    $DataAtual = date('d/m/Y H:i:s');

    // Datas para o calculo
    $DataInicioPdf = $_POST['DataInicioPdf'];
    $DataTerminoPdf = $_POST['DataTerminoPdf'];

    $DataInicialPdf = date('d/m/Y',$DataInicioPdf);
    $DataFinalPdf = date('d/m/Y',$DataTerminoPdf);

    // Consultas SQL
    // SQL Conta Capacitados
    $SQL_ContaCapacitados = "
        SELECT
            COUNT (mdl_user.id)
        FROM
            mdl_user
            INNER JOIN
            mdl_role_assignments
            ON
                mdl_user.id = mdl_role_assignments.userid
            INNER JOIN
            mdl_context
            ON
                mdl_role_assignments.contextid = mdl_context.id
            INNER JOIN
            mdl_course
            ON
                mdl_context.instanceid = mdl_course.id
            INNER JOIN
            mdl_course_categories
            ON
                mdl_course.category = mdl_course_categories.id
        WHERE
            mdl_role_assignments.timemodified BETWEEN ? AND ?
    ";
    $ContaCapacitados = $DB->count_records_sql($SQL_ContaCapacitados, [$DataInicioPdf,$DataTerminoPdf]);
//
//
//    // SQL Conta Categorias (Melhor não filtrar por data)
//    $SQL_ContaCategorias = "
//        SELECT
//            COUNT(mdl_course_categories.name)
//        FROM
//            mdl_course_categories
//    ";
//    $ContaCategorias = $DB->count_records_sql($SQL_ContaCategorias);
//
//
//    // SQL Lista Categorias (Melhor não filtrar por data)
//    $SQL_ListaCategorias = "
//        SELECT
//            mdl_course_categories.id,
//            mdl_course_categories.name
//        FROM
//            mdl_course_categories
//        ORDER BY
//            mdl_course_categories.name ASC
//    ";
//    $RES_ListaCategorias = $DB->get_records_sql($SQL_ListaCategorias);
//
//
//    // SQL Total de Matriculas
//    // Conta total de matricula pela data, melhor seria pegar o total do sistema, sem data
//    $SQL_TotalMatriculas = "
//        SELECT
//            COUNT(mdl_role_assignments.id)
//        FROM
//            mdl_role_assignments
//            INNER JOIN
//            mdl_context
//            ON
//                mdl_role_assignments.contextid = mdl_context.id
//            INNER JOIN
//            mdl_course
//            ON
//                mdl_context.instanceid = mdl_course.id
//        WHERE
//            mdl_role_assignments.timemodified BETWEEN ? AND ?
//    ";
//    $TotalMatriculas = $DB->count_records_sql($SQL_TotalMatriculas, [$DataInicio,$DataTermino]);
//
//
//    // SQL Conta Total de Concluído
//    // Conta total de concluidos pela data, melhor seria pegar o total do sistema, sem data
//    $SQL_ContaTotalConcluidos = "
//        SELECT
//            COUNT(mdl_course_categories.id)
//        FROM
//            mdl_grade_items
//            INNER JOIN
//            mdl_grade_grades
//            ON
//                mdl_grade_items.id = mdl_grade_grades.itemid
//            INNER JOIN
//            mdl_course
//            ON
//                mdl_grade_items.courseid = mdl_course.id
//            INNER JOIN
//            mdl_course_categories
//            ON
//                mdl_course.category = mdl_course_categories.id
//        WHERE
//            mdl_grade_items.itemtype = 'course'  AND
//            mdl_grade_grades.timemodified BETWEEN ? AND ?
//    ";
//    $ContaTotalConcluidos = $DB->count_records_sql($SQL_ContaTotalConcluidos,[$DataInicio,$DataTermino]);
//
//
//    // SQL Lista Categorias Horas
//    $SQL_ListaCategoriasHoras = "
//        SELECT
//            mdl_course_categories.id,
//            mdl_course_categories.name
//        FROM
//            mdl_course_categories
//        ORDER BY
//            mdl_course_categories.name ASC
//    ";
//    $RES_ListaCategoriasHoras = $DB->get_records_sql($SQL_ListaCategorias);


    // Chama o MPDF
    include('./assets/mpdf/vendor/autoload.php');

    $mpdf = new \Mpdf\Mpdf(['orientation' => '']);

    $mpdf->SetHTMLFooter('
        <p style="text-align:center; font-size:10px">Universidade Corporativa | Emitido em: '.$DataAtual.'</p>
    ');

    $mpdf->WriteHTML('
    <div style="text-align: left">
        <img src="https://www.universidadecorporativa.celepar.pr.gov.br/pluginfile.php/1/theme_edumy/headerlogo1/1650379743/Logo%20com%20bot%C3%A3o%20coral.png" width="30%" alt="Logo Celepar">
    </div>
    
    <div style="text-align: right">
        <p>Dados de '.$DataInicialPdf.' à '.$DataFinalPdf.'</p>
    </div>
    
    <div>
        <p>Colaboradores Capacitados: '.$ContaCapacitados.'</p>
    </div>
    ');

//$mpdf->Output();
$mpdf->Output('Relatorio_'.$DataInicioPdf.'.pdf', 'D');

?>