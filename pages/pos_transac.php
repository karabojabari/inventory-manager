<?php
include'../includes/connection.php';

session_start();

// Check if product data exists
if (!isset($_POST['name']) || !is_array($_POST['name'])) {
    echo "<script>alert('Error: No products in cart!'); window.location = 'pos.php';</script>";
    exit();
}

$date = $_POST['date'];
$customer = $_POST['customer'];
$subtotal = $_POST['subtotal'];
$vat_amount = $_POST['vat_amount'] ?? 0;
$total = $_POST['total'];
$cash = $_POST['cash'];
$emp = $_POST['employee'];
$rol = $_POST['role'];
$today = date("mdGis"); 

$countID = count($_POST['name']);

switch($_GET['action']){
    case 'add':  
        // Start transaction for data consistency
        mysqli_autocommit($db, false);
        $all_success = true;
        
        try {
            // First, check if all products have sufficient stock
            for($i = 0; $i < $countID; $i++) {
                $product_name = mysqli_real_escape_string($db, $_POST['name'][$i]);
                $quantity = intval($_POST['quantity'][$i]);
                
                // Get current stock for this product
                $stock_query = "SELECT PRODUCT_ID, QTY_STOCK FROM product WHERE NAME = '$product_name' AND QTY_STOCK >= $quantity LIMIT 1";
                $stock_result = mysqli_query($db, $stock_query);
                
                if (!$stock_result || mysqli_num_rows($stock_result) == 0) {
                    throw new Exception("Insufficient stock for $product_name");
                }
            }
            
            // If all products have sufficient stock, proceed with the transaction
            for($i = 0; $i < $countID; $i++) {
                $product_name = mysqli_real_escape_string($db, $_POST['name'][$i]);
                $quantity = intval($_POST['quantity'][$i]);
                $price = floatval($_POST['price'][$i]);
                
                // Insert into transaction_details
                $query = "INSERT INTO `transaction_details`
                         (`ID`, `TRANS_D_ID`, `PRODUCTS`, `QTY`, `PRICE`, `EMPLOYEE`, `ROLE`)
                         VALUES (Null, '{$today}', '{$product_name}', '{$quantity}', '{$price}', '{$emp}', '{$rol}')";

                if (!mysqli_query($db, $query)) {
                    throw new Exception("Error saving transaction details: " . mysqli_error($db));
                }
                
                // Update product stock - subtract sold quantity
                $update_query = "UPDATE product SET QTY_STOCK = QTY_STOCK - $quantity WHERE NAME = '$product_name' AND QTY_STOCK >= $quantity";
                
                if (!mysqli_query($db, $update_query)) {
                    throw new Exception("Error updating stock for $product_name: " . mysqli_error($db));
                }
                
                // Check if any rows were affected (stock was actually updated)
                if (mysqli_affected_rows($db) == 0) {
                    throw new Exception("Failed to update stock for $product_name - stock may be insufficient");
                }
            }
            
            // Insert main transaction record
            $query111 = "INSERT INTO `transaction`
                       (`TRANS_ID`, `CUST_ID`, `NUMOFITEMS`, `SUBTOTAL`, `LESSVAT`, `NETVAT`, `ADDVAT`, `GRANDTOTAL`, `CASH`, `DATE`, `TRANS_D_ID`)
                       VALUES (Null,'{$customer}','{$countID}','{$subtotal}','{$vat_amount}','0','0','{$total}','{$cash}','{$date}','{$today}')";
            
            if (!mysqli_query($db, $query111)) {
                throw new Exception("Error saving transaction: " . mysqli_error($db));
            }
            
            // Commit transaction if all queries succeeded
            mysqli_commit($db);
            $all_success = true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($db);
            $all_success = false;
            $error_message = $e->getMessage();
        }
        
        // Re-enable autocommit
        mysqli_autocommit($db, true);
        
        if ($all_success) {
            // Clear cart and show success
            unset($_SESSION['pointofsale']);
            echo "<script type='text/javascript'>
                alert('Transaction successful! Stock has been updated.');
                window.location = 'pos.php';
            </script>";
        } else {
            echo "<script type='text/javascript'>
                alert('Error: $error_message');
                window.location = 'pos.php';
            </script>";
        }
        break;
}
?>