<?php
session_start();
include "./data-handling/db/connection.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
      $error = "Please complete the reCAPTCHA verification.";
  } else {
      $recaptcha_secret = "6LdUMPUqAAAAAFZIGMIsx26RiIwZmCPKsBMBIPew";
      $recaptcha_response = $_POST['g-recaptcha-response'];

      // Verify reCAPTCHA
      $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response");
      $responseKeys = json_decode($response, true);

      if (!$responseKeys["success"]) {
          $error = "reCAPTCHA verification failed. Please try again.";
      } else {
          // Proceed with signup if reCAPTCHA is valid
          $fname = $_POST["fname"];
          $lname = $_POST["lname"];
          $email = $_POST["email"];
          $mobile = $_POST["mobile"];
          $password = $_POST["password"];
          $province = $_POST["province_name"];
          $city = $_POST["city_name"];
          $barangay = $_POST["barangay_name"];
          $street = $_POST["street"];
          $role = 0;

          $stmt = $con->prepare("SELECT id FROM user WHERE email = ?");
          $stmt->bind_param("s", $email);
          $stmt->execute();
          $stmt->store_result();

          if ($stmt->num_rows > 0) {
              $error = "Email already exists!";
          } else {
              $_SESSION["registration_data"] = [
                  "fname" => $fname,
                  "lname" => $lname,
                  "email" => $email,
                  "mobile" => $mobile,
                  "password" => password_hash($password, PASSWORD_DEFAULT),
                  "province" => $province,
                  "city" => $city,
                  "barangay" => $barangay,
                  "street" => $street,
                  "role" => $role
              ];

              $otp = rand(100000, 999999);
              $_SESSION["otp"] = $otp;

              try {
                  $mail->isSMTP();
                  $mail->Host = 'smtp.gmail.com';
                  $mail->SMTPAuth = true;
                  $mail->Username = 'cholescatering@gmail.com';
                  $mail->Password = 'kuse tvje epft vvuq';
                  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                  $mail->Port = 587;

                  $mail->setFrom('cholescatering@gmail.com', 'CHOLES Support');
                  $mail->addAddress($email, "$fname $lname");

                  $mail->isHTML(true);
                  $mail->Subject = 'OTP Verification - CHOLES';
                  $mail->Body = "<div style='font-family: Arial, sans-serif; color: #333; max-width: 500px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9; text-align: center;'>
                                    <h3 style='color: #2c3e50;'>Hello, " . htmlspecialchars($fname) . "!</h3>
                                    <p style='font-size: 16px; line-height: 1.5;'>Your OTP code is:</p>
                                    <div style='display: inline-block; font-size: 24px; font-weight: bold; color: #e74c3c; background: #fff; padding: 10px 20px; border: 2px dashed #e74c3c; border-radius: 5px; margin: 10px 0;'>
                                        " . htmlspecialchars($otp) . "
                                    </div>
                                    <p style='font-size: 16px; line-height: 1.5;'>Please enter this code to verify your account.</p>
                                    <p style='font-size: 14px; color: #777;'>This OTP will expire in 10 minutes. Do not share it with anyone.</p>
                                </div>
                            ";

                  $mail->send();


                  header("Location: otp.php");
                  exit();
              } catch (Exception $e) {
                  error_log("Mailer Error: " . $mail->ErrorInfo);
                  $error = "Failed to send OTP. Please try again.";
              }
          }

          $stmt->close();
          $con->close();
      }
  }
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Signup</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">


</head>

<body>
<header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <h1 class="sitename">CHOLES</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home<br></a></li>
          <li><a href="about.php">About Us</a></li>
          <li><a href="catering.php">Catering</a></li>
          <li><a href="events.php">Event Styling</a></li>
          <!-- <li><a href="contact.php">Contact Us</a></li> -->
          <li><a href="login.php">Login</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="signup.php">Create an account</a>

    </div>
  </header>

  <main class="main d-flex align-items-center justify-content-center p-2" style="min-height: 80vh; background-color: rgb(43, 43, 43);">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="card shadow p-4">
            <h3 class="text-center">Sign Up</h3>
            <form method="post">
              <?php if (isset($error)) : ?>
                <div class="alert alert-danger text-center">
                  <?php echo $error; ?>
                </div>
              <?php endif; ?>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="fname" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lname" class="form-control" required>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Mobile</label>
                <input type="text" name="mobile" class="form-control" required  oninput="formatPhoneNumber(this)">
              </div>

              <div class="mb-3">
              <label class="form-label" for="province">Province</label>
                <select id="province" class="form-control" required name="province">
                    <option value="">Select Province</option>
                </select>
                <input type="hidden" id="province_name" name="province_name"> <!-- Hidden input -->
              </div>

              <div class="mb-3">
                <label class="form-label" for="city">City</label>
                <select id="city" class="form-control" required name="city">
                    <option value="">Select City</option>
                </select>
                <input type="hidden" id="city_name" name="city_name"> <!-- Hidden input -->
              </div>

              <div class="mb-3">
                <label class="form-label" for="barangay">Barangay</label>
                <select id="barangay" class="form-control" required name="barangay">
                    <option value="">Select Barangay</option>
                </select>
                <input type="hidden" id="barangay_name" name="barangay_name"> <!-- Hidden input -->
              </div>


              <div class="mb-3">
                <label class="form-label">Street</label>
                <input type="text" name="street" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>

              <div class="mb-3 d-flex justify-content-center ialign-items-center">
                  <div class="g-recaptcha" data-sitekey="6LdUMPUqAAAAALZHOSRgzVY2T5e-usP6Iv3kjoss"></div>
              </div>

              <button type="submit" class="btn btn-warning w-100">Sign Up</button>
              <p class="text-center mt-3">Already have an account? <a href="login.php">Login</a></p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer id="footer" class="footer position-relative light-background">

  <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename" style="color: orange;">CHOLES Catering Services</span>
          </a>
          <div class="footer-contact pt-3">
            <p>Cabanatuan</p>
            <p>Nueva Ecija, Philippines</p>
            <p class="mt-3"><strong>Phone:</strong> <span>+63 945-300-7815</span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href="https://www.facebook.com/CholesSeriouslyPulutan"><i class="bi bi-facebook"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">About us</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="#">Terms of service</a></li>
            <li><a href="#">Privacy policy</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Our Services</h4>
          <ul>
            <li><a href="#">Catering</a></li>
            <li><a href="#">Venue Styling</a></li>
            <li><a href="#">Menu Choosing</a></li>
            <li><a href="#">Chairs and Tables</a></li>
            <li><a href="#">And many more</a></li>
          </ul>
        </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>©  <strong class="px-1 sitename">CHOLES</strong> <span>All Rights Reserved</span></p>
    </div>

  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Preloader -->
<div id="preloader"></div>

<!-- Vendor JS Files -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/php-email-form/validate.js"></script>
<script src="assets/vendor/aos/aos.js"></script>
<script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>


<!-- Main JS File -->
<script src="assets/js/main.js"></script>

<script>
let provinces = [];
let cities = [];
let barangays = [];

$(document).ready(function() {
    // Default values
    let defaultProvinceCode = "0349";
    let defaultProvinceName = "Nueva Ecija";
    let defaultCityCode = "034903";
    let defaultCityName = "Cabanatuan City";

    // Disable Province and City Selects
    $("#province").prop("disabled", true);
    $("#city").prop("disabled", true);

    // Load Province Data
    $.getJSON("province.json", function(data) {
        provinces = data;
        $.each(provinces, function(index, province) {
            $("#province").append(`<option value="${province.province_code}">${province.province_name}</option>`);
        });

        // Set default province
        $("#province").val(defaultProvinceCode);
        $("#province_name").val(defaultProvinceName);
    });

    // Load City Data
    $.getJSON("city.json", function(data) {
        cities = data;
        $.each(cities, function(index, city) {
            $("#city").append(`<option value="${city.city_code}">${city.city_name}</option>`);
        });

        // Set default city
        $("#city").val(defaultCityCode);
        $("#city_name").val(defaultCityName);
    });

    // Load Barangay Data and Display All Barangays of Cabanatuan City
    $.getJSON("barangay.json", function(data) {
        barangays = data;
        $("#barangay").html('<option value="">Select Barangay</option>');

        $.each(barangays, function(index, barangay) {
            if (barangay.city_code === defaultCityCode) {
                $("#barangay").append(`<option value="${barangay.brgy_code}">${barangay.brgy_name}</option>`);
            }
        });
    });

    // Barangay Change Event
    $("#barangay").change(function() {
        let selectedBarangayCode = $(this).val();
        let selectedBarangay = barangays.find(brgy => brgy.brgy_code === selectedBarangayCode);

        // Store barangay name in the hidden input
        $("#barangay_name").val(selectedBarangay ? selectedBarangay.brgy_name : "");
    });
});


</script>

<script>
      function formatPhoneNumber(input) {
    // Remove non-numeric characters
    var phoneNumber = input.value.replace(/\D/g, '');

    // Ensure the length does not exceed 11 digits
    if (phoneNumber.length > 11) {
        phoneNumber = phoneNumber.slice(0, 11);
    }

    // Format the phone number without hyphens
    var formattedNumber = phoneNumber;

    // Update the input value with the formatted number
    input.value = formattedNumber;

    // Check if the entered number starts with "09"
    if (phoneNumber.length !== 11) {
        input.setCustomValidity('Invalid phone number format. Must start with "09" and 11 digits.');
    } else if (!/^09\d{9}$/.test(phoneNumber)) {
        input.setCustomValidity('Invalid phone number format. Must start with "09".');
    } else {
        input.setCustomValidity('');
    }

    // Trigger custom validation styles (if you have any)
    input.parentElement.classList.add('was-validated');
}
</script>
</body>
</html>
