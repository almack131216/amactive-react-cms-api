<?php

$sql = null;
$sqlCust = null;
$debug = "";

$method = $_SERVER['REQUEST_METHOD'];
if(isset($_REQUEST['id'])) $itemId = $_POST['id'];
if($_POST['type']) $type = $_POST['type'];

if($method == 'POST' && $_GET['api'] == "update"){
    if($type === "category"){
        $name = $_POST['name'] ? $_POST['name'] : 'Hard Code';
        $slug = $_POST["slug"] ? $_POST["slug"] : 'hard-code';
        $sql = "UPDATE catalogue_cats SET category='$name',slug='$slug' WHERE id=$itemId";
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