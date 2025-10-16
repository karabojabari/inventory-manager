<?php
include'../includes/connection.php';
include'../includes/sidebar.php';

// Check if user is admin
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
           
    if ($Aa=='User'){
?>
        <script type="text/javascript">
            alert("Restricted Page! You will be redirected to POS");
            window.location = "pos.php";
        </script>
<?php
    }
}

// Delete employee with user accounts
if (isset($_GET['type']) && $_GET['type'] == 'employee' && isset($_GET['id'])) {
    $employee_id = mysqli_real_escape_string($db, $_GET['id']);
    
    // Check if employee exists
    $check_query = "SELECT FIRST_NAME, LAST_NAME FROM employee WHERE EMPLOYEE_ID = '$employee_id'";
    $check_result = mysqli_query($db, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $employee_data = mysqli_fetch_assoc($check_result);
        $employee_name = $employee_data['FIRST_NAME'] . ' ' . $employee_data['LAST_NAME'];
        
        // Delete user accounts first
        $delete_users_query = "DELETE FROM users WHERE EMPLOYEE_ID = '$employee_id'";
        $delete_users_result = mysqli_query($db, $delete_users_query);
        
        // Then delete employee
        $delete_employee_query = "DELETE FROM employee WHERE EMPLOYEE_ID = '$employee_id'";
        $delete_employee_result = mysqli_query($db, $delete_employee_query);
        
        if ($delete_employee_result) {
?>
            <script type="text/javascript">
                alert("Employee '<?php echo $employee_name; ?>' and user accounts successfully deleted.");
                window.location = "employee.php";
            </script>
<?php
        } else {
?>
            <script type="text/javascript">
                alert("Error deleting employee: <?php echo mysqli_error($db); ?>");
                window.location = "employee.php";
            </script>
<?php
        }
    } else {
?>
        <script type="text/javascript">
            alert("Employee not found!");
            window.location = "employee.php";
        </script>
<?php
    }
} else {
?>
    <script type="text/javascript">
        alert("Invalid request parameters!");
        window.location = "employee.php";
    </script>
<?php
}
?>