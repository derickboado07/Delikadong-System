<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Registration</title>
    <link rel="stylesheet" href="userdesign.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<style>
body {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    overflow: hidden;
    opacity: 0;
    transition: opacity 0.8s ease-in-out;
}

body.loaded {
    opacity: 1;
}
        
.left-half {
    flex: 4;
    background-image: url('../images/BG.jpg');
    background-size: cover;
    position: relative;
    z-index: 1;
}
</style>
<body>  
    <div class="left-half">
        <div class="logo">
            <img src="../Images/Icon.png" alt="Logo" />
            <h1>User Registration</h1>
            <p>This user registration form is intended for newly hired employees of Arat Kape MCU. 
               It is part of the official process for granting access to the company’s web-based system.</p>
        </div>
        <div class="out-container">
            <a href="user.php" class="inner-container">
                <h1 class="back-text">GO BACK</h1>
                <i class="fa-solid fa-arrow-left back-icon"></i>
            </a>
        </div>
    </div>

    <div class="right-half">
        <div class="form-container">
            <h2>Account Information</h2>

            <!-- ✅ Now sends data to Verify.php -->
            <form id="signup-form" action="Verify.php" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="input-group">
                        <label for="surname">Surname</label>
                        <input type="text" id="surname" name="surname" placeholder="Enter your Surname" maxlength="16" required />
                    </div>
                    <div class="input-group">
                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" placeholder="Enter your First Name" maxlength="16" required />
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter your Username" maxlength="16" required />
                    </div>
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your Email" maxlength="50" required />
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your Password" maxlength="12" required />
                        <small id="strengthMessage" class="strength"></small>
                    </div>
                    <div class="input-group">
                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm Password" maxlength="12" required />
                    </div>
                </div>

        

                <!-- ✅ Real submit button (no more <a>) -->
                <button type="submit" class="submit-btn">SUBMIT</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
