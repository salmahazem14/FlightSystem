<?php
session_start(); // Start the session
// Check if the user is logged in as a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    // Redirect to login page if not logged in as company
    header("Location: login.php?type=company");
    exit();
}
$company_id = $_SESSION['user_id']; 

$host = 'localhost';
$dbname = 'travel';
$username = 'root';
$password = '';

$connect = mysqli_connect($host, $username, $password, $dbname);

if (!$connect) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flight_id'])) {
    $flight_id = (int)$_POST['flight_id'];

    $query = "DELETE FROM flights WHERE flight_id = $flight_id";
    
    if (mysqli_query($connect, $query)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Error deleting flight"]);
    }

    mysqli_close($connect);
    exit;
}

$flight_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$query = "SELECT * FROM flights WHERE flight_id = $flight_id";
$result = mysqli_query($connect, $query);



$query = "SELECT * FROM companies WHERE id = $company_id";
$result1 = mysqli_query($connect, $query);

if (!$result1) {
    die("Query failed: " . mysqli_error($connect));
}

$companyInfo = mysqli_fetch_assoc($result1);

if (!$result) {
    die("Query failed: " . mysqli_error($connect));
}

$flight = mysqli_fetch_assoc($result);

mysqli_close($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Details</title>
    <link rel="stylesheet" href="../CSS-File/styles.css">
</head>
<body>
  
    <div class="flight-details-container">
      
        <header class="flight-header">
            <img src="" alt="Company Logo" class="company-logo">
            <h1 class="company-name"><?php echo $companyInfo["company_name"]; ?></h1> 
        </header>

        <section class="flight-info">
            <h2>Flight Information</h2>
            <p><strong>ID:</strong> <?php echo htmlspecialchars($flight['flight_id']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($flight['flight_name']); ?></p>
            <p><strong>Itinerary:</strong> <?php echo htmlspecialchars($flight['departure'] . " - " . $flight['arrival']); ?></p>
        </section>

        <section class="passenger-list">
            <h3>Pending Passengers</h3>
            <ul id="pending-passengers">
            </ul>
        </section>

        <section class="passenger-list">
            <h3>Registered Passengers</h3>
            <ul id="registered-passengers">
            </ul>
        </section>

        <section class="cancel-flight">
            <button id="cancel-flight-btn" data-flight-id="<?php echo $flight['flight_id']; ?>">Cancel Flight and Refund Fees</button>
        </section>

        <div class="navigation-buttons">
            <button onclick="window.location.href='company-dashboard.php'">Back to Dashboard</button>
        </div>
    </div>

    <script src="../JS-File/script.js"></script>
    <script>
        document.getElementById("cancel-flight-btn").addEventListener("click", function() {
            const flightId = this.getAttribute("data-flight-id");

            if (confirm("Are you sure you want to cancel this flight? This action cannot be undone.")) {
             
                fetch("<?php echo $_SERVER['PHP_SELF']; ?>", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: new URLSearchParams({ flight_id: flightId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Flight cancelled successfully.");
                        window.location.href = "company-dashboard.php"; 
                    } else {
                        alert("Failed to cancel the flight. Please try again.");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("An error occurred. Please try again.");
                });
            }
        });
    </script> 
</body>
</html>
