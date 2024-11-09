<link rel="stylesheet" href="css/header.css">
<header>
    <nav>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'admin'): ?>
            <a href="admin.php">Home</a>
            <a href="view_languages.php">View Programming Languages</a> 
            <a href="add_language.php" class="button">Add New Language</a> 
            <a href="admin_weights.php" class="button">Update weights</a> 
        <?php endif; ?>
        
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'team_lead'): ?>
            <a href="team_lead.php">Home</a>
            <a href="create_project.php" class="button">Create New Project</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'user'): ?>
            <a href="index.php">Home</a>
            <a href="all_projects.php" class="button">Projects</a>
            <a href="my_applications.php" class="button">My Applications</a> 
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <span>Hello, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
