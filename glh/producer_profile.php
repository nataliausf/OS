<?php

include 'config.php';

session_start();

$producer_id = $_SESSION['producer_id'];

if(!isset($producer_id)){
   header('location:login.php');
}

// Handle profile update
if(isset($_POST['update_profile'])){
   $description = mysqli_real_escape_string($conn, $_POST['description']);

   mysqli_query($conn, "UPDATE `users` SET description = '$description' WHERE id = '$producer_id'") or die('query failed');
   $message[] = 'Profile updated successfully!';
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
      .dashboard-header {
         background: linear-gradient(135deg, #3f5f2f 0%, #2d4620 100%);
         color: white;
         padding: 3rem 2rem;
         text-align: center;
         margin-bottom: 3rem;
      }

      .dashboard-header h1 {
         font-size: 3rem;
         margin-bottom: 0.5rem;
      }

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

      .logout-btn {
         background: #c0392b !important;
         float: right;
         margin-top: 0;
      }

      .logout-btn:hover {
         background: #a0290c !important;
      }
   </style>

</head>
<body>
<?php include 'producer_header.php'; ?>

   
   <!-- Profile Management Section -->
   <div class="dashboard-content" style="margin-top: 2rem;">
      <h1 class="title">Manage Your Profile</h1>
      

      
      <div style="max-width: 600px; margin: 0 auto;">
         <form action="" method="post" style="background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);">
            <h3 style="margin-bottom: 1rem; color: var(--black);">About You</h3>
            <p style="color: var(--light-color); margin-bottom: 1.5rem; font-size: 1.4rem;">
               Add a brief description about yourself and your farming practices. This will be visible to customers on your profile page.
            </p>

            <?php
               $select_producer = mysqli_query($conn, "SELECT description FROM `users` WHERE id = '$producer_id'") or die('query failed');
               $fetch_producer = mysqli_fetch_assoc($select_producer);
            ?>

            <textarea name="description" placeholder="Tell customers about yourself, your farm, your farming practices, what makes your products special..." rows="6" class="box" style="width: 100%; resize: vertical; min-height: 120px;"><?php echo htmlspecialchars($fetch_producer['description'] ?? ''); ?></textarea>

            <input type="submit" value="Update Profile" name="update_profile" class="btn" style="width: 100%; margin-top: 1rem;">
         </form>
      </div>
   </div>
   <!-- custom producer js file link  -->
   <script src="js/producer_script.js"></script>

</body>
</html>
