<?php
include'../includes/connection.php';

if (!isset($_GET['do']) || $_GET['do'] != 1) {
    switch ($_GET['type']) {
        case 'product':
            $query = 'DELETE FROM product WHERE PRODUCT_ID = ' . $_GET['id'];
            $result = mysqli_query($db, $query) or die(mysqli_error($db));				
?>
            <script type="text/javascript">
                alert("Product Successfully Deleted.");
                window.location = "inventory.php";
            </script>					
<?php
            //break;
    }
}
?>

<!-- Simple Back Button at Bottom -->
<div style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 1000;">
    <a href="inventory.php" type="button" class="btn btn-primary" 
       style="background-color: #047857; border-color: #047857; color: white; padding: 10px 25px;">
        <i class="fas fa-arrow-left"></i> Back to Inventory
    </a>
</div>

<?php
include'../includes/footer.php';
?>