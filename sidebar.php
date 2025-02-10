<div class="sidebar">
    <h2>Menu</h2>
    <ul>
        <!-- Stock Dropdown -->
        <li>
            <button class="dropdown-button">
                Stock ▼
            </button>
            <ul class="dropdown-content">
                <li><a href="pc.php">PC</a></li>
                <li><a href="peripheriques.php">Périphériques</a></li>
                <li><a href="imprimantes.php">Imprimantes</a></li>
                <li><a href="telephones.php">Téléphones</a></li>
            </ul>
        </li>

        <!-- Assistance Dropdown -->
        <li>
            <button class="dropdown-button">
                Assistance ▼
            </button>
            <ul class="dropdown-content">
                <li><a href="assistance.php">Tickets</a></li>
            </ul>
        </li>
        
        <!-- Alert Dropdown -->
        <li>
            <button class="dropdown-button">
                Alert ▼
            </button>
            <ul class="dropdown-content">
                <li><a href="view_alerts.php">View Alerts</a></li>
            </ul>
        </li>

        <!-- Admin Dropdown -->
        <li>
            <button class="dropdown-button">
                Admin ▼
            </button>
            <ul class="dropdown-content">
                <!-- Show this link only if the user is an admin -->
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <li><a href="manage_users.php">Gérer les utilisateurs</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                <li><a href="reset_password.php">Réinitialiser mot de passe utilisateurs</a></li> <!-- Lien vers la page de réinitialisation -->
                <?php endif; ?>
                <li><a href="change_password.php">Edit Password</a></li>
                <a href="logout.php">Déconnexion </a>
            </ul>
        </li>

        
    </ul>
</div>
