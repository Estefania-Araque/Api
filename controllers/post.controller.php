<?php 

require_once "models/get.model.php";
require_once "models/post.model.php";
require_once "models/connection.php";

require_once "vendor/autoload.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once "models/put.model.php";

class PostController{

	/*=============================================
	Peticion POST para crear datos
	=============================================*/

	static public function postData($table, $data){

		$response = PostModel::postData($table, $data);
		
		$return = new PostController();
		$return -> fncResponse($response,null,null);

	}

	/*=============================================
	Peticion POST para registrar usuario
	=============================================*/

	static public function postRegister($table, $data, $suffix){

		if(isset($data["password_".$suffix]) && $data["password_".$suffix] != null){

			$crypt = crypt($data["password_".$suffix], '$2a$07$azybxcags23425sdg23sdfhsd$');

			$data["password_".$suffix] = $crypt;

			$response = PostModel::postData($table, $data);

			$return = new PostController();
			$return -> fncResponse($response,null,$suffix);

		}else{

			/*=============================================
			Registro de usuarios desde APP externas
			=============================================*/

			$response = PostModel::postData($table, $data);

			if(isset($response["comment"]) && $response["comment"] == "The process was successful" ){

				/*=============================================
				Validar que el usuario exista en BD
				=============================================*/

				$response = GetModel::getDataFilter($table, "*", "email_".$suffix, $data["email_".$suffix], null,null,null,null);
				
				if(!empty($response)){		

					$token = Connection::jwt($response[0]->{"id_".$suffix}, $response[0]->{"email_".$suffix});

					$jwt = JWT::encode($token, "dfhsdfg34dfchs4xgsrsdry46", 'HS256');

					/*=============================================
					Actualizamos la base de datos con el Token del usuario
					=============================================*/

					$data = array(

						"token_".$suffix => $jwt,
						"token_exp_".$suffix => $token["exp"]

					);

					$update = PutModel::putData($table, $data, $response[0]->{"id_".$suffix}, "id_".$suffix);

					if(isset($update["comment"]) && $update["comment"] == "The process was successful" ){

						$response[0]->{"token_".$suffix} = $jwt;
						$response[0]->{"token_exp_".$suffix} = $token["exp"];

						$return = new PostController();
						$return -> fncResponse($response, null,$suffix);

					}

				}


			}


		}

	}

	/*=============================================
	Peticion POST para login de usuario
	=============================================*/

	static public function postLogin($table, $data, $suffix){

		/*=============================================
		Validar que el usuario exista en BD
		=============================================*/

		$response = GetModel::getDataFilter($table, "*", "email_".$suffix, $data["email_".$suffix], null,null,null,null);
		
		if(!empty($response)){	

			if($response[0]->{"password_".$suffix} != null)	{
			
				/*=============================================
				Encriptamos la contraseña
				=============================================*/

				$crypt = crypt($data["password_".$suffix], '$2a$07$azybxcags23425sdg23sdfhsd$');

				if($response[0]->{"password_".$suffix} == $crypt){

					$token = Connection::jwt($response[0]->{"id_".$suffix}, $response[0]->{"email_".$suffix});

					$jwt = JWT::encode($token, "dfhsdfg34dfchs4xgsrsdry46", 'HS256');

					/*=============================================
					Actualizamos la base de datos con el Token del usuario
					=============================================*/

					$data = array(

						"token_".$suffix => $jwt,
						"token_exp_".$suffix => $token["exp"]

					);

					$update = PutModel::putData($table, $data, $response[0]->{"id_".$suffix}, "id_".$suffix);

					if(isset($update["comment"]) && $update["comment"] == "The process was successful" ){

						$response[0]->{"token_".$suffix} = $jwt;
						$response[0]->{"token_exp_".$suffix} = $token["exp"];

						$return = new PostController();
						$return -> fncResponse($response, null,$suffix);

					}
					
					
				}else{

					$response = null;
					$return = new PostController();
					$return -> fncResponse($response, "Wrong password",$suffix);

				}

			}else{

				/*=============================================
				Actualizamos el token para usuarios logueados desde app externas
				=============================================*/

				$token = Connection::jwt($response[0]->{"id_".$suffix}, $response[0]->{"email_".$suffix});

				$jwt = JWT::encode($token, "dfhsdfg34dfchs4xgsrsdry46", 'HS256');				

				$data = array(

					"token_".$suffix => $jwt,
					"token_exp_".$suffix => $token["exp"]

				);

				$update = PutModel::putData($table, $data, $response[0]->{"id_".$suffix}, "id_".$suffix);

				if(isset($update["comment"]) && $update["comment"] == "The process was successful" ){

					$response[0]->{"token_".$suffix} = $jwt;
					$response[0]->{"token_exp_".$suffix} = $token["exp"];

					$return = new PostController();
					$return -> fncResponse($response, null,$suffix);

				}

			}

		}else{

			$response = null;
			$return = new PostController();
			$return -> fncResponse($response, "Wrong email",$suffix);

		}


	}

	/*=============================================
	Respuestas del controlador
	=============================================*/

	public function fncResponse($response,$error,$suffix){

		if(!empty($response)){

			/*=============================================
			Quitamos la contraseña de la respuesta
			=============================================*/

			if(isset($response[0]->{"password_".$suffix})){

				unset($response[0]->{"password_".$suffix});

			}

			$json = array(

				'status' => 200,
				'results' => $response

			);

		}else{

			if($error != null){

				$json = array(
					'status' => 400,
					"results" => $error
				);

			}else{

				$json = array(

					'status' => 404,
					'results' => 'Not Found',
					'method' => 'post'

				);
			}

		}

		echo json_encode($json, http_response_code($json["status"]));

	}

	/*=============================================
	Metodo para solicitud de recuperacion contraseña
	=============================================*/
	public function postPasswordRecoveryRequest($table, $data, $suffix) {
		if (!isset($data["email_".$suffix])) {
			$this->fncResponse(null, "Email is required", $suffix);
			return;
		}
	
		$user = GetModel::getDataFilter($table, "*", "email_".$suffix, $data["email_".$suffix], null, null, null, null);
		
		if (!empty($user)) {
			// Generar un código numérico aleatorio de 6 dígitos
			$code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
			
			// Establecer el tiempo de expiración a 1 hora desde ahora
			$expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
			
			$updateData = [
				"reset_code_".$suffix => $code,
				"reset_code_exp_".$suffix => $expiration,
				"reset_attempts_".$suffix => 0
			];
			
			$update = PutModel::putData($table, $updateData, $user[0]->{"id_".$suffix}, "id_".$suffix);
			
			if (isset($update["comment"]) && $update["comment"] == "The process was successful") {
				// Crear una instancia de EmailSender
				$emailSender = new EmailSender();
				
				// Enviar el correo
				$emailSent = $emailSender->sendRecoveryCode($data["email_".$suffix], $code);
				
				if ($emailSent) {
					
					error_log("Email enviado correctamente");
					$this->fncResponse([
						"message" => "Recovery code sent to your email"
					], null, $suffix);
				} else {
					$this->fncResponse(null, "Error sending email", $suffix);
				}
			} else {
				$this->fncResponse(null, "Error updating user", $suffix);
			}
		} else {
			$this->fncResponse(null, "Email not found", $suffix);
		}
	}
	/*=============================================
	Metodo para verificar y validar el codigo enviado
	=============================================*/
	public function postVerifyRecoveryCode($table, $data, $suffix) {
		if (!isset($data["email_user"]) || !isset($data["reset_code_user"])) {
			$this->fncResponse(null, "Email and reset code are required", $suffix);
			return;
		}
	
		$select = "*";
		$linkTo = "email_user";
		$equalTo = $data["email_user"];
		$orderBy = null;
		$orderMode = null;
		$startAt = null;
		$endAt = null;
	
		$response = new GetController();
		$userData = $response->getDataClave($table, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt);
	
		if (!empty($userData) && is_array($userData) && isset($userData[0])) {
			$user = $userData[0];
			
			if ($user->reset_code_user === null || $user->reset_code_exp_user === null) {
				$this->fncResponse(null, "No reset code found for this user", $suffix);
				return;
			}
	
			if (strtotime($user->reset_code_exp_user) < time()) {
				$this->fncResponse(null, "Recovery code has expired", $suffix);
				return;
			}
			
			if ($user->reset_attempts_user >= 5) {
				$this->fncResponse(null, "Max attempts reached. Request a new code.", $suffix);
				return;
			}
			
			if ($data["reset_code_user"] === $user->reset_code_user) {
				// Código válido, resetear los intentos
				$updateData = [
					"reset_attempts_user" => 0
				];
				$update = PutModel::putData($table, $updateData, $user->id_user, "id_user");
	
				if (isset($update["comment"]) && $update["comment"] == "The process was successful") {
					$this->fncResponse(["message" => "Code verified successfully"], null, $suffix);
				} else {
					$this->fncResponse(null, "Error updating user data", $suffix);
				}
			} else {
				// Código inválido, incrementar los intentos
				$attempts = $user->reset_attempts_user + 1;
				$updateData = [
					"reset_attempts_user" => $attempts
				];
				$update = PutModel::putData($table, $updateData, $user->id_user, "id_user");
	
				if (isset($update["comment"]) && $update["comment"] == "The process was successful") {
					$this->fncResponse(null, "Invalid code", $suffix);
				} else {
					$this->fncResponse(null, "Error updating user data", $suffix);
				}
			}
		} else {
			$this->fncResponse(null, "User not found", $suffix);
		}
	}
	/*=============================================
	Metodo para cambiar la contraseña
	=============================================*/
	public function postChangePassword($table, $data, $suffix) {
		if (!isset($data["email_".$suffix]) || !isset($data["reset_code_".$suffix]) || !isset($data["password_".$suffix])) {
			$this->fncResponse(null, "Email, reset code, and new password are required", $suffix);
			return;
		}
	
		$user = GetModel::getDataFilter($table, "*", "email_".$suffix, $data["email_".$suffix], null, null, null, null);
		
		if (!empty($user)) {
			$user = $user[0];
			
			if (strtotime($user->{"reset_code_exp_".$suffix}) < time()) {
				$this->fncResponse(null, "Recovery code has expired", $suffix);
				return;
			}
			
			if ($data["reset_code_".$suffix] === $user->{"reset_code_".$suffix}) {
				// Encriptar la nueva contraseña
				$crypt = crypt($data["password_".$suffix], '$2a$07$azybxcags23425sdg23sdfhsd$');
				
				// Generar nuevo token JWT
				$token = Connection::jwt($user->{"id_".$suffix}, $user->{"email_".$suffix});
				$jwt = JWT::encode($token, "dfhsdfg34dfchs4xgsrsdry46", 'HS256');
	
				$updateData = [
					"password_".$suffix => $crypt,
					"reset_code_".$suffix => null,
					"reset_code_exp_".$suffix => null,
					"reset_attempts_".$suffix => 0,
					"token_".$suffix => $jwt,
					"token_exp_".$suffix => $token["exp"]
				];
				
				$update = PutModel::putData($table, $updateData, $user->{"id_".$suffix}, "id_".$suffix);
				
				if (isset($update["comment"]) && $update["comment"] == "The process was successful") {
					$response = [
						"comment" => "The process was successful",
						"token" => $jwt
					];
					$this->fncResponse($response, null, $suffix);
				} else {
					$this->fncResponse(null, "Error updating password", $suffix);
				}
			} else {
				$this->fncResponse(null, "Invalid code", $suffix);
			}
		} else {
			$this->fncResponse(null, "Email not found", $suffix);
		}
	}

}