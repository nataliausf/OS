<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

if(isset($_POST['order_btn'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $number = $_POST['number'];
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $method = mysqli_real_escape_string($conn, $_POST['method']);
   $order_type = mysqli_real_escape_string($conn, $_POST['order_type']);
   
   // Handle address based on order type
   if($order_type == 'collection') {
      $address = mysqli_real_escape_string($conn, 'Collection from store');
   } else {
      $address = mysqli_real_escape_string($conn, 'flat no. '. $_POST['flat']);
   }
   
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products[] = '';

   $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
   if(mysqli_num_rows($cart_query) > 0){
      while($cart_item = mysqli_fetch_assoc($cart_query)){
         $cart_products[] = $cart_item['name'].' ('.$cart_item['quantity'].') ';
         $sub_total = ($cart_item['price'] * $cart_item['quantity']);
         $cart_total += $sub_total;
      }
   }

   $total_products = implode(', ',$cart_products);

   $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE name = '$name' AND number = '$number' AND email = '$email' AND method = '$method' AND address = '$address' AND total_products = '$total_products' AND total_price = '$cart_total'") or die('query failed');

   if($cart_total == 0){
      $message[] = 'your cart is empty';
   }else{
      if(mysqli_num_rows($order_query) > 0){
         $message[] = 'order already placed!'; 
      }else{
         // Calculate loyalty points before inserting order
         $points_earned = floor($cart_total); // 1 point per £1 spent

         mysqli_query($conn, "INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, points_earned, points_used, order_type, placed_on) VALUES('$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$cart_total', '$points_earned', '0', '$order_type', '$placed_on')") or die('query failed');
         $order_id = mysqli_insert_id($conn);
         $message[] = 'order placed successfully!';

         // Award loyalty points and update user totals
         $new_total_spent = 0;

         // Get current user data
         $user_query = mysqli_query($conn, "SELECT loyalty_points, total_spent FROM `users` WHERE id = '$user_id'") or die('query failed');
         $user_data = mysqli_fetch_assoc($user_query);

         $current_points = $user_data['loyalty_points'];
         $current_total_spent = $user_data['total_spent'];

         $new_points = $current_points + $points_earned;
         $new_total_spent = $current_total_spent + $cart_total;

         // Update user loyalty data
         mysqli_query($conn, "UPDATE `users` SET loyalty_points = '$new_points', total_spent = '$new_total_spent' WHERE id = '$user_id'") or die('query failed');

         // Record loyalty history
         mysqli_query($conn, "INSERT INTO `user_loyalty_history`(user_id, action_type, points, description, order_id) VALUES('$user_id', 'earned', '$points_earned', 'Points earned from purchase', '$order_id')") or die('query failed');

         mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
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
   <title>checkout</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <!-- Accessibility CSS -->
   <link rel="stylesheet" href="css/accessibility.css">

   <style>
      .order-type {
         margin: 2rem 0;
         padding: 2rem;
         background: var(--light-bg);
         border-radius: 0.5rem;
         border: 1px solid var(--border);
      }

      .order-type h4 {
         margin-bottom: 1.5rem;
         color: var(--black);
         font-size: 1.8rem;
         text-align: center;
      }

      .type-options {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
         gap: 1.5rem;
      }

      .type-option {
         position: relative;
         background: white;
         border: 2px solid var(--border);
         border-radius: 0.5rem;
         padding: 1.5rem;
         cursor: pointer;
         transition: all 0.3s ease;
         text-align: center;
      }

      .type-option:hover {
         border-color: var(--orange);
         transform: translateY(-2px);
         box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
      }

      .type-option input[type="radio"] {
         position: absolute;
         opacity: 0;
         width: 0;
         height: 0;
      }

      .type-option input[type="radio"]:checked + .option-label {
         background: var(--orange);
         color: white;
         border-color: var(--orange);
      }

      .type-option input[type="radio"]:checked + .option-label::before {
         content: '✓';
         position: absolute;
         top: -10px;
         right: -10px;
         background: var(--orange);
         color: white;
         border-radius: 50%;
         width: 24px;
         height: 24px;
         display: flex;
         align-items: center;
         justify-content: center;
         font-weight: bold;
      }

      .option-label {
         display: block;
         transition: all 0.3s ease;
         border-radius: 0.3rem;
         padding: 1rem;
      }

      .option-label i {
         font-size: 2.5rem;
         margin-bottom: 0.5rem;
         display: block;
      }

      .option-label strong {
         font-size: 1.6rem;
         display: block;
         margin-bottom: 0.5rem;
      }

      .option-label small {
         opacity: 0.8;
         font-size: 1.2rem;
      }

      @media (max-width: 768px) {
         .type-options {
            grid-template-columns: 1fr;
         }

         .order-type {
            padding: 1.5rem;
         }
      }
   </style>

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>checkout</h3>
   <p> <a href="home.php">home</a> / checkout </p>
</div>

<section class="display-order">

   <div class="order-summary">
      <h3>Order Summary</h3>
      <?php  
         $grand_total = 0;
         $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
         if(mysqli_num_rows($select_cart) > 0){
            while($fetch_cart = mysqli_fetch_assoc($select_cart)){
               $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
               $grand_total += $total_price;
      ?>
      <p><?php echo $fetch_cart['name']; ?> <span>£<?php echo number_format($fetch_cart['price'], 2); ?> × <?php echo $fetch_cart['quantity']; ?></span></p>
      <?php
         }
      }else{
         echo '<p class="empty">your cart is empty</p>';
      }
      ?>
      <div class="grand-total">Grand total: <span>£<?php echo number_format($grand_total, 2); ?></span></div>
   </div>

   <section class="checkout">
      <form action="" method="post">
         <h3>Billing & Delivery</h3>
         <div class="flex">
            <div class="inputBox">
               <input type="text" name="name" required placeholder="Enter your name">
            </div>
            <div class="inputBox">
               <input type="number" name="number" required placeholder="Enter your number">
            </div>
            <div class="inputBox">
               <input type="email" name="email" required placeholder="Enter your email">
            </div>
            <div class="inputBox">
               <select name="method">
                  <option value="cash on delivery">Cash on delivery</option>
                  <option value="credit card">Credit card</option>
                  <option value="paypal">PayPal</option>
               </select>
            </div>
            <div class="inputBox">
               <input type="text" name="flat" required placeholder="Enter your address">
            </div>
         </div>

         <div class="order-type">
            <h4>Order Type</h4>
            <div class="type-options">
               <label class="type-option">
                  <input type="radio" name="order_type" value="delivery" checked>
                  <span class="option-label">
                     <i class="fas fa-truck"></i>
                     <strong>Delivery</strong><br>
                     <small>Delivered to your address</small>
                  </span>
               </label>
               <label class="type-option">
                  <input type="radio" name="order_type" value="collection">
                  <span class="option-label">
                     <i class="fas fa-store"></i>
                     <strong>Collection</strong><br>
                     <small>Collect from store</small>
                  </span>
               </label>
            </div>
         </div>

         <input type="submit" value="order now" class="btn" name="order_btn">
      </form>
   </section>
</section>









<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<!-- Accessibility JavaScript -->
<script src="js/accessibility.js"></script>

<script>
// Handle order type selection
document.addEventListener('DOMContentLoaded', function() {
   const deliveryRadio = document.querySelector('input[name="order_type"][value="delivery"]');
   const collectionRadio = document.querySelector('input[name="order_type"][value="collection"]');
   const addressField = document.querySelector('input[name="flat"]');
   const addressInputBox = addressField.closest('.inputBox');

   function toggleAddressField() {
      if (collectionRadio.checked) {
         addressInputBox.style.display = 'none';
         addressField.required = false;
         addressField.value = 'Collection from store';
      } else {
         addressInputBox.style.display = 'block';
         addressField.required = true;
         if (addressField.value === 'Collection from store') {
            addressField.value = '';
         }
      }
   }

   // Initial check
   toggleAddressField();

   // Add event listeners
   deliveryRadio.addEventListener('change', toggleAddressField);
   collectionRadio.addEventListener('change', toggleAddressField);

   // Announce order type changes to screen readers
   const orderTypeRadios = document.querySelectorAll('input[name="order_type"]');
   orderTypeRadios.forEach(radio => {
      radio.addEventListener('change', function() {
         const orderType = this.value;
         if(window.announceToScreenReader) {
            announceToScreenReader('Order type changed to ' + orderType);
         }
      });
   });
});
</script>

</body>
</html>