<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Login Method</title>

   <link rel="stylesheet" href="../css/htm.css">
    <link rel="stylesheet" href="../css/mainlogin.css">

    <style>
        /* Layout tightening */
        .method-section {
            display: flex;
            flex-direction: column;
            gap: 18px;
            width: 100%;
            margin-top: 10px;
        }

       
        .otp-row {
            display: flex;
            width: 100%;
        }

        .otp-input {
            flex: 1;
            padding: 15px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 10px 0 0 10px;
            border-right: none;
            box-sizing: border-box;
        }

        .otp-input:focus {
            outline: none;
            border-color: #ff7a00;
            box-shadow: 0 0 5px rgba(255,122,0,0.4);
        }

        .send-btn {
            width: 110px;
            background: #ff7a00;
            color: #fff;
            border: 1px solid #ff7a00;
            border-radius: 0 10px 10px 0;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: 0.2s;
        }

        .send-btn:hover {
            background: #e56d00;
        }

        /* Center text improvements */
        .small-or {
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            color: #444;
            margin: 0;
        }

        .subtext {
            text-align: center;
            font-size: 15px;
            margin: -5px 0 5px;
            color: #666;
        }

        /* Email text size fix */
        .email-text {
            font-size: 16px;
            margin-bottom: 15px;
            color: #444;
        }

        /* Final login button */
        .continue-final-btn {
            margin-top: 5px;
            padding: 15px;
            background: #ff7a00;
            color: #fff;
            font-size: 17px;
            font-weight: 600;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.25s ease-in-out;
        }

        .continue-final-btn:hover {
            background: #e56d00;
            transform: translateY(-2px);
        }

    </style>
</head>

<body>

<header class="logo-header">
    <a href="../hmt.html" class="logo">Merokala</a>
</header>

<section class="login-area">
    <div class="login-container">

        <h2 class="title">Choose a login method</h2>

        <p class="email-text">
            Logging in as <b>example@example.com</b>
        </p>

        <div class="method-section">

            <!-- Password input -->
            <input type="password" placeholder="Enter your password" class="input-field">

            <!-- OR -->
            <div class="small-or">OR</div>
            <div class="subtext">Login through OTP</div>

            <!-- OTP input with flush Send button -->
            <div class="otp-row">
                <input type="text" placeholder="Enter OTP" class="otp-input">
                <button class="send-btn">Send</button>
            </div>

            <!-- Continue/Login button -->
            <button class="continue-final-btn">Login</button>

        </div>

    </div>
</section>

</body>
</html>
