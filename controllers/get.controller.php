<?php

require_once "models/get.model.php";

class GetController{

	/*=============================================
	Peticiones GET sin filtro
	=============================================*/

	static public function getData($table, $select,$orderBy,$orderMode,$startAt,$endAt){

		$response = GetModel::getData($table, $select,$orderBy,$orderMode,$startAt,$endAt);

		$return = new GetController();
		$return -> fncResponse($response);

	}

	/*=============================================
	Peticiones GET con filtro
	=============================================*/

	static public function getDataFilter($table, $select, $linkTo, $equalTo,$orderBy,$orderMode,$startAt,$endAt){

		$response = GetModel::getDataFilter($table, $select, $linkTo, $equalTo,$orderBy,$orderMode,$startAt,$endAt);

		$return = new GetController();
		$return -> fncResponse($response);

	}

	/*=============================================
	Peticiones GET sin filtro entre tablas relacionadas
	=============================================*/

	static public function getRelData1($rel,$type,$select,$orderBy,$orderMode,$startAt,$endAt){

		$response = GetModel::getRelData($rel,$type,$select,$orderBy,$orderMode,$startAt,$endAt);
		
		$return = new GetController();
		$return -> fncResponse($response);

	}


	/*=============================================
	Peticiones GET con filtro entre tablas relacionadas
	=============================================*/

	static public function getRelDataFilter($rel,$type,$select, $linkTo, $equalTo,$orderBy,$orderMode,$startAt,$endAt){

		$response = GetModel::getRelDataFilter($rel,$type,$select, $linkTo, $equalTo,$orderBy,$orderMode,$startAt,$endAt);
		
		$return = new GetController();
		$return -> fncResponse($response);

	}

	/*=============================================
	Peticiones GET para el buscador sin relaciones
	=============================================*/

	static public function getDataSearch($table, $select, $linkTo, $search,$orderBy,$orderMode,$startAt,$endAt){

		$response = GetModel::getDataSearch($table, $select, $linkTo, $search,$orderBy,$orderMode,$startAt,$endAt);
		
		$return = new GetController();
		$return -> fncResponse($response);

	}

	/*=============================================
	Peticiones GET para el buscador entre tablas relacionadas
	=============================================*/

	static public function getRelDataSearch($rel,$type,$select, $linkTo, $search,$orderBy,$orderMode,$startAt,$endAt){

		$response = GetModel::getRelDataSearch($rel,$type,$select, $linkTo, $search,$orderBy,$orderMode,$startAt,$endAt);
		
		$return = new GetController();
		$return -> fncResponse($response);

	}

	/*=============================================
	Peticiones GET para selección de rangos
	=============================================*/

	static public function getDataRange($table,$select,$linkTo,$between1,$between2,$orderBy,$orderMode,$startAt,$endAt, $filterTo, $inTo){

		$response = GetModel::getDataRange($table,$select,$linkTo,$between1,$between2,$orderBy,$orderMode,$startAt,$endAt, $filterTo, $inTo);
		
		$return = new GetController();
		$return -> fncResponse($response);

	}

	/*=============================================
	Peticiones GET para selección de rangos con relaciones
	=============================================*/

	static public function getRelDataRange($rel,$type,$select,$linkTo,$between1,$between2,$orderBy,$orderMode,$startAt,$endAt, $filterTo, $inTo){

		$response = GetModel::getRelDataRange($rel,$type,$select,$linkTo,$between1,$between2,$orderBy,$orderMode,$startAt,$endAt, $filterTo, $inTo);
		
		$return = new GetController();
		$return -> fncResponse($response);

	}

	/*=============================================
	Respuestas del controlador
	=============================================*/

	public function fncResponse($response){

		if(!empty($response)){

			$json = array(

				'status' => 200,
				'total' => count($response),
				'results' => $response

			);

		}else{

			$json = array(

				'status' => 404,
				'results' => 'Not Found',
				'method' => 'get'

			);

		}

		echo json_encode($json, http_response_code($json["status"]));

	}

}