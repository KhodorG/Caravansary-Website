<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

function sendEmail($to, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'khodorghalem@gmail.com'; // Your email
        $mail->Password = 'lpab aqip gjkd xyak';   // Your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender & recipient
        $mail->setFrom('khodorghalem@gmail.com', 'Caravansary Capital');
        $mail->addAddress($to);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "<p>$body</p>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

$host = '172.24.160.1';
$port = '5432';
$dbname = 'my_database';
$user = 'postgres';
$password = 'khodor2002';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $form_type = $_POST['form_type'] ?? '';

        switch ($form_type) {
            case 'general_inquiry':
                $email = $_POST['email'] ?? ''; // Ensure email is set
                $sql = "INSERT INTO general_inquiry (full_name, email, inquiry_type, message, consent) 
                        VALUES (:full_name, :email, :inquiry_type, :message, :consent)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':full_name' => $_POST['full-name'],
                    ':email' => $_POST['email'],
                    ':inquiry_type' => $_POST['inquiry-type'],
                    ':message' => $_POST['message'],
                    ':consent' => isset($_POST['consent']) ? true : false
                ]);

                // Send confirmation email
                if (!empty($email)) {
                    sendEmail($email, "Thank You for Contacting Caravansary Capital", "
                    <style>
                        p {
                            margin-bottom: 15px; /* Adjust this value for more or less space */
                        }
                    </style>
                    <p>Dear {$_POST['full-name']},</p>
                    <p>Thank you for reaching out to Caravansary Capital. We have received your inquiry and appreciate your interest in our mission to bridge trade and investment between West Africa and Turkey.</p>
                    <p>Our team is currently reviewing your message and will respond as soon as possible. If your request requires immediate attention, please feel free to reach us at <a href='mailto:Support@CaravansaryCapital.com'>Support@CaravansaryCapital.com</a>, or call us at [phone number].</p>
                    <p>In the meantime, we invite you to explore more about our work on our website: <a href='http://www.CaravansaryCapital.com'>www.CaravansaryCapital.com</a>.</p>
                    <p>We look forward to connecting with you soon.</p>
                    <p>Best regards,<br>Caravansary Capital</p>
                    ");
                }

                break;

            case 'investor_screening':
                // Process file upload
                $credentials = null;
                if (isset($_FILES['business-credentials']) && $_FILES['business-credentials']['error'] == 0) {
                    $file_tmp_name = $_FILES['business-credentials']['tmp_name'];
                    $file_name = $_FILES['business-credentials']['name'];
                    $file_size = $_FILES['business-credentials']['size'];
                    $file_type = $_FILES['business-credentials']['type'];

                    // Set the absolute path to the uploads directory inside your project
                    $upload_dir = '/home/khodor/Caravansary Website/Caravansary-Website/uploads/business_credentials/';
                    $file_path = $upload_dir . basename($file_name);

                    // Create the directory if it doesn't exist
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true); // Create directory with permissions
                    }

                    // Validate file size and type
                    if ($file_size <= 5000000 && in_array($file_type, ['application/pdf', 'image/jpeg', 'image/png'])) {
                        // Attempt to move the uploaded file
                        if (move_uploaded_file($file_tmp_name, $file_path)) {
                            // File uploaded successfully, store file path
                            $credentials = $file_path;
                        } else {
                            // Error while moving file
                            echo "Error moving file.";
                            $credentials = null;
                        }
                    } else {
                        // Invalid file type or size
                        echo "Invalid file type or size.";
                        $credentials = null;
                    }
                } else {
                    // No file uploaded or an error occurred
                    echo "No file uploaded or error occurred.";
                    $credentials = null;
                }

                // Prepare the SQL query for investor_screening form
                $email = $_POST['email'] ?? ''; // Ensure email is set
                $sql = "INSERT INTO investor_screening (full_name, company, email, experience, investment_range, reason, hear_about_us, credentials, consent) 
                        VALUES (:full_name, :company, :email, :experience, :investment_range, :reason, :hear_about_us, :credentials, :consent)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':full_name' => $_POST['full-name'],
                    ':company' => $_POST['company'],
                    ':email' => $_POST['email'],
                    ':experience' => $_POST['experience'],
                    ':investment_range' => $_POST['investment-range'],
                    ':reason' => $_POST['reason'],
                    ':hear_about_us' => $_POST['hear'],
                    ':credentials' => $credentials,
                    ':consent' => isset($_POST['confidentiality']) ? true : false
                ]);

                // Send confirmation email
                if (!empty($email)) {
                    sendEmail($email, "Thank You for Your Interest in Caravansary Capital", "
                    <style>
                        p {
                            margin-bottom: 15px; /* Adjust this value for more or less space */
                        }
                    </style>
                    <p>Dear {$_POST['full-name']},</p>
                    <p>Thank you for submitting your Investor Screening Form. We appreciate your interest in Caravansary Capital and our mission to create strategic trade and investment opportunities between West Africa and Turkey.</p>
                    <p>Our team is currently reviewing your submission to assess potential alignment with our vision and investment opportunities. If your profile matches our current investment criteria, we will reach out within 5-7 business days to schedule an introductory discussion.</p>
                    <p>In the meantime, please find attached a brief overview of Caravansary Capital and our key focus areas. Should you have any questions, feel free to reach out to us at <a href='mailto:Invest@CaravansaryCapital.com'>Invest@CaravansaryCapital.com</a>.</p>
                    <p>We look forward to the possibility of working together.<br></p>
                    Best regards,<br>Maher K. Salma<br>Caravansary Capital<br>
                    <a href='http://www.CaravansaryCapital.com'>www.CaravansaryCapital.com</a> | <a href='mailto:Invest@CaravansaryCapital.com'>Invest@CaravansaryCapital.com</a>"
                    );
                }
                break;

            case 'strategic_partnership':
                $email = $_POST['email'] ?? ''; // Ensure email is set
                $sql = "INSERT INTO strategic_partnership (full_name, company, email, sector, revenue, interest, goals, engagement, consent) 
                        VALUES (:full_name, :company, :email, :sector, :revenue, :interest, :goals, :engagement, :consent)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':full_name' => $_POST['full-name'],
                    ':company' => $_POST['company'],
                    ':email' => $_POST['email'],
                    ':sector' => $_POST['sector'],
                    ':revenue' => $_POST['revenue'],
                    ':interest' => $_POST['interest'],
                    ':goals' => $_POST['goals'],
                    ':engagement' => $_POST['engagement'],
                    ':consent' => isset($_POST['confidentiality']) ? true : false
                ]);

                // Send confirmation email
                if (!empty($email)) {
                    sendEmail($email, "Thank You for Your Interest in Partnering with Caravansary Capital", "
                    <style>
                        p {
                            margin-bottom: 15px; /* Adjust this value for more or less space */
                        }
                    </style>
                    <p>Dear {$_POST['full-name']},</p>
                    <p>Thank you for submitting your details through our Strategic Partner Form. We appreciate your interest in collaborating with Caravansary Capital to drive trade and investment opportunities between West Africa and Turkey.</p>
                    <p>Our team is currently reviewing your submission to assess potential synergies. If your profile aligns with our strategic initiatives, we will reach out within 5-7 business days to explore next steps.</p>
                    <p>Should you have any urgent questions, please feel free to contact us at <a href='mailto:Partnership@CaravansaryCapital.com'>Partnership@CaravansaryCapital.com</a> or visit our website: <a href='http://www.CaravansaryCapital.com'>www.CaravansaryCapital.com</a>.</p>
                    <p>We look forward to the possibility of working together.</p>
                    <p>Best regards,<br>Caravansary Capital</p>
                    ");
                }
                break;

            case 'media_press':
                $email = $_POST['email'] ?? ''; // Ensure email is set
                $sql = "INSERT INTO media_press (full_name, email, media_outlet, inquiry, deadline) 
                        VALUES (:full_name, :email :media_outlet, :inquiry, :deadline)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':full_name' => $_POST['full-name'],
                    ':email' => $_POST['email'],
                    ':media_outlet' => $_POST['media-outlet'],
                    ':inquiry' => $_POST['inquiry'],
                    ':deadline' => $_POST['deadline']
                ]);

                // Send confirmation email
                if (!empty($email)) {
                    sendEmail($email, "Thank You for Your Media &amp; Press Inquiry", "
                    <style>
                        p {
                            margin-bottom: 15px; /* Adjust this value for more or less space */
                        }
                    </style>
                    <p>Dear {$_POST['full-name']},</p>
                    <p>Thank you for reaching out to Caravansary Capital. We have received your media and press inquiry and appreciate your interest in our mission to bridge trade and investment between West Africa and Turkey.</p>
                    <p>Our communications team is reviewing your request and will get back to you within 1-2 business days. If your inquiry is time-sensitive, please feel free to reach us directly at <a href='mailto:Press@CaravansaryCapital.com'>Press@CaravansaryCapital.com</a> or call [phone number].</p>
                    <p>For general information about Caravansary Capital, you can visit our website: <a href='http://www.CaravansaryCapital.com'>www.CaravansaryCapital.com</a>.</p>
                    <p>We look forward to connecting with you soon.</p>
                    <p>Best regards,<br>Caravansary Capital</p>
                    ");
                }
                break;

            default:
                // This will be triggered if no matching form_type is found
                echo "Invalid form submission: form_type = " . $form_type;
                echo "<pre>";
                print_r($_POST); // Displays all the form data
                echo "</pre>";
                exit; // Stops further execution after printing
        }

        echo "Form submitted successfully!";

    }

    echo "Connected to PostgreSQL successfully";
} catch (PDOException $e) {
    die("connection failed: " . $e->getMessage());
}
?>