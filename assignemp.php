<?php
session_start();
require_once 'connection.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Delete from task_user first
        mysqli_query($conn, "DELETE FROM task_user WHERE user_id = $id");
        // Delete from project_user
        mysqli_query($conn, "DELETE FROM project_user WHERE user_id = $id");
        // Delete from user
        mysqli_query($conn, "DELETE FROM user WHERE user_id = $id");
        
        mysqli_commit($conn);
        $_SESSION['message'] = "Employee deleted successfully!";
        $_SESSION['message_type'] = 'success';
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['message'] = "Error deleting employee!";
        $_SESSION['message_type'] = 'error';
    }
    
    header('Location: assignemp.php');
    exit;
}

// Get filter values
$roleFilter = isset($_GET['role']) ? $_GET['role'] : 'all';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query with JOIN to get project count
$sql = "SELECT 
            u.user_id as id,
            u.full_name as name,
            u.role,
            u.work_start,
            u.work_end,
            COUNT(DISTINCT pu.project_id) as projects,
            -- Calculate hours between work_start and work_end
            TIMESTAMPDIFF(HOUR, u.work_start, u.work_end) as hours,
            -- Default capacity (40 hours per week)
            40 as capacity
        FROM user u
        LEFT JOIN project_user pu ON u.user_id = pu.user_id
        WHERE 1=1";

// Apply search filter
if (!empty($search)) {
    $sql .= " AND u.full_name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
}

// Apply role filter
if ($roleFilter !== 'all') {
    $sql .= " AND u.role = '" . mysqli_real_escape_string($conn, $roleFilter) . "'";
}

$sql .= " GROUP BY u.user_id";

// Apply sorting
switch ($sortBy) {
    case 'name':
        $sql .= " ORDER BY u.full_name ASC";
        break;
    case 'hours-low':
        $sql .= " ORDER BY hours ASC";
        break;
    case 'hours-high':
        $sql .= " ORDER BY hours DESC";
        break;
    case 'projects-low':
        $sql .= " ORDER BY projects ASC";
        break;
    case 'projects-high':
        $sql .= " ORDER BY projects DESC";
        break;
    default:
        $sql .= " ORDER BY u.full_name ASC";
}

$result = mysqli_query($conn, $sql);
$employees = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management - Employee Assignments</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assign.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Employee Assignments</h1>
            <a href="AdminDash.php" class="back-btn">← Back to Dashboard</a>
        </div>

        <!-- Display Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type']; ?>">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Controls -->
        <div class="controls">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Filter by Role:</label>
                        <select name="role" onchange="this.form.submit()">
                            <option value="all" <?php echo $roleFilter === 'all' ? 'selected' : ''; ?>>All Roles</option>
                            <option value="Admin" <?php echo $roleFilter === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="Supervisor" <?php echo $roleFilter === 'Supervisor' ? 'selected' : ''; ?>>Supervisor</option>
                            <option value="Employee" <?php echo $roleFilter === 'Employee' ? 'selected' : ''; ?>>Employee</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Sort By:</label>
                        <select name="sort" onchange="this.form.submit()">
                            <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="hours-low" <?php echo $sortBy === 'hours-low' ? 'selected' : ''; ?>>Working Hours (Low to High)</option>
                            <option value="hours-high" <?php echo $sortBy === 'hours-high' ? 'selected' : ''; ?>>Working Hours (High to Low)</option>
                            <option value="projects-low" <?php echo $sortBy === 'projects-low' ? 'selected' : ''; ?>>Projects (Low to High)</option>
                            <option value="projects-high" <?php echo $sortBy === 'projects-high' ? 'selected' : ''; ?>>Projects (High to Low)</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Search:</label>
                        <div style="display: flex; gap: 5px;">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search employees...">
                            <button type="submit" class="submit-btn" style="padding: 8px 16px;">Search</button>
                            <?php if ($search): ?>
                                <a href="assignemp.php" class="cancel-btn" style="padding: 8px 16px;">Clear</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
            
            <div class="action-row">
                <div>
                    <strong><?php echo count($employees); ?> employee(s) found</strong>
                </div>
                <a href="CreateEmployee.html" class="add-btn">+ New Employee</a>
            </div>
        </div>

        <!-- Employee Table -->
        <div class="table-container">
            <?php if (empty($employees)): ?>
                <div class="empty-state">
                    <h3>No employees found</h3>
                    <p><?php echo $search ? 'Try a different search term' : 'Add your first employee to get started'; ?></p>
                    <a href="CreateEmployee.php" class="add-btn">Add First Employee</a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Role</th>
                            <th>Working Projects</th>
                            <th>Working Hours</th>
                            <th>Capacity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): 
                            $hours = $employee['hours'] ?: 0;
                            $capacity = $employee['capacity'] ?: 40;
                            $percentage = $capacity > 0 ? round(($hours / $capacity) * 100) : 0;
                            $available = $capacity - $hours;
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($employee['name']); ?></strong><br>
                                <small>ID: <?php echo $employee['id']; ?></small>
                            </td>
                            <td>
                                <span class="status-badge <?php echo strtolower($employee['role']); ?>">
                                    <?php echo ucfirst($employee['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo $employee['projects'] ?: 0; ?>
                            </td>
                            <td>
                                <?php echo $hours; ?> hours
                                <?php if ($employee['work_start'] && $employee['work_end']): ?>
                                    <br><small><?php echo date('h:i A', strtotime($employee['work_start'])); ?> - <?php echo date('h:i A', strtotime($employee['work_end'])); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="capacity-info">
                                    <span><?php echo $percentage; ?>%</span>
                                    <div class="capacity-bar">
                                        <div class="capacity-fill" style="width: <?php echo min($percentage, 100); ?>%"></div>
                                    </div>
                                </div>
                                <small>
                                    <?php if ($available > 0): ?>
                                        <?php echo $available; ?>h available
                                    <?php else: ?>
                                        <span style="color: var(--danger);">Over capacity!</span>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <div class="action-links">
                                    <a href="assign.php?id=<?php echo $employee['id']; ?>" class="assign-link" title="Assign hours">Assign</a>
                                    <a href="assignedit.php?id=<?php echo $employee['id']; ?>" class="edit-link" title="Edit">Edit</a>
                                    <a href="?delete=<?php echo $employee['id']; ?>" class="delete-link" title="Delete" onclick="return confirm('Are you sure you want to delete this employee?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-hide message after 3 seconds
        setTimeout(function() {
            var msg = document.querySelector('.message');
            if (msg) {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(function() {
                    if (msg.parentNode) {
                        msg.remove();
                    }
                }, 500);
            }
        }, 3000);
    </script>
</body>
</html>