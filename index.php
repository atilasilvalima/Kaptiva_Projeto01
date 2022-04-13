<?php

    // REPORTAR ERROS
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // CONFIGURA CHARSET UTF-8 E SETA FORTALEZA COMO FUSO HORARIO
    header("Content-type: text/html; charset=utf-8");
    date_default_timezone_set('America/Fortaleza');

    // CONEXAO COM BANCO DE DADOS
    require_once ("./conn/conexao.php");


    // Variáveis de texto
    $Texto_TituloPagina = "[VARIVAEL] Título da Página";
    $Texto_NomeDaPagina = "[VARIAVEL] Relatório de Capacitados";
    $Texto_Tabela01 = "[VARIAVEL] - Nome da Tabela";

    // Chama o Header
    require_once("./layouts/header.php");


    // SQL Conta Capacitados
    $SQL_ContaCapacitados = "
        SELECT
            mdl_user.id
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
    $RES_ContaCapacitados = pg_query($conn, $SQL_ContaCapacitados);
    $ContaCapacitados = pg_num_rows($RES_ContaCapacitados);


    // SQL Conta Categorias
    $SQL_ContaCategorias = "
        SELECT
            mdl_course_categories.name
        FROM
            mdl_course_categories
    ";
    $RES_ContaCategorias = pg_query($conn, $SQL_ContaCategorias);
    $ContaCategorias = pg_num_rows($RES_ContaCategorias);



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

                    <!-- Row Dados Categorias - BEGIN -->
                    <div class="row">

                        <!-- Area Chart -->
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
                                                                    echo "<td>CC</td>";
                                                                    echo "<td>DDDDDD</td>";
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

                        <!-- Pie Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Revenue Sources</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Dropdown Header:</div>
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="myPieChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-primary"></i> Direct
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-success"></i> Social
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-info"></i> Referral
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Row - Dados Categorias - END -->

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Content Column -->
                        <div class="col-lg-6 mb-4">

                            <!-- Project Card Example -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Projects</h6>
                                </div>
                                <div class="card-body">
                                    <h4 class="small font-weight-bold">Server Migration <span
                                            class="float-right">20%</span></h4>
                                    <div class="progress mb-4">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 20%"
                                            aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <h4 class="small font-weight-bold">Sales Tracking <span
                                            class="float-right">40%</span></h4>
                                    <div class="progress mb-4">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 40%"
                                            aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <h4 class="small font-weight-bold">Customer Database <span
                                            class="float-right">60%</span></h4>
                                    <div class="progress mb-4">
                                        <div class="progress-bar" role="progressbar" style="width: 60%"
                                            aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <h4 class="small font-weight-bold">Payout Details <span
                                            class="float-right">80%</span></h4>
                                    <div class="progress mb-4">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 80%"
                                            aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <h4 class="small font-weight-bold">Account Setup <span
                                            class="float-right">Complete!</span></h4>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%"
                                            aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Color System -->
                            <div class="row">
                                <div class="col-lg-6 mb-4">
                                    <div class="card bg-primary text-white shadow">
                                        <div class="card-body">
                                            Primary
                                            <div class="text-white-50 small">#4e73df</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <div class="card bg-success text-white shadow">
                                        <div class="card-body">
                                            Success
                                            <div class="text-white-50 small">#1cc88a</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <div class="card bg-info text-white shadow">
                                        <div class="card-body">
                                            Info
                                            <div class="text-white-50 small">#36b9cc</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <div class="card bg-warning text-white shadow">
                                        <div class="card-body">
                                            Warning
                                            <div class="text-white-50 small">#f6c23e</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <div class="card bg-danger text-white shadow">
                                        <div class="card-body">
                                            Danger
                                            <div class="text-white-50 small">#e74a3b</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <div class="card bg-secondary text-white shadow">
                                        <div class="card-body">
                                            Secondary
                                            <div class="text-white-50 small">#858796</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <div class="card bg-light text-black shadow">
                                        <div class="card-body">
                                            Light
                                            <div class="text-black-50 small">#f8f9fc</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <div class="card bg-dark text-white shadow">
                                        <div class="card-body">
                                            Dark
                                            <div class="text-white-50 small">#5a5c69</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-lg-6 mb-4">

                            <!-- Illustrations -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Illustrations</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 25rem;"
                                            src="img/undraw_posting_photo.svg" alt="...">
                                    </div>
                                    <p>Add some quality, svg illustrations to your project courtesy of <a
                                            target="_blank" rel="nofollow" href="https://undraw.co/">unDraw</a>, a
                                        constantly updated collection of beautiful svg images that you can use
                                        completely free and without attribution!</p>
                                    <a target="_blank" rel="nofollow" href="https://undraw.co/">Browse Illustrations on
                                        unDraw &rarr;</a>
                                </div>
                            </div>

                            <!-- Approach -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Development Approach</h6>
                                </div>
                                <div class="card-body">
                                    <p>SB Admin 2 makes extensive use of Bootstrap 4 utility classes in order to reduce
                                        CSS bloat and poor page performance. Custom CSS classes are used to create
                                        custom components and custom utility classes.</p>
                                    <p class="mb-0">Before working with this theme, you should become familiar with the
                                        Bootstrap framework, especially the utility classes.</p>
                                </div>
                            </div>

                        </div>
                    </div>

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