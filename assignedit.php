<?php
session_start();
require_once 'connection.php';

// Get employee ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch employee data
$sql = "SELECT user_id as id, full_name as name, email, phone, role, work_start, work_end FROM user WHERE user_id = $id";
$result = mysqli_query($conn, $sql);
$employee = mysqli_fetch_assoc($result);

// If employee not found, redirect
if (!$employee) {
    $_SESSION['message'] = 'Employee not found!';
    $_SESSION['message_type'] = 'error';
    header('Location: assignemp.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $work_start = mysqli_real_escape_string($conn, $_POST['work_start']);
    $work_end = mysqli_real_escape_string($conn, $_POST['work_end']);
    
    // Update database
    $sql = "UPDATE user SET 
            full_name = '$name',
            email = '$email',
            phone = '$phone',
            role = '$role',
            work_start = '$work_start',
            work_end = '$work_end'
            WHERE user_id = $id";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = 'Employee updated successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
    
    header('Location: assignemp.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <!-- Add this after the title and before the CSS link -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">  
  <link rel="stylesheet" href="assign.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit Employee</h1>
            <a href="assignemp.php" class="back-btn">← Cancel</a>
        </div>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($employee['phone']); ?>" required pattern="[0-9]{10}">
                </div>
                
                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" required>
                        <option value="Employee" <?php echo $employee['role'] == 'Employee' ? 'selected' : ''; ?>>Employee</option>
                        <option value="Supervisor" <?php echo $employee['role'] == 'Supervisor' ? 'selected' : ''; ?>>Supervisor</option>
                        <option value="Admin" <?php echo $employee['role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="work_start">Work Start Time</label>
                    <input type="time" id="work_start" name="work_start" value="<?php echo $employee['work_start']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="work_end">Work End Time</label>
                    <input type="time" id="work_end" name="work_end" value="<?php echo $employee['work_end']; ?>">
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="submit-btn">Update Employee</button>
                    <a href="assignemp.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>