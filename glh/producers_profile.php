<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

// Get producer ID from URL
$producer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Producer Profile</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      .producer-profile-section{
         min-height: 80vh;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 3rem 2rem;
      }

      .producer-profile-container{
         max-width: 1000px;
         width: 100%;
         display: grid;
         grid-template-columns: 1fr 2fr;
         gap: 3rem;
         background-color: var(--white);
         padding: 3rem;
         border-radius: .5rem;
         box-shadow: var(--box-shadow);
      }

      .producer-image{
         display: flex;
         flex-direction: column;
         align-items: center;
         text-align: center;
      }

      .producer-image img{
         width: 200px;
         height: 200px;
         border-radius: 50%;
         object-fit: cover;
         border: 4px solid var(--green);
         margin-bottom: 1rem;
      }

      .producer-info h1{
         font-size: 3rem;
         color: var(--black);
         margin-bottom: .5rem;
      }

      .producer-info .email{
         font-size: 1.6rem;
         color: var(--light-color);
         margin-bottom: 2rem;
      }

      .producer-stats{
         display: grid;
         grid-template-columns: repeat(3, 1fr);
         gap: 1rem;
         margin-bottom: 2rem;
      }

      .stat-box{
         background: var(--light-bg);
         padding: 1.5rem;
         border-radius: .5rem;
         text-align: center;
         border: 1px solid #e0e0e0;
      }

      .stat-box .number{
         font-size: 2.5rem;
         color: var(--green);
         font-weight: bold;
         display: block;
         margin-bottom: .5rem;
      }

      .stat-box .label{
         font-size: 1.4rem;
         color: var(--black);
         font-weight: 500;
      }

      .producer-description{
         background: var(--light-bg);
         padding: 2rem;
         border-radius: .5rem;
         border: 1px solid #e0e0e0;
         margin-bottom: 2rem;
      }

      .producer-description h3{
         font-size: 2rem;
         color: var(--black);
         margin-bottom: 1rem;
      }

      .producer-description p{
         font-size: 1.6rem;
         color: var(--light-color);
         line-height: 1.6;
      }

      .producer-products{
         margin-top: 2rem;
      }

      .producer-products h3{
         font-size: 2.5rem;
         color: var(--black);
         margin-bottom: 2rem;
         text-align: center;
      }

      .products-grid{
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
         gap: 2rem;
      }

      .product-card{
         background: var(--white);
         border: 1px solid #e0e0e0;
         border-radius: .5rem;
         overflow: hidden;
         transition: transform .2s;
      }

      .product-card:hover{
         transform: translateY(-5px);
         box-shadow: var(--box-shadow);
      }

      .product-card img{
         width: 100%;
         height: 150px;
         object-fit: cover;
      }

      .product-card .content{
         padding: 1rem;
      }

      .product-card .name{
         font-size: 1.6rem;
         color: var(--black);
         font-weight: 500;
         margin-bottom: .5rem;
      }

      .product-card .price{
         font-size: 1.8rem;
         color: var(--green);
         font-weight: bold;
      }

      .product-card .stock-status{
         margin-top: .5rem;
         padding: .3rem .6rem;
         border-radius: .3rem;
         font-size: 1.2rem;
         font-weight: 500;
      }

      .product-card .stock-status.in-stock{
         background: #d4edda;
         color: #155724;
      }

      .product-card .stock-status.out-stock{
         background: #f8d7da;
         color: #721c24;
      }

      .back-link{
         display: inline-block;
         margin: 2rem;
         padding: 0.8rem 1.5rem;
         background: var(--green);
         color: white;
         border-radius: 0.3rem;
         text-decoration: none;
         transition: all 0.2s;
      }

      .back-link:hover{
         background: #2d4620;
      }

      .producer-not-found{
         min-height: 60vh;
         display: flex;
         align-items: center;
         justify-content: center;
      }

      .producer-not-found .empty{
         font-size: 2.5rem;
         padding: 3rem;
      }

      @media(max-width: 768px){
         .producer-profile-container{
            grid-template-columns: 1fr;
            padding: 2rem 1.5rem;
            gap: 2rem;
         }

         .producer-stats{
            grid-template-columns: 1fr;
         }

         .products-grid{
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
         }
      }
   </style>

</head>
<body>

<?php include 'header.php'; ?>

<a href="producers.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Producers</a>

<section class="producer-profile-section">

   <?php
      if($producer_id > 0){
         $select_producer = mysqli_query($conn, "SELECT * FROM `users` WHERE id = '$producer_id' AND user_type = 'producer'") or die('query failed');

         if(mysqli_num_rows($select_producer) > 0){
            $fetch_producer = mysqli_fetch_assoc($select_producer);

            // Get producer's products
            $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE producer_id = '$producer_id'") or die('query failed');
            $total_products = mysqli_num_rows($select_products);

            // Get in stock products
            $select_in_stock = mysqli_query($conn, "SELECT * FROM `products` WHERE producer_id = '$producer_id' AND in_stock = 1") or die('query failed');
            $in_stock_count = mysqli_num_rows($select_in_stock);
   ?>

   <div class="producer-profile-container">

      <!-- Producer Image & Basic Info -->
      <div class="producer-image">
         
         <h1><?php echo htmlspecialchars($fetch_producer['name']); ?></h1>
         <p class="email"><?php echo htmlspecialchars($fetch_producer['email']); ?></p>

         <!-- Producer Stats -->
         <div class="producer-stats">
            <div class="stat-box">
               <span class="number"><?php echo $total_products; ?></span>
               <span class="label">Products</span>
            </div>
            <div class="stat-box">
               <span class="number"><?php echo $in_stock_count; ?></span>
               <span class="label">In Stock</span>
            </div>
            <div class="stat-box">
               <span class="number"><?php echo $total_products - $in_stock_count; ?></span>
               <span class="label">Out of Stock</span>
            </div>
         </div>
      </div>

      <!-- Producer Details -->
      <div class="producer-info">

         <!-- Producer Description -->
         <div class="producer-description">
            <h3>About <?php echo htmlspecialchars($fetch_producer['name']); ?></h3>
            <p>
               <?php
                  if(!empty($fetch_producer['description'])){
                     echo nl2br(htmlspecialchars($fetch_producer['description']));
                  } else {
                     echo "This producer hasn't added a description yet.";
                  }
               ?>
            </p>
         </div>

         <!-- Producer's Products -->
         <div class="producer-products">
            <h3><?php echo htmlspecialchars($fetch_producer['name']); ?>'s Products</h3>

            <?php if($total_products > 0): ?>
            <div class="products-grid">
               <?php
                  mysqli_data_seek($select_products, 0); // Reset pointer
                  while($fetch_product = mysqli_fetch_assoc($select_products)){
               ?>
               <div class="product-card">
                  <a href="product.php?id=<?php echo $fetch_product['id']; ?>">
                     <img src="uploaded_img/<?php echo htmlspecialchars($fetch_product['image']); ?>" alt="<?php echo htmlspecialchars($fetch_product['name']); ?>">
                     <div class="content">
                        <div class="name"><?php echo htmlspecialchars($fetch_product['name']); ?></div>
                        <div class="price">£<?php echo number_format($fetch_product['price'], 2); ?></div>
                        <div class="stock-status <?php echo $fetch_product['in_stock'] ? 'in-stock' : 'out-stock'; ?>">
                           <?php echo $fetch_product['in_stock'] ? 'In Stock' : 'Out of Stock'; ?>
                        </div>
                     </div>
                  </a>
               </div>
               <?php } ?>
            </div>
            <?php else: ?>
               <p class="empty" style="text-align: center; padding: 3rem;">No products added yet.</p>
            <?php endif; ?>
         </div>

      </div>

   </div>

   <?php
         }else{
            echo '<div class="producer-not-found"><p class="empty">Producer not found!</p></div>';
         }
      }else{
         echo '<div class="producer-not-found"><p class="empty">No producer selected! <a href="producers.php" style="color: var(--green); text-decoration: underline;">View All Producers</a></p></div>';
      }
   ?>

</section>

<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>