<?php
require_once __DIR__.'/../lib/auth.php'; start_session(); require_role(['admin']);
require_once __DIR__.'/../lib/csrf.php'; verify_csrf();
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/helpers.php';

if (isset($_POST['delete_id'])) {
  $id=(int)$_POST['delete_id'];
  $pdo->prepare('DELETE FROM rooms WHERE id=?')->execute([$id]);
  set_flash('ok','Room deleted'); redirect('rooms.php');
}

$name=trim($_POST['name']??'');
$location=trim($_POST['location']??'');
$capacity=(int)($_POST['capacity']??0);
$type=trim($_POST['type']??'collab');
$open_time=$_POST['open_time']??'08:00';
$close_time=$_POST['close_time']??'21:00';
$amenities=trim($_POST['amenities']??'');
$description=trim($_POST['description']??'');
$active=isset($_POST['is_active'])?1:0;
$id=(int)($_POST['id']??0);

if(!$name||!$location||$capacity<1){
  set_flash('err','Invalid room data','danger'); redirect('rooms.php');
}

$imagePath=null;
if (!empty($_FILES['image']['name']) && $_FILES['image']['error']===UPLOAD_ERR_OK) {
  $ext=strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
  if(!in_array($ext,['jpg','jpeg','png','webp'])){
    set_flash('err','Image must be JPG/PNG/WEBP','danger'); redirect('rooms.php');
  }
  $destRel = '../assets/img/rooms/'.uniqid('room_', true).'.'.$ext;
  $destAbs = __DIR__.'/'.$destRel;
  if(!move_uploaded_file($_FILES['image']['tmp_name'],$destAbs)){
    set_flash('err','Failed to upload image','danger'); redirect('rooms.php');
  }
  $imagePath = str_replace('/public/..','', base_url($destRel));
  $imagePath = parse_url($imagePath, PHP_URL_PATH);
}

if($id){
  $sql='UPDATE rooms SET name=?,location=?,capacity=?,type=?,is_active=?,open_time=?,close_time=?,amenities=?,description=?'
     . ($imagePath? ',image_path=?':'')
     . ' WHERE id=?';
  $params=[$name,$location,$capacity,$type,$active,$open_time,$close_time,$amenities,$description];
  if($imagePath){ $params[]=$imagePath; }
  $params[]=$id;
  $pdo->prepare($sql)->execute($params);
  set_flash('ok','Room updated');
}else{
  $pdo->prepare('INSERT INTO rooms(name,location,capacity,type,is_active,open_time,close_time,amenities,description,image_path)
    VALUES(?,?,?,?,?,?,?,?,?,?)')
    ->execute([$name,$location,$capacity,$type,$active,$open_time,$close_time,$amenities,$description,$imagePath]);
  set_flash('ok','Room added');
}
redirect('rooms.php');
