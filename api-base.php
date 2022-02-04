<?php
header("Access-Control-Allow-Origin: *");

include("db.php");
$method = $_SERVER['REQUEST_METHOD'];

$printDebug = false;
if(isset($_REQUEST['debug']) && $_REQUEST['debug'] = true){
    $printDebug = true;
}

header('Content-Type: application/json');

if($_REQUEST['api'] === "categories"){
    include("api-categories.php");
} else if($_REQUEST['api'] === "subcategories"){
    include("api-categories.php");
} else if($_REQUEST['api'] === "items"){
    include("api-items.php");
} else if($_REQUEST['api'] === "delete"){
    include("api-delete.php");
} else if($_GET['api'] === "update"){        
    include("api-update.php");
}

?>
