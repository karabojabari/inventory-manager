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
    //then it will be redirected
    alert("Restricted Page! You will be redirected to POS");
    window.location = "pos.php";
  </script>
<?php
  }           
} 
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Supplier&nbsp;<a href="#" data-toggle="modal" data-target="#supplierModal" type="button" class="btn btn-primary" style="background-color: #047857; border-color: #047857; border-radius: 0px;"><i class="fas fa-fw fa-plus" style="color: white;"></i></a></h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>District</th>
                        <th>City</th>
                        <th>Phone Number</th>
                        <th>Option</th>
                    </tr>
                </thead>
                <tbody>
                    <?php                  
                    $query = 'SELECT SUPPLIER_ID, COMPANY_NAME, l.PROVINCE, l.CITY, PHONE_NUMBER FROM supplier s join location l on s.LOCATION_ID=l.LOCATION_ID';
                    $result = mysqli_query($db, $query) or die (mysqli_error($db));
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>'. $row['COMPANY_NAME'].'</td>';
                        echo '<td>'. $row['PROVINCE'].'</td>';
                        echo '<td>'. $row['CITY'].'</td>';
                        echo '<td>'. $row['PHONE_NUMBER'].'</td>';
                        echo '<td align="right"> 
                                <div class="btn-group">
                                    <!-- Details Button -->
                                    <a type="button" class="btn btn-primary" style="background-color: #047857; border-color: #047857; color: white;" 
                                       href="sup_searchfrm.php?action=edit & id='.$row['SUPPLIER_ID'] . '"
                                       title="View Details">
                                        <i class="fas fa-fw fa-list-alt" style="color: white;"></i>
                                    </a>
                                    <!-- Edit Button -->
                                    <a type="button" class="btn btn-primary" style="background-color: #065f46; border-color: #065f46; color: white;" 
                                       href="sup_edit.php?action=edit & id='.$row['SUPPLIER_ID']. '"
                                       title="Edit Supplier">
                                        <i class="fas fa-fw fa-edit" style="color: white;"></i>
                                    </a>
                                    <!-- Delete Button - Dark Green Shade -->
                                    <a type="button" class="btn btn-primary" style="background-color: #064e3b; border-color: #064e3b; color: white;" 
                                       href="delete_supplier.php?type=supplier&id='.$row['SUPPLIER_ID']. '" 
                                       onclick="return confirm(\'Are you sure you want to delete '.$row['COMPANY_NAME'].'?\\nThis action cannot be undone.\')"
                                       title="Delete Supplier">
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

<!-- Supplier Modal-->
<div class="modal fade" id="supplierModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary" id="exampleModalLabel">Add Supplier</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="sup_transac.php?action=add">
                    <div class="form-group">
                        <input class="form-control" placeholder="Company Name" name="companyname" required>
                    </div>
                    <div class="form-group">
                        <select class="form-control" id="province" placeholder="Province" name="province" required></select>
                    </div>
                    <div class="form-group">
                        <select class="form-control" id="city" placeholder="City" name="city" required></select>
                    </div>
                    <div class="form-group">
                        <input class="form-control" placeholder="Phone Number" name="phonenumber" required>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Save</button>
                    <button type="reset" class="btn btn-danger"><i class="fa fa-times fa-fw"></i>Reset</button>
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>      
                </form>  
            </div>
        </div>
    </div>
</div>
<?php
include'../includes/footer.php';
?>