<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_POST['add_to_cart'])){

   $product_name = $_POST['product_name'];
   $product_price = number_format((float) $_POST['product_price'], 2, '.', '');
   $product_image = $_POST['product_image'];
   $product_quantity = $_POST['product_quantity'];

   $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

   if(mysqli_num_rows($check_cart_numbers) > 0){
      $message[] = 'already added to cart!';
   }else{
      mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');
      $message[] = 'product added to cart!';
   }

};

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>search page</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>search page</h3>
   <p> <a href="home.php">home</a> / search </p>
</div>

<section class="search-form">
   <form action="" method="post">
      <input type="text" name="search" placeholder="search products..." class="box">
      <input type="submit" name="submit" value="search" class="btn">
   </form>
</section>

<section class="products" style="padding-top: 0;">

   <div class="box-container">
   <?php
      if(isset($_POST['submit'])){
         $search_item = $_POST['search'];
         $select_products = mysqli_query($conn, "SELECT p.*, u.name as producer_name FROM `products` p LEFT JOIN `users` u ON p.producer_id = u.id WHERE p.name LIKE '%{$search_item}%'") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
         while($fetch_product = mysqli_fetch_assoc($select_products)){
   ?>
   <div class="box">
      <?php if($fetch_product['in_stock'] == 0): ?>
         <div class="stock-overlay">
            <span>OUT OF STOCK</span>
         </div>
      <?php endif; ?>
      <a class="card-image" href="product.php?id=<?php echo $fetch_product['id']; ?>">
         <img src="uploaded_img/<?php echo $fetch_product['image']; ?>" alt="<?php echo htmlspecialchars($fetch_product['name']); ?>" class="image">
      </a>
      <div class="product-details">
         <a href="product.php?id=<?php echo $fetch_product['id']; ?>" style="text-decoration: none; color: inherit;">
            <div class="name"><?php echo $fetch_product['name']; ?></div>
         </a>
         <div class="producer">Produced by <strong><?php echo $fetch_product['producer_name'] ? $fetch_product['producer_name'] : 'Greenfield Local Hub'; ?></strong></div>
         <div class="price">£<?php echo number_format($fetch_product['price'], 2); ?></div>
         <div class="stock-pill <?php echo $fetch_product['in_stock'] == 1 ? 'available' : 'unavailable'; ?>">
            <?php echo $fetch_product['in_stock'] == 1 ? '<i class="fas fa-check-circle"></i> In Stock' : '<i class="fas fa-times-circle"></i> Out of Stock'; ?>
         </div>
         <form action="" method="post" class="product-form">
            <div class="form-row">
               <div class="quantity-picker">
                  <button type="button" class="qty-btn change-qty" data-change="-1" <?php echo $fetch_product['in_stock'] == 0 ? 'disabled' : ''; ?>>-</button>
                  <input type="number" min="1" name="product_quantity" value="1" class="qty" <?php echo $fetch_product['in_stock'] == 0 ? 'disabled' : ''; ?>>
                  <button type="button" class="qty-btn change-qty" data-change="1" <?php echo $fetch_product['in_stock'] == 0 ? 'disabled' : ''; ?>>+</button>
               </div>
               <input type="submit" value="Add to cart" name="add_to_cart" class="btn add-cart-btn" <?php echo $fetch_product['in_stock'] == 0 ? 'disabled' : ''; ?>>
            </div>
            <input type="hidden" name="product_id" value="<?php echo $fetch_product['id']; ?>">
            <input type="hidden" name="product_name" value="<?php echo $fetch_product['name']; ?>">
            <input type="hidden" name="product_price" value="<?php echo $fetch_product['price']; ?>">
            <input type="hidden" name="product_image" value="<?php echo $fetch_product['image']; ?>">
         </form>
      </div>
   </div>
   <?php
            }
         }else{
            echo '<p class="empty">no result found!</p>';
         }
      }else{
         echo '<p class="empty">search something!</p>';
      }
   ?>
   </div>
  

</section>









<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>