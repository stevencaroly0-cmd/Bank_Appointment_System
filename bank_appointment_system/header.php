<?php
require_once 'config.php';
require_login();
$pageTitle = $pageTitle ?? 'Dashboard';
$active = $active ?? '';
$flashData = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?> | Bank Appointment System</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
<style>.content-wrapper{min-height:calc(100vh - 57px)} .required:after{content:" *";color:#dc3545}</style>
</head><body class="hold-transition sidebar-mini layout-fixed"><div class="wrapper">
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
<ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li><li class="nav-item d-none d-sm-inline-block"><span class="nav-link">Bank Appointment Booking System</span></li></ul>
<ul class="navbar-nav ml-auto"><li class="nav-item"><span class="nav-link"><?= e($_SESSION['full_name'] ?? '') ?> (<?= e($_SESSION['role'] ?? '') ?>)</span></li><li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li></ul>
</nav>
<aside class="main-sidebar sidebar-dark-primary elevation-4"><a href="index.php" class="brand-link"><span class="brand-text font-weight-light ml-2">Bank Appointments</span></a>
<div class="sidebar"><nav class="mt-2"><ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
<li class="nav-item"><a href="index.php" class="nav-link <?= $active==='dashboard'?'active':'' ?>"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
<li class="nav-item"><a href="appointments.php" class="nav-link <?= $active==='appointments'?'active':'' ?>"><i class="nav-icon far fa-calendar-check"></i><p>Appointments</p></a></li>
<li class="nav-item"><a href="customers.php" class="nav-link <?= $active==='customers'?'active':'' ?>"><i class="nav-icon fas fa-users"></i><p>Customers</p></a></li>
<li class="nav-item"><a href="services.php" class="nav-link <?= $active==='services'?'active':'' ?>"><i class="nav-icon fas fa-concierge-bell"></i><p>Services</p></a></li>
<li class="nav-item"><a href="staff.php" class="nav-link <?= $active==='staff'?'active':'' ?>"><i class="nav-icon fas fa-user-tie"></i><p>Staff</p></a></li>
<?php if (($_SESSION['role'] ?? '') === 'Administrator'): ?><li class="nav-item"><a href="users.php" class="nav-link <?= $active==='users'?'active':'' ?>"><i class="nav-icon fas fa-user-shield"></i><p>User Management</p></a></li><?php endif; ?>
</ul></nav></div></aside>
<div class="content-wrapper"><section class="content-header"><div class="container-fluid"><h1><?= e($pageTitle) ?></h1></div></section><section class="content"><div class="container-fluid">
<?php if ($flashData): ?><div class="alert alert-<?= e($flashData['type']) ?> alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?= e($flashData['message']) ?></div><?php endif; ?>
