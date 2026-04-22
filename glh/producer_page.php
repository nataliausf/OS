<?php

include 'config.php';

session_start();

$producer_id = $_SESSION['producer_id'];

if(!isset($producer_id)){
   header('location:login.php');
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Producer Dashboard</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom producer css file link  -->
   <link rel="stylesheet" href="css/producer_style.css">

   <style>

      .dashboard-content {
         max-width: 1200px;
         margin: 0 auto;
         padding: 2rem;
      }

      .dashboard-grid {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 2rem;
         margin-bottom: 3rem;
      }

      .dashboard-card {
         background: white;
         padding: 2rem;
         border-radius: 0.5rem;
         box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
         text-align: center;
         border-top: 4px solid #3f5f2f;
      }

      .dashboard-card h3 {
         font-size: 1.8rem;
         color: #333;
         margin-bottom: 1rem;
      }

      .dashboard-card .number {
         font-size: 3rem;
         color: #3f5f2f;
         font-weight: bold;
         margin: 1rem 0;
      }

      .dashboard-card a {
         display: inline-block;
         margin-top: 1rem;
         padding: 0.8rem 1.5rem;
         background: #3f5f2f;
         color: white;
         border-radius: 0.3rem;
         text-decoration: none;
         transition: all 0.2s;
      }

      .dashboard-card a:hover {
         background: #2d4620;
         transform: translateY(-2px);
      }

   </style>

</head>
<body>

<?php include 'producer_header.php'; ?>

<!-- admin dashboard section starts  -->

<section class="dashboard">

   <h1 class="title">dashboard</h1>

   <div class="box-container">

        <div class="box">
            <div class="icon"><i class="fas fa-box"></i></div>
            <h3>My Products</h3>
            <?php
               $count_products = mysqli_query($conn, "SELECT * FROM `products` WHERE producer_id = '$producer_id'") or die('query failed');
               $total_products = mysqli_num_rows($count_products);
               echo '<div class="number">'.$total_products.'</div>';
            ?>
            <a href="producer_products.php">Manage Products</a>
         </div>

        <div class="box">
            <div class="icon"><i class="fas fa-chart-bar"></i></div>
            <h3>In Stock</h3>
            
            <?php
               $count_stock = mysqli_query($conn, "SELECT * FROM `products` WHERE producer_id = '$producer_id' AND in_stock = 1") or die('query failed');
               $total_stock = mysqli_num_rows($count_stock);
               echo '<div class="number">'.$total_stock.'</div>';
            ?>
            <p>Products available</p>
         </div>

         <div class="box">
            <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
            <h3>Out of Stock</h3>
            <?php
               $count_outstock = mysqli_query($conn, "SELECT * FROM `products` WHERE producer_id = '$producer_id' AND in_stock = 0") or die('query failed');
               $total_outstock = mysqli_num_rows($count_outstock);
               echo '<div class="number">'.$total_outstock.'</div>';
            ?>
            <p>Need attention</p>
         </div>   
         
  
      

      <div class="box">
        <div class="icon"><i class="fa-solid fa-truck"></i></div>
        <h3>Total Orders</h3>
         <?php 
            $select_orders = mysqli_query($conn, "SELECT * FROM `orders`") or die('query failed');
            $number_of_orders = mysqli_num_rows($select_orders);
         ?>
         <div class="number"><?php echo $number_of_orders; ?></div>
        <a href="producer_orders.php">Manage Orders</a>
      </div>

      <div class="box">
        <div class="icon"><i class="fa-solid fa-envelope"></i></div>
        <h3>New Messages</h3>
         <?php 
            $select_messages = mysqli_query($conn, "SELECT * FROM `message`") or die('query failed');
            $number_of_messages = mysqli_num_rows($select_messages);
         ?>
         <div class="number"><?php echo $number_of_messages; ?></div>
         <p>new messages</p>
      </div>

   </div>

</section>

<!-- admin dashboard section ends -->


   
   <!-- custom producer js file link  -->
   <script src="js/producer_script.js"></script>

</body>
</html>
