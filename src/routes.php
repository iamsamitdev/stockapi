<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args)
{
    // Sample log message
    //$this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    //return $this->renderer->render($response, 'index.phtml', $args);
    echo "<center><h1>STOCK API for IONIC Application</h1></center>";
});

// ทดสอบสร้าง Group Routes
// Group
$app->group('/api', function () use ($app)
{
    $app->group('/v1', function () use ($app)
    {
        // =======================================================================
        // CRUD TABLE Category
        // ========================================================================
        // ดึงข้อมูลหมวดหมู่ออกมาแสดงเป็น JSON
        $app->get('/stock/category', function (Request $request, Response $response, array $args)
        {
            $sth = $this->db->prepare("SELECT * FROM category ORDER BY id DESC");
            $sth->execute();
            $category = $sth->fetchAll();

            return $this->response->withJson($category);
        });

        // ดึงข้อมูลหมวดหมู่ตาม ID ออกมาแสดงเป็น JSON
        $app->get('/stock/category/edit/[{id}]', function (Request $request, Response $response, array $args)
        {
            $sql = "SELECT * FROM category WHERE id=:id";
            $sth = $this->db->prepare($sql);
            $sth->bindParam("id", $args['id']);
            $sth->execute();
            $category = $sth->fetchAll();

            return $this->response->withJson($category);
        });

        // เพิ่มข้อมูลเข้าตาราง Category
        $app->post('/stock/category', function (Request $request, Response $response, array $args)
        {
            $body = $this->request->getBody();
            $data = json_decode($body, true);
            $sql  = "INSERT INTO category (category_name,category_image,category_status)
                          VALUES (:category_name,:category_image,:category_status)";
            $sth = $this->db->prepare($sql);
            $sth->bindParam("category_name", $data['category_name']);
            $sth->bindParam("category_image", $data['category_image']);
            $sth->bindParam("category_status", $data['category_status']);
            $sth->execute();
            $data['id'] = $this->db->lastInsertId();

            return $this->response->withJson($data);
        });

        // แก้ไขข้อมูลในตาราง category
        $app->put('/stock/category/[{id}]', function (Request $request, Response $response, array $args)
        {
            $body = $this->request->getBody();
            $data = json_decode($body, true);

            if (empty($data['category_image']))
            {
                $sql = "UPDATE category
                         SET category_name=:category_name,
                         category_status=:category_status
                         WHERE id=:id";
                $sth = $this->db->prepare($sql);
                $sth->bindParam("id", $args['id']);
                $sth->bindParam("category_name", $data['category_name']);
                $sth->bindParam("category_status", $data['category_status']);
            }
            else
            {
                $sql = "UPDATE category
                         SET category_name=:category_name,
                         category_image=:category_image,
                         category_status=:category_status
                         WHERE id=:id";
                $sth = $this->db->prepare($sql);
                $sth->bindParam("id", $args['id']);
                $sth->bindParam("category_name", $data['category_name']);
                $sth->bindParam("category_image", $data['category_image']);
                $sth->bindParam("category_status", $data['category_status']);
            }

            if ($sth->execute())
            {
                $input = [
                    'id'     => $args['id'],
                    'status' => 'success',
                ];
            }
            else
            {
                $input = [
                    'id'     => $args['id'],
                    'status' => 'fail',
                ];
            }

            return $this->response->withJson($input);
        });

        // ลบหมวดหมู่จากตาราง category
        $app->delete('/stock/category/[{id}]', function (Request $request, Response $response, array $args)
        {
            $sth = $this->db->prepare("DELETE FROM category WHERE id=:id");
            $sth->bindParam("id", $args['id']);

            if ($sth->execute())
            {
                $input = [
                    'id'     => $args['id'],
                    'status' => 'success',
                ];
            }
            else
            {
                $input = [
                    'id'     => $args['id'],
                    'status' => 'fail',
                ];
            }

            return $this->response->withJson($input);
        });

        // =======================================================================
        // CRUD TABLE Stock
        // ========================================================================
        // ดึงข้อมูล stock ออกมาแสดงเป็น JSON
        $app->get('/stock', function (Request $request, Response $response, array $args)
        {
            $sth = $this->db->prepare("SELECT * FROM stock ORDER BY id");
            $sth->execute();
            $category = $sth->fetchAll();

            return $this->response
                ->withJson($category);
        });

        // ค้นหาข้อมูลใน stock ออกมาแสดงเป็น JSON
        $app->post('/stock/search', function (Request $request, Response $response, array $args)
        {
            $body = $this->request->getBody();
            $data = json_decode($body, true);
            $sql  = "SELECT * FROM stock WHERE product_barcode=:product_barcode";
            $sth  = $this->db->prepare($sql);
            $sth->bindParam("product_barcode", $data['product_barcode']);
            $sth->execute();
            $category = $sth->fetchAll();

            return $this->response->withJson($category);
        });

        // เพิ่มข้อมูลเข้าตาราง Stock
        $app->post('/stock', function (Request $request, Response $response, array $args)
        {
            $body         = $this->request->getBody();
            $data         = json_decode($body, true);
            $current_date = date('Y-m-d');
            $sql          = "INSERT INTO stock (
                product_name,
                product_barcode,
                product_qty,
                product_price,
                product_date,
                product_image,
                product_category,
                product_status)
                VALUES (
                    :product_name,
                    :product_barcode,
                    :product_qty,
                    :product_price,
                    :product_date,
                    :product_image,
                    :product_category,
                    :product_status)";
            $sth = $this->db->prepare($sql);
            $sth->bindParam("product_name", $data['product_name']);
            $sth->bindParam("product_barcode", $data['product_barcode']);
            $sth->bindParam("product_qty", $data['product_qty']);
            $sth->bindParam("product_price", $data['product_price']);
            $sth->bindParam("product_date", $current_date);
            $sth->bindParam("product_image", $data['product_image']);
            $sth->bindParam("product_category", $data['product_category']);
            $sth->bindParam("product_status", $data['product_status']);
            $sth->execute();
            $data['id'] = $this->db->lastInsertId();

            return $this->response->withJson($data);
        });

        // แก้ไขข้อมูลในตาราง Stock
        $app->put('/stock/[{id}]', function (Request $request, Response $response, array $args)
        {
            $body = $this->request->getBody();
            $data = json_decode($body, true);
            $sql  = "UPDATE stock
                         SET product_name=:product_name,
                         product_barcode=:product_barcode,
                         product_qty=:product_qty,
                         product_price=:product_price,
                         product_image=:product_image,
                         product_category=:product_category,
                         product_status=:product_status
                         WHERE id=:id";
            $sth = $this->db->prepare($sql);
            $sth->bindParam("id", $args['id']);
            $sth->bindParam("product_name", $data['product_name']);
            $sth->bindParam("product_barcode", $data['product_barcode']);
            $sth->bindParam("product_qty", $data['product_qty']);
            $sth->bindParam("product_price", $data['product_price']);
            $sth->bindParam("product_image", $data['product_image']);
            $sth->bindParam("product_category", $data['product_category']);
            $sth->bindParam("product_status", $data['product_status']);

            if ($sth->execute())
            {
                $input = [
                    'id'     => $args['id'],
                    'status' => 'success',
                ];
            }
            else
            {
                $input = [
                    'id'     => $args['id'],
                    'status' => 'fail',
                ];
            }

            return $this->response->withJson($input);
        });

        // ลบข้อมูลจากตาราง Stock
        $app->delete('/stock/[{id}]', function (Request $request, Response $response, array $args)
        {
            $sth = $this->db->prepare("DELETE FROM stock WHERE id=:id");
            $sth->bindParam("id", $args['id']);

            if ($sth->execute())
            {
                $input = [
                    'id'     => $args['id'],
                    'status' => 'success',
                ];
            }
            else
            {
                $input = [
                    'id'     => $args['id'],
                    'status' => 'fail',
                ];
            }

            return $this->response->withJson($input);
        });

        // ฟังก์ชันการอัพโหลดไฟล์
        $app->post('/categoryupload', function (Request $request, Response $response, array $args)
        {
            $target_path = "images/category/";
            $target_path = $target_path . basename($_FILES['file']['name']);
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path))
            {
                echo "Upload and move success";
            }
            else
            {
                echo $target_path;
                echo "There was an error uploading the file, please try again!";
            }
        });

    });
});
