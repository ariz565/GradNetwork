<?php session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'admin/db_connect.php'; 

    $email = $_POST['username'];
    $password = $_POST['password'];

    // Implement additional input validation and sanitization as needed
    

    // Use prepared statements to protect against SQL injection
    $stmt = $conn->prepare("SELECT id, verified FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $stmt->bind_result($user_id, $verified);
    $stmt->fetch();
    $stmt->close();

    if ($user_id) {
        if ($verified == 1) {
            // Successful login
            $_SESSION['user_id'] = $user_id;
            $redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php?page=home';
            header("Location: $redirect_url");
            exit();
        } else {
            $error_message = "Your account is not yet verified.";
        }
    } else {
        $error_message = "Email or password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Add your head content here -->
</head>

<body>
    <div class="container-fluid" id="login-form-container">
        <button type="button" class="close" aria-label="Close" onclick="closeLoginForm()">
            <span aria-hidden="true">&times;</span>
        </button>
        <form action="" id="login-frm" method="POST">
            <div class="form-group">
                <label for="username" class="control-label">Email</label>
                <input type="email" name="username" required class="form-control">
            </div>
            <div class="form-group">
                <label for="password" class="control-label">Password</label>
                <input type="password" name="password" required class="form-control">
                <small><a href="index.php?page=signup" id="new_account">Create New Account</a></small>
            </div>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <button class="button btn btn-info btn-sm">Login</button>
        </form>
    </div>

    <style>
        #uni_modal .modal-footer {
            display: none;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function closeLoginForm() {
            // $('#login-form-container').hide();
			location.href = 'index.php?page=home';
        }

        $('#login-frm').submit(function(e) {
            e.preventDefault();
            $('#login-frm button[type="submit"]').attr('disabled', true).html('Logging in...');
            if ($(this).find('.alert-danger').length > 0)
                $(this).find('.alert-danger').remove();
            $.ajax({
                url: 'admin/ajax.php?action=login2',
                method: 'POST',
                data: $(this).serialize(),
                error: function(err) {
                    console.log(err);
                    $('#login-frm button[type="submit"]').removeAttr('disabled').html('Login');
                },
                success: function(resp) {
                    if (resp == 1) {
                        location.href = '<?php echo isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php?page=home' ?>';
                    } else if (resp == 2) {
                        $('#login-frm').prepend('<div class="alert alert-danger">Your account is not yet verified.</div>');
                        $('#login-frm button[type="submit"]').removeAttr('disabled').html('Login');
                    } else {
                        $('#login-frm').prepend('<div class="alert alert-danger">Email or password is incorrect.</div>');
                        $('#login-frm button[type="submit"]').removeAttr('disabled').html('Login');
                    }
                }
            });
        });
    </script>
</body>

</html>





