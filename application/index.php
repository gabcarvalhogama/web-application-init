<?php
	header("Access-Control-Allow-Origin: *");
	require_once "autoload.php";

	$router = new \Bramus\Router\Router();

	$router->get('/users', function(){
		if(empty($_GET["email"]) OR empty($_GET["password"])){
			die(json_encode(["res"=>"Por favor, preencha todos os campos!", "fields"=>['email', 'password']]));
		}else{
			$User = new User();
			if($User->login($_GET["email"], $_GET["password"])){

				session_start();
				$_SESSION["email"] = filter_var($_GET["email"], FILTER_SANITIZE_EMAIL);
				$_SESSION["password"] = $_GET["password"];
				die(json_encode(["res"=>1]));

			}else{
				die(json_encode(["res"=>"E-mail e/ou Senha incorreto(s)!", "fields"=>['email', 'password']]));
			}
		}
	});

	$router->post('/clients', function(){
		if(empty($_POST["fantname"]) OR empty($_POST["address"]) OR empty($_POST["cellphone"]) OR empty($_POST["email"]) OR empty($_POST["cnpjcpf"])){
			die(json_encode(["res"=>"Por favor, preencha todos os campos obrigatórios!"]));
		}else{
			$User = new User();
			if(!$User->isUserAuthenticated())
				die(json_encode(["res"=>"Ops, parece que você não está logado.", "redirect"=>"login"]));

			$Client = new Client();
			if($Client->getClientByCNPJCPF($_POST["cnpjcpf"])->rowCount() > 0)
				die(json_encode(["res"=>"O CNPJ/CPF já foi registrado."]));
			

			if($Client->insert($_POST["corpname"], $_POST["fantname"], $_POST["address"], $_POST["phone"], $_POST["cellphone"], $_POST["email"], $_POST["cnpjcpf"], $User->getAgencyIdByUser($_SESSION["email"])))
				die(json_encode(["res"=>1]));
			else
				die(json_encode(["res"=>"Algo deu errado ao salvar o novo cliente. Verifique os dados e tente novamente!"]));
		}
	});

	$router->post('/services', function(){
		if(empty($_POST["name"]) OR empty($_POST["minimum"]) OR empty($_POST["maximum"]) OR empty($_POST["frequency"])){
			die(json_encode(["res"=>"Por favor, preencha todos os campos obrigatórios."]));
		}else if(!Service::isFrequencyValid($_POST["frequency"])){
			die(json_encode(["res"=>"A frequência informada é inválida."]));
		}else if(Service::realToDecimal($_POST["maximum"]) < Service::realToDecimal($_POST["minimum"])){
			die(json_encode(["res"=>"O valor mínimo deve ser menor que o valor máximo."]));
		}else{
			$User = new User();
			if(!$User->isUserAuthenticated())
				die(json_encode(["res"=>"Ops, parece que você não está logado.", "redirect"=>"login"]));
			
			$Service = new Service();
			if($Service->insert($_POST["name"], $_POST["minimum"], $_POST["maximum"], $_POST["frequency"], $User->getAgencyIdByUser($_SESSION["email"])))
				die(json_encode(["res"=>1]));
			else
				die(json_encode(["res"=>"Algo deu errado ao salvar o novo serviço. Verifique os dados e tente novamente!"]));
		}
	});

	$router->post('/contracts', function(){
		if(empty($_POST["client"])){
			die(json_encode(["res"=>"Informe o cliente do contrato."]));
		}else if(empty($_POST["value"])){
			die(json_encode(["res"=>"Informe o valor do contrato."]));
		}else if(empty($_POST["frequency"]) OR !Service::isFrequencyValid($_POST["frequency"])){
			die(json_encode(["res"=>"Informe uma frequência válida."]));
		}else if(
			(!empty($_POST["validate"]) AND !strtotime($_POST["validate"])) OR 
			(!empty($_POST["validate"]) AND $_POST["validate"] < date("Y-m-d"))
		)
		{
			die(json_encode(["res"=>"Você deve informar uma data válida."]));
		}else{
			$User = new User();
			if(!$User->isUserAuthenticated())
				die(json_encode(["res"=>"Ops, parece que você não está logado.", "redirect"=>"login"]));

			$idagency = $User->getAgencyIdByUser($_SESSION["email"]);
			if(!Client::isClientFromAgency($_POST["client"], $idagency))
				die(json_encode(["res"=>"Você não tem permissão para operar esse cliente."]));
			
			if(!Service::validateServiceByID($_POST["service"], $idagency))
				die(json_encode(["res"=>"Você não tem permissão para operar o serviço selecionado."]));

			$Contract = new Contract();
			if($Contract->insert($_POST["client"], $_POST["service"], $_POST["value"], $_POST["frequency"], $_POST["payday"], $idagency, $_POST["validate"]))
				die(json_encode(["res"=>1]));
			else
				die(json_encode(["res"=>"Algo deu errado ao salvar o novo contrato. Verifique os dados e tente novamente!"]));
		}
	});

	// $router->get('/payment/(\d+)', function(){
		
	// 	MercadoPago\MercadoPago::initialize(); 
	// 	$config = MercadoPago::config(); 

	// 	MercadoPago\SDK::setAccessToken("TEST-1244134020996483-022701-351ddbb37d1c197b4fb9645dd3f60eaa-530876058"); // On Sandbox

	// 	$payment = new MercadoPago\Payment();
  
	// 	  $payment->transaction_amount = 100;
	// 	  $payment->token = hash($, data);
	// 	  $payment->description = "Title of what you are paying for";
	// 	  $payment->installments = 1;
	// 	  $payment->payment_method_id = "visa";
		  
	// 	  $payment->payer = $payer;
	// 	  $payment->save(); 
		  
	// 	  echo $payment->status;
	// 	  echo $payment->status_detail;
		  
	// 	  echo "\n";
		  
	// 	  echo "PaymentId: " . $payment->id . "\n";

	// });

	$router->set404(function() {
		echo "OK, deu 404";
	});
	$router->run();