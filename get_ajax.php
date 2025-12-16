<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'curdapp';
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if($conn->connect_error) {
    json_response(['status'=>'error','message'=>'DB connection failed: '.$conn->connect_error]);
}

function json_response($arr){
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if($action == 'fetch'){
    $page = isset($_POST['page']) ? max(1,intval($_POST['page'])) : 1;
    $per_page = isset($_POST['per_page']) ? max(1,intval($_POST['per_page'])) : 5;
    $q = isset($_POST['q']) ? $conn->real_escape_string(trim($_POST['q'])) : '';

    $offset = ($page - 1) * $per_page;

    $where = '1';
    if($q !== '') {
        $where .= " AND (name LIKE '%$q%' OR email LIKE '%$q%' OR mobile LIKE '%$q%' )";
    }

    $totalRes = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE $where");
    $totalRow = $totalRes->fetch_assoc();
    $total = intval($totalRow['cnt']);
    $pages = $total ? ceil($total / $per_page) : 1;

    $sql = "SELECT * FROM users WHERE $where ORDER BY id DESC LIMIT $offset, $per_page";
    $res = $conn->query($sql);

    $html = '<div class="table-responsive"><table class="table table-bordered table-striped">';
    $html .= '<thead><tr>
                <th>#</th>
                <th>Image</th>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Gender</th>
                <th>Hobbies</th>
                <th>Language</th>
                <th>Skill</th>
                <th>Action</th>
              </tr></thead><tbody>';
    if($res && $res->num_rows){
        $i = $offset + 1;
        while($row = $res->fetch_assoc()){
            $img = $row['image'] ? '<img src="uploads/'.htmlspecialchars($row['image']).'" class="profile-thumb">' : '';
            $html .= '<tr>
                        <td>'.($i++).'</td>
                        <td>'.$img.'</td>
                        <td>'.htmlspecialchars($row['name']).'</td>
                        <td>'.htmlspecialchars($row['email']).'</td>
                        <td>'.htmlspecialchars($row['mobile']).'</td>
                        <td>'.htmlspecialchars($row['gender']).'</td>
                        <td>'.htmlspecialchars($row['hobbies']).'</td>
                        <td>'.htmlspecialchars($row['language']).'</td>
                        <td>'.htmlspecialchars($row['skill']).'</td>
                        <td>
                          <button class="btn btn-sm btn-info btn-edit" data-id="'.$row['id'].'">Edit</button>
                          <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row['id'].'">Delete</button>
                        </td>
                      </tr>';
        }
    } else {
        $html .= '<tr><td colspan="10" class="text-center">No records found</td></tr>';
    }
    $html .= '</tbody></table></div>';
    if($pages > 1){
        $html .= '<nav><ul class="pagination">';
        for($p=1;$p<=$pages;$p++){
            $active = $p==$page ? ' active' : '';
            $html .= '<li class="page-item'.$active.'"><a href="#" class="page-link" data-page="'.$p.'">'.$p.'</a></li>';
        }
        $html .= '</ul></nav>';
    }
    json_response(['status'=>'success','html'=>$html]);
}
function validate_input($data, $isUpdate=false){
    $errors = [];
    if(!isset($data['name']) || trim($data['name']) === '') $errors[] = 'Name is required';
    elseif(mb_strlen(trim($data['name'])) < 2) $errors[] = 'Name must be at least 2 characters';
    if(!isset($data['email']) || trim($data['email']) === '') $errors[] = 'Email is required';
    elseif(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email is invalid';
    if(!isset($data['mobile']) || trim($data['mobile']) === '') $errors[] = 'Mobile is required';
    else {
        $m = preg_replace('/\D+/', '', $data['mobile']);
        if(strlen($m) != 10) $errors[] = 'Mobile must be 10 digits';
    }
    if(!isset($data['gender']) || trim($data['gender']) === '') $errors[] = 'Gender is required';
    if(!isset($data['hobbies']) || !is_array($data['hobbies']) || count($data['hobbies']) == 0) $errors[] = 'Select at least one hobby';
    if(!isset($data['language']) || trim($data['language']) === '') $errors[] = 'Language is required';

    if(!isset($data['skill']) || !is_array($data['skill']) || count($data['skill']) == 0) $errors[] = 'Select at least one skill';

    return $errors;
}

if($action == 'add' || $action == 'update'){
    $isUpdate = ($action == 'update');
    $id = $isUpdate ? intval($_POST['id']) : 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $hobbies = isset($_POST['hobbies']) ? $_POST['hobbies'] : [];
    $language = trim($_POST['language'] ?? '');
    $skill = isset($_POST['skill']) ? $_POST['skill'] : [];

    $hobbies = array_map('trim', $hobbies);
    $skill = array_map('trim', $skill);

    $hobbies_str = $hobbies ? implode(',', $hobbies) : '';
    $skill_str = $skill ? implode(',', $skill) : '';

    $dataForVal = [
        'name'=>$name,
        'email'=>$email,
        'mobile'=>$mobile,
        'gender'=>$gender,
        'hobbies'=>$hobbies,
        'language'=>$language,
        'skill'=>$skill
    ];
    $errors = validate_input($dataForVal, $isUpdate);
    if($email !== ''){
        if($isUpdate){
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param('si', $email, $id);
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param('s', $email);
        }
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows > 0) $errors[] = 'Email already exists';
        $stmt->close();
    }

    $image_name = '';
    $image_uploaded_now = false;
    if(isset($_FILES['image']) && $_FILES['image']['error'] != 4){ // 4 = no file
        $f = $_FILES['image'];
        if($f['error'] !== 0){
            $errors[] = 'Image upload error';
        } else {
            $allowed_ext = ['jpg','jpeg','png','gif','webp'];
            $max_size = 2 * 1024 * 1024; // 2MB
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if(!in_array($ext, $allowed_ext)) $errors[] = 'Only jpg/png/gif/webp allowed';
            if($f['size'] > $max_size) $errors[] = 'Image must be <= 2MB';
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $f['tmp_name']);
            finfo_close($finfo);
            if(strpos($mime, 'image/') !== 0) $errors[] = 'Uploaded file is not an image';
            if(empty($errors)){
                $image_name = time().'_'.preg_replace('/[^A-Za-z0-9_.-]/','',basename($f['name']));
                $dest = __DIR__.'/uploads/'.$image_name;
                if(!move_uploaded_file($f['tmp_name'], $dest)){
                    $errors[] = 'Failed to save uploaded image';
                } else {
                    $image_uploaded_now = true;
                }
            }
        }
    } else {
        if(!$isUpdate){
            $errors[] = 'Profile image is required';
        }
    }
    if(!empty($errors)){
        if($image_uploaded_now && $image_name && file_exists(__DIR__.'/uploads/'.$image_name)){
            @unlink(__DIR__.'/uploads/'.$image_name);
        }
        json_response(['status'=>'validation','errors'=>$errors]);
    }

    if($isUpdate){
        if(!$image_uploaded_now){
            $res = $conn->query("SELECT image FROM users WHERE id = ".intval($id));
            if($res && $res->num_rows){
                $r = $res->fetch_assoc();
                $image_name = $r['image'];
            } else {
                $image_name = '';
            }
        } else {
            $res = $conn->query("SELECT image FROM users WHERE id = ".intval($id));
            if($res && $res->num_rows){
                $r = $res->fetch_assoc();
                if($r['image'] && file_exists(__DIR__.'/uploads/'.$r['image'])){
                    @unlink(__DIR__.'/uploads/'.$r['image']);
                }
            }
        }
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, mobile=?, gender=?, hobbies=?, language=?, skill=?, image=? WHERE id=?");
        $stmt->bind_param('ssssssssi', $name, $email, $mobile, $gender, $hobbies_str, $language, $skill_str, $image_name, $id);
        $ok = $stmt->execute();
        $stmt->close();
        if($ok) json_response(['status'=>'success','message'=>'Updated']);
        else json_response(['status'=>'error','message'=>'Update failed']);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name,email,mobile,gender,hobbies,language,skill,image) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssssssss', $name, $email, $mobile, $gender, $hobbies_str, $language, $skill_str, $image_name);
        $ok = $stmt->execute();
        $stmt->close();
        if($ok) json_response(['status'=>'success','message'=>'Inserted']);
        else json_response(['status'=>'error','message'=>'Insert failed']);
    }
}
if($action == 'edit_fetch'){
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows){
        $row = $res->fetch_assoc();
        json_response(['status'=>'success','data'=>$row]);
    } else {
        json_response(['status'=>'error','message'=>'Not found']);
    }
}

if($action == 'delete'){
    $id = intval($_POST['id']);
    $res = $conn->query("SELECT image FROM users WHERE id = $id");
    if($res && $res->num_rows){
        $r = $res->fetch_assoc();
        if($r['image'] && file_exists(__DIR__.'/uploads/'.$r['image'])){
            @unlink(__DIR__.'/uploads/'.$r['image']);
        }
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i',$id);
    $ok = $stmt->execute();
    $stmt->close();
    if($ok) json_response(['status'=>'success','message'=>'Deleted']);
    else json_response(['status'=>'error','message'=>'Delete failed']);
}
json_response(['status'=>'error','message'=>'Invalid action']);
?>