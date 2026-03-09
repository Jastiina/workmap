<?php
session_start();
require_once 'connection.php';

// Get employee ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch employee data
$sql = "SELECT user_id as id, full_name as name, role, work_start, work_end,
        TIMESTAMPDIFF(HOUR, work_start, work_end) as hours,
        40 as capacity
        FROM user WHERE user_id = $id";
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
    $project_id = (int)$_POST['project_id'];
    $user_role = mysqli_real_escape_string($conn, $_POST['user_role']);
    $work_start = mysqli_real_escape_string($conn, $_POST['work_start']);
    $work_end = mysqli_real_escape_string($conn, $_POST['work_end']);
    
    // Check if assignment exists
    $check = mysqli_query($conn, "SELECT * FROM project_user WHERE project_id = $project_id AND user_id = $id");
    
    if (mysqli_num_rows($check) > 0) {
        // Update existing assignment
        $sql = "UPDATE project_user SET 
                user_role = '$user_role',
                user_work_start = '$work_start',
                user_work_end = '$work_end'
                WHERE project_id = $project_id AND user_id = $id";
    } else {
        // Insert new assignment
        $sql = "INSERT INTO project_user (project_id, user_id, user_role, user_work_start, user_work_end) 
                VALUES ($project_id, $id, '$user_role', '$work_start', '$work_end')";
    }
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = 'Employee assigned to project successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
    
    header('Location: assignemp.php');
    exit;
}

// Get all projects for dropdown
$projects = mysqli_query($conn, "SELECT project_id, project_name FROM project ORDER BY project_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign to Project</title>
    <!-- Add this after the title and before the CSS link -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">   
 <link rel="stylesheet" href="assign.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Assign Employee to Project</h1>
            <a href="assignemp.php" class="back-btn">← Cancel</a>
        </div>
        
        <div class="form-container">
            <div style="background: var(--ivory); padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--sage);">
                <h3 style="color: var(--deep-green); margin-bottom: 10px;"><?php echo htmlspecialchars($employee['name']); ?></h3>
                <p><strong>Role:</strong> <?php echo ucfirst($employee['role']); ?></p>
                <p><strong>Work Hours:</strong> <?php echo $employee['work_start'] ? date('h:i A', strtotime($employee['work_start'])) . ' - ' . date('h:i A', strtotime($employee['work_end'])) : 'Not set'; ?></p>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="project_id">Select Project *</label>
                    <select id="project_id" name="project_id" required>
                        <option value="">-- Choose a project --</option>
                        <?php while ($project = mysqli_fetch_assoc($projects)): ?>
                            <option value="<?php echo $project['project_id']; ?>">
                                <?php echo htmlspecialchars($project['project_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="user_role">Role in Project *</label>
                    <select id="user_role" name="user_role" required>
                        <option value="Employee">Employee</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Viewer">Viewer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="work_start">Work Start Time (in project)</label>
                    <input type="time" id="work_start" name="work_start" value="<?php echo $employee['work_start']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="work_end">Work End Time (in project)</label>
                    <input type="time" id="work_end" name="work_end" value="<?php echo $employee['work_end']; ?>">
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="submit-btn">Assign to Project</button>
                    <a href="assignemp.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>