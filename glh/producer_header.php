<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">

   <div class="flex">

      <a href="producer_page.php" class="logo">Producer<span>Panel</span></a>

      <nav class="navbar">
         <a href="producer_page.php">Dashboard</a>
         <a href="producer_profile.php">Profile</a>
         <a href="producer_products.php">Products</a>
         <a href="producer_orders.php">Orders</a>
         <a href="producer_contacts.php">Messages</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="account-box">
         <p>username : <span><?php echo $_SESSION['producer_name']; ?></span></p>
         <p>email : <span><?php echo $_SESSION['producer_email']; ?></span></p>
         <a href="logout.php" class="delete-btn">logout</a>
         <div>new <a href="login.php">login</a> | <a href="register.php">register</a></div>
      </div>

   </div>

</header>