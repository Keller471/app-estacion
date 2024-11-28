<?php 

	/**
	* @file User.php
	* @brief Declaraciones de la clase User para la conexión con la base de datos.
	* @author Matias Leonardo Baez
	* @date 2024
	* @contact elmattprofe@gmail.com
	*/

	// incluye la libreria para conectar con la db
	include_once 'DBAbstract.php';

	// se crea la clase User que hereda de DBAbstract
	class User extends DBAbstract{

		private $nameOfFields = array();

		/**
		 * 
		 * @brief Es el constructor de la clase User
		 * 
		 * Al momento de instanciar User se llama al padre para que ejecute su constructor
		 * 
		 * */
		function __construct(){
		
			// quiero salir de la clase actual e invocar al constructor
			parent::__construct();

			/**< Obtiene la estructura de la tabla */
			$result = $this->query('DESCRIBE users');

			foreach ($result as $key => $row) {
				$buff =$row["Field"];
				
				/**< Almacena los nombres de los campos*/
				$this->nameOfFields[] = $buff;

				/**< Autocarga de atributos a la clase */
				$this->$buff=NULL;
			}
			

		}

		/**
		 * 
		 * Hace soft delete del registro
		 * @return bool siempre verdadero
		 * 
		 * */
		function leaveOut(){

			$id = $this->id;
			$fecha_hora = date("Y-m-d H:i:s");

			$ssql = "UPDATE users SET delete_at='$fecha_hora' WHERE id=$id";

			$this->query($ssql);

			return true;
		}

		/**
		 * 
		 * Finaliza la sesión
		 * @return bool true
		 * 
		 * */
		function logout(){
			return true;
		}

		/**
		 * 
		 * Intenta loguear al usuario mediante email y contraseña
		 * @param array $form indexado de forma asociativa
		 * @return array que posee códigos de error especiales
		 * 
		 * */
		function login($request_method, $form){


			if($request_method!="GET"){
				return ["errno" => 410, "error" => "Metodo invalido"];
			}

			$email = $form["txt_email"];

			$result = $this->query("CALL `login`('$email')");

			// el email no existe
			if(count($result)==0){
				return ["error" => "Email no registrado", "errno" => 404];
			}

			$result = $result[0];

			// si el email existe y la contraseña es valida
			if($result["password"]==md5($form["txt_pass"]."morphyx")){

				/**< autocarga de valores en los atributos de la clase */
				foreach ($this->nameOfFields as $key => $value) {
					$this->$value = $result[$value];
				}

				// para que los avatares sean gatitos
				$this->avatar = str_replace("set5", "set4", $this->avatar); 

				$_SESSION["morphyx"]['user'] = $this;

				//var_dump($_SESSION);

				return ["error" => "Acceso valido", "errno" => 200];
			}

			// email existe pero la contraseña invalida
			return ["error" => "Error de contraseña", "errno" => 405];

		}

		/**
		 * 
		 * Agrega un nuevo usuario si no existe el correo electronico en la tabla users
		 * @param array $form es un arreglo assoc con los datos del formulario
		 * @return array que posee códigos de error especiales 
		 * 
		 * */
		function register($form){
			$email = $form["txt_email"];

			$result = $this->query("SELECT * FROM users WHERE email = '$email'")[0];


			// el email no existe entonces se registra
			if(is_null($result)){

				$pass = md5($form["txt_pass"]."morphyx");

				$ssql = "INSERT INTO users (email, password) VALUES ('$email','$pass')";

				$result = $this->query($ssql);

				$this->id = $this->db->insert_id;

				return ["error" => "Usuario registrado", "errno" => 200];
			}

			$date_zero = '0000-00-00 00:00:00';

			// El usuario volvio a la aplicacion
			if($result['delete_at']!=$date_zero){

				$id=$result["id"];
				$this->id = $result["id"];

				$pass = md5($form["txt_pass"]."morphyx");

				$ssql = "UPDATE users SET first_name='', last_name='', `password`='$pass', delete_at='0000-00-00 00:00:00' WHERE id=$id";


				$result = $this->query($ssql);



				return ["error" => "Usuario vuelve arrastrandose", "errno" => 201];
			}

			// si el email existe 
			return ["error" => "Correo ya registrado", "errno" => 405];

		}


		/**
		 * 
		 * Actualiza los datos del usuario con los datos de un formulario
		 * @param array $form es un arregle asociativo con los datos a actualizar
		 * @return array arreglo con el código de error y descripción
		 * 
		 * */
		function update($form){
			$nombre = $form["txt_first_name"];
			$apellido = $form["txt_last_name"];
			$id = $this->id;


			$this->first_name = $nombre;
			$this->last_name = $apellido;

			$ssql = "UPDATE users SET first_name='$nombre', last_name='$apellido' WHERE id=$id";

			$result = $this->query($ssql);

			return ["error" => "Se actualizo correctamente", "errno" => 200];
		}

		/**
		 * 
		 * Cantidad de usuarios registrados
		 * @return int cantidad de usuarios registrados
		 * 
		 * */
		function getCantUsers(){

			$result = $this->query("SELECT * FROM users");

			return $this->db->affected_rows;
		}


		/**
		 * 
		 * @brief Retorna un listado limitado
		 * @param string $request_method espera a GET
		 * @param array $request [inicio][cantidad]
		 * @return array lista con los datos de los usuarios 
		 * 
		 * */
		function getAllUsers($request_method, $request){


			if($request_method!="GET"){
				return ["errno" => 410, "error" => "Metodo invalido"];
			}

			$inicio = 0;

			if(isset($request["inicio"])){
				$inicio = $request["inicio"];
			}

			if(!isset($request["cantidad"])){
				return ["errno" => 404, "error" => "falta cantidad por GET"];
			}

			$cantidad = $request["cantidad"];

			$result = $this->query("SELECT * FROM users LIMIT $inicio, $cantidad");

			return $result;
		}


	}

 ?>