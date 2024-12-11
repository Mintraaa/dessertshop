<?php
session_start();
include 'config.php';
$product_name = trim($_POST['product_name']);
$price = trim($_POST['price']);
$detail = trim($_POST['detail']);
$image_name = $_FILES['profile_image']['name'];

$image_tmp = $_FILES['profile_image']['tmp_name'];
$folder =  'upload_image/';
$image_location = $folder . $image_name;

$query = mysqli_query($conn, "INSERT INTO products (product_name,price,profile_image,detail) VALUES ('{$product_name}', '{$price}', '{$image_name}', '{$detail}')") or die ('query failed');

if($query) {
    move_uploaded_file($image_tmp, $image_location);

    $_SESSION['message'] = 'Product added successfully!';
    header('location: ' . $base_url . '/index.php');

} else {
    $_SESSION['message'] = 'Unable to add product!';
    header('location: ' . $base_url . '/index.php');
}
