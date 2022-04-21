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
    $DataAtual = date('d/m/Y - H:i:s');

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
    $TotalMatriculas = $DB->count_records_sql($SQL_TotalMatriculas, [$DataInicioPdf,$DataTerminoPdf]);


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
    $ContaTotalConcluidos = $DB->count_records_sql($SQL_ContaTotalConcluidos,[$DataInicioPdf,$DataTerminoPdf]);


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


    // Chama o MPDF
    include('./assets/mpdf/vendor/autoload.php');

    $mpdf = new \Mpdf\Mpdf(['orientation' => '']);

    // Rodapé do PDF
    $mpdf->SetHTMLFooter('
        <p style="text-align:center; font-size:12px">Universidade Corporativa | Emitido em: '.$DataAtual.'</p>
    ');


    // Criação do PDF
    $mpdf->WriteHTML('
        <center>
        <table width="900" border="0">
          <tbody>
            <tr>
              <td colspan="5" align="center" valign="middle"><img src="https://www.universidadecorporativa.celepar.pr.gov.br/pluginfile.php/1/theme_edumy/headerlogo1/1650379743/Logo%20com%20bot%C3%A3o%20coral.png" alt="Logo Celepar" width="448" height="104"></td>
            </tr>
            <tr>
              <td width="180">&nbsp;</td>
              <td width="180">&nbsp;</td>
              <td width="180">&nbsp;</td>
              <td width="180">&nbsp;</td>
              <td width="180">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="5" align="center"><strong><span style="font-size:40px">RELATÓRIO DE DADOS</span></strong></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td colspan="5" style="font-size: 25px"><strong>PERÍODO DA CONSULTA:</strong> '.$DataInicialPdf.' à '.$DataFinalPdf.'</td>
            </tr>
            <tr>
              <td colspan="5" style="font-size: 25px"><strong>EMISSÃO:</strong> '.$DataAtual.'</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td colspan="5" style="font-size: 25px"><strong>COLABORADORES CAPACITADOS:</strong> '.$ContaCapacitados.'</td>
            </tr>
            <tr>
              <td colspan="5" style="font-size: 25px"><strong>CURSOS:</strong> '.$ContaCategorias.'</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
          </tbody>
        </table>
        <br>
        <span style="font-size: 15px"><strong>Nome da Tabela 01</strong></span><br>
        <table width="900" border="1" cellspacing="0" cellpadding="0" style="font-size: 18px; line-height: 30pt">
          <tbody>
            <tr style="background-color: #8B1D6B">
              <th style="text-align: center; padding: 10px; color:#FFFFFF" scope="col">ESCOLAS</th>
              <th style="text-align: center; padding: 10px; color:#FFFFFF" scope="col">QTD.MATRÍCULA</th>
              <th style="text-align: center; padding: 10px; color:#FFFFFF" scope="col">% MATRÍCULAS</th>
              <th style="text-align: center; padding: 10px; color:#FFFFFF" scope="col">QTD.CONCLUÍDOS</th>
              <th style="text-align: center; padding: 10px; color:#FFFFFF" scope="col">% CONCLUÍDOS</th>
            </tr>
    ');

            foreach ($RES_ListaCategorias as $Categoria) {
                $mpdf->WriteHTML('
                    <tr>
                ');

                $Id_Categoria = $Categoria->id;

                $mpdf->WriteHTML('
                    <td align="center" valign="middle" style="text-align: center">'.$Categoria->name.'</td>
                ');

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
                $ContaMatriculas = $DB->count_records_sql($SQL_ContaMatriculas,[$DataInicioPdf,$DataTerminoPdf]);

                $mpdf->WriteHTML('
                    <td align="center" valign="middle" style="text-align: center">'.$ContaMatriculas.'</td>;
                ');

                // % de Matriculas com base no total
                $PorcMatricula = (100*$ContaMatriculas)/$TotalMatriculas;
                $PorcMatricula = number_format($PorcMatricula,2,",",".");

                $mpdf->WriteHTML('
                    <td align="center" valign="middle" style="text-align: center">'.$PorcMatricula.'%</td>;
                ');

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
                $ContaConcluidosCategoria = $DB->count_records_sql($SQL_ContaConcluidosCategoria,[$DataInicioPdf,$DataTerminoPdf]);

                $mpdf->WriteHTML('
                    <td align="center" valign="middle" style="text-align: center">'.$ContaConcluidosCategoria.'</td>;
                ');

                // % de Concluídos com base no total
                $PorcConcluidos = (100*$ContaConcluidosCategoria)/$ContaTotalConcluidos;
                $PorcConcluidos = number_format($PorcConcluidos,2,",",".");

                $mpdf->WriteHTML('
                    <td align="center" valign="middle" style="text-align: center">'.$PorcConcluidos.'%</td>;
                ');
            }
            $mpdf->WriteHTML('
                </tr>
            ');

            $mpdf->WriteHTML('
          </tbody>
        </table>
        <p>&nbsp;</p>
        </center>   
    ');

$mpdf->Output();
//$mpdf->Output('Relatorio_'.$DataInicioPdf.'.pdf', 'D');

?>