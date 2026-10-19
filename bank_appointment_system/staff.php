<?php
require_once 'config.php'; require_login(); 

// Only Administrator can access Staff Management
if (($_SESSION['role'] ?? '') !== 'Administrator') {
    http_response_code(403);
    exit('Access denied. Only Administrators can manage staff.');
}
verify_csrf();
$pageTitle='Staff'; $active='staff'; $action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $mode=$_POST['mode']??'';
 if($mode==='save'){
  $sid=(int)($_POST['staff_id']??0);$name=trim($_POST['full_name']??'');$phone=trim($_POST['phone']??'');$email=trim($_POST['email']??'');$position=trim($_POST['position']??'');
  if($name===''||$phone===''||$email===''||$position==='')$error='All fields are required.';
  elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))$error='Enter a valid email address.';
  else try{if($sid){$q=$pdo->prepare("UPDATE staff SET full_name=?,phone=?,email=?,position=? WHERE staff_id=?");$q->execute([$name,$phone,$email,$position,$sid]);flash('Staff member updated.');}else{$q=$pdo->prepare("INSERT INTO staff(full_name,phone,email,position) VALUES(?,?,?,?)");$q->execute([$name,$phone,$email,$position]);flash('Staff member added.');}redirect('staff.php');}catch(PDOException $e){$error='Could not save. Ensure this email address is not already used.';}
  $action=$sid?'edit':'create';$id=$sid;
 }elseif($mode==='delete'){try{$q=$pdo->prepare("DELETE FROM staff WHERE staff_id=?");$q->execute([(int)$_POST['staff_id']]);flash('Staff member deleted.');}catch(PDOException $e){flash('Cannot delete this staff member because an appointment refers to them.','danger');}redirect('staff.php');}
}
$record=['staff_id'=>0,'full_name'=>'','phone'=>'','email'=>'','position'=>''];
if($action==='edit'&&$id){$q=$pdo->prepare("SELECT * FROM staff WHERE staff_id=?");$q->execute([$id]);$record=$q->fetch()?:$record;}
$search=trim($_GET['q']??'');$q=$pdo->prepare("SELECT * FROM staff WHERE full_name LIKE ? OR phone LIKE ? OR email LIKE ? OR position LIKE ? ORDER BY staff_id DESC");$like='%'.$search.'%';$q->execute([$like,$like,$like,$like]);$rows=$q->fetchAll();
require 'header.php';
?>
<?php if($error):?>
    <div class="alert alert-danger"><?=e($error)?>
</div>
<?php endif;?>
<?php if(in_array($action,['create','edit'],true)):?>
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title"><?=$action==='edit'?'Edit Staff Member':'Add Staff Member'?>
            </h3>
        </div>
        <form method="post">
            <div class="card-body">
                <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
                <input type="hidden" name="mode" value="save">
                <input type="hidden" name="staff_id" value="<?=e($record['staff_id'])?>">
<div class="form-group">
    <label>Full Name *</label>
    <input class="form-control" name="full_name" maxlength="100" required value="<?=e($_POST['full_name']??$record['full_name'])?>">
</div>
<div class="form-group">
    <label>Phone *</label>
    <input class="form-control" name="phone" maxlength="20" required value="<?=e($_POST['phone']??$record['phone'])?>">
</div>
<div class="form-group">
    <label>Email *</label>
    <input class="form-control" type="email" name="email" maxlength="100" required value="<?=e($_POST['email']??$record['email'])?>">
</div>
<div class="form-group">
    <label>Position *</label>
    <input class="form-control" name="position" maxlength="50" required value="<?=e($_POST['position']??$record['position'])?>">
</div>
</div>
<div class="card-footer">
    <button class="btn btn-primary">Save Staff</button>
     <a class="btn btn-secondary" href="staff.php">Cancel</a>
</div>
</form>
</div>


<?php endif;?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Bank Staff</h3>
        <div class="card-tools">
            <a href="staff.php?action=create" class="btn btn-success btn-sm">Add Staff Member</a>
        </div>
    </div>
        <div class="card-body">
            <form class="form-inline mb-3" method="get">
                <input class="form-control mr-2" name="q" placeholder="Search staff" value="<?=e($search)?>">
                <button class="btn btn-default">Search</button>
            </form>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Position</th>
                <th>Actions</th>
            </tr>
        </thead>
    <tbody>
<?php foreach($rows as $r):?>
    <tr>
        <td><?=e($r['staff_id'])?></td>
        <td><?=e($r['full_name'])?></td>
        <td><?=e($r['phone'])?></td>
        <td><?=e($r['email'])?></td>
        <td><?=e($r['position'])?></td>
        <td class="text-nowrap">
            <a class="btn btn-warning btn-sm" href="staff.php?action=edit&id=<?=$r['staff_id']?>">Edit</a> 
            <form method="post" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
                <input type="hidden" name="mode" value="delete">
                <input type="hidden" name="staff_id" value="<?=$r['staff_id']?>">
                <button class="btn btn-danger btn-sm" type="submit" data-confirm="Delete this staff member?">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach;?>
<?php if(!$rows):?>
    <tr>
        <td colspan="6" class="text-center">No staff records found.</td>
    </tr>
    <?php endif;?>
</tbody>
</table>
</div>
</div>
</div>
<?php require 'footer.php'; ?>
