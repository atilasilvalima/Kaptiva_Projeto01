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

    $DataAtual = date(U);

    // Datas para o calculo
    $DataInicioXls = $_POST['DataInicioPdf'];
    $DataTerminoXls = $_POST['DataTerminoPdf'];

    $DataInicialXls = date('d/m/Y',$DataInicioXls);
    $DataFinalXls = date('d/m/Y',$DataTerminoXls);

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
    $ContaCapacitados = $DB->count_records_sql($SQL_ContaCapacitados, [$DataInicioXls,$DataTerminoXls]);


    // SQL Conta Categorias (Melhor não filtrar por data)
    $SQL_ContaCategorias = "
        SELECT
            COUNT(mdl_course_categories.name)
        FROM
            mdl_course_categories
    ";
    $ContaCategorias = $DB->count_records_sql($SQL_ContaCategorias);


    // SQL Lista Categorias (Melhor não filtrar por data)
    $SQL_ListaCategorias = "
        SELECT
            mdl_course_categories.id,
            mdl_course_categories.name
        FROM
            mdl_course_categories
        ORDER BY
            mdl_course_categories.name ASC
    ";
    $RES_ListaCategorias = $DB->get_records_sql($SQL_ListaCategorias);


    // SQL Total de Matriculas
    // Conta total de matricula pela data, melhor seria pegar o total do sistema, sem data
    $SQL_TotalMatriculas = "
        SELECT
            COUNT(mdl_role_assignments.id)
        FROM
            mdl_role_assignments
            INNER JOIN
            mdl_context
            ON
                mdl_role_assignments.contextid = mdl_context.id
            INNER JOIN
            mdl_course
            ON
                mdl_context.instanceid = mdl_course.id
        WHERE
            mdl_role_assignments.timemodified BETWEEN ? AND ?
    ";
    $TotalMatriculas = $DB->count_records_sql($SQL_TotalMatriculas, [$DataInicioXls,$DataTerminoXls]);


    // SQL Conta Total de Concluído
    // Conta total de concluidos pela data, melhor seria pegar o total do sistema, sem data
    $SQL_ContaTotalConcluidos = "
        SELECT
            COUNT(mdl_course_categories.id)
        FROM
            mdl_grade_items
            INNER JOIN
            mdl_grade_grades
            ON
                mdl_grade_items.id = mdl_grade_grades.itemid
            INNER JOIN
            mdl_course
            ON
                mdl_grade_items.courseid = mdl_course.id
            INNER JOIN
            mdl_course_categories
            ON
                mdl_course.category = mdl_course_categories.id
        WHERE
            mdl_grade_items.itemtype = 'course'  AND
            mdl_grade_grades.timemodified BETWEEN ? AND ?
    ";
    $ContaTotalConcluidos = $DB->count_records_sql($SQL_ContaTotalConcluidos,[$DataInicioXls,$DataTerminoXls]);


    // SQL Lista Categorias Horas
    $SQL_ListaCategoriasHoras = "
        SELECT
            mdl_course_categories.id,
            mdl_course_categories.name
        FROM
            mdl_course_categories
        ORDER BY
            mdl_course_categories.name ASC
    ";
    $RES_ListaCategoriasHoras = $DB->get_records_sql($SQL_ListaCategorias);

    // Nome do arquivo XLS que vai gerar
    $NomeArquivo = $DataAtual.'.xls';

    $DataAtual = date('d/m/Y - H:i:s');

    // Criamos uma tabela HTML com o formato da planilha
    $html = '';
    $html .= '<meta charset="utf-8">';
    $html .= '<table>';
        $html .= '<tr>';
            $html .= '<td>PERÍODO DA CONSULTA: '.$DataInicialXls.' à '.$DataFinalXls.'</td>';
        $html .= '</tr>';
        $html .= '<tr>';
            $html .= '<td>EMISSÃO: '.$DataAtual.'</td>';
        $html .= '</tr>';
        $html .= '<tr style="background-color: #8B1D6B">';
            $html .= '<th style="text-align: center; padding: 10px; color:#FFFFFF" scope="col">ESCOLAS</th>';
            $html .= '<th style="text-align: center; padding: 10px; color:#FFFFFF" scope="col">QTD.MATRÍCULA</th>';
            $html .= '<th style="text-align: center; padding: 10px; color:#FFFFFF" scope="col">% MATRÍCULAS</th>';
            $html .= '<th style="text-align: center; padding: 10px; color:#FFFFFF" scope="col">QTD.CONCLUÍDOS</th>';
            $html .= '<th style="text-align: center; padding: 10px; color:#FFFFFF" scope="col">% CONCLUÍDOS</th>';
        $html .= '</tr>';
        foreach ($RES_ListaCategorias as $Categoria) {
            $html .= '<tr>';
            $Id_Categoria = $Categoria->id;
            $html .= '<td>'.$Categoria->name.'</td>';

            // SQL Conta Matriculas Por Categoria OK
            $SQL_ContaMatriculas = "
                    SELECT
                        COUNT(mdl_role_assignments.id)
                    FROM
                        mdl_role_assignments
                        INNER JOIN
                        mdl_context
                        ON
                            mdl_role_assignments.contextid = mdl_context.id
                        INNER JOIN
                        mdl_course
                        ON
                            mdl_context.instanceid = mdl_course.id
                    WHERE category = $Id_Categoria AND 
                          mdl_role_assignments.timemodified BETWEEN ? AND ?
                ";
            $ContaMatriculas = $DB->count_records_sql($SQL_ContaMatriculas,[$DataInicioXls,$DataTerminoXls]);

            $html .= '<td>'.$ContaMatriculas.'</td>';

            // % de Matriculas com base no total
            $PorcMatricula = (100*$ContaMatriculas)/$TotalMatriculas;
            $PorcMatricula = number_format($PorcMatricula,2,",",".");

            $html .= '<td>'.$PorcMatricula.'%</td>';

            // SQL Conta Concluídos OK
            $SQL_ContaConcluidosCategoria = "
                    SELECT
                        COUNT(mdl_course_categories.id)
                    FROM
                        mdl_grade_items
                        INNER JOIN
                        mdl_grade_grades
                        ON
                            mdl_grade_items.id = mdl_grade_grades.itemid
                        INNER JOIN
                        mdl_course
                        ON
                            mdl_grade_items.courseid = mdl_course.id
                        INNER JOIN
                        mdl_course_categories
                        ON
                            mdl_course.category = mdl_course_categories.id
                    WHERE
                        mdl_grade_items.itemtype = 'course' AND
                        mdl_course_categories.id = $Id_Categoria AND
                        mdl_grade_grades.timemodified BETWEEN ? AND ?
                ";
            $ContaConcluidosCategoria = $DB->count_records_sql($SQL_ContaConcluidosCategoria,[$DataInicioXls,$DataTerminoXls]);

            $html .= '<td>'.$ContaConcluidosCategoria.'</td>';

            // % de Concluídos com base no total
            $PorcConcluidos = (100*$ContaConcluidosCategoria)/$ContaTotalConcluidos;
            $PorcConcluidos = number_format($PorcConcluidos,2,",",".");

            $html .= '<td>'.$PorcConcluidos.'%</td>';

        }
    $html .= '</table>';

    // Configurações header para forçar o download
    header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
    header ("Cache-Control: no-cache, must-revalidate");
    header ("Pragma: no-cache");
    header ("Content-type: application/x-msexcel");
    header ("Content-Disposition: attachment; filename=\"{$NomeArquivo}\"" );
    header ("Content-Description: PHP Generated Data" );
    // Envia o conteúdo do arquivo
    echo $html;
    exit; ?>
    </body>
</html>