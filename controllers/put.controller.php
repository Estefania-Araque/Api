<?php 

require_once "models/put.model.php";

class PutController{

	/*=============================================
	Peticion Put para editar datos
	=============================================*/

	static public function putData($table, $data, $id, $nameId){

		$response = PutModel::putData($table, $data, $id, $nameId);
		
		$return = new PutController();
		$return -> fncResponse($response);

	}

	/*=============================================
	Respuestas del controlador
	=============================================*/

	public function fncResponse($response){

		if(!empty($response)){

			$json = array(

				'status' => 200,
				'results' => $response

			);

		}else{

			$json = array(

				'status' => 404,
				'results' => 'Not Found',
				'method' => 'put'

			);

		}

		echo json_encode($json, http_response_code($json["status"]));

	}

}