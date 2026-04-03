/**
 * CRUD User Manager — Frontend JavaScript
 * Handles all CRUD operations via AJAX calls
 */

const API_URL = 'api.php';

// ─── State ──────────────────────────────────────────────
let editingUserId = null;

// ─── DOM Elements ───────────────────────────────────────
const userForm = document.getElementById('userForm');
const formTitle = document.getElementById('formTitle');
const formTitleIcon = document.getElementById('formTitleIcon');
const submitBtn = document.getElementById('submitBtn');
const cancelBtn = document.getElementById('cancelBtn');
const userIdField = document.getElementById('userId');
const nameField = document.getElementById('userName');
const emailField = document.getElementById('userEmail');
const phoneField = document.getElementById('userPhone');
const roleField = document.getElementById('userRole');
const tableBody = document.getElementById('tableBody');
const searchInput = document.getElementById('searchInput');
const toastContainer = document.getElementById('toastContainer');

// Stats
const statTotal = document.getElementById('statTotal');
const statAdmins = document.getElementById('statAdmins');
const statEditors = document.getElementById('statEditors');
const statViewers = document.getElementById('statViewers');

// Modal
const deleteModal = document.getElementById('deleteModal');
const deleteUserName = document.getElementById('deleteUserName');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const modalCloseBtn = document.getElementById('modalCloseBtn');

let pendingDeleteId = null;

// ─── Avatar Colors ──────────────────────────────────────
const avatarColors = [
    '#6366f1', '#8b5cf6', '#a855f7', '#ec4899', '#f43f5e',
    '#ef4444', '#f97316', '#f59e0b', '#10b981', '#14b8a6',
    '#06b6d4', '#3b82f6', '#2563eb', '#7c3aed', '#c026d3'
];

function getAvatarColor(name) {
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return avatarColors[Math.abs(hash) % avatarColors.length];
}

function getInitials(name) {
    return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
}

// ─── Toast Notification ─────────────────────────────────
function showToast(message, type = 'success') {
    const icons = { success: '✓', error: '✕', info: 'ℹ' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span>${icons[type] || '●'}</span> ${message}`;
    toastContainer.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

// ─── Fetch Users ────────────────────────────────────────
async function fetchUsers() {
    try {
        const response = await fetch(`${API_URL}?action=read`);
        const result = await response.json();

        if (result.success) {
            renderTable(result.data);
            updateStats(result.data);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Failed to fetch users. Is the server running?', 'error');
        console.error('Fetch error:', error);
    }
}

// ─── Render Table ───────────────────────────────────────
function renderTable(users) {
    const searchTerm = (searchInput.value || '').toLowerCase();

    const filtered = users.filter(u =>
        u.name.toLowerCase().includes(searchTerm) ||
        u.email.toLowerCase().includes(searchTerm) ||
        u.role.toLowerCase().includes(searchTerm)
    );

    if (filtered.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="5">
                    <div class="empty-state">
                        <div class="empty-icon">👤</div>
                        <h3>${searchTerm ? 'No matching users found' : 'No users yet'}</h3>
                        <p>${searchTerm ? 'Try a different search term' : 'Add your first user using the form'}</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    tableBody.innerHTML = filtered.map((user, i) => `
        <tr style="animation-delay: ${i * 50}ms">
            <td>
                <div class="user-info">
                    <div class="user-avatar" style="background: ${getAvatarColor(user.name)}">
                        ${getInitials(user.name)}
                    </div>
                    <div>
                        <div class="user-name">${escapeHtml(user.name)}</div>
                        <div class="user-email">${escapeHtml(user.email)}</div>
                    </div>
                </div>
            </td>
            <td>${escapeHtml(user.phone || '—')}</td>
            <td>
                <span class="role-badge ${user.role.toLowerCase()}">
                    ${user.role === 'Admin' ? '🛡️' : user.role === 'Editor' ? '✏️' : '👁️'}
                    ${user.role}
                </span>
            </td>
            <td><span class="date-text">${formatDate(user.created_at)}</span></td>
            <td>
                <div class="action-btns">
                    <button class="btn btn-edit btn-sm" onclick="editUser(${user.id})" title="Edit">✏️ Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="confirmDelete(${user.id}, '${escapeHtml(user.name)}')" title="Delete">🗑️</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ─── Update Stats ───────────────────────────────────────
function updateStats(users) {
    statTotal.textContent = users.length;
    statAdmins.textContent = users.filter(u => u.role === 'Admin').length;
    statEditors.textContent = users.filter(u => u.role === 'Editor').length;
    statViewers.textContent = users.filter(u => u.role === 'Viewer').length;
}

// ─── Form Submit (Create / Update) ──────────────────────
userForm.addEventListener('submit', async function (e) {
    e.preventDefault();

    // Client-side validation
    const name = nameField.value.trim();
    const email = emailField.value.trim();
    const phone = phoneField.value.trim();
    const role = roleField.value;

    if (name.length < 2) {
        showToast('Name must be at least 2 characters.', 'error');
        nameField.focus();
        return;
    }

    if (!isValidEmail(email)) {
        showToast('Please enter a valid email address.', 'error');
        emailField.focus();
        return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone', phone);
    formData.append('role', role);

    let action = 'create';
    if (editingUserId) {
        action = 'update';
        formData.append('id', editingUserId);
    }
    formData.append('action', action);

    // Disable button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner"></span> Saving...';

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            resetForm();
            fetchUsers();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = editingUserId
            ? '💾 Update User'
            : '➕ Add User';
    }
});

// ─── Edit User ──────────────────────────────────────────
async function editUser(id) {
    try {
        const response = await fetch(`${API_URL}?action=read_one&id=${id}`);
        const result = await response.json();

        if (result.success) {
            const user = result.data;
            editingUserId = user.id;
            userIdField.value = user.id;
            nameField.value = user.name;
            emailField.value = user.email;
            phoneField.value = user.phone || '';
            roleField.value = user.role;

            formTitle.textContent = 'Edit User';
            formTitleIcon.textContent = '✏️';
            submitBtn.innerHTML = '💾 Update User';
            cancelBtn.style.display = 'flex';

            // Scroll form into view on mobile
            document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Error loading user data.', 'error');
    }
}

// ─── Delete User ────────────────────────────────────────
function confirmDelete(id, name) {
    pendingDeleteId = id;
    deleteUserName.textContent = name;
    deleteModal.classList.add('active');
}

confirmDeleteBtn.addEventListener('click', async function () {
    if (!pendingDeleteId) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', pendingDeleteId);

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            fetchUsers();
            if (editingUserId === pendingDeleteId) {
                resetForm();
            }
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Network error during deletion.', 'error');
    } finally {
        closeModal();
    }
});

// ─── Modal Controls ─────────────────────────────────────
function closeModal() {
    deleteModal.classList.remove('active');
    pendingDeleteId = null;
}

cancelDeleteBtn.addEventListener('click', closeModal);
modalCloseBtn.addEventListener('click', closeModal);
deleteModal.addEventListener('click', function (e) {
    if (e.target === deleteModal) closeModal();
});

// ─── Reset Form ─────────────────────────────────────────
function resetForm() {
    editingUserId = null;
    userIdField.value = '';
    userForm.reset();
    formTitle.textContent = 'Add New User';
    formTitleIcon.textContent = '➕';
    submitBtn.innerHTML = '➕ Add User';
    cancelBtn.style.display = 'none';
}

cancelBtn.addEventListener('click', resetForm);

// ─── Search ─────────────────────────────────────────────
searchInput.addEventListener('input', fetchUsers);

// ─── Helpers ────────────────────────────────────────────
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateStr) {
    const d = new Date(dateStr);
    const options = { month: 'short', day: 'numeric', year: 'numeric' };
    return d.toLocaleDateString('en-US', options);
}

// ─── Keyboard Shortcuts ─────────────────────────────────
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        if (deleteModal.classList.contains('active')) {
            closeModal();
        } else if (editingUserId) {
            resetForm();
        }
    }
});

// ─── Initialize ─────────────────────────────────────────
fetchUsers();
