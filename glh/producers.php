<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `users` WHERE id = '$delete_id' AND user_type = 'producer'") or die('query failed');
   header('location:producers.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>producers</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>Producers</h3>
   <p> <a href="home.php">Home</a> / Producers </p>
</div>

<section class="users">

   <h1 class="title"> producers </h1>

   <div class="box-container">
      <?php
         $select_producers = mysqli_query($conn, "SELECT * FROM `users` WHERE user_type = 'producer'") or die('query failed');
         while($fetch_producers = mysqli_fetch_assoc($select_producers)){
      ?>
      <div class="box">
         <a href="producers_profile.php?id=<?php echo $fetch_producers['id']; ?>" style="text-decoration: none; color: inherit;">
            
            <p> Producer : <span><?php echo $fetch_producers['name']; ?></span> </p>
            <p> email : <span><?php echo $fetch_producers['email']; ?></span> </p>
            <p style="color: var(--green); font-weight: 500;">Click for more information</p>
         </a>
      </div>
      <?php
         };
      ?>
   </div>

</section>









<!-- custom admin js file link  -->
<script src="js/admin_script.js"></script>

</body>
</html>