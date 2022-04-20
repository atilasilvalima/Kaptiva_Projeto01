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

    // Variáveis de texto
    $Texto_TituloPagina = "[VARIVAEL] Título da Página";
    $Texto_NomeDaPagina = "[VARIAVEL] Relatório de Capacitados";
    $Texto_Tabela01 = "[VARIAVEL] - Nome da Tabela 01";
    $Texto_Tabela02 = "[VARIAVEL] - Nome da Tabela 02";

    $TextoSemData = "Sem período selecionado";
    $Texto_SelecionarPeriodo = "Favor selecionar um período acima";

    // Chama o Header
    require_once("./layouts/header.php");

?>

<body id="page-top">
<!-- Wrapper -->
<div id="wrapper">
    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <!-- Main Content -->
        <div id="content">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow BarraSuperior">
                <!-- Sidebar Toggle (Topbar) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>
                <?php echo $Texto_TituloPagina ?>
            </nav>
            <!-- End of Topbar -->

            <!-- Begin Page Content -->
            <div class="container-fluid">
                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800"><?php echo $Texto_NomeDaPagina ?></h1>
                </div>

                <div class="row">
                    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search" method="POST">
                        <div class="input-group">
                            <label>Período</label> &nbsp
                            <input id="date" type="date" name="DataInicio">
                            &nbsp a &nbsp
                            <input id="date" type="date" name="DataTermino">
                            &nbsp
                            &nbsp
                            <div><button name="submit" type="submit" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-chart-line fa-sm text-white-10"></i> Visualizar Dados</button>
                            <button name="submit" type="cancel" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm"><i class="fas fa-recycle fa-sm text-white-10"></i> Limpar Dados</button></div>
                        </div>

                        <?php
                            $DataInicio = strtotime($_POST['DataInicio']);
                            $DataTermino = strtotime($_POST['DataTermino']);

                            $DataInicial = date('d/m/Y',$DataInicio);
                            $DataFinal = date('d/m/Y',$DataTermino);
                        ?>
                    </form>
                </div>


                <?php

                    if ((empty($DataInicio)) AND (empty($DataTermino))) {
                        echo "
                            <br>
                            <div class='card border-left-danger'>
                                <div class='card-body'>
                                    $Texto_SelecionarPeriodo
                                </div>
                            </div>
                        ";
                    } else {

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
                    $ContaCapacitados = $DB->count_records_sql($SQL_ContaCapacitados, [$DataInicio,$DataTermino]);


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
                    $TotalMatriculas = $DB->count_records_sql($SQL_TotalMatriculas, [$DataInicio,$DataTermino]);


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
                    $ContaTotalConcluidos = $DB->count_records_sql($SQL_ContaTotalConcluidos,[$DataInicio,$DataTermino]);


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
                ?>


                <br>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Período da consulta:<strong> <?php echo $DataInicial ."  à ". $DataFinal ?></strong></h1>
                    </div>

                <!-- Row Card -->
                <div class="row">
                    <!-- Cards - BEGIN -->
                    <!-- Colaboradores Capacitados -->
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="font-weight-bold text-primary text-uppercase mb-1">
                                            Colaboradores Capacitados</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $ContaCapacitados ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cursos -->
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="font-weight-bold text-success text-uppercase mb-1">
                                            Cursos</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $ContaCategorias ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-school fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Cards - END -->
                </div>

                <!-- Row Dados Concluídos - BEGIN -->
                <div class="row">
                    <!-- Tabela Concluídos -->
                    <div class="col-xl-12 col-lg-12">
                        <div class="card shadow mb-4">
                            <!-- Tabela de Dado - BEGIN -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary"><?php echo $Texto_Tabela01 ?></h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive table-hover">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                            <tr>
                                                <th>Escola</th>
                                                <th>Qtd. Matrículas</th>
                                                <th>% Matrículas</th>
                                                <th>Qtd. Concluídos</th>
                                                <th>% Concluídos</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            foreach ($RES_ListaCategorias as $Categoria) {
                                                echo "<tr>";
                                                $Id_Categoria = $Categoria->id;
                                                echo "<td>" . $Categoria->name. "</td>";

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
                                                $ContaMatriculas = $DB->count_records_sql($SQL_ContaMatriculas,[$DataInicio,$DataTermino]);

                                                echo "<td>".$ContaMatriculas."</td>";

                                                // % de Matriculas com base no total
                                                $PorcMatricula = (100*$ContaMatriculas)/$TotalMatriculas;
                                                $PorcMatricula = number_format($PorcMatricula,2,",",".");
                                                echo "<td>".$PorcMatricula."%</td>";

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
                                                $ContaConcluidosCategoria = $DB->count_records_sql($SQL_ContaConcluidosCategoria,[$DataInicio,$DataTermino]);
                                                echo "<td>".$ContaConcluidosCategoria."</td>";

                                                // % de Concluídos com base no total
                                                $PorcConcluidos = (100*$ContaConcluidosCategoria)/$ContaTotalConcluidos;
                                                $PorcConcluidos = number_format($PorcConcluidos,2,",",".");
                                                echo "<td>".$PorcConcluidos."%</td>";
                                            }
                                            echo "</tr>";
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- Tabela de Dado - END -->
                        </div>
                    </div>
                </div>
                <!-- Row - Dados Concluídos - END -->

                <!-- Row Dados Horas - BEGIN -->
                <div class="row">
                    <!-- Tabela Concluídos -->
                    <div class="col-xl-12 col-lg-12">
                        <div class="card shadow mb-4">
                            <!-- Tabela de Dado - BEGIN -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary"><?php echo $Texto_Tabela02 ?></h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive table-hover">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                            <tr>
                                                <th>Escola</th>
                                                <th>Qtd. Horas Treinadas</th>
                                                <th>% Horas Treinadas</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            foreach ($RES_ListaCategoriasHoras as $ListaCategoriasHoras) {
                                                echo "<tr>";
                                                $Id_Categoria = $ListaCategoriasHoras->id;
                                                echo "<td>" . $ListaCategoriasHoras->name. "</td>";
                                                echo "<td> 12h </td>";
                                                echo "<td> 5% </td>";
                                            }
                                            echo "</tr>";
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- Tabela de Dado - END -->
                        </div>
                    </div>
                </div>
                <!-- Row - Dados Horas - END -->

                <!-- Row - Botão Gerar PDF - BEGIN -->
                        <div class="d-sm-flex align-items-end mb-4">
                            <h1 class="h3 mb-0 text-gray-800"></h1>
                            <form action="GerarPdf.php" method="POST">
                                <input TYPE="hidden" name="DataInicioPdf" value="<?php echo $DataInicio ?>">
                                <input type="hidden" name="DataTerminoPdf" value="<?php echo $DataTermino ?>">
                                <button name="submit" type="submit" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm"><i class="fas fa-download"></i> Gerar PDF</button>
                            </form>
                            &nbsp;
                            <form action="GerarPlanilha.php" method="POST">
                                <input TYPE="hidden" name="DataInicioPdf" value="<?php echo $DataInicio ?>">
                                <input type="hidden" name="DataTerminoPdf" value="<?php echo $DataTermino ?>">
                                <button name="submit" type="submit" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm"><i class="fas fa-file-excel"></i> Gerar XLS</button>
                            </form>
                        </div>
                <!-- Row - Botão Gerar PDF - END -->
                <br>
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->

        <!-- Footer -->
        <?php require_once("./layouts/footer.php"); ?>
        <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->
</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Footer JS -->
<?php require_once("./layouts/footer_js.php"); ?>
<!-- Footer JS -->

</body>
</html>
<?php
                    }
                    ?>