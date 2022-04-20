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
    $DataInicioXls = $_POST['DataInicioPdf'];
    $DataTerminoXls = $_POST['DataTerminoPdf'];

    $DataInicialXls = date('d/m/Y',$DataInicioXls);
    $DataFinalXls = date('d/m/Y',$DataTerminoXls);

// SQL
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
$ContaCapacitados = $DB->count_records_sql($SQL_ContaCapacitados, [$DataInicioXls,$DataTerminoXls]);


    $arquivo = 'planilhactts.xls';

    // Criamos uma tabela HTML com o formato da planilha
    $html = '';
    $html .= '<meta charset="utf-8">';
    $html .= '<table border="1">';
    $html .= '<tr>';
    $html .= '<td>'.$ContaCapacitados.'</tr>';
    $html .= '</tr>';
    $html .= '</table>';

    // Configurações header para forçar o download
    header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
    header ("Cache-Control: no-cache, must-revalidate");
    header ("Pragma: no-cache");
    header ("Content-type: application/x-msexcel");
    header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" );
    header ("Content-Description: PHP Generated Data" );
    // Envia o conteúdo do arquivo
    echo $html;
    exit; ?>
    </body>
</html>