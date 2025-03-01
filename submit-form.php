<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
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
                $sql = "INSERT INTO investor_screening (full_name, company, experience, investment_range, reason, hear_about_us, credentials, consent) 
                        VALUES (:full_name, :company, :experience, :investment_range, :reason, :hear_about_us, :credentials, :consent)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':full_name' => $_POST['full-name'],
                    ':company' => $_POST['company'],
                    ':experience' => $_POST['experience'],
                    ':investment_range' => $_POST['investment-range'],
                    ':reason' => $_POST['reason'],
                    ':hear_about_us' => $_POST['hear'],
                    ':credentials' => $credentials,
                    ':consent' => isset($_POST['confidentiality']) ? true : false
                ]);
                break;

            case 'strategic_partnership':
                $sql = "INSERT INTO strategic_partnership (company, sector, revenue, interest, goals, engagement, consent) 
                        VALUES (:company, :sector, :revenue, :interest, :goals, :engagement, :consent)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':company' => $_POST['company'],
                    ':sector' => $_POST['sector'],
                    ':revenue' => $_POST['revenue'],
                    ':interest' => $_POST['interest'],
                    ':goals' => $_POST['goals'],
                    ':engagement' => $_POST['engagement'],
                    ':consent' => isset($_POST['confidentiality']) ? true : false
                ]);
                break;

            case 'media_press':
                $sql = "INSERT INTO media_press (full_name, media_outlet, inquiry, deadline) 
                        VALUES (:full_name, :media_outlet, :inquiry, :deadline)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':full_name' => $_POST['full-name'],
                    ':media_outlet' => $_POST['media-outlet'],
                    ':inquiry' => $_POST['inquiry'],
                    ':deadline' => $_POST['deadline']
                ]);
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