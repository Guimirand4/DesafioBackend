<?php


namespace Contatoseguro\TesteBackend\Service;

use PDO; 
use Contatoseguro\TesteBackend\Config\DB;

class ProductService
{
    private \PDO $pdo;
    public function __construct()
    {
        $this->pdo = DB::connect();
    }

    public function getAll($adminUserId)
    {
        $query = "
            SELECT p.*, c.title as category
            FROM product p
            INNER JOIN product_category pc ON pc.product_id = p.id
            INNER JOIN category c ON c.id = pc.cat_id
            WHERE p.company_id = {$adminUserId}
        ";
    
        $stm = $this->pdo->prepare($query);
    
        $stm->execute();
    
        return $stm;
    }
    

    public function getOne($id)
    {
        $query = "
            SELECT p.*, GROUP_CONCAT(c.title) AS categories
            FROM product p
            LEFT JOIN product_category pc ON pc.product_id = p.id
            LEFT JOIN category c ON c.id = pc.cat_id
            WHERE p.id = :id
            GROUP BY p.id
        ";
    
        $stm = $this->pdo->prepare($query);
        $stm->bindParam(':id', $id, PDO::PARAM_INT);
        $stm->execute();
    
        $result = $stm->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;  
    }
    
      
    public function insertOne($body, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            INSERT INTO product (
                company_id,
                title,
                price,
                active
            ) VALUES (
                {$body['company_id']},
                '{$body['title']}',
                {$body['price']},
                {$body['active']}
            )
        ");
        if (!$stm->execute())
            return false;

        $productId = $this->pdo->lastInsertId();

        $stm = $this->pdo->prepare("
            INSERT INTO product_category (
                product_id,
                cat_id
            ) VALUES (
                {$productId},
                {$body['category_id']}
            );
        ");
        if (!$stm->execute())
            return false;

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                {$productId},
                {$adminUserId},
                'create'
            )
        ");

        return $stm->execute();
    }

    public function updateOne($id, $body, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            UPDATE product
            SET company_id = {$body['company_id']},
                title = '{$body['title']}',
                price = {$body['price']},
                active = {$body['active']}
            WHERE id = {$id}
        ");
        if (!$stm->execute())
            return false;

        $stm = $this->pdo->prepare("
            UPDATE product_category
            SET cat_id = {$body['category_id']}
            WHERE product_id = {$id}
        ");
        if (!$stm->execute())
            return false;

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                {$id},
                {$adminUserId},
                'update'
            )
        ");

        return $stm->execute();
    }

    public function deleteOne($id, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            DELETE FROM product_category WHERE product_id = {$id}
        ");
        if (!$stm->execute())
            return false;
        
        $stm = $this->pdo->prepare("DELETE FROM product WHERE id = {$id}");
        if (!$stm->execute())
            return false;

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                {$id},
                {$adminUserId},
                'delete'
            )
        ");

        return $stm->execute();
    }

    public function getLog($id)
    {
        $stm = $this->pdo->prepare("
            SELECT *
            FROM product_log
            WHERE product_id = {$id}
        ");
        $stm->execute();

        return $stm;
    }
public function getByStatus($adminUserId, $status)
{
    $query = "
        SELECT p.*, COALESCE(c.title, 'Sem Categoria') AS category
        FROM product p
        LEFT JOIN product_category pc ON pc.product_id = p.id
        LEFT JOIN category c ON c.id = pc.cat_id
        WHERE p.company_id = :company_id AND p.active = :status
    ";

    $stm = $this->pdo->prepare($query);
    $stm->bindParam(':company_id', $adminUserId, PDO::PARAM_INT);
    $stm->bindParam(':status', $status, PDO::PARAM_INT);
    
    $stm->execute();
    return $stm;
}
public function getByCategory($adminUserId, $categoryId)
{
    $query = "
        SELECT p.*, c.title AS category
        FROM product p
        INNER JOIN product_category pc ON pc.product_id = p.id
        INNER JOIN category c ON c.id = pc.cat_id
        WHERE p.company_id = :company_id AND c.id = :category_id
    ";

    $stm = $this->pdo->prepare($query);
    $stm->bindParam(':company_id', $adminUserId, PDO::PARAM_INT);
    $stm->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    
    $stm->execute();
    return $stm;
}
public function getAllOrderedByDate($adminUserId, $order = 'DESC')
{
    $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC'; 

    $query = "
        SELECT p.*, c.title as category
        FROM product p
        LEFT JOIN product_category pc ON pc.product_id = p.id
        LEFT JOIN category c ON c.id = pc.cat_id
        WHERE p.company_id = :company_id
        ORDER BY p.created_at $order
    ";

    $stm = $this->pdo->prepare($query);
    $stm->bindParam(':company_id', $adminUserId, PDO::PARAM_INT);
    $stm->execute();

    return $stm->fetchAll(PDO::FETCH_ASSOC);
}
}



