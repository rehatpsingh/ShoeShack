<?php
include 'header.php';
include 'Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $firstname = $_POST['firstname'];
  $lastname = $_POST['lastname'];
  $email = $_POST['email'];
  $mobile = $_POST['mobile'];
  $message = $_POST['message'];

  // Validate input
  if (empty($firstname) || empty($lastname) || empty($email) || empty($mobile) || empty($message)) {
    $error = 'Please fill in all required fields.';
  } else {
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = 'Invalid email address.';
    } else {
      // Save data to database
      $contactUs = new ContactUs();
      $contactUs->addContactUs($firstname, $lastname, $email, $mobile, $message);
      header('Location: index.php');
      exit();
    }
  }
}
?>
<main>
  <div class="contact-section">
    <h2 class="contact-section-heading heading">Contact us</h2>
    <div class="contact-us-form">
      <form action="contact.php" method="post">
        <input type="text" name="firstname" placeholder="First Name" />
        <input type="text" name="lastname" placeholder="Last Name" />
        <input type="email" name="email" placeholder="Email" />
        <input type="tel" name="mobile" placeholder="Mobile Number" />
        <textarea name="message" placeholder="Message"></textarea>
        <?php if (isset($error)) : ?>
          <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <button type="submit">Submit</button>
      </form>
    </div>
  </div>
</main>

<?php
include 'footer.php';
?>