<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin · Projects</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="AdminDash.css" />
</head>
<body>

<header class="header">
    <div class="header-content">
        <div class="header-flex-row">
            <h1>Projects</h1>
            
            <nav class="navbar">
                <ul class="nav-links">
                    <li><a href="AdminDash.php" class="active">Dashboard</a></li>
                    <li><a href="CreateProject.html">Create Projects</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropbtn">Team <i class="material-icons">expand_more</i></a>
                        <div class="dropdown-content">
                            <a href="EmployeeTable.php">View Employee</a>
                            <a href="EmployeeTable.php">Remove Employee</a>
                            <a href="CreateEmployee.html">Add Employee</a>
                        </div>
                    </li>
                </ul>

                <a href="logout.php" class="logout-pill">
                    <span>Logout</span>
                    <i class="material-icons">north_east</i>
                </a>
            </nav>
        </div>
        <p class="greeting">Welcome back, Admin</p>
    </div>

</header>

<main class="main-content">

    <section class="projects-section">
        <div class="section-header">
            <h2>All Projects</h2>
            <div class="sort-container">
                <label for="sort-select">Sort by:</label>
                <select id="sort-select" class="sort-select">
                    <option value="newest">Newest first</option>
                    <option value="oldest">Oldest first</option>
                    <option value="name-asc">Name (A–Z)</option>
                    <option value="name-desc">Name (Z–A)</option>
                    <option value="status">Status</option>
                </select>
            </div>
        </div>

        <div class="projects-grid">
            <?php
            require "connection.php";

            $data = mysqli_query($conn, "SELECT * FROM project");

            if (mysqli_num_rows($data) > 0) {
                while ($row = mysqli_fetch_assoc($data)) {
                    $p_id = $row['project_id'];
                    ?>
                    <a href="Detail/ProjectDetailList.php?id=<?php echo $p_id; ?>" class="project-card">
                        <div class="project-info">
                            <h3><?php echo htmlspecialchars($row['project_name']); ?></h3>
                            <p class="project-meta">Created • <?php echo htmlspecialchars($row['project_work_start']); ?></p>
                        </div>
                        <div class="project-status">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </div>
                    </a>
                    <?php
                }
            } else {
                echo '<p style="text-align:center; color:#888;">No projects found yet. Add some!</p>';
            }
            ?>
        </div>
    </section>

</main>

<!-- Floating Action Buttons -->
<div class="fab-container">
    <a href="CreateProject.html" class="fab fab-primary" title="New Project">
        <span class="material-icons">add_box</span>
    </a>
    <a href="CreateEmployee.html" class="fab fab-secondary" title="Add Team Member">
        <span class="material-icons">person_add</span>
    </a>
</div>

</body>
</html>