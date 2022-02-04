<?php

$sql = null;
$sqlCust = null;
$debug = "";

if(isset($_REQUEST['id'])) $itemId = $_REQUEST['id'];
if($_REQUEST['type']) $type = $_REQUEST['type'];

if($_REQUEST['api'] === 'delete'){
    switch($type){
        case "category":
            $sql = "DELETE FROM catalogue_cats WHERE id = $itemId";
            break;

        case "subcategory":
            $sql = "DELETE FROM catalogue_subcats WHERE id = $itemId";          
            break;

        case "item":
            $sql = "DELETE FROM catalogue WHERE ((id=$itemId AND id_xtra=0) OR (id!=$itemId AND id_xtra=$itemId))";          
            break;
    }
}

if($sql){
    $debug .= $sql;

    if (!$result = $mysqli->query($sql)) {
        return "Sorry, the website is experiencing problems.";
        exit;
    }
    
    if($printDebug){
        echo $debug;
        echo $sql;
        echo '<br>------------<br>';
    }

    echo json_encode($result);
    $mysqli->close();
}
?>