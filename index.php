<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PHP MySQL CRUD User Manager — Create, Read, Update, Delete user records with validation and secure storage.">
    <title>User Manager — PHP &amp; MySQL CRUD</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>👥</text></svg>">
</head>
<body>

<!-- ═══════════ TOAST CONTAINER ═══════════ -->
<div id="toastContainer" class="toast-container"></div>

<!-- ═══════════ DELETE CONFIRM MODAL ═══════════ -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>⚠️ Confirm Deletion</h3>
            <button id="modalCloseBtn" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon">🗑️</div>
            <div class="confirm-text">
                <h3>Delete this user?</h3>
                <p>You are about to permanently delete <strong id="deleteUserName"></strong>. This action cannot be undone.</p>
            </div>
        </div>
        <div class="modal-footer">
            <button id="cancelDeleteBtn" class="btn btn-secondary">Cancel</button>
            <button id="confirmDeleteBtn" class="btn btn-danger">🗑️ Delete</button>
        </div>
    </div>
</div>

<div class="container">

    <!-- ═══════════ HEADER ═══════════ -->
    <header class="header">
        <div class="header-icon">👥</div>
        <h1>User Manager</h1>
        <p>Full CRUD Operations with PHP &amp; MySQL — Validated &amp; Secure</p>
        <div class="tech-badge">
            <span>PHP</span>
            <span>MySQL</span>
            <span>PDO</span>
            <span>AJAX</span>
        </div>
    </header>

    <!-- ═══════════ STATS BAR ═══════════ -->
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-icon total">📊</div>
            <div class="stat-info">
                <h3 id="statTotal">0</h3>
                <p>Total Users</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon admin">🛡️</div>
            <div class="stat-info">
                <h3 id="statAdmins">0</h3>
                <p>Admins</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon editor">✏️</div>
            <div class="stat-info">
                <h3 id="statEditors">0</h3>
                <p>Editors</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon viewer">👁️</div>
            <div class="stat-info">
                <h3 id="statViewers">0</h3>
                <p>Viewers</p>
            </div>
        </div>
    </div>

    <!-- ═══════════ MAIN LAYOUT ═══════════ -->
    <div class="main-layout">

        <!-- ──── FORM PANEL ──── -->
        <div class="card form-card">
            <div class="card-header">
                <h2><span class="icon" id="formTitleIcon">➕</span> <span id="formTitle">Add New User</span></h2>
            </div>
            <div class="card-body">
                <form id="userForm" autocomplete="off">
                    <input type="hidden" id="userId" name="id">

                    <div class="form-group">
                        <label for="userName">Full Name <span class="required">*</span></label>
                        <input type="text" id="userName" name="name" class="form-control"
                               placeholder="e.g. Nidhi Sharma" required minlength="2" maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="userEmail">Email Address <span class="required">*</span></label>
                        <input type="email" id="userEmail" name="email" class="form-control"
                               placeholder="e.g. nidhi@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="userPhone">Phone Number</label>
                        <input type="tel" id="userPhone" name="phone" class="form-control"
                               placeholder="e.g. +91 98765 43210">
                    </div>

                    <div class="form-group">
                        <label for="userRole">Role <span class="required">*</span></label>
                        <select id="userRole" name="role" class="form-control" required>
                            <option value="Viewer">👁️ Viewer</option>
                            <option value="Editor">✏️ Editor</option>
                            <option value="Admin">🛡️ Admin</option>
                        </select>
                    </div>

                    <div class="btn-group">
                        <button type="submit" id="submitBtn" class="btn btn-primary btn-full">
                            ➕ Add User
                        </button>
                        <button type="button" id="cancelBtn" class="btn btn-secondary" style="display: none; flex: 0 0 auto;">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ──── TABLE PANEL ──── -->
        <div class="card">
            <div class="card-header">
                <h2><span class="icon">📋</span> User Records</h2>
                <div class="search-wrapper">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" class="form-control"
                           placeholder="Search users..." style="width: 220px; padding-left: 2.2rem;">
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-icon">👤</div>
                                        <h3>No users yet</h3>
                                        <p>Add your first user using the form</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="script.js"></script>
</body>
</html>
