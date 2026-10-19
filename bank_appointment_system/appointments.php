<?php
require_once 'config.php'; require_login(); verify_csrf();
$pageTitle='Appointments'; $active='appointments'; $action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $error='';
$customers=$pdo->query("SELECT customer_id,full_name FROM customers ORDER BY full_name")->fetchAll();
$services=$pdo->query("SELECT service_id,service_name,duration_minutes FROM services ORDER BY service_name")->fetchAll();
$staffList=$pdo->query("SELECT staff_id,full_name,position FROM staff ORDER BY full_name")->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
 $mode=$_POST['mode']??'';
 if($mode==='save'){
  $aid=(int)($_POST['appointment_id']??0);$cid=(int)($_POST['customer_id']??0);$sid=(int)($_POST['service_id']??0);$stid=(int)($_POST['staff_id']??0);
  $date=$_POST['appointment_date']??'';$start=$_POST['start_time']??'';$end=$_POST['end_time']??'';$status=$_POST['status']??'Scheduled';
  if(!$cid||!$sid||!$stid||$date===''||$start===''||$end==='')$error='Please complete all required fields.';
  elseif($end <= $start)$error='End time must be later than start time.';
  elseif(!in_array($status,['Scheduled','Completed','Cancelled'],true))$error='Invalid appointment status.';
  else {
   $check=$pdo->prepare("SELECT COUNT(*) FROM appointments WHERE staff_id=? AND appointment_date=? AND status='Scheduled' AND appointment_id<>? AND start_time < ? AND end_time > ?");
   $check->execute([$stid,$date,$aid,$end,$start]);
   if($status==='Scheduled' && (int)$check->fetchColumn()>0) $error='This staff member already has an appointment overlapping that time.';
   else {
    if($aid){$q=$pdo->prepare("UPDATE appointments SET customer_id=?,service_id=?,staff_id=?,appointment_date=?,start_time=?,end_time=?,status=? WHERE appointment_id=?");$q->execute([$cid,$sid,$stid,$date,$start,$end,$status,$aid]);flash('Appointment updated.');}
    else {$q=$pdo->prepare("INSERT INTO appointments(customer_id,service_id,staff_id,appointment_date,start_time,end_time,status) VALUES(?,?,?,?,?,?,?)");$q->execute([$cid,$sid,$stid,$date,$start,$end,$status]);flash('Appointment created.');}
    redirect('appointments.php');
   }
  }
  $action=$aid?'edit':'create';$id=$aid;
 }elseif($mode==='delete'){
  $q=$pdo->prepare("DELETE FROM appointments WHERE appointment_id=?");$q->execute([(int)$_POST['appointment_id']]);flash('Appointment deleted.');redirect('appointments.php');
 }elseif($mode==='set_status'){
  $new=$_POST['status']??'';
  if(in_array($new,['Scheduled','Completed','Cancelled'],true)){$q=$pdo->prepare("UPDATE appointments SET status=? WHERE appointment_id=?");$q->execute([$new,(int)$_POST['appointment_id']]);flash('Appointment status updated.');}
  redirect('appointments.php');
 }
}
$record=['appointment_id'=>0,'customer_id'=>'','service_id'=>'','staff_id'=>'','appointment_date'=>'','start_time'=>'09:00','end_time'=>'09:30','status'=>'Scheduled'];
if($action==='edit'&&$id){$q=$pdo->prepare("SELECT * FROM appointments WHERE appointment_id=?");$q->execute([$id]);$record=$q->fetch()?:$record;}
$search=trim($_GET['q']??'');$dateFilter=$_GET['date']??'';$statusFilter=$_GET['status']??'';
$sql="SELECT a.*,c.full_name customer_name,s.service_name,st.full_name staff_name FROM appointments a JOIN customers c ON a.customer_id=c.customer_id JOIN services s ON a.service_id=s.service_id JOIN staff st ON a.staff_id=st.staff_id WHERE (c.full_name LIKE ? OR s.service_name LIKE ? OR st.full_name LIKE ?)";
$params=['%'.$search.'%','%'.$search.'%','%'.$search.'%'];
if($dateFilter!==''){$sql.=" AND a.appointment_date=?";$params[]=$dateFilter;}
if(in_array($statusFilter,['Scheduled','Completed','Cancelled'],true)){$sql.=" AND a.status=?";$params[]=$statusFilter;}
$sql.=" ORDER BY a.appointment_date DESC,a.start_time DESC";$q=$pdo->prepare($sql);$q->execute($params);$rows=$q->fetchAll();
require 'header.php';
?>
<?php if($error):?>
    <div class="alert alert-danger"><?=e($error)?>
    </div>
    <?php endif;?>

<?php if(in_array($action,['create','edit'],true)):?>
<?php if(!$customers||!$services||!$staffList):?>
    <div class="alert alert-warning">Before creating an appointment, add at least one customer, one service, and one staff member.
    </div>
    <?php endif;?>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><?=$action==='edit'?'Edit Appointment':'New Appointment'?>
        </h3>
    </div>
    <form method="post">
        <div class="card-body">
            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
            <input type="hidden" name="mode" value="save">
            <input type="hidden" name="appointment_id" value="<?=e($record['appointment_id'])?>">
            <div class="form-group">
                <label>Customer *</label>
                <select class="form-control" name="customer_id" required>
                    <option value="">Select customer</option>
                    <?php foreach($customers as $c):?>
                        <option value="<?=$c['customer_id']?>" <?=((string)($_POST['customer_id']??$record['customer_id'])===(string)$c['customer_id'])?'selected':''?>><?=e($c['full_name'])?>
                        </option>
                        <?php endforeach;?>
                </select>
            </div>
            <div class="form-group">
                <label>Banking Service *</label>
                <select class="form-control" name="service_id" required>
                    <option value="">Select service</option>
                    <?php foreach($services as $s):?>
                        <option value="<?=$s['service_id']?>" <?=((string)($_POST['service_id']??$record['service_id'])===(string)$s['service_id'])?'selected':''?>><?=e($s['service_name'])?> (<?=e($s['duration_minutes'])?> min)
                        </option>
                        <?php endforeach;?>
                </select>
            </div>
            <div class="form-group">
                <label>Assigned Staff *</label>
                <select class="form-control" name="staff_id" required>
                    <option value="">Select staff member</option>
                    <?php foreach($staffList as $st):?>
                        <option value="<?=$st['staff_id']?>" <?=((string)($_POST['staff_id']??$record['staff_id'])===(string)$st['staff_id'])?'selected':''?>><?=e($st['full_name'])?> — <?=e($st['position'])?>
                        </option>
                        <?php endforeach;?>
                    </select>
            </div>
            <div class="form-group">
                <label>Appointment Date *</label>
                <input class="form-control" type="date" name="appointment_date" required value="<?=e($_POST['appointment_date']??$record['appointment_date'])?>">
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label>Start Time *</label>
                    <input class="form-control" type="time" name="start_time" required value="<?=e(isset($_POST['start_time'])?$_POST['start_time']:substr($record['start_time'],0,5))?>">
                </div>
                <div class="form-group col-md-6">
                    <label>End Time *</label>
                    <input class="form-control" type="time" name="end_time" required value="<?=e(isset($_POST['end_time'])?$_POST['end_time']:substr($record['end_time'],0,5))?>">
                </div>
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select class="form-control" name="status"><?php foreach(['Scheduled','Completed','Cancelled'] as $st):?>
                    <option <?=($_POST['status']??$record['status'])===$st?'selected':''?>><?=e($st)?>
                    </option>
                    <?php endforeach;?>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-primary" <?=(!$customers||!$services||!$staffList)?'disabled':''?>>Save Appointment</button> 
            <a class="btn btn-secondary" href="appointments.php">Cancel</a>
        </div>
    </form>
</div>
<?php endif;?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Appointment Records</h3>
        <div class="card-tools">
            <a href="appointments.php?action=create" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> New Appointment</a>
        </div>
    </div>
<div class="card-body">
    <form method="get" class="form-row mb-3">
        <div class="col-md-4 mb-2">
            <input class="form-control" name="q" placeholder="Search customer, service or staff" value="<?=e($search)?>">
        </div>
        <div class="col-md-3 mb-2">
            <input class="form-control" type="date" name="date" value="<?=e($dateFilter)?>">
        </div>
        <div class="col-md-3 mb-2">
            <select class="form-control" name="status">
                <option value="">All statuses</option>
                <?php foreach(['Scheduled','Completed','Cancelled'] as $st):?>
                    <option <?= $statusFilter===$st?'selected':''?>><?=e($st)?></option>
                    <?php endforeach;?>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <button class="btn btn-default btn-block">Filter</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Staff</th><
                    th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($rows as $r):?>
                    <tr>
                        <td><?=e($r['appointment_id'])?></td>
                        <td><?=e($r['customer_name'])?></td>
                        <td><?=e($r['service_name'])?></td>
                        <td><?=e($r['staff_name'])?></td>
                        <td><?=e($r['appointment_date'])?></td>
                        <td><?=e(substr($r['start_time'],0,5))?>–<?=e(substr($r['end_time'],0,5))?></td>
                        <td>
                            <span 
                            class="badge badge-<?= $r['status']==='Completed'?'success':($r['status']==='Cancelled'?'danger':'primary')?>"><?=e($r['status'])?>
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <a class="btn btn-warning btn-xs" href="appointments.php?action=edit&id=<?=$r['appointment_id']?>">Edit</a>                            <form method="post" style="display:inline"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="mode" value="set_status"><input type="hidden" name="appointment_id" value="<?=$r['appointment_id']?>"><select name="status" onchange="this.form.submit()" aria-label="Change status" class="form-control form-control-sm d-inline-block" style="width:125px"><?php foreach(['Scheduled','Completed','Cancelled'] as $st):?><option <?= $r['status']===$st?'selected':''?>><?=e($st)?></option><?php endforeach;?></select></form>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
                                    <input type="hidden" name="mode" value="delete">
                                    <input type="hidden" name="appointment_id" value="<?=$r['appointment_id']?>">
                                    <button class="btn btn-danger btn-xs" type="submit" data-confirm="Permanently delete this appointment?">Delete</button>
                                </form>
                        </td>
                    </tr>
                    <?php endforeach;?>
                    <?php if(!$rows):?>
                        <tr>
                            <td colspan="8" class="text-center">No appointments found.</td>
                        </tr><?php endif;?>
            </tbody>
        </table>
    </div>
</div>
</div>
<?php require 'footer.php'; ?>
