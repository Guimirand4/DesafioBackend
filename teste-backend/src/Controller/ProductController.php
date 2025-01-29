<?php

namespace Contatoseguro\TesteBackend\Controller;
use PDO;

use Contatoseguro\TesteBackend\Model\Product;
use Contatoseguro\TesteBackend\Service\CategoryService;
use Contatoseguro\TesteBackend\Service\ProductService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProductController
{
    private ProductService $service;
    private CategoryService $categoryService;

    public function __construct()
    {
        $this->service = new ProductService();
        $this->categoryService = new CategoryService();
    }

    public function getAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];
        
        $stm = $this->service->getAll($adminUserId);
        $response->getBody()->write(json_encode($stm->fetchAll()));
        return $response->withStatus(200);
    }

    public function getOne($request, $response, $args)
    {
        $id = $args['id'];
        $product = $this->service->getOne($id);
        if ($product) {
            $response->getBody()->write(json_encode($product));  
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            return $response->withStatus(404)->write('Produto não encontrado');
        }
    }

    public function insertOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->insertOne($body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    public function updateOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->updateOne($args['id'], $body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->deleteOne($args['id'], $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

public function getByStatus(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    
    $adminUserId = $request->getHeader('admin_user_id')[0];
    
    $status = $request->getQueryParams()['status'];
    $stm = $this->service->getByStatus($adminUserId, $status);

    $response->getBody()->write(json_encode($stm->fetchAll()));
    return $response->withStatus(200);
}
public function getActive(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $adminUserId = $request->getHeader('admin_user_id')[0];
    $stm = $this->service->getByStatus($adminUserId, 1); 
    $response->getBody()->write(json_encode($stm->fetchAll()));
    return $response->withStatus(200);
}

public function getInactive(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $adminUserId = $request->getHeader('admin_user_id')[0];
    $stm = $this->service->getByStatus($adminUserId, 0); 
    $response->getBody()->write(json_encode($stm->fetchAll()));
    return $response->withStatus(200);
}

public function getByCategory(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $adminUserId = $request->getHeader('admin_user_id')[0];
    $categoryId = $args['categoryId'];

    $stm = $this->service->getByCategory($adminUserId, $categoryId);
    $response->getBody()->write(json_encode($stm->fetchAll(PDO::FETCH_ASSOC)));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
}


public function getAllOrderedByDate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
{
    $adminUserId = $request->getHeader('admin_user_id')[0];
    $order = $request->getQueryParams()['order'] ?? 'DESC'; 

    $products = $this->service->getAllOrderedByDate($adminUserId, $order);

    $response->getBody()->write(json_encode($products));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
}


}

