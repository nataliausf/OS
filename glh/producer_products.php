<?php

include 'config.php';

session_start();

$producer_id = $_SESSION['producer_id'];

if(!isset($producer_id)){
   header('location:login.php');
}

// Add product
if(isset($_POST['add_product'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $price = number_format((float) $_POST['price'], 2, '.', '');
   $category = mysqli_real_escape_string($conn, $_POST['category']);
   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;
   $in_stock = isset($_POST['in_stock']) ? 1 : 0;

   $select_product_name = mysqli_query($conn, "SELECT name FROM `products` WHERE name = '$name' AND producer_id = '$producer_id'") or die('query failed');

   if(mysqli_num_rows($select_product_name) > 0){
      $message[] = 'Product name already added';
   }else{
      $add_product_query = mysqli_query($conn, "INSERT INTO `products`(name, price, category, image, producer_id, in_stock) VALUES('$name', '$price', '$category', '$image', '$producer_id', '$in_stock')") or die('query failed');

      if($add_product_query){
         if($image_size > 2000000){
            $message[] = 'Image size is too large';
         }else{
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'Product added successfully!';
         }
      }else{
         $message[] = 'Product could not be added!';
      }
   }
}

// Delete product
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_check = mysqli_query($conn, "SELECT producer_id, image FROM `products` WHERE id = '$delete_id'") or die('query failed');
   $fetch_delete = mysqli_fetch_assoc($delete_check);

   // Only allow producer to delete their own products
   if($fetch_delete['producer_id'] == $producer_id){
      unlink('uploaded_img/'.$fetch_delete['image']);
      mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('query failed');
      header('location:producer_products.php');
   }else{
      $message[] = 'You cannot delete this product!';
   }
}

// Update product
if(isset($_POST['update_product'])){

   $update_p_id = $_POST['update_p_id'];
   $update_name = $_POST['update_name'];
   $update_price = number_format((float) $_POST['update_price'], 2, '.', '');
   $update_category = mysqli_real_escape_string($conn, $_POST['update_category']);
   $in_stock = isset($_POST['in_stock']) ? 1 : 0;

   $check_product = mysqli_query($conn, "SELECT producer_id FROM `products` WHERE id = '$update_p_id'") or die('query failed');
   $fetch_check = mysqli_fetch_assoc($check_product);

   // Only allow producer to update their own products
   if($fetch_check['producer_id'] == $producer_id){
      mysqli_query($conn, "UPDATE `products` SET name = '$update_name', price = '$update_price', category = '$update_category', in_stock = '$in_stock' WHERE id = '$update_p_id'") or die('query failed');

      $update_image = $_FILES['update_image']['name'];
      $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
      $update_image_size = $_FILES['update_image']['size'];
      $update_folder = 'uploaded_img/'.$update_image;
      $update_old_image = $_POST['update_old_image'];

      if(!empty($update_image)){
         if($update_image_size > 2000000){
            $message[] = 'Image file size is too large';
         }else{
            mysqli_query($conn, "UPDATE `products` SET image = '$update_image' WHERE id = '$update_p_id'") or die('query failed');
            move_uploaded_file($update_image_tmp_name, $update_folder);
            unlink('uploaded_img/'.$update_old_image);
         }
      }

      $message[] = 'Product updated successfully!';
      header('location:producer_products.php');
   }else{
      $message[] = 'You cannot update this product!';
   }
}

// Toggle stock status
if(isset($_GET['toggle_stock'])){
   $product_id = $_GET['toggle_stock'];
   
   $check_product = mysqli_query($conn, "SELECT producer_id, in_stock FROM `products` WHERE id = '$product_id'") or die('query failed');
   $fetch_product = mysqli_fetch_assoc($check_product);

   if($fetch_product['producer_id'] == $producer_id){
      $new_status = $fetch_product['in_stock'] == 1 ? 0 : 1;
      mysqli_query($conn, "UPDATE `products` SET in_stock = '$new_status' WHERE id = '$product_id'") or die('query failed');
      $message[] = 'stock status updated!';
      header('location:producer_products.php');
   }else{
      $message[] = 'You cannot update this product!';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>manage products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom producer css file link  -->
   <link rel="stylesheet" href="css/producer_style.css">

   <style>
      .back-link {
         display: inline-block;
         margin: 2rem;
         padding: 0.8rem 1.5rem;
         background: #3f5f2f;
         color: white;
         border-radius: 0.3rem;
         text-decoration: none;
         transition: all 0.2s;
      }

      .back-link:hover {
         background: #2d4620;
      }

      .stock-toggle {
         display: inline-block;
         padding: 0.5rem 1rem;
         border-radius: 0.3rem;
         cursor: pointer;
         font-size: 1.4rem;
         transition: all 0.2s;
         text-decoration: none;
      }

      .stock-toggle.in-stock {
         background: #27ae60;
         color: white;
      }

      .stock-toggle.in-stock:hover {
         background: #229954;
      }

      .stock-toggle.out-stock {
         background: #c0392b;
         color: white;
      }

      .stock-toggle.out-stock:hover {
         background: #a0290c;
      }

      .box .stock-info {
         margin: 1rem 0;
         padding: 1rem;
         text-align: center;
         border-radius: 0.3rem;
      }

      .box .stock-info.in-stock-info {
         background: #d4edda;
         color: #155724;
      }

      .box .stock-info.out-stock-info {
         background: #f8d7da;
         color: #721c24;
      }

      .box a{
         padding: 1rem 1rem;
         border-radius: 0.3rem;
      }
   </style>

</head>
<body>

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
<?php include 'producer_header.php'; ?>

<a href="producer_page.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

<!-- product CRUD section starts  -->

<section class="add-products">

   <h1 class="title">Manage Your Products</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <h3>add product</h3>
      <input type="text" name="name" class="box" placeholder="Enter product name" required>
      <input type="number" min="0" step="0.01" name="price" class="box" placeholder="Enter product price" required>
      <select name="category" class="box" required>
         <option value="">Select Category</option>
         <option value="Fruits">Fruits</option>
         <option value="Vegetables">Vegetables</option>
         <option value="Dairy">Dairy</option>
         <option value="Meat">Meat</option>
         <option value="Bakery">Bakery</option>
         <option value="Beverages">Beverages</option>
         <option value="Other">Other</option>
      </select>
      <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
      
      <label style="margin: 1rem 0; display: flex; align-items: center; cursor: pointer; font-size: 1.8rem;">
         <input type="checkbox" name="in_stock" id="in_stock" checked style="width: 20px; height: 20px; cursor: pointer; margin-right: 0.5rem;">
         <span>Product is in stock</span>
      </label>

      <input type="submit" value="add product" name="add_product" class="btn">
   </form>

</section>

<!-- product CRUD section ends -->

<!-- show products  -->

<section class="show-products">

   <div class="box-container">

      <?php
         $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE producer_id = '$producer_id'") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
      ?>
      <div class="box">
         <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
         <div class="name"><?php echo $fetch_products['name']; ?></div>
         <div class="price">£<?php echo number_format($fetch_products['price'], 2); ?></div>
         
         <div class="stock-info <?php echo $fetch_products['in_stock'] ? 'in-stock-info' : 'out-stock-info'; ?>">
            <?php echo $fetch_products['in_stock'] ? '<i class="fas fa-check-circle"></i> In Stock' : '<i class="fas fa-times-circle"></i> Out of Stock'; ?>
         </div>

         <a href="producer_products.php?toggle_stock=<?php echo $fetch_products['id']; ?>" class="stock-toggle <?php echo $fetch_products['in_stock'] ? 'in-stock' : 'out-stock'; ?>">
            <?php echo $fetch_products['in_stock'] ? 'Mark Out of Stock' : 'Mark In Stock'; ?>
         </a>

         <div class="action-buttons">
            <a href="producer_products.php?update=<?php echo $fetch_products['id']; ?>" class="option-btn">update</a>
            <a href="producer_products.php?delete=<?php echo $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
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

<section class="edit-product-form">

   <?php
      if(isset($_GET['update'])){
         $update_id = $_GET['update'];
         $update_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$update_id' AND producer_id = '$producer_id'") or die('query failed');
         if(mysqli_num_rows($update_query) > 0){
            while($fetch_update = mysqli_fetch_assoc($update_query)){
   ?>
   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="update_p_id" value="<?php echo $fetch_update['id']; ?>">
      <input type="hidden" name="update_old_image" value="<?php echo $fetch_update['image']; ?>">
      <img src="uploaded_img/<?php echo $fetch_update['image']; ?>" alt="">
      <input type="text" name="update_name" value="<?php echo $fetch_update['name']; ?>" class="box" required placeholder="enter product name">
      <input type="number" name="update_price" value="<?php echo $fetch_update['price']; ?>" min="0" step="0.01" class="box" required placeholder="enter product price">
      <select name="update_category" class="box" required>
         <option value="">Select Category</option>
         <option value="Fruits" <?php echo ($fetch_update['category'] == 'Fruits') ? 'selected' : ''; ?>>Fruits</option>
         <option value="Vegetables" <?php echo ($fetch_update['category'] == 'Vegetables') ? 'selected' : ''; ?>>Vegetables</option>
         <option value="Dairy" <?php echo ($fetch_update['category'] == 'Dairy') ? 'selected' : ''; ?>>Dairy</option>
         <option value="Meat" <?php echo ($fetch_update['category'] == 'Meat') ? 'selected' : ''; ?>>Meat</option>
         <option value="Bakery" <?php echo ($fetch_update['category'] == 'Bakery') ? 'selected' : ''; ?>>Bakery</option>
         <option value="Beverages" <?php echo ($fetch_update['category'] == 'Beverages') ? 'selected' : ''; ?>>Beverages</option>
         <option value="Other" <?php echo ($fetch_update['category'] == 'Other') ? 'selected' : ''; ?>>Other</option>
      </select>
      
      <label style="margin: 1rem 0; display: flex; align-items: center; cursor: pointer; font-size: 1.8rem;">
         <input type="checkbox" name="in_stock" id="update_in_stock" <?php echo $fetch_update['in_stock'] ? 'checked' : ''; ?> style="width: 20px; height: 20px; cursor: pointer; margin-right: 0.5rem;">
         <span>Product is in stock</span>
      </label>

      <input type="file" class="box" name="update_image" accept="image/jpg, image/jpeg, image/png">
      <input type="submit" value="update" name="update_product" class="btn">
      <input type="reset" value="cancel" id="close-update" class="option-btn">
   </form>
   <?php
         }
      }
      }else{
         echo '<script>document.querySelector(".edit-product-form").style.display = "none";</script>';
      }
   ?>

</section>

</body>
</html>
