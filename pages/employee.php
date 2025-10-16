<?php
include'../includes/connection.php';
include'../includes/sidebar.php';

$query = 'SELECT ID, t.TYPE
          FROM users u
          JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
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
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Employee&nbsp;<a href="#" data-toggle="modal" data-target="#employeeModal" type="button" class="btn btn-primary" style="background-color: #047857; border-color: #047857; border-radius: 0px;"><i class="fas fa-fw fa-plus" style="color: white;"></i></a></h4>
    </div>
    
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php                  
                    $query = 'SELECT EMPLOYEE_ID, FIRST_NAME, LAST_NAME, j.JOB_TITLE FROM employee e JOIN job j ON e.JOB_ID=j.JOB_ID';
                    $result = mysqli_query($db, $query) or die (mysqli_error($db));
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>'. $row['FIRST_NAME'].'</td>';
                        echo '<td>'. $row['LAST_NAME'].'</td>';
                        echo '<td>'. $row['JOB_TITLE'].'</td>';
                        echo '<td align="right"> 
                                <div class="btn-group">
                                    <!-- Details Button -->
                                    <a type="button" class="btn btn-primary" style="background-color: #047857; border-color: #047857; color: white;" 
                                       href="emp_searchfrm.php?action=edit & id='.$row['EMPLOYEE_ID'] . '"
                                       title="View Details">
                                        <i class="fas fa-fw fa-list-alt" style="color: white;"></i>
                                    </a>
                                    <!-- Edit Button -->
                                    <a type="button" class="btn btn-primary" style="background-color: #065f46; border-color: #065f46; color: white;" 
                                       href="emp_edit.php?action=edit & id='.$row['EMPLOYEE_ID']. '"
                                       title="Edit Employee">
                                        <i class="fas fa-fw fa-edit" style="color: white;"></i>
                                    </a>
                                    <!-- Delete Button - Dark Green Shade -->
                                    <a type="button" class="btn btn-primary" style="background-color: #064e3b; border-color: #064e3b; color: white;" 
                                       href="delete_employee.php?type=employee&id='.$row['EMPLOYEE_ID']. '" 
                                       onclick="return confirm(\'Are you sure you want to delete '.$row['FIRST_NAME'].' '.$row['LAST_NAME'].'?\\nThis action cannot be undone.\')"
                                       title="Delete Employee">
                                        <i class="fas fa-fw fa-trash" style="color: white;"></i>
                                    </a>
                                </div>
                            </td>';
                        echo '</tr> ';
                    }
                    ?> 
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include'../includes/footer.php';
?>