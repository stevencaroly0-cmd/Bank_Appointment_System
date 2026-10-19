<?php
require_once 'config.php'; require_login(); verify_csrf();
$pageTitle='Services'; $active='services'; $action=$_GET['action']??'list'; $id=(int)($_GET['id']??0); $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $mode=$_POST['mode']??'';
 if($mode==='save'){
  $sid=(int)($_POST['service_id']??0);$name=trim($_POST['service_name']??'');$desc=trim($_POST['description']??'');$duration=(int)($_POST['duration_minutes']??0);
  if($name===''||$duration<=0)$error='Service name and a positive duration are required.';
  else try{if($sid){$q=$pdo->prepare("UPDATE services SET service_name=?,description=?,duration_minutes=? WHERE service_id=?");$q->execute([$name,$desc?:null,$duration,$sid]);flash('Service updated.');}else{$q=$pdo->prepare("INSERT INTO services(service_name,description,duration_minutes) VALUES(?,?,?)");$q->execute([$name,$desc?:null,$duration]);flash('Service added.');}redirect('services.php');}catch(PDOException $e){$error='Could not save. Check that the service name is unique.';}
  $action=$sid?'edit':'create';$id=$sid;
 }elseif($mode==='delete'){try{$q=$pdo->prepare("DELETE FROM services WHERE service_id=?");$q->execute([(int)$_POST['service_id']]);flash('Service deleted.');}catch(PDOException $e){flash('Cannot delete this service because an appointment refers to it.','danger');}redirect('services.php');}
}
$record=['service_id'=>0,'service_name'=>'','description'=>'','duration_minutes'=>30];
if($action==='edit'&&$id){$q=$pdo->prepare("SELECT * FROM services WHERE service_id=?");$q->execute([$id]);$record=$q->fetch()?:$record;}
$search=trim($_GET['q']??'');$q=$pdo->prepare("SELECT * FROM services WHERE service_name LIKE ? OR description LIKE ? ORDER BY service_id DESC");$like='%'.$search.'%';$q->execute([$like,$like]);$rows=$q->fetchAll();
require 'header.php';
?>
<?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?>
<?php if(in_array($action,['create','edit'],true)):?><div class="card card-primary"><div class="card-header"><h3 class="card-title"><?=$action==='edit'?'Edit Service':'Add Service'?></h3></div><form method="post"><div class="card-body"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="mode" value="save"><input type="hidden" name="service_id" value="<?=e($record['service_id'])?>">
<div class="form-group"><label>Service Name *</label><input class="form-control" name="service_name" maxlength="100" required value="<?=e($_POST['service_name']??$record['service_name'])?>"></div>
<div class="form-group"><label>Description</label><textarea class="form-control" name="description" maxlength="255"><?=e($_POST['description']??$record['description'])?></textarea></div>
<div class="form-group"><label>Duration (minutes) *</label><input class="form-control" type="number" min="1" name="duration_minutes" required value="<?=e($_POST['duration_minutes']??$record['duration_minutes'])?>"></div></div><div class="card-footer"><button class="btn btn-primary">Save Service</button> <a class="btn btn-secondary" href="services.php">Cancel</a></div></form></div><?php endif;?>
<div class="card"><div class="card-header"><h3 class="card-title">Banking Services</h3><div class="card-tools"><a href="services.php?action=create" class="btn btn-success btn-sm">Add Service</a></div></div><div class="card-body"><form class="form-inline mb-3" method="get"><input class="form-control mr-2" name="q" placeholder="Search services" value="<?=e($search)?>"><button class="btn btn-default">Search</button></form>
<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>ID</th><th>Service</th><th>Description</th><th>Duration</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><?=e($r['service_id'])?></td><td><?=e($r['service_name'])?></td><td><?=e($r['description']?:'—')?></td><td><?=e($r['duration_minutes'])?> min</td><td class="text-nowrap"><a class="btn btn-warning btn-sm" href="services.php?action=edit&id=<?=$r['service_id']?>">Edit</a> <form method="post" style="display:inline"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="mode" value="delete"><input type="hidden" name="service_id" value="<?=$r['service_id']?>"><button class="btn btn-danger btn-sm" type="submit" data-confirm="Delete this service?">Delete</button></form></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="5" class="text-center">No services found.</td></tr><?php endif;?></tbody></table></div></div></div>
<?php require 'footer.php'; ?>
