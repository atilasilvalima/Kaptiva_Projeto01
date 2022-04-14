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

    // Chama o Header
    require_once("./layouts/header.php");


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
    ";
    $ContaCapacitados = $DB->count_records_sql($SQL_ContaCapacitados);


    // SQL Conta Categorias
    $SQL_ContaCategorias = "
        SELECT
            COUNT(mdl_course_categories.name)
        FROM
            mdl_course_categories
    ";
    $ContaCategorias = $DB->count_records_sql($SQL_ContaCategorias);



    // SQL Lista Categorias
    $SQL_ListaCategorias = "
        SELECT
            mdl_course_categories.id,
            mdl_course_categories.name
        FROM
            mdl_course_categories
        ORDER BY 
            mdl_course_categories.name ASC
    ";
    $RES_ListaCategorias = pg_query($conn, $SQL_ListaCategorias);
    $ListaCategorias = pg_num_rows($RES_ListaCategorias);


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
    $RES_ListaCategoriasHoras = pg_query($conn, $SQL_ListaCategoriasHoras);


    // SQL Total de Matriculas
    $SQL_TotalMatriculas = "
        SELECT
            mdl_role_assignments.id, 
            mdl_role_assignments.userid, 
            mdl_course.fullname, 
            mdl_course.category
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
    ";
    $RES_TotalMatriculas = pg_query($conn, $SQL_TotalMatriculas);
    $TotalMatriculas = pg_num_rows($RES_TotalMatriculas);


    // SQL Conta Total de Concluídos
    $SQL_ContaTotalConcluidos = "
        SELECT
            mdl_course_categories.id
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
            mdl_grade_items.itemtype = 'course' 
        ";
    $RES_ContaTotalConcluidos = pg_query($conn, $SQL_ContaTotalConcluidos);
    $ContaTotalConcluidos = pg_num_rows($RES_ContaTotalConcluidos);

    // Consultas testes - BEGIN
//    $ContaUser = $DB->count_records('user');
//    echo $ContaUser;


//    $ListaDeCategorias = $DB -> get_record ( 'course_categories');
//    echo $ListaDeCategorias;



    // Consultas testes - END

?>

<body id="page-top">

    <!-- Page Wrapper -->
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
                        <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                            <div class="input-group">
                                <label>Período</label> &nbsp
                                <input id="date" type="date">
                                &nbsp a &nbsp
                                <input id="date" type="date">
                                &nbsp
                                &nbsp
                                <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-chart-line fa-sm text-white-10"></i> Visualizar Dados</a>
                            </div>

                        </form>

                    </div>

                    <br>

                    <!-- Row Card -->
                    <div class="row">
                        <!-- Cards - BEGIN -->

                        <!-- Colaboradores Capacitados -->
                        <div class="col-xl-6 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
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
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
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
                                                            while ($RowCategorias = pg_fetch_array($RES_ListaCategorias)) {                                                                
                                                                echo "<tr>";
                                                                    $Id_Categoria = $RowCategorias['id'];
                                                                    echo "<td>" . $RowCategorias['name']. "</td>";
                                                                    
                                                                    // SQL Conta Matriculas Por Categoria
                                                                    $SQL_ContaMatriculas = "
                                                                        SELECT
                                                                            mdl_role_assignments.id, 
                                                                            mdl_role_assignments.userid, 
                                                                            mdl_course.fullname, 
                                                                            mdl_course.category
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
                                                                        WHERE category = $Id_Categoria                                                                      
                                                                    ";
                                                                    $RES_ContaMatriculas = pg_query($conn, $SQL_ContaMatriculas);
                                                                    $ContaMatriculas = pg_num_rows($RES_ContaMatriculas);

                                                                    echo "<td>".$ContaMatriculas."</td>";

                                                                    // % de Matriculas com base no total
                                                                    $PorcMatricula = (100*$ContaMatriculas)/$TotalMatriculas;
                                                                    $PorcMatricula = number_format($PorcMatricula,2,",",".");
                                                                    echo "<td>".$PorcMatricula."%</td>";

                                                                // SQL Conta Concluídos
                                                                $SQL_ContaConcluidosCategoria = "
                                                                    SELECT
                                                                        mdl_course_categories.id
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
                                                                        mdl_course_categories.id = $Id_Categoria   
                                                                ";
                                                                $RES_ContaConcluidosCategoria = pg_query($conn, $SQL_ContaConcluidosCategoria);
                                                                $ContaConcluidosCategoria = pg_num_rows($RES_ContaConcluidosCategoria);
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
                                                        while ($RowCategoriasHoras = pg_fetch_array($RES_ListaCategoriasHoras)) {
                                                            echo "<tr>";
                                                            $Id_Categoria = $RowCategoriasHoras['id'];
                                                            echo "<td>" . $RowCategoriasHoras['name']. "</td>";
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

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="login.html">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="assets/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="assets/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="assets/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-pie-demo.js"></script>

</body>

</html>