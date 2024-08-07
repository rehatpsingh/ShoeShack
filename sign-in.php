<?php
session_start();
include 'Database.php';

$user = new User();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $user->sanitize($_POST['email']);
    $password = $_POST['password'];

    if ($user->authenticateUser($email, $password)) {
        header('Location: index.php');
        exit();
    } else {
        $error = 'Invalid email or password.';
    }
}
?>

<?php include 'header.php'; ?>

<main>
    <section class="sign-in section section--xl">
        <div class="container">
            <div class="form-wrapper">
                <h6 class="font-title--sm">Sign in</h6>
                <form action="" method="post">
                    <div class="form-input">
                        <input type="email" name="email" placeholder="Email" required />
                    </div>
                    <div class="form-input">
                        <input type="password" name="password" placeholder="Password" id="password" required />
                        <button type="button" class="icon icon-eye" onclick="showPassword('password',this)">
                            <ion-icon name="eye"></ion-icon>
                        </button>
                    </div>
                    <div class="form-button">
                        <button class="button button--md w-100" type="submit">Login</button>
                    </div>
                    <?php if (isset($error)) : ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endif; ?>
                    <div class="form-register">
                        Don't have an account? <a href="create-account.php">Register</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>