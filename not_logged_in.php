<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Not Logged In</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f4f4f9;
        }
        .redirect-message-container {
            text-align: center;
            background-color: #ffffff;
            border: 2px solid #e74c3c;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 28px;
        }
        .redirect-message h2 {
            color: #e74c3c;
            font-size: 1.8rem;
        }
        .redirect-message p {
            margin: 10px 0;
            font-size: 1rem;
            color: #333;
        }
        .redirect-message a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .redirect-message a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="redirect-message-container">
        <div class="redirect-message">
            <h2>You must be logged in to send a message.</h2>
            <p>Please <a href="login.php">log in</a> or <a href="signup.php">sign up</a> to continue.</p>
        </div>
    </div>
</body>
</html>
