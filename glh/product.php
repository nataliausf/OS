<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle Add to Cart
if(isset($_POST['add_to_cart'])){
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
   <title>Product Details</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      .product-detail-section{
         min-height: 80vh;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 3rem 2rem;
      }

      .product-detail-container{
         max-width: 1200px;
         width: 100%;
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 3rem;
         background-color: var(--white);
         padding: 3rem;
         border-radius: .5rem;
         box-shadow: var(--box-shadow);
      }

      .product-image{
         display: flex;
         align-items: center;
         justify-content: center;
         min-height: 400px;
         background-color: var(--light-green);
         border-radius: .5rem;
         border: 1px solid #e0e0e0;
         overflow: hidden;
      }

      .product-image img{
         max-width: 100%;
         max-height: 100%;
         object-fit: contain;
         padding: 2rem;
      }

      .product-info h1{
         font-size: 3rem;
         color: var(--black);
         margin-bottom: .5rem;
      }

      .product-info .producer{
         font-size: 1.6rem;
         color: var(--light-color);
         margin-bottom: 2rem;
      }

      .product-info .category{
         font-size: 1.6rem;
         color: var(--green);
         margin-bottom: 2rem;
         font-weight: 500;
      }

      .product-details-box{
         background-color: #f9f9f9;
         padding: 2rem;
         border-radius: .5rem;
         border: 1px solid #e0e0e0;
      }

      .product-details-box .header{
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 2rem;
         padding-bottom: 1rem;
         border-bottom: 1px solid #e0e0e0;
      }

      .product-details-box .header h3{
         font-size: 2rem;
         color: var(--black);
         margin: 0;
      }

      .product-details-box .header .price{
         font-size: 2rem;
         color: var(--green);
         font-weight: 600;
      }

      .product-description{
         margin-bottom: 2rem;
         font-size: 1.6rem;
         color: var(--light-color);
         line-height: 1.6;
      }

      .product-description p{
         margin: .5rem 0;
      }

      .quantity-selector{
         display: flex;
         align-items: center;
         gap: 1rem;
         margin: 2rem 0;
         font-size: 1.8rem;
      }

      .quantity-selector button{
         background-color: var(--light-bg);
         border: 1px solid #ddd;
         padding: .5rem 1rem;
         cursor: pointer;
         border-radius: .3rem;
         font-size: 1.8rem;
         color: var(--black);
         transition: all .2s;
         min-width: 45px;
      }

      .quantity-selector button:hover{
         background-color: var(--green);
         color: var(--white);
         border-color: var(--green);
      }

      .quantity-selector input{
         width: 60px;
         text-align: center;
         padding: .5rem;
         border: 1px solid #ddd;
         border-radius: .3rem;
         font-size: 1.8rem;
      }

      .total-price{
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin: 2rem 0;
         padding: 1.5rem;
         background-color: var(--white);
         border-radius: .3rem;
         font-size: 1.8rem;
      }

      .total-price .label{
         color: var(--black);
         font-weight: 500;
      }

      .total-price .amount{
         color: var(--green);
         font-weight: 600;
         font-size: 2rem;
      }

      .add-to-cart-btn{
         width: 100%;
         padding: 1.2rem;
         background-color: var(--green);
         color: var(--white);
         border: none;
         border-radius: .5rem;
         font-size: 1.8rem;
         cursor: pointer;
         font-weight: 600;
         text-transform: uppercase;
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 1rem;
         transition: all .2s;
      }

      .add-to-cart-btn:hover{
         background-color: var(--black);
         transform: translateY(-2px);
      }

      .product-not-found{
         min-height: 60vh;
         display: flex;
         align-items: center;
         justify-content: center;
      }

      .product-not-found .empty{
         font-size: 2.5rem;
         padding: 3rem;
      }

      @media(max-width: 768px){
         .product-detail-container{
            grid-template-columns: 1fr;
            padding: 2rem 1.5rem;
            gap: 2rem;
         }

         .product-info h1{
            font-size: 2rem;
         }

         .product-details-box .header h3{
            font-size: 1.6rem;
         }

         .product-details-box .header .price{
            font-size: 1.6rem;
         }
      }
   </style>

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="product-detail-section">

   <?php
      if($product_id > 0){
         $select_product = mysqli_query($conn, "SELECT p.*, u.name as producer_name FROM `products` p LEFT JOIN `users` u ON p.producer_id = u.id WHERE p.id = '$product_id'") or die('query failed');
         
         if(mysqli_num_rows($select_product) > 0){
            $fetch_product = mysqli_fetch_assoc($select_product);
   ?>

   <div class="product-detail-container">
      
      <!-- Product Image -->
      <div class="product-image">
         <img src="uploaded_img/<?php echo htmlspecialchars($fetch_product['image']); ?>" alt="<?php echo htmlspecialchars($fetch_product['name']); ?>">
      </div>

      <!-- Product Info -->
      <div class="product-info">
         <h1><?php echo htmlspecialchars($fetch_product['name']); ?></h1>
         <p class="producer">Produced by <strong><?php echo htmlspecialchars($fetch_product['producer_name'] ? $fetch_product['producer_name'] : 'Greenfield Local Hub'); ?></strong></p>
         <p class="category">Category: <strong><?php echo htmlspecialchars($fetch_product['category']); ?></strong></p>

         <div class="product-details-box">
            <!-- Header with Name and Price -->
            <div class="header">
               <h3><?php echo htmlspecialchars($fetch_product['name']); ?></h3>
               <div class="price">£<?php echo number_format($fetch_product['price'], 2); ?></div>
            </div>

            <!-- Stock Status -->
            <div style="padding: 1rem; margin: 1rem 0; border-radius: 0.3rem; text-align: center; <?php echo $fetch_product['in_stock'] == 1 ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'; ?>">
               <?php echo $fetch_product['in_stock'] == 1 ? '<i class="fas fa-check-circle"></i> <strong>In Stock</strong>' : '<i class="fas fa-times-circle"></i> <strong>Out of Stock</strong>'; ?>
            </div>

            <!-- Product Description -->
            <div class="product-description">
               <p><strong>Product Info:</strong></p>
               <p>Fresh, locally sourced product from trusted producers in the Greenfield area.</p>
               <p>Our products are harvested at peak freshness and delivered directly to you, ensuring the highest quality and taste.</p>
               <p>By purchasing from Greenfield Local Hub, you're supporting local farmers and sustainable agriculture
            </div>

            <!-- Quantity Selector Form -->
            <form action="" method="POST" id="cartForm">
               <div style="display: flex; align-items: center; gap: 1rem; margin: 2rem 0;">
                  <label for="quantity" style="font-size: 1.8rem; color: var(--black);">Quantity:</label>
                  <div class="quantity-selector">
                     <button type="button" onclick="decreaseQuantity()" <?php echo $fetch_product['in_stock'] == 0 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>−</button>
                     <input type="number" id="quantity" name="product_quantity" value="1" min="1" readonly <?php echo $fetch_product['in_stock'] == 0 ? 'disabled' : ''; ?>>
                     <button type="button" onclick="increaseQuantity()" <?php echo $fetch_product['in_stock'] == 0 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>+</button>
                  </div>
               </div>

               <!-- Total Price Display -->
               <div class="total-price">
                  <span class="label">Total:</span>
                  <span class="amount">£<span id="totalPrice"><?php echo number_format($fetch_product['price'], 2); ?></span></span>
               </div>

               <!-- Hidden Fields for Add to Cart -->
               <input type="hidden" name="product_id" value="<?php echo $fetch_product['id']; ?>">
               <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($fetch_product['name']); ?>">
               <input type="hidden" name="product_price" value="<?php echo $fetch_product['price']; ?>">
               <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($fetch_product['image']); ?>">

               <!-- Add to Cart Button -->
               <button type="submit" name="add_to_cart" class="add-to-cart-btn" <?php echo $fetch_product['in_stock'] == 0 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                  <i class="fas fa-shopping-cart"></i> Add to cart
               </button>
            </form>

         </div>

      </div>

   </div>

   <?php
         }else{
            echo '<div class="product-not-found"><p class="empty">Product not found!</p></div>';
         }
      }else{
         echo '<div class="product-not-found"><p class="empty">No product selected! <a href="shop.php" style="color: var(--green); text-decoration: underline;">Go to Shop</a></p></div>';
      }
   ?>

</section>

<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<script>
   const basePrice = <?php echo isset($fetch_product['price']) ? $fetch_product['price'] : 0; ?>;
   
   function increaseQuantity(){
      const quantityInput = document.getElementById('quantity');
      quantityInput.value = parseInt(quantityInput.value) + 1;
      updateTotalPrice();
   }

   function decreaseQuantity(){
      const quantityInput = document.getElementById('quantity');
      if(parseInt(quantityInput.value) > 1){
         quantityInput.value = parseInt(quantityInput.value) - 1;
         updateTotalPrice();
      }
   }

   function updateTotalPrice(){
      const quantity = parseInt(document.getElementById('quantity').value);
      const total = (basePrice * quantity).toFixed(2);
      document.getElementById('totalPrice').textContent = total;
   }
</script>

</body>
</html>