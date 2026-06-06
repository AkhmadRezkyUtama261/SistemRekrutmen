<?php
require 'C:/xampp/htdocs/recruitment-enterprise/config/database.php';
$pdo = Database::getConnection();
$hash = password_hash('password123', PASSWORD_DEFAULT);
$pdo->exec("UPDATE users SET password_hash='$hash' WHERE email='hr@tokopintar.id'");
echo "Password reset to: password123\n";
