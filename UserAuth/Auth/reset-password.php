<?php
if (isset($_GET['token'])) {
    $token = htmlspecialchars($_GET['token']); // Sanitize the token input
} else {
    echo "No token provided.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Token</title>
</head>

<body>
    <h1>Password Reset Token</h1>
    <p>Your password reset token is:</p>
    <h2 style="color: red;"><?php echo $token; ?></h2>
    <p>Use this token to reset your password.</p>
</body>

</html>