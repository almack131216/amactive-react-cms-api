<?php

function getFriendlyURL($string) {
    setlocale(LC_CTYPE, 'en_US.UTF8');
    $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
    $string = preg_replace('~[^\-\pL\pN\s]+~u', '-', $string);
    $string = str_replace(' ', '-', $string);
    $string = trim($string, "-");
    $string = strtolower($string);
    return $string;
} 

function returnSqlCommonSelectItems(){
    $ret = " catalogue.id,catalogue.status,catalogue.name,catalogue.slug";
    $ret .= ",catalogue.category AS categoryId";
    $ret .= ",catalogue.upload_date AS createdAt,catalogue.upload_date AS updatedAt";
    $ret .= ",catalogue.image_large AS image,catalogue.image_dir AS imageDir,catalogue.image_hi AS imageHi";  
    return $ret;
}

function returnSqlCommonSelectBrandArr(){
    $ret = ",catalogue_subcat.id AS catalogue_subcat_id";
    $ret .= ",catalogue_subcat.subcategory AS catalogue_subcat_name";
    $ret .= ",catalogue_subcat.slug AS catalogue_subcat_slug";
    return $ret;
}

function returnSqlInnerJoinBrands(){
    $ret = " INNER JOIN catalogue_subcats AS catalogue_subcat";
    $ret .= " ON catalogue.subcategory=catalogue_subcat.id";
    return $ret;
}

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

if(isset($_GET['api']))  $api = $_GET['api'];
if(isset($_GET['id']))  $itemId = $_GET['id'];
if(isset($_GET['ids'])) $itemIds = $_GET['ids'];
if(isset($_GET['categoryId'])) $categoryId = $_GET['categoryId'];
if(isset($_GET['subcategoryId'])) $subcategoryId = $_GET['subcategoryId'];
if(isset($_GET['preview'])) $preview = true;
if(isset($_GET['limit'])) $limit = $_GET['limit'];

if($api === 'items'){
    $isStockPage = false;
    $isItemListPage = true;
    $sqlSelect = "";
    $sqlSelectCommonStock = ",catalogue.subcategory AS subcategoryId,catalogue.detail_1 AS year";
    $sqlSelectCommonPrice = ",catalogue.price,catalogue.price_details";
    $sqlSelectCommonExcerpt = ",catalogue.detail_6 AS excerpt,catalogue.description AS brief";
    $sqlWhere = "";
    $sqlGroup = " GROUP BY catalogue.id,catalogue.name";
    $sqlOrder = " ORDER BY catalogue.upload_date DESC";
    $qLimit = " LIMIT 500";
    if($limit) $qLimit = " LIMIT ".$limit;        

    if($categoryId == 2) {
        $isStockPage = true;
        $sqlSelect .= $sqlSelectCommonStock; 
        $sqlSelect .= $sqlSelectCommonPrice;               
        $sqlWhere .= " AND catalogue.category=2";
		if($preview){
			$sqlWhere .= " AND catalogue.status=0";
		}else{
			$sqlWhere .= " AND catalogue.status=1";
		}

        if($subcategoryId){
            $sqlWhere .= " AND catalogue.subcategory=".$subcategoryId;
        }
    }
    
    if(isset($itemId) || isset($itemIds)) {
        $sqlGroup = "";
        $isItemListPage = false;
        if($_GET['spec'] === 'ItemRelated') $isItemListPage = true;
        $sqlSelect .= ",catalogue.detail_6 AS excerpt";
        $sqlSelect .= ",catalogue.description";
        $sqlSelect .= ",catalogue.related";
        $sqlSelect .= ",catalogue.youtube";

        if(!$itemId && $itemIds){
            $itemIdsArr = explode(",", $itemIds);
            $sqlWhere = " AND (catalogue.id=".$itemIdsArr[0];
            for($i=1;$i<count($itemIdsArr);$i++){
                $sqlWhere .= " OR catalogue.id=".$itemIdsArr[$i];
            }
            $sqlWhere .= ")";
        }else{
            $sqlWhere = " AND catalogue.id=".$itemId;
        }
        
    }else{
        $sqlSelect .= $sqlSelectCommonExcerpt;
    }

$sql = "SELECT ";
$sql .= returnSqlCommonSelectItems();
$sql .= $sqlSelect;
$sql .= returnSqlCommonSelectBrandArr();
$sql .= " FROM catalogue AS catalogue";
$sql .= returnSqlInnerJoinBrands();
$sql .= " WHERE catalogue.id_xtra = 0";
$sql .= $sqlWhere;
$sql .= $sqlGroup;
$sql .= $sqlOrder;
$sql .= $qLimit;
}

if($sqlCust) $sql = $sqlCust;

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
        $row['categoryId'] = intval($row['categoryId']);
        if($isStockPage){
            $row['subcategoryId'] = intval($row['subcategoryId']);
            $row['year'] = intval($row['year']);
            if(isset($row['price'])) $row['price'] = intval($row['price']);
        }
        if($isItemListPage && !$row['excerpt']){
            $tmpExcerpt = strip_tags($row['brief']);
            $tmpExcerpt = removeBadChars($tmpExcerpt);
            $row['excerpt'] = implode(' ', array_slice(explode(' ', $tmpExcerpt), 0, 30));
        }
        if($isItemListPage && $row['excerpt']){
            $row['excerpt'] = removeBadChars($row['excerpt']);
            $row['brief'] = '';
        }        
        
        if(!$isItemListPage && isset($row['description'])){
            // REF: https://www.w3resource.com/php/function-reference/addcslashes.php
            $description = removeBadChars($row['description']);
            $row['description'] = addcslashes($description,'"');
        }

        if($_GET['api'] != 'categories'){
            $row['catalogue_subcat'] = array();
            $row['catalogue_subcat']['id'] = intval($row['catalogue_subcat_id']);
            $row['catalogue_subcat']['name'] = $row['catalogue_subcat_name'];
            $row['catalogue_subcat']['slug'] = $row['catalogue_subcat_slug'];
        }
        $dbdata[]=$row;
        $debug .= '<br>'.$tmpCount.' > '.$row['id'].' | '.$row['name'].' | ';
    }

    $ignore = false;
    if($_GET['spec'] === 'ItemRelated') $ignore = true;
    if(!$ignore && isset($itemId)){
        $sql = "SELECT id, name, image_large AS image FROM catalogue WHERE id_xtra=$itemId AND image_large!=''";
        $sql .= " ORDER BY position_initem, id ASC";        

        if (!$result = $mysqli->query($sql)) {
            // return "Sorry, the website is experiencing problems.";
            // exit;
        }else{
            if(mysqli_num_rows($result) === 0 ){
                // return "Nothing to do";
                // exit;
            } else {
                $tmpCount = 0;
                //Fetch into associative array
                while ( $row = $result->fetch_assoc())  {
                    $tmpCount = $tmpCount + 1;
                    $row['id'] = intval($row['id']);
                    $row['name'] = removeBadChars($row['name']);    
                    if(!$row['name']) $row['name'] = $itemName;
                    $dbdata[]=$row;
                    $debug .= '<br>'.$tmpCount.' > '.$row['id'].' | '.$row['name'].' | ';
                }
            }
        }
        
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