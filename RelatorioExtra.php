<?php
    // REPORTAR ERROS
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // CONFIGURA CHARSET UTF-8 E SETA FORTALEZA COMO FUSO HORARIO
    header("Content-type: text/html; charset=utf-8");
    date_default_timezone_set('America/Fortaleza');

    // CONEXAO COM BANCO DE DADOS
    require_once ("./conn/conexao.php");

    // LISTA CURSOS
    $SQL_ContaUsersCategoria = "
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
          WHERE mdl_course_categories.name = '2022.01'
    ";
    $RES_ContaUsersCategoria = pg_query($conn, $SQL_ContaUsersCategoria);
    $ContaUsersCategoria = pg_num_rows($RES_ContaUsersCategoria);

    echo $ContaUsersCategoria;


?>