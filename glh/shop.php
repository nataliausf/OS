<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(isset($_POST['add_to_cart'])){

   if(!isset($user_id)){
      header('location:login.php');
      exit();
   }

   $product_name = $_POST['product_name'];
   $product_price = number_format((float) $_POST['product_price'], 2, '.', '');
   $product_image = $_POST['product_image'];
   $product_quantity = $_POST['product_quantity'];
   $product_id = $_POST['product_id'];

   // Check if product is in stock
   $check_stock = mysqli_query($conn, "SELECT in_stock FROM `products` WHERE id = '$product_id'") or die('query failed');
   $stock_check = mysqli_fetch_assoc($check_stock);

   if($stock_check['in_stock'] == 0){
      $message[] = 'This product is out of stock!';
   }else{
      $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

      if(mysqli_num_rows($check_cart_numbers) > 0){
         $message[] = 'Already added to cart!';
      }else{
         mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');
         $message[] = 'Product added to cart!';
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
   <title>shop</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>our shop</h3>
   <p> <a href="home.php">home</a> / shop </p>
</div>

<section class="products">

   <h1 class="title">our shop</h1>

   <!-- Search and Filter Form -->
   <div class="search-filter">
      <form action="" method="GET" class="filter-form">
         <div class="search-bar">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
         </div>
         <div class="category-filter">
            <select name="category" onchange="this.form.submit()">
               <option value="">All Categories</option>
               <option value="Fruits" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Fruits') ? 'selected' : ''; ?>>Fruits</option>
               <option value="Vegetables" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Vegetables') ? 'selected' : ''; ?>>Vegetables</option>
               <option value="Dairy" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Dairy') ? 'selected' : ''; ?>>Dairy</option>
               <option value="Meat" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Meat') ? 'selected' : ''; ?>>Meat</option>
               <option value="Bakery" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Bakery') ? 'selected' : ''; ?>>Bakery</option>
               <option value="Beverages" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Beverages') ? 'selected' : ''; ?>>Beverages</option>
               <option value="Other" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
         </div>
         <?php if(isset($_GET['search']) || isset($_GET['category'])): ?>
         <a href="shop.php" class="clear-filters">Clear Filters</a>
         <?php endif; ?>
      </form>
   </div>

   <div class="box-container">

      <?php  
         $where_clauses = [];
         $params = [];

         if(isset($_GET['search']) && !empty(trim($_GET['search']))){
            $search = trim($_GET['search']);
            $where_clauses[] = "p.name LIKE '%$search%'";
         }

         if(isset($_GET['category']) && !empty($_GET['category'])){
            $category = mysqli_real_escape_string($conn, $_GET['category']);
            $where_clauses[] = "p.category = '$category'";
         }

         $where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

         $select_products = mysqli_query($conn, "SELECT p.*, u.name as producer_name FROM `products` p LEFT JOIN `users` u ON p.producer_id = u.id $where_sql ORDER BY p.id DESC") or die('query failed');
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
         <div class="category">Category: <strong><?php echo $fetch_products['category']; ?></strong></div>
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

</section>








<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>