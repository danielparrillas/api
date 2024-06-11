<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

return function (App $app) {

	$app->group('/inmuebles', function (Group $group) {
		$group->get('', function (Request $request, Response $response) {
			$pdo = $this->get('db');
			$stmt = $pdo->query('SELECT * FROM inmuebles');
			$inmuebles = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$response->getBody()->write(json_encode($inmuebles));
			return $response->withHeader('Content-Type', 'application/json');
		});
		$group->post('', function (Request $request, Response $response) {
			$pdo = $this->get('db');
			$data = $request->getParsedBody();

			$validation = v::key('departamento', v::stringType()->length(1, 30))
				->key('municipio', v::stringType()->length(1, 30))
				->key('residencia', v::stringType()->length(1, 30))
				->key('calle', v::stringType()->length(1, 30))
				->key('poligono', v::stringType()->length(1, 15))
				->key('numeroCasa', v::intType())
				->key('idPropietario', v::intType());

			try {
				$validation->assert($data);

				$sql = "INSERT INTO inmuebles (departamento, municipio, residencia, calle, poligono, numeroCasa, idPropietario) VALUES (:departamento, :municipio, :residencia, :calle, :poligono, :numeroCasa, :idPropietario)";
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':departamento', $data['departamento']);
				$stmt->bindParam(':municipio', $data['municipio']);
				$stmt->bindParam(':residencia', $data['residencia']);
				$stmt->bindParam(':calle', $data['calle']);
				$stmt->bindParam(':poligono', $data['poligono']);
				$stmt->bindParam(':numeroCasa', $data['numeroCasa']);
				$stmt->bindParam(':idPropietario', $data['idPropietario']);
				$stmt->execute();

				$response->getBody()->write(json_encode(['message' => 'Se ha creado el inmueble correctamente']));
				return $response->withHeader('Content-Type', 'application/json');
			} catch (Exception $exception) {
				if ($exception instanceof ValidationException) {
					$response->getBody()->write(json_encode([
						'message' => 'Error de validación',
						'errors' => $exception->getMessages(),
					]));
					return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
				} else {
					$response->getBody()->write(json_encode([
						'message' => 'Error al crear el inmueble',
						'error' => $exception->getMessage(),
					]));
					return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
				}
			}
		});
	});

	$app->group('/propietarios', function (Group $group) {
		$group->get('', function (Request $request, Response $response) {
			$pdo = $this->get('db');
			$stmt = $pdo->query('SELECT * FROM propietarios');
			$propietarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$response->getBody()->write(json_encode($propietarios));
			return $response->withHeader('Content-Type', 'application/json');
		});
		$group->post('', function (Request $request, Response $response) {
			$pdo = $this->get('db');
			$data = $request->getParsedBody();
			$validation = v::key('nombres', v::stringType()->length(1, 30))
				->key('apellidos', v::stringType()->length(1, 30))
				->key('fechaNacimiento', v::date('Y-m-d'))
				->key('genero', v::stringType()->length(1, 1)->regex('/[MF]/'))
				->key('telefono', v::stringType()->length(8, 8)->regex('/^\d{8}$/'))
				->key('email', v::email());

			try {
				$validation->assert($data);
				$sql = "INSERT INTO propietarios (nombres, apellidos, fechaNacimiento, genero, telefono, email) VALUES (:nombres, :apellidos, :fechaNacimiento, :genero, :telefono, :email)";
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':nombres', $data['nombres']);
				$stmt->bindParam(':apellidos', $data['apellidos']);
				$stmt->bindParam(':fechaNacimiento', $data['fechaNacimiento']);
				$stmt->bindParam(':genero', $data['genero']);
				$stmt->bindParam(':telefono', $data['telefono']);
				$stmt->bindParam(':email', $data['email']);
				$stmt->execute();

				$response->getBody()->write(json_encode(['message' => 'Se ha creado el propietario correctamente']));
				return $response->withHeader('Content-Type', 'application/json');
			} catch (Exception $exception) {
				if ($exception instanceof ValidationException) {
					$response->getBody()->write(json_encode([
						'message' => 'Error de validación',
						'errors' => $exception->getMessages(),
					]));
					return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
				} else {
					$response->getBody()->write(json_encode([
						'message' => 'Error al crear el propietario',
						'error' => $exception->getMessage(),
					]));
					return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
				}
			}
		});
	});
};
