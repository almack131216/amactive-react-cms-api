<?php

$sql = null;
$sqlCust = null;
$debug = "";

$method = $_SERVER['REQUEST_METHOD'];
if(isset($_REQUEST['id'])) $id = $_POST['id'];
if($_POST['type']) $type = $_POST['type'];

if($method == 'POST' && $_GET['api'] == "update" ){
    $name = $_POST['name'];
    $slug = $_POST["slug"];

    if($type === "category"){        
        $sql = "UPDATE catalogue_cats SET category='$name',slug='$slug' WHERE id=$id";
    }
    if($type === "subcategory"){        
        $sql = "UPDATE catalogue_subcats SET subcategory='$name',slug='$slug' WHERE id=$id";
    }
}

$printDebug = false;
if($sql){
    $debug .= $sql;

    $result = $mysqli->query($sql);

    if (!$result = $mysqli->query($sql)) {
        return "Sorry, the website is experiencing problems.";
        exit;
    }
    
    if($printDebug){
        echo $debug;
        echo '------------';
    }

    echo json_encode($result);
    $mysqli->close();
}else{
    echo json_encode(false);
}
?>