<?php
require_once 'config.php';
require_login();
if (($_SESSION['role'] ?? '') !== 'Administrator') {
    http_response_code(403);
    exit('Access denied. Only an Administrator can manage user accounts.');
}
verify_csrf();
$pageTitle = 'User Management';
$active = 'users';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? '';
    if ($mode === 'create') {
        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        $allowedRoles = ['Administrator', 'Receptionist', 'Bank Officer'];

        if ($fullName === '' || $username === '' || $password === '') {
            $error = 'Please complete all required fields.';
        } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,8}$/', $username)) {
            $error = 'Username must be 3–8 characters and may contain letters, numbers, dots, underscores or hyphens.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must contain at least 8 characters.';
        } elseif (!in_array($role, $allowedRoles, true)) {
            $error = 'Please select a valid role.';
        } else {
            $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
            $check->execute([$username]);
            if ((int)$check->fetchColumn() > 0) {
                $error = 'That username already exists. Choose another username.';
            } else {
                $insert = $pdo->prepare('INSERT INTO users (full_name, username, password_hash, role) VALUES (?, ?, ?, ?)');
                $insert->execute([$fullName, $username, password_hash($password, PASSWORD_DEFAULT), $role]);
                flash('User account created successfully.');
                redirect('users.php');
            }
        }
    } elseif ($mode === 'delete') {
        $deleteId = (int)($_POST['user_id'] ?? 0);
        if ($deleteId === (int)($_SESSION['user_id'] ?? 0)) {
            $error = 'You cannot delete the account you are currently using.';
        } else {
            $find = $pdo->prepare('SELECT user_id, role FROM users WHERE user_id = ?');
            $find->execute([$deleteId]);
            $target = $find->fetch();
            if (!$target) {
                $error = 'User account was not found.';
            } elseif ($target['role'] === 'Administrator') {
                $adminCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Administrator'")->fetchColumn();
                if ($adminCount <= 1) {
                    $error = 'The last Administrator account cannot be deleted.';
                } else {
                    $del = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
                    $del->execute([$deleteId]);
                    flash('User account deleted.');
                    redirect('users.php');
                }
            } else {
                $del = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
                $del->execute([$deleteId]);
                flash('User account deleted.');
                redirect('users.php');
            }
        }
    }
}

$users = $pdo->query('SELECT user_id, full_name, username, role, created_at FROM users ORDER BY user_id DESC')->fetchAll();
require 'header.php';
?>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<div class="row">
  <div class="col-lg-5">
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title">Create User Account</h3>
     </div>
      <form method="post">
        <div class="card-body">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="mode" value="create">
          <div class="form-group">
            <label>Full Name *</label>
            <input class="form-control" name="full_name" maxlength="100" required value="<?= e($_POST['full_name'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Username *</label>
            <input class="form-control" name="username" minlength="3" maxlength="50" required value="<?= e($_POST['username'] ?? '') ?>">
            <small class="form-text text-muted">3–8 characters: letters, numbers, dot, underscore or hyphen.</small>
          </div>
          <div class="form-group">
            <label>Temporary Password *</label>
            <input class="form-control" type="password" name="password" minlength="8" required autocomplete="new-password">
            <small class="form-text text-muted">At least 8 characters. Give the password securely to the user.</small>
          </div>
          <div class="form-group">
            <label>Role *</label>
            <select class="form-control" name="role" required>
                <option value="">Select role</option>
                <?php foreach (['Administrator','Receptionist','Bank Officer'] as $role): ?>
                    <option value="<?= e($role) ?>" <?= (($_POST['role'] ?? '') === $role) ? 'selected' : '' ?>><?= e($role) ?></option>
                    <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-primary" type="submit"><i class="fas fa-user-plus"></i> Create Account</button>
        </div>
      </form>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">System User Accounts</h3>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-striped table-bordered mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
        <tbody>
       <?php foreach ($users as $u): ?>
        <tr>
            <td><?= e($u['user_id']) ?></td>
            <td><?= e($u['full_name']) ?></td>
            <td><?= e($u['username']) ?></td>
            <td>
                <span class="badge badge-<?= $u['role'] === 'Administrator' ? 'danger' : ($u['role'] === 'Receptionist' ?
                 'info' : 'primary') ?>"><?= e($u['role']) ?>
                </span>
            </td>
            <td><?= e($u['created_at']) ?></td>
            <td><?php if ((int)$u['user_id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                <form method="post" onsubmit="return confirm('Delete this user account?')">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="mode" value="delete">
                    <input type="hidden" name="user_id" value="<?= e($u['user_id']) ?>">
                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form><?php else: ?>
                    <span class="text-muted">Current user</span>
                    <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>

      <?php if (!$users): ?>
        <tr>
            <td colspan="6" class="text-center">No user accounts found.</td>
        </tr>
        <?php endif; ?>
        </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<div class="alert alert-info">
    <strong>Security note:</strong> 
    Passwords are stored as password hashes. Do not share passwords in public or leave default passwords in use.
</div>
<?php require 'footer.php'; ?>
