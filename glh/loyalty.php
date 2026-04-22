<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

// Get user loyalty info
$user_query = mysqli_query($conn, "SELECT * FROM `users` WHERE id = '$user_id'") or die('query failed');
$user_data = mysqli_fetch_assoc($user_query);

// Calculate loyalty level based on points and spending
$points = $user_data['loyalty_points'];
$total_spent = $user_data['total_spent'];

if($points >= 5000 || $total_spent >= 500){
   $loyalty_level = 'Platinum';
}elseif($points >= 2500 || $total_spent >= 250){
   $loyalty_level = 'Gold';
}elseif($points >= 1000 || $total_spent >= 100){
   $loyalty_level = 'Silver';
}else{
   $loyalty_level = 'Bronze';
}

// Update loyalty level if changed
if($loyalty_level != $user_data['loyalty_level']){
   mysqli_query($conn, "UPDATE `users` SET loyalty_level = '$loyalty_level' WHERE id = '$user_id'") or die('query failed');
   $user_data['loyalty_level'] = $loyalty_level;
}

// Get available offers for this user
$offers_query = mysqli_query($conn, "
   SELECT * FROM `loyalty_offers` 
   WHERE is_active = 1 
   AND (min_level = '$loyalty_level' OR min_level = 'Bronze')
   AND (min_points <= $points OR min_points = 0)
   AND (min_spent <= $total_spent OR min_spent = 0.00)
   AND (start_date IS NULL OR start_date <= CURDATE())
   AND (end_date IS NULL OR end_date >= CURDATE())
   ORDER BY discount_value DESC
") or die('query failed');

// Get recent loyalty history
$history_query = mysqli_query($conn, "
   SELECT * FROM `user_loyalty_history` 
   WHERE user_id = '$user_id' 
   ORDER BY created_at DESC 
   LIMIT 10
") or die('query failed');

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Loyalty Program</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <!-- Accessibility CSS -->
   <link rel="stylesheet" href="css/accessibility.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="heading">
   <h3>Loyalty Program</h3>
   <p>Earn points, unlock rewards, and enjoy exclusive benefits!</p>
</section>

<section class="products loyalty-content">

   <!-- Loyalty Level Progress -->
   <div class="level-progress">
      <h2>Your Loyalty Level</h2>
      <div class="stat-card <?php echo strtolower($loyalty_level); ?>" style="margin: 0 auto; max-width: 400px;">
         <div class="icon">
            <?php
            if($loyalty_level == 'Bronze') echo '<i class="fas fa-crown" aria-hidden="true"></i>';
            elseif($loyalty_level == 'Silver') echo '<i class="fas fa-crown" aria-hidden="true"></i>';
            elseif($loyalty_level == 'Gold') echo '<i class="fas fa-crown" aria-hidden="true"></i>';
            else echo '<i class="fas fa-diamond" aria-hidden="true"></i>';
            ?>
         </div>
         <div class="number"><?php echo $loyalty_level; ?></div>
         <div class="label">Current Level</div>
      </div>

      <?php
      // Calculate progress to next level
      $next_level_points = 0;
      $next_level_name = '';

      if($loyalty_level == 'Bronze'){
         $next_level_points = 1000;
         $next_level_name = 'Silver';
      }elseif($loyalty_level == 'Silver'){
         $next_level_points = 2500;
         $next_level_name = 'Gold';
      }elseif($loyalty_level == 'Gold'){
         $next_level_points = 5000;
         $next_level_name = 'Platinum';
      }

      if($next_level_points > 0){
         $progress_percentage = min(100, ($points / $next_level_points) * 100);
         echo '<div class="level-info">
                  <span>Progress to ' . $next_level_name . '</span>
                  <span>' . $points . ' / ' . $next_level_points . ' points</span>
               </div>
               <div class="progress-bar" role="progressbar" aria-valuenow="' . $points . '" aria-valuemin="0" aria-valuemax="' . $next_level_points . '" aria-label="Progress to ' . $next_level_name . ' level">
                  <div class="progress-fill" style="width: ' . $progress_percentage . '%"></div>
               </div>';
      }
      ?>
   </div>

   <!-- Loyalty Stats -->
   <div class="loyalty-stats">
      <div class="stat-card bronze">
         <div class="icon"><i class="fas fa-coins" aria-hidden="true"></i></div>
         <div class="number"><?php echo number_format($points); ?></div>
         <div class="label">Loyalty Points</div>
      </div>

      <div class="stat-card silver">
         <div class="icon"><i class="fas fa-shopping-cart" aria-hidden="true"></i></div>
         <div class="number">£<?php echo number_format($total_spent, 2); ?></div>
         <div class="label">Total Spent</div>
      </div>

      <div class="stat-card gold">
         <div class="icon"><i class="fas fa-calendar-alt" aria-hidden="true"></i></div>
         <div class="number"><?php echo date('M Y', strtotime($user_data['join_date'])); ?></div>
         <div class="label">Member Since</div>
      </div>
   </div>

   <!-- Available Offers -->
   <div class="loyalty-offers">
      <h2>Available Offers & Rewards</h2>
      <div class="offers-grid">
         <?php if(mysqli_num_rows($offers_query) > 0): ?>
            <?php while($offer = mysqli_fetch_assoc($offers_query)): ?>
            <div class="offer-card">
               <h3><?php echo htmlspecialchars($offer['title']); ?></h3>
               <div class="discount">
                  <?php
                  if($offer['discount_type'] == 'percentage'){
                     echo $offer['discount_value'] . '% OFF';
                  }elseif($offer['discount_type'] == 'fixed'){
                     echo '£' . number_format($offer['discount_value'], 2) . ' OFF';
                  }else{
                     echo $offer['discount_value'] . ' Points';
                  }
                  ?>
               </div>
               <div class="description"><?php echo htmlspecialchars($offer['description']); ?></div>
               <div class="requirements">
                  <strong>Requirements:</strong><br>
                  Level: <?php echo $offer['min_level']; ?><br>
                  <?php if($offer['min_points'] > 0): ?>
                     Points: <?php echo number_format($offer['min_points']); ?><br>
                  <?php endif; ?>
                  <?php if($offer['min_spent'] > 0): ?>
                     Spent: £<?php echo number_format($offer['min_spent'], 2); ?>
                  <?php endif; ?>
               </div>
            </div>
            <?php endwhile; ?>
         <?php else: ?>
            <div class="offer-card">
               <h3>No Offers Available</h3>
               <div class="description">Keep shopping to unlock exclusive rewards and discounts!</div>
            </div>
         <?php endif; ?>
      </div>
   </div>

   <!-- Loyalty History -->
   <div class="loyalty-history">
      <h2 class="title">Points History</h2>
      <?php if(mysqli_num_rows($history_query) > 0): ?>
      <div class="table-responsive">
         <table class="history-table">
            <thead>
               <tr>
                  <th>Date</th>
                  <th>Action</th>
                  <th>Points</th>
                  <th>Description</th>
               </tr>
            </thead>
            <tbody>
               <?php while($history = mysqli_fetch_assoc($history_query)): ?>
               <tr>
                  <td><?php echo date('d M Y', strtotime($history['created_at'])); ?></td>
                  <td>
                     <span class="points-<?php echo $history['action_type']; ?>">
                        <?php echo ucfirst($history['action_type']); ?>
                     </span>
                  </td>
                  <td><?php echo ($history['action_type'] == 'used' ? '-' : '+') . number_format($history['points']); ?></td>
                  <td><?php echo htmlspecialchars($history['description']); ?></td>
               </tr>
               <?php endwhile; ?>
            </tbody>
         </table>
      </div>
      <?php else: ?>
         <p class="empty" style="text-align: center;">No loyalty activity yet. Start shopping to earn points!</p>
      <?php endif; ?>
   </div>

</section>

<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<!-- Accessibility JavaScript -->
<script src="js/accessibility.js"></script>

<script>
// Keyboard navigation for offer cards
document.addEventListener('DOMContentLoaded', function() {
   const offerCards = document.querySelectorAll('.offer-card');

   offerCards.forEach(card => {
      card.setAttribute('tabindex', '0');
      card.setAttribute('role', 'button');
      card.setAttribute('aria-label', card.querySelector('h3').textContent + ' - ' + card.querySelector('.discount').textContent);

      card.addEventListener('keydown', function(e) {
         if(e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            // Could add functionality to apply offer
         }
      });
   });

   // Announce page load to screen readers
   const announcement = document.createElement('div');
   announcement.setAttribute('aria-live', 'polite');
   announcement.setAttribute('aria-atomic', 'true');
   announcement.className = 'sr-only';
   announcement.textContent = 'Loyalty program page loaded. You have ' + <?php echo $points; ?> + ' loyalty points and are at ' + '<?php echo $loyalty_level; ?>' + ' level.';
   document.body.appendChild(announcement);

   // Remove announcement after a delay
   setTimeout(() => {
      document.body.removeChild(announcement);
   }, 1000);
});
</script>

</body>
</html>