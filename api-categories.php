<?php

function removeBadChars( $text ) {
    $text = utf8_encode($text);
    $pattern = array (	"/&nbsp;/",
                        "/&pound;/",
                        "/&#39;/",
                        "/&rsquo;/",
                        "/&ldquo;/",
                        "/&amp;/",
                        "/Â/",
                        "/â/",
                        "/â€˜/",
                        "/â€™/" );
    
    $replace = array (	" ",
                        "£",
                        "'",
                        "'",
                        "\"",
                        "&",
                        "",
                        "'",
                        "'",
                        "'" );
                    
    return preg_replace( $pattern, $replace, $text );
}

$sql = null;
$sqlCust = null;
$debug = "";

if($_REQUEST['id']) $id = $_REQUEST['id'];
if($_REQUEST['categoryId']) $categoryId = $_REQUEST['categoryId'];
if($_REQUEST['subcategoryId']) $subcategoryId = $_REQUEST['subcategoryId'];

if($_REQUEST['api'] === 'categories'){
    $sql = "SELECT id,category AS name,status,slug";
    // $sql .= ",(SELECT COUNT(c.id) FROM catalogue AS c WHERE c.category = catalogue_cats.id) AS itemCount";
    // $sql .= ",(SELECT COUNT(sc.id) FROM catalogue_subcats AS sc WHERE sc.category = catalogue_cats.id) AS subcategoryCount";
    $sql .= " FROM catalogue_cats WHERE id!=0";
    if($id) $sql .= " AND id=".$id;
    $sql .= " ORDER BY category ASC";
}
if($_REQUEST['api'] === 'subcategories'){
    $sql = "SELECT id,subcategory AS name,category AS categoryId,slug,status FROM catalogue_subcats WHERE id!=0";
    if($categoryId) $sql .= " AND category=".$categoryId;
    if($id) $sql .= " AND id=".$id;
    $sql .= " ORDER BY subcategory ASC";
}
if($_REQUEST['api'] === 'items'){
    $sql = "SELECT id,name,category AS categoryId,subcategory AS subcategoryId,status FROM catalogue";
    $sql .= " WHERE id!=0";
    if($categoryId) $sql .= " AND category=".$categoryId;
    if($subcategoryId) $sql .= " AND subcategory=".$subcategoryId;
    $sql .= " ORDER BY upload_date DESC";
    if($_REQUEST['limit']) $sql .= " LIMIT ".$_REQUEST['limit'];
}
if(isset($_REQUEST['id'])) {
    $itemId = $_REQUEST['id'];
}

if($sql){
    $debug .= $sql;

    if (!$result = $mysqli->query($sql)) {
        return "Sorry, the website is experiencing problems.";
        exit;
    }else{
        if(mysqli_num_rows($result) === 0 ){
            return "Nothing to do";
            exit;
        }
    }

    //Initialize array variable
    $dbdata = array();
    $tmpCount = 0;
    //Fetch into associative array
    while ( $row = $result->fetch_assoc())  {
        $tmpCount = $tmpCount + 1;
        $row['id'] = intval($row['id']);
        $row['name'] = removeBadChars($row['name']);
        $row['status'] = intval($row['status']);
        $row['slug'] = $row['slug'];
        $dbdata[]=$row;
        $debug .= '<br>'.$tmpCount.' > '.$row['id'].' | '.$row['name'].' | ';
    }
    
    if($printDebug){
        echo $debug;
        echo $sql;
        echo '<br>------------<br>';
    }

    echo json_encode($dbdata, JSON_PRETTY_PRINT);
    // The script will automatically free the result and close the MySQL
    // connection when it exits, but let's just do it anyways
    $result->free();
    $mysqli->close();
}
?>