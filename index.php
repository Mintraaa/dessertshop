<?php
// index.php
session_start();

// ตั้งค่าฐานข้อมูล
$base_url = 'http://localhost/dessertshop';
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'storedessert';

// เชื่อมต่อฐานข้อมูล
$connection = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($connection->connect_error) {
    die('Connection failed: ' . $connection->connect_error);
}

// ค่าเริ่มต้นของ product
$product = [
    'id' => '',
    'product_name' => '',
    'price' => '',
    'detail' => '',
    'image' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $connection->real_escape_string($_POST['product_name']);
    $price = $connection->real_escape_string($_POST['price']);
    $detail = $connection->real_escape_string($_POST['detail']);
    $image_file = '';

    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "images/";
        $image_file = basename($_FILES['image']['name']);
        $upload_path = $upload_dir . $image_file;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $_SESSION['error'] = 'Failed to upload image';
            header('Location: index.php');
            exit();
        }
    }

    if (!empty($_POST['id'])) {
        $id = $connection->real_escape_string($_POST['id']);
        $query = "UPDATE products SET product_name='$product_name', price='$price', detail='$detail', image='$image_file' WHERE id='$id'";
    } else {
        $query = "INSERT INTO products (product_name, price, detail, image) VALUES ('$product_name', '$price', '$detail', '$image_file')";
    }

    if ($connection->query($query)) {
        $_SESSION['success'] = 'saved successfully';
    } else {
        $_SESSION['error'] = 'Error: ' . $connection->error;
    }
    header('Location: index.php');
    exit();
}

if (!empty($_GET['delete_id'])) {
    $delete_id = $connection->real_escape_string($_GET['delete_id']);
    $delete_query = $connection->query("SELECT image FROM products WHERE id='$delete_id'");
    $data = $delete_query->fetch_assoc();

    if (!empty($data['image']) && file_exists("images/" . $data['image'])) {
        unlink("images/" . $data['image']);
    }

    $connection->query("DELETE FROM products WHERE id='$delete_id'");
    $_SESSION['success'] = 'deleted successfully';
    header('Location: index.php');
    exit();
}

if (!empty($_GET['id'])) {
    $id = $connection->real_escape_string($_GET['id']);
    $product_query = $connection->query("SELECT * FROM products WHERE id='$id'");
    $product = $product_query->fetch_assoc();
}

$product_list_query = $connection->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #ffe6f2, #ffccf2);
            color: #4a4a4a;
        }
        h2 {
            font-family: 'Lobster', cursive;
            color: #ff6699;
        }
        .btn-primary {
            background-color: #ff99cc;
            border: none;
        }
        .btn-primary:hover {
            background-color: #ff6699;
        }
        .btn-warning {
            background-color: #ffcc99;
            border: none;
        }
        .btn-danger {
            background-color: #ff9999;
            border: none;
        }
        .table {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .img-thumbnail {
            border-radius: 10px;
        }
        .bg-light {
            background-color: #fff5f8 !important;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .container {
            max-width: 800px;
        }
        footer {
            margin-top: 20px;
            text-align: center;
            color: #888;
        }

        /* เปลี่ยนสีกล่องแจ้งเตือน */
        .alert-success {
            background-color: #ffccff; /* สีชมพูอ่อน */
            color: #990066;
            border: 1px solid #ff99cc;
        }

        .alert-danger {
            background-color: #ffcccc; /* สีแดงอ่อน */
            color: #990000;
            border: 1px solid #ff6666;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php elseif (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <h2 class="text-center">Manage the dessert menu</h2>
    <form action="" method="POST" enctype="multipart/form-data" class="bg-light p-4 rounded">
        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
        <div class="mb-3">
            <label for="product_name" class="form-label">Product Name</label>
            <input type="text" id="product_name" name="product_name" class="form-control" value="<?php echo $product['product_name']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Price</label>
            <input type="number" id="price" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="detail" class="form-label">Detail</label>
            <textarea id="detail" name="detail" class="form-control" required><?php echo $product['detail']; ?></textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Image</label>
            <input type="file" id="image" name="image" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary w-100">Save</button>
    </form>

    <table class="table mt-4 table-striped table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $product_list_query->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><img src="images/<?php echo $row['image']; ?>" alt="Product Image" class="img-thumbnail" width="50"></td>
                <td><?php echo $row['product_name']; ?></td>
                <td><?php echo number_format($row['price'], 2); ?> THB</td>
                <td>
                    <a href="index.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="index.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
