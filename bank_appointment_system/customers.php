<?php
require_once 'config.php'; require_login(); verify_csrf();
$pageTitle='Customers'; $active='customers';
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $mode=$_POST['mode']??'';
    if ($mode==='save') {
        $customerId=(int)($_POST['customer_id']??0); $name=trim($_POST['full_name']??''); $phone=trim($_POST['phone']??''); $email=trim($_POST['email']??'');
        if ($name==='' || $phone==='') $error='Full name and phone are required.';
        elseif ($email!=='' && !filter_var($email,FILTER_VALIDATE_EMAIL)) $error='Enter a valid email address.';
        else {
            if ($customerId>0) {$q=$pdo->prepare("UPDATE customers SET full_name=?,phone=?,email=? WHERE customer_id=?");$q->execute([$name,$phone,$email!==''?$email:null,$customerId]);flash('Customer updated.');}
            else {$q=$pdo->prepare("INSERT INTO customers(full_name,phone,email) VALUES(?,?,?)");$q->execute([$name,$phone,$email!==''?$email:null]);flash('Customer added.');}
            redirect('customers.php');
        }
        $action=$customerId>0?'edit':'create'; $id=$customerId;
    } elseif ($mode==='delete') {
        try {$q=$pdo->prepare("DELETE FROM customers WHERE customer_id=?");$q->execute([(int)$_POST['customer_id']]);flash('Customer deleted.');}
        catch(PDOException $e){flash('Cannot delete this customer because an appointment refers to them. Cancel or retain the appointment first.','danger');}
        redirect('customers.php');
    }
}
$record=['customer_id'=>0,'full_name'=>'','phone'=>'','email'=>''];
if ($action==='edit' && $id) {$q=$pdo->prepare("SELECT * FROM customers WHERE customer_id=?");$q->execute([$id]);$record=$q->fetch() ?: $record;}
$search=trim($_GET['q']??'');
$q=$pdo->prepare("SELECT * FROM customers WHERE full_name LIKE ? OR phone LIKE ? OR email LIKE ? ORDER BY customer_id DESC");
$like='%'.$search.'%';$q->execute([$like,$like,$like]);$rows=$q->fetchAll();
require 'header.php';
?>
<?php if ($error): ?><div class="alert alert-danger"><?=e($error)?></div><?php endif; ?>
<?php if (in_array($action,['create','edit'],true)): ?>
<div class="card card-primary"><div class="card-header"><h3 class="card-title"><?= $action==='edit'?'Edit Customer':'Add Customer' ?></h3></div><form method="post"><div class="card-body"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="mode" value="save"><input type="hidden" name="customer_id" value="<?=e($record['customer_id'])?>">
<div class="form-group"><label>Full Name *</label><input class="form-control" name="full_name" maxlength="100" required value="<?=e($_POST['full_name']??$record['full_name'])?>"></div>
<div class="form-group"><label>Phone *</label><input class="form-control" name="phone" maxlength="20" required value="<?=e($_POST['phone']??$record['phone'])?>"></div>
<div class="form-group"><label>Email (optional)</label><input class="form-control" type="email" name="email" maxlength="100" value="<?=e($_POST['email']??$record['email'])?>"></div>
</div><div class="card-footer"><button class="btn btn-primary">Save Customer</button> <a class="btn btn-secondary" href="customers.php">Cancel</a></div></form></div>
<?php endif; ?>
<div class="card"><div class="card-header"><h3 class="card-title">Customer Records</h3><div class="card-tools"><a href="customers.php?action=create" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Add Customer</a></div></div>
<div class="card-body"><form class="form-inline mb-3" method="get"><input class="form-control mr-2" name="q" placeholder="Search name, phone or email" value="<?=e($search)?>"><button class="btn btn-default">Search</button></form>
<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>ID</th><th>Full Name</th><th>Phone</th><th>Email</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?=e($r['customer_id'])?></td><td><?=e($r['full_name'])?></td><td><?=e($r['phone'])?></td><td><?=e($r['email'] ?: '—')?></td><td class="text-nowrap"><a class="btn btn-warning btn-sm" href="customers.php?action=edit&id=<?=$r['customer_id']?>">Edit</a> <form method="post" style="display:inline"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="mode" value="delete"><input type="hidden" name="customer_id" value="<?=$r['customer_id']?>"><button class="btn btn-danger btn-sm" data-confirm="Delete this customer?" type="submit">Delete</button></form></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="5" class="text-center">No customers found.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php require 'footer.php'; ?>
