<?php
header("Access-Control-Allow-Origin: *");

echo <<<EOD
    <ul>
    <li><strong>Categories</strong></li>
    <li><a href="api-base.php?api=categories">Category List</a></li>
    <li><a href="api-base.php?api=categories&id=#">Category Edit (id=#)</a></li>
    <li>---</li>
    <li><strong>Subcategories</strong></li>
    <li><a href="api-base.php?api=subcategories">Subcategory List</a></li>
    <li><a href="api-base.php?api=subcategories&categoryId=#">Subcategory List (categoryId=#)</a></li>
    <li><a href="api-base.php?api=subcategories&id=#">Subcategory Edit (id=#)</a></li>
    <li>---</li>
    <li><strong>Items</strong></li>
    <li><a href="api-base.php?api=items">Item List</a></li>
    <li><a href="api-base.php?api=items&categoryId=#">Item List (category=#)</a></li>
    <li><a href="api-base.php?api=items&categoryId=#&subcategoryId=#">Item List (categoryId=# & subcategoryId=#)</a></li>
    <li><a href="api-base.php?api=items&id=#">Item Edit (id=#)</a></li>
    </ul>
EOD;
?>