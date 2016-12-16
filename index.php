<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new Slim\App();

require 'reports/deliveryList.php';
require 'reports/unpaid.php';
require 'reports/paid.php';
require 'reports/recapitulation.php';

$app->get('/', function($request, $response){
	return $response->write('<h1>Welcome to Lois Report Engine</h1>');	
});

$app->post('/paid', function($request, $response) use($token){
	$data = json_decode($request->getBody(), true);
	
	if(!isset($data['token']) || $data['token'] != $token){
		$response->withStatus(401);
		return $response;
	}
	
	$data = json_decode($request->getBody(), true);
	$pdf = new Paid($data['orientation'], $data['unit'], $data['paper']);
	$pdf->AliasNbPages('{nb}');
	$pdf->setUserName($data['user']);
	$pdf->SetFont('Helvetica', '', 9);
	$pdf->AddPage();
	$pdf->buildReport($data);
	
	try{
		$pdf->Output($data['title'].'.pdf', 'I');
	}
	catch(Exception $e){
		var_dump($e->getMessage());
	}
});

$app->post('/unpaid', function($request, $response) use($token){
	$data = json_decode($request->getBody(), true);
	
	if(!isset($data['token']) || $data['token'] != $token){
		$response->withStatus(401);
		return $response;
	}
	
	$data = json_decode($request->getBody(), true);
	$pdf = new Unpaid($data['orientation'], $data['unit'], $data['paper']);
	$pdf->AliasNbPages('{nb}');
	$pdf->setUserName($data['user']);
	$pdf->SetFont('Helvetica', '', 9);
	$pdf->AddPage();
	$pdf->buildReport($data);
	
	try{
		$pdf->Output($data['title'].'.pdf', 'I');
	}
	catch(Exception $e){
		var_dump($e->getMessage());
	}
});

$app->post('/recapitulation', function($request, $response) use($token){
	$data = json_decode($request->getBody(), true);
	
	if(!isset($data['token']) || $data['token'] != $token){
		$response->withStatus(401);
		return $response;
	}
	
	$data = json_decode($request->getBody(), true);
	$pdf = new Recapitulation($data['orientation'], $data['unit'], $data['paper']);
	$pdf->AliasNbPages('{nb}');
	$pdf->setUserName($data['user']);
	$pdf->SetFont('Helvetica', '', 9);
	$pdf->AddPage();
	$pdf->buildReport($data);
	
	try{
		$pdf->Output($data['title'].'.pdf', 'I');
	}
	catch(Exception $e){
		var_dump($e->getMessage());
	}
});

$app->post('/delivery', function($request, $response) use($token){
	$data = json_decode($request->getBody(), true);
	
	if(!$data['token']){
		$response->setStatus(401);
		return $response;
	}
	
	return $response;
});

$app->post('/return', function($request, $response) use($token){
	$data = json_decode($request->getBody(), true);
	
	if(!$data['token']){
		$response->setStatus(401);
		return $response;
	}
	
	return $response;
});

$app->post('/sj-balik', function($request, $response) use($token){
	$data = json_decode($request->getBody(), true);
	
	if(!$data['token']){
		$response->setStatus(401);
		return $response;
	}
	
	return $response;
});

$app->post('/deliveryList', function($request, $response) use($token){
	$data = json_decode($request->getBody(), true);
	
	if(!isset($data['token']) || $data['token'] != $token){
		$response->withStatus(401);
		return $response;
	}
	
	$pdf = new DeliveryList($data['orientation'], $data['unit'], $data['paper']);
	$pdf->AliasNbPages('{nb}');
	$pdf->setUserName($data['user']);
	$pdf->SetFont('Helvetica', '', 9);
	$pdf->AddPage();
	$pdf->buildReport($data);
	
	try{
		$pdf->Output($data['title'].'.pdf', 'I');
	}
	catch(Exception $e){
		var_dump($e->getMessage());
	}
});

$app->post('/commision', function($request, $response) use($token){
	$data = json_decode($request->getBody(), true);
	
	if(!$data['token']){
		$response->setStatus(401);
		return $response;
	}
	
	return $response;
});

$app->post('/pay-off', function($request, $response) use($token){
	$data = json_decode($request->getBody(), true);
	
	if(!$data['token']){
		$response->setStatus(401);
		return $response;
	}
	
	return $response;
});

$app->post('/partner', function($request, $response) use($token){
	$data = json_decode($request->getBody(), true);
	
	if(!$data['token']){
		$response->setStatus(401);
		return $response;
	}
	
	return $response;
});

$app->run();
?>