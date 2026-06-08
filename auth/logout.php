<?php
/*
 * @Module:      Logout Page
 * @Author:      BE-02 (Auth Engine)
 * @Date:        2026-05-24
 * @Description: Safely destroys the session and redirects to login.
 * @Ownership:   BE-02
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/session.php';

// Destroy the session using the unified session manager
destroySession();

// Redirect to login page
header("Location: " . BASE_URL . "/auth/login.php");
exit();
