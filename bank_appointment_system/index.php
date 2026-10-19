<?php
require_once 'config.php'; require_login();
$pageTitle='Dashboard'; $active='dashboard';
$total=(int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$todayStmt=$pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()"); $todayStmt->execute(); $today=(int)$todayStmt->fetchColumn();
$completed=(int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status='Completed'")->fetchColumn();
$cancelled=(int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status='Cancelled'")->fetchColumn();
$stmt=$pdo->query("SELECT a.*, c.full_name customer_name, s.service_name, st.full_name staff_name
FROM appointments a JOIN customers c ON a.customer_id=c.customer_id JOIN services s ON a.service_id=s.service_id
JOIN staff st ON a.staff_id=st.staff_id WHERE a.appointment_date >= CURDATE() AND a.status='Scheduled'
ORDER BY a.appointment_date, a.start_time LIMIT 10");
$upcoming=$stmt->fetchAll();
require 'header.php';
?>
<div class="row">
<?php foreach ([['Total Appointments',$total,'fa-calendar','info'],["Today's Appointments",$today,'fa-calendar-day','primary'],['Completed',$completed,'fa-check-circle','success'],['Cancelled',$cancelled,'fa-times-circle','danger']] as $box): ?>
<div class="col-lg-3 col-6"><div class="small-box bg-<?= e($box[3]) ?>"><div class="inner"><h3><?= e($box[1]) ?></h3><p><?= e($box[0]) ?></p></div><div class="icon"><i class="fas <?= e($box[2]) ?>"></i></div><a href="appointments.php" class="small-box-footer">Manage appointments <i class="fas fa-arrow-circle-right"></i></a></div></div>
<?php endforeach; ?>
</div>
<div class="card"><div class="card-header"><h3 class="card-title">Upcoming Appointments</h3><div class="card-tools"><a class="btn btn-primary btn-sm" href="appointments.php?action=create"><i class="fas fa-plus"></i> New Appointment</a></div></div>
<div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap"><thead><tr><th>Customer</th><th>Service</th><th>Staff</th><th>Date</th><th>Time</th><th>Status</th></tr></thead><tbody>
<?php if (!$upcoming): ?><tr><td colspan="6" class="text-center">No upcoming appointments found.</td></tr><?php endif; ?>
<?php foreach($upcoming as $a): ?><tr><td><?=e($a['customer_name'])?></td><td><?=e($a['service_name'])?></td><td><?=e($a['staff_name'])?></td><td><?=e($a['appointment_date'])?></td><td><?=e(substr($a['start_time'],0,5))?>–<?=e(substr($a['end_time'],0,5))?></td><td><span class="badge badge-primary"><?=e($a['status'])?></span></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php require 'footer.php'; ?>
