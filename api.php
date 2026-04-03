<?php
/**
 * API Handler for CRUD Operations
 * Handles Create, Read, Update, Delete with validation and security
 */

header('Content-Type: application/json');
require_once 'config.php';

// Get the action from the request
$action = $_REQUEST['action'] ?? '';

switch ($action) {

    // ─── CREATE ────────────────────────────────────────────
    case 'create':
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role  = trim($_POST['role'] ?? 'Viewer');

        // Validation
        $errors = [];
        if (empty($name) || strlen($name) < 2 || strlen($name) > 100) {
            $errors[] = 'Name must be between 2 and 100 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (!empty($phone) && !preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $phone)) {
            $errors[] = 'Please enter a valid phone number.';
        }
        if (!in_array($role, ['Admin', 'Editor', 'Viewer'])) {
            $errors[] = 'Invalid role selected.';
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            exit;
        }

        // Sanitize inputs
        $name  = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, role) VALUES (:name, :email, :phone, :role)");
            $stmt->execute([
                ':name'  => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':role'  => $role,
            ]);
            echo json_encode(['success' => true, 'message' => 'User created successfully!', 'id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['success' => false, 'message' => 'This email address is already registered.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error creating user: ' . $e->getMessage()]);
            }
        }
        break;

    // ─── READ ──────────────────────────────────────────────
    case 'read':
        try {
            $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
            $users = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $users]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error fetching users: ' . $e->getMessage()]);
        }
        break;

    // ─── READ ONE ──────────────────────────────────────────
    case 'read_one':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch();
            if ($user) {
                echo json_encode(['success' => true, 'data' => $user]);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;

    // ─── UPDATE ────────────────────────────────────────────
    case 'update':
        $id    = intval($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role  = trim($_POST['role'] ?? 'Viewer');

        // Validation
        $errors = [];
        if ($id <= 0) {
            $errors[] = 'Invalid user ID.';
        }
        if (empty($name) || strlen($name) < 2 || strlen($name) > 100) {
            $errors[] = 'Name must be between 2 and 100 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (!empty($phone) && !preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $phone)) {
            $errors[] = 'Please enter a valid phone number.';
        }
        if (!in_array($role, ['Admin', 'Editor', 'Viewer'])) {
            $errors[] = 'Invalid role selected.';
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            exit;
        }

        // Sanitize
        $name  = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');

        try {
            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, phone = :phone, role = :role WHERE id = :id");
            $stmt->execute([
                ':id'    => $id,
                ':name'  => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':role'  => $role,
            ]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'User updated successfully!']);
            } else {
                echo json_encode(['success' => true, 'message' => 'No changes were made.']);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['success' => false, 'message' => 'This email is already used by another user.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating user: ' . $e->getMessage()]);
            }
        }
        break;

    // ─── DELETE ────────────────────────────────────────────
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'User deleted successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()]);
        }
        break;

    // ─── DEFAULT ───────────────────────────────────────────
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
        break;
}
?>
