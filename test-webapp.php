<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "birthday_wishnote";

// Establish local mysqli connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['FirstName'] ?? '';
    $lastName = $_POST['LastName'] ?? '';
    $govPosition = $_POST['GovPosition'] ?? '';
    $location = $_POST['Location'] ?? ''; // Captured from form, but intentionally left out of SQL query below

    /*
     * INTENTIONAL FLAW EXPLANATION:
     * The Location field is collected from the front-end form inputs and captured above,
     * but it is intentionally omitted from the INSERT INTO query and bind_param parameters 
     * to simulate a data-handling bug / schema mismatch against the main_corrupt_politicians table.
     */
    $sql = "INSERT INTO main_corrupt_politicians (FirstName, LastName, GovPosition) VALUES (?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sss", $firstName, $lastName, $govPosition);
        
        if ($stmt->execute()) {
            $successMessage = "Success! The wish note has been sent.";
        } else {
            $errorMessage = "Error executing query: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "Error preparing query: " . $conn->error;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kara David's Wish Note</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }
        h2 {
            color: #1a73e8;
            text-align: center;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button.submit-btn {
            background-color: #1a73e8;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        button.submit-btn:hover {
            background-color: #1557b0;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .modal-content h3 {
            margin-top: 0;
            color: #1a73e8;
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        .modal-actions button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-cancel {
            background-color: #ccc;
            color: #333;
        }
        .btn-confirm {
            background-color: #1a73e8;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Kara David's Wish Note</h2>

    <?php if (!empty($successMessage)): ?>
        <div class="alert-success"><?php echo $successMessage; ?></div>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <div class="alert-error"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <form id="wishForm" method="POST" action="">
        <div class="form-group">
            <label for="FirstName">Target's First Name</label>
            <input type="text" id="FirstName" name="FirstName" maxlength="30" required>
        </div>
        <div class="form-group">
            <label for="LastName">Target's Last Name</label>
            <input type="text" id="LastName" name="LastName" maxlength="30" required>
        </div>
        <div class="form-group">
            <label for="GovPosition">Target's Government Position</label>
            <input type="text" id="GovPosition" name="GovPosition" maxlength="20" required>
        </div>
        <div class="form-group">
            <label for="Location">Target's Location</label>
            <input type="text" id="Location" name="Location" maxlength="40">
        </div>
        <button type="submit" class="submit-btn">Send Wish Note</button>
    </form>
</div>

<!-- Custom Modal Overlay -->
<div id="verifyModal" class="modal-overlay">
    <div class="modal-content">
        <h3>Verify Target Data</h3>
        <p>Please review your entered details:</p>
        <p><strong>First Name:</strong> <span id="modalFirstName"></span></p>
        <p><strong>Last Name:</strong> <span id="modalLastName"></span></p>
        <p><strong>Government Position:</strong> <span id="modalGovPosition"></span></p>
        <p><strong>Location:</strong> <span id="modalLocation"></span></p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" id="modalCancel">Cancel</button>
            <button type="button" class="btn-confirm" id="modalConfirm">Confirm & Send</button>
        </div>
    </div>
</div>

<script>
    const form = document.getElementById('wishForm');
    const modal = document.getElementById('verifyModal');
    const modalCancel = document.getElementById('modalCancel');
    const modalConfirm = document.getElementById('modalConfirm');

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Intercept submission to show modal
        
        // Populate modal fields
        document.getElementById('modalFirstName').textContent = document.getElementById('FirstName').value;
        document.getElementById('modalLastName').textContent = document.getElementById('LastName').value;
        document.getElementById('modalGovPosition').textContent = document.getElementById('GovPosition').value;
        document.getElementById('modalLocation').textContent = document.getElementById('Location').value;

        // Display modal
        modal.style.display = 'flex';
    });

    modalCancel.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    modalConfirm.addEventListener('click', function() {
        modal.style.display = 'none';
        form.submit(); // Bypass interception and submit form data via POST
    });
</script>

</body>
</html>
