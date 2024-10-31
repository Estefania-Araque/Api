<?php

require_once "models/connection.php";
require_once "controllers/post.controller.php";

if(isset($_POST)) {
    /*=============================================
    Separar propiedades en un arreglo
    =============================================*/
    $columns = array();
    
    foreach (array_keys($_POST) as $key => $value) {
        array_push($columns, $value);
    }

    /*=============================================
    Validar la tabla y las columnas
    =============================================*/
    if (empty(Connection::getColumnsData($table, $columns))) {
        $json = array(
            'status' => 400,
            'results' => "Error: Los campos del formulario no coinciden con la base de datos"
        );
        echo json_encode($json, http_response_code($json["status"]));
        return;
    }

    $response = new PostController();

    /*=============================================
    Peticion POST para registrar usuario
    =============================================*/    
    if (isset($_GET["register"]) && $_GET["register"] == true) {
        $suffix = $_GET["suffix"] ?? "user";
        $response->postRegister($table, $_POST, $suffix);

    /*=============================================
    Peticion POST para login de usuario
    =============================================*/    
    } else if (isset($_GET["login"]) && $_GET["login"] == true) {
        $suffix = $_GET["suffix"] ?? "user";
        $response->postLogin($table, $_POST, $suffix);

    /*=============================================
    Peticion POST para solicitar código de recuperación
    =============================================*/
    } else if (isset($_GET["passwordRecoveryRequest"]) && $_GET["passwordRecoveryRequest"] == true) {
        $suffix = $_GET["suffix"] ?? "user";
        $response->postPasswordRecoveryRequest($table, $_POST, $suffix);

    /*=============================================
    Peticion POST para verificar código de recuperación
    =============================================*/
    } else if (isset($_GET["verifyRecoveryCode"]) && $_GET["verifyRecoveryCode"] == true) {
        $suffix = $_GET["suffix"] ?? "user";
        $response->postVerifyRecoveryCode($table, $_POST, $suffix);
        return; // Añade este return para evitar que se ejecute más código después

    /*=============================================
    Peticion POST para cambiar contraseña
    =============================================*/
    } else if (isset($_GET["changePassword"]) && $_GET["changePassword"] == true) {
        $suffix = $_GET["suffix"] ?? "user";
        $response->postChangePassword($table, $_POST, $suffix);
    }
}