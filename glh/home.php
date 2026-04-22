<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(isset($_POST['add_to_cart'])){

   if(!isset($user_id)){
      header('location:login.php');
      exit();
   }

   $product_id = $_POST['product_id'];
   $product_name = $_POST['product_name'];
   $product_price = number_format((float) $_POST['product_price'], 2, '.', '');
   $product_image = $_POST['product_image'];
   $product_quantity = $_POST['product_quantity'];

   $check_stock = mysqli_query($conn, "SELECT in_stock FROM `products` WHERE id = '$product_id'") or die('query failed');
   $stock_check = mysqli_fetch_assoc($check_stock);

   if($stock_check['in_stock'] == 0){
      $message[] = 'This product is out of stock!';
   }else{
      $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

      if(mysqli_num_rows($check_cart_numbers) > 0){
         $message[] = 'already added to cart!';
      }else{
         mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');
         $message[] = 'product added to cart!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>home</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="home">

   <div class="content">
      <h3>Support Local Farmers.</h3>
      <p>Fresh, traceable, and sustainably sourced food.</p>
      <a href="about.php" class="white-btn">discover more</a>
   </div>

</section>


<section class="products">

   <h1 class="title">latest products</h1>

   <div class="box-container">

      <?php  
         $select_products = mysqli_query($conn, "SELECT p.*, u.name as producer_name FROM `products` p LEFT JOIN `users` u ON p.producer_id = u.id WHERE p.in_stock = 1 ORDER BY p.id DESC LIMIT 4") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
      ?>
     <div class="box">
      <?php if($fetch_products['in_stock'] == 0): ?>
         <div class="stock-overlay">
            <span>OUT OF STOCK</span>
         </div>
      <?php endif; ?>
      <a class="card-image" href="product.php?id=<?php echo $fetch_products['id']; ?>">
         <img class="image" src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="<?php echo htmlspecialchars($fetch_products['name']); ?>">
      </a>
      <div class="product-details">
         <a href="product.php?id=<?php echo $fetch_products['id']; ?>" style="text-decoration: none; color: inherit;">
            <div class="name"><?php echo $fetch_products['name']; ?></div>
         </a>
         <div class="producer">Produced by <strong><?php echo $fetch_products['producer_name'] ? $fetch_products['producer_name'] : 'Greenfield Local Hub'; ?></strong></div>
         <div class="price">£<?php echo number_format($fetch_products['price'], 2); ?></div>
         <div class="stock-pill <?php echo $fetch_products['in_stock'] == 1 ? 'available' : 'unavailable'; ?>">
            <?php echo $fetch_products['in_stock'] == 1 ? '<i class="fas fa-check-circle"></i> In Stock' : '<i class="fas fa-times-circle"></i> Out of Stock'; ?>
         </div>
         <form action="" method="post" class="product-form">
            <div class="form-row">
               <div class="quantity-picker">
                  <button type="button" class="qty-btn change-qty" data-change="-1" <?php echo $fetch_products['in_stock'] == 0 ? 'disabled' : ''; ?>>-</button>
                  <input type="number" min="1" name="product_quantity" value="1" class="qty" <?php echo $fetch_products['in_stock'] == 0 ? 'disabled' : ''; ?>>
                  <button type="button" class="qty-btn change-qty" data-change="1" <?php echo $fetch_products['in_stock'] == 0 ? 'disabled' : ''; ?>>+</button>
               </div>
               <input type="submit" value="Add to cart" name="add_to_cart" class="btn add-cart-btn" <?php echo $fetch_products['in_stock'] == 0 ? 'disabled' : ''; ?>>
            </div>
            <input type="hidden" name="product_id" value="<?php echo $fetch_products['id']; ?>">
            <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
            <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
            <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
         </form>
      </div>
     </div>
      <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
      ?>
   </div>

   <div class="load-more" style="margin-top: 2rem; text-align:center">
      <a href="shop.php" class="option-btn">load more</a>
   </div>

</section>

<section class="about">

   <div class="flex">

      <div class="image">
         <img src="images/local.webp" alt="Support Local Farmers">
      </div>

      <div class="content">
         <h3>Why Buy Local?</h3>
         <p>Supporting local farmers is a multifaceted approach that enhances economic stability, promotes environmental sustainability, improves health outcomes, and strengthens community ties. By choosing to buy local, you contribute to a healthier, more vibrant community.</p>
         <a href="producers.php" class="btn">read more</a>
      </div>

   </div>

</section>

<section class="home-contact">

   <div class="content">
      <h3>have any questions?</h3>
      <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Atque cumque exercitationem repellendus, amet ullam voluptatibus?</p>
      <a href="contact.php" class="white-btn">contact us</a>
   </div>

</section>





<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<!-- Accessibility JavaScript -->
<script src="js/accessibility.js"></script>

</body>
</html>