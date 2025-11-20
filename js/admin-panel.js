/**
 * ============================================================================
 * js/admin-panel.js
 * ============================================================================
 * DESCRIPTION:
 * JavaScript functionality for the DABS admin panel.
 * Handles user management, log viewing, and email testing.
 *
 * AUTHOR: System
 * CREATED: 20/11/2025 (UK Time)
 * ============================================================================
 */

'use strict';

// ============================================================================
// User Management Functions
// ============================================================================

function loadUsers() {
    const container = document.getElementById('usersTable');
    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    
    fetch('ajax_admin.php?action=list_users')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayUsersTable(data.users);
            } else {
                container.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(err => {
            container.innerHTML = `<div class="alert alert-danger">Error loading users: ${err.message}</div>`;
        });
}

function displayUsersTable(users) {
    const container = document.getElementById('usersTable');
    
    if (!users || users.length === 0) {
        container.innerHTML = '<div class="alert alert-info">No users found.</div>';
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    users.forEach(user => {
        const roleBadge = getRoleBadge(user.role);
        const lastLogin = user.last_login ? formatUKDateTime(user.last_login) : 'Never';
        
        html += `
            <tr>
                <td>${user.id}</td>
                <td>${escapeHtml(user.name)}</td>
                <td>${escapeHtml(user.username)}</td>
                <td>${escapeHtml(user.email)}</td>
                <td>${roleBadge}</td>
                <td>${lastLogin}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="editUser(${user.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id}, '${escapeHtml(user.username)}')"
                            ${user.id === 1 ? 'disabled title="Cannot delete system admin"' : ''}>
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function getRoleBadge(role) {
    const badges = {
        admin: '<span class="badge bg-danger">Admin</span>',
        manager: '<span class="badge bg-warning">Manager</span>',
        user: '<span class="badge bg-info">User</span>'
    };
    return badges[role] || '<span class="badge bg-secondary">Unknown</span>';
}

function showAddUserModal() {
    document.getElementById('userModalTitle').textContent = 'Add New User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('userPassword').required = true;
    
    const modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
}

function editUser(userId) {
    fetch(`ajax_admin.php?action=get_user&id=${userId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('userModalTitle').textContent = 'Edit User';
                document.getElementById('userId').value = data.user.id;
                document.getElementById('userName').value = data.user.name;
                document.getElementById('userUsername').value = data.user.username;
                document.getElementById('userEmail').value = data.user.email;
                document.getElementById('userRole').value = data.user.role;
                document.getElementById('userPassword').required = false;
                document.getElementById('userPassword').value = '';
                
                const modal = new bootstrap.Modal(document.getElementById('userModal'));
                modal.show();
            } else {
                showNotification('Error loading user: ' + data.message, 'danger');
            }
        })
        .catch(err => {
            showNotification('Error: ' + err.message, 'danger');
        });
}

function saveUser() {
    const userId = document.getElementById('userId').value;
    const formData = new FormData(document.getElementById('userForm'));
    formData.append('action', userId ? 'update_user' : 'create_user');
    
    fetch('ajax_admin.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
                loadUsers();
            } else {
                showNotification('Error: ' + data.message, 'danger');
            }
        })
        .catch(err => {
            showNotification('Error: ' + err.message, 'danger');
        });
}

function deleteUser(userId, username) {
    if (userId === 1) {
        showNotification('Cannot delete system administrator account', 'warning');
        return;
    }
    
    if (!confirm(`Are you sure you want to delete user "${username}"?`)) {
        return;
    }
    
    fetch('ajax_admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete_user&id=${userId}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                loadUsers();
            } else {
                showNotification('Error: ' + data.message, 'danger');
            }
        })
        .catch(err => {
            showNotification('Error: ' + err.message, 'danger');
        });
}

// ============================================================================
// Log Viewer Functions
// ============================================================================

function loadLogFiles() {
    fetch('ajax_admin.php?action=list_logs')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('logFileSelect');
                select.innerHTML = '<option value="">-- Select a log file --</option>';
                
                data.logs.forEach(log => {
                    const option = document.createElement('option');
                    option.value = log.name;
                    option.textContent = `${log.name} (${log.size})`;
                    select.appendChild(option);
                });
            } else {
                showNotification('Error loading log files: ' + data.message, 'danger');
            }
        })
        .catch(err => {
            showNotification('Error: ' + err.message, 'danger');
        });
}

function loadLogContent() {
    const logFile = document.getElementById('logFileSelect').value;
    if (!logFile) {
        showNotification('Please select a log file', 'warning');
        return;
    }
    
    const container = document.getElementById('logContent');
    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    
    fetch(`ajax_admin.php?action=read_log&file=${encodeURIComponent(logFile)}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayLogContent(data.content, logFile);
            } else {
                container.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(err => {
            container.innerHTML = `<div class="alert alert-danger">Error: ${err.message}</div>`;
        });
}

function displayLogContent(content, filename) {
    const container = document.getElementById('logContent');
    
    if (!content || content.trim() === '') {
        container.innerHTML = '<div class="alert alert-info">Log file is empty.</div>';
        return;
    }
    
    const lines = content.split('\n');
    let html = `
        <div class="mb-2">
            <strong>File:</strong> ${escapeHtml(filename)} | 
            <strong>Lines:</strong> ${lines.length}
        </div>
        <div class="log-viewer">
    `;
    
    lines.forEach((line, index) => {
        if (line.trim()) {
            html += `<div class="log-line">${escapeHtml(line)}</div>`;
        }
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// ============================================================================
// Email Configuration Functions
// ============================================================================

function loadEmailSettings() {
    fetch('ajax_admin.php?action=get_email_settings')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.settings) {
                populateEmailForm(data.settings);
            } else {
                showNotification('Failed to load email settings', 'warning');
            }
        })
        .catch(err => {
            showNotification('Error loading email settings: ' + err.message, 'danger');
        });
}

function populateEmailForm(settings) {
    // SMTP settings
    document.getElementById('smtpEnabled').checked = Boolean(settings.smtp_enabled);
    document.getElementById('smtpHost').value = settings.smtp_host || '';
    document.getElementById('smtpPort').value = settings.smtp_port || 587;
    document.getElementById('smtpEncryption').value = settings.smtp_encryption || 'tls';
    document.getElementById('smtpAuth').checked = Boolean(settings.smtp_auth);
    document.getElementById('smtpUsername').value = settings.smtp_username || '';
    // Don't populate password for security
    document.getElementById('smtpPassword').value = '';
    document.getElementById('smtpPassword').placeholder = 'Leave blank to keep existing';
    
    // From settings
    document.getElementById('emailFrom').value = settings.from_email || 'noreply@example.com';
    document.getElementById('emailFromName').value = settings.from_name || 'DABS System';
    
    // Toggle SMTP section visibility
    toggleSmtpSection();
}

function toggleSmtpSection() {
    const smtpEnabled = document.getElementById('smtpEnabled').checked;
    const smtpSettings = document.getElementById('smtpSettings');
    
    if (smtpEnabled) {
        smtpSettings.style.display = 'block';
    } else {
        smtpSettings.style.display = 'none';
    }
    
    toggleSmtpAuth();
}

function toggleSmtpAuth() {
    const smtpAuth = document.getElementById('smtpAuth').checked;
    const smtpAuthSection = document.getElementById('smtpAuthSection');
    
    if (smtpAuth) {
        smtpAuthSection.style.display = 'flex';
    } else {
        smtpAuthSection.style.display = 'none';
    }
}

function saveEmailSettings() {
    const form = document.getElementById('emailConfigForm');
    const formData = new FormData(form);
    formData.append('action', 'save_email_settings');
    
    // Handle checkboxes explicitly
    if (document.getElementById('smtpEnabled').checked) {
        formData.set('smtp_enabled', '1');
    }
    if (document.getElementById('smtpAuth').checked) {
        formData.set('smtp_auth', '1');
    }
    
    const statusDiv = document.getElementById('emailConfigStatus');
    statusDiv.innerHTML = '<div class="alert alert-info">Saving settings...</div>';
    
    fetch('ajax_admin.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                statusDiv.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                // Reload settings
                setTimeout(() => {
                    loadEmailSettings();
                    statusDiv.innerHTML = '';
                }, 3000);
            } else {
                statusDiv.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
            }
        })
        .catch(err => {
            statusDiv.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-times-circle me-2"></i>Error: ${err.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        });
}

function testEmail() {
    const emailAddress = document.getElementById('testEmailAddress').value;
    if (!emailAddress) {
        showNotification('Please enter an email address', 'warning');
        return;
    }
    
    const resultDiv = document.getElementById('emailTestResult');
    resultDiv.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary"></div><div>Sending test email...</div></div>';
    
    const formData = new FormData();
    formData.append('action', 'test_email');
    formData.append('email', emailAddress);
    
    fetch('ajax_admin.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success test-result">
                        <h6><i class="fas fa-check-circle me-2"></i>Test Email Sent Successfully</h6>
                        <p class="mb-0">${data.message}</p>
                        <small class="d-block mt-2">Check ${emailAddress} inbox and spam folder</small>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger test-result">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Test Email Failed</h6>
                        <p class="mb-0">${data.message}</p>
                        ${data.debug ? `<pre class="mt-2 mb-0"><code>${escapeHtml(data.debug)}</code></pre>` : ''}
                    </div>
                `;
            }
        })
        .catch(err => {
            resultDiv.innerHTML = `
                <div class="alert alert-danger test-result">
                    <h6><i class="fas fa-times-circle me-2"></i>Error</h6>
                    <p class="mb-0">${err.message}</p>
                </div>
            `;
        });
}

// ============================================================================
// Email Testing Functions
// ============================================================================

function testEmail(event) {
    event.preventDefault();
    
    const emailAddress = document.getElementById('testEmailAddress').value;
    if (!emailAddress) {
        showNotification('Please enter an email address', 'warning');
        return;
    }
    
    const resultDiv = document.getElementById('emailTestResult');
    resultDiv.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary"></div><div>Sending test email...</div></div>';
    
    const formData = new FormData();
    formData.append('action', 'test_email');
    formData.append('email', emailAddress);
    formData.append('from', document.getElementById('emailFrom').value);
    formData.append('from_name', document.getElementById('emailFromName').value);
    
    fetch('ajax_admin.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success test-result">
                        <h6><i class="fas fa-check-circle me-2"></i>Test Email Sent Successfully</h6>
                        <p class="mb-0">${data.message}</p>
                        <small class="d-block mt-2">Check ${emailAddress} inbox and spam folder</small>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger test-result">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Test Email Failed</h6>
                        <p class="mb-0">${data.message}</p>
                        ${data.debug ? `<pre class="mt-2 mb-0"><code>${escapeHtml(data.debug)}</code></pre>` : ''}
                    </div>
                `;
            }
        })
        .catch(err => {
            resultDiv.innerHTML = `
                <div class="alert alert-danger test-result">
                    <h6><i class="fas fa-times-circle me-2"></i>Error</h6>
                    <p class="mb-0">${err.message}</p>
                </div>
            `;
        });
}

// ============================================================================
// Utility Functions
// ============================================================================

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatUKDateTime(datetime) {
    if (!datetime) return 'Never';
    const date = new Date(datetime);
    return date.toLocaleString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Europe/London'
    });
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// ============================================================================
// Event Listeners
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // Load users on page load
    loadUsers();
    
    // Load log files
    loadLogFiles();
    
    // Load email settings
    loadEmailSettings();
    
    // User management event listeners
    document.getElementById('addUserBtn').addEventListener('click', showAddUserModal);
    document.getElementById('saveUserBtn').addEventListener('click', saveUser);
    
    // Log viewer event listeners
    document.getElementById('loadLogBtn').addEventListener('click', loadLogContent);
    document.getElementById('refreshLogsBtn').addEventListener('click', loadLogFiles);
    
    // Email configuration event listeners
    document.getElementById('smtpEnabled').addEventListener('change', toggleSmtpSection);
    document.getElementById('smtpAuth').addEventListener('change', toggleSmtpAuth);
    document.getElementById('saveEmailConfigBtn').addEventListener('click', saveEmailSettings);
    document.getElementById('sendTestEmailBtn').addEventListener('click', testEmail);
});
