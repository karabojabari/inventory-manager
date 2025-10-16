<?php
include '../includes/connection.php';
include '../includes/sidebar.php';

// Check user permissions securely
$query = 'SELECT ID, t.TYPE FROM users u 
          JOIN type t ON t.TYPE_ID = u.TYPE_ID 
          WHERE ID = ?';
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['MEMBER_ID']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $userType = $row['TYPE'];
    
    if ($userType == 'User') {
        echo '<script type="text/javascript">
                alert("Restricted Page! You will be redirected to POS");
                window.location = "pos.php";
              </script>';
        exit();
    }
}

// Get jobs for dropdown
$sql = "SELECT DISTINCT JOB_TITLE, JOB_ID FROM job ORDER BY JOB_ID ASC";
$result = mysqli_query($db, $sql) or die ("Bad SQL: $sql");

$opt = "<select class='form-control' name='jobs' id='jobs'>
        <option value=''>Select Job</option>";
while ($row = mysqli_fetch_assoc($result)) {
    $opt .= "<option value='".$row['JOB_ID']."'>".$row['JOB_TITLE']."</option>";
}
$opt .= "</select>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee</title>
    <style>
        .card {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .btn-block {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Botswana districts and cities/towns data
    const botswanaData = {
        'Central': ['Palapye', 'Serowe', 'Mahalapye', 'Tutume', 'Selibe Phikwe', 'Bobonong', 'Tonota', 'Letlhakane'],
        'Ghanzi': ['Ghanzi', 'Charles Hill', 'Bere', 'D''Kar'],
        'Kgalagadi': ['Tsabong', 'Hukuntsi', 'Tshane', 'Kang', 'Lokgwabe', 'Khawa'],
        'Kgatleng': ['Mochudi', 'Lentsweletau', 'Oodi', 'Mmathubudukwane'],
        'Kweneng': ['Molepolole', 'Mogoditshane', 'Thamaga', 'Letlhakeng', 'Kopong'],
        'North West': ['Maun', 'Kasane', 'Gumare', 'Shakawe', 'Kazungula', 'Sepopa', 'Nata'],
        'North East': ['Francistown', 'Masunga', 'Tamasane', 'Tati Siding'],
        'South East': ['Gaborone', 'Ramotswa', 'Otse', 'Taung'],
        'Southern': ['Kanye', 'Moshupa', 'Lobatse', 'Jwaneng', 'Mmathethe', 'Good Hope']
    };

    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');

    // Function to populate provinces dropdown
    function populateProvinces() {
        provinceSelect.innerHTML = '<option value="">Select District</option>';
        for (const district in botswanaData) {
            provinceSelect.innerHTML += `<option value="${district}">${district} District</option>`;
        }
    }

    // Function to update cities based on selected district
    function updateCities(selectedDistrict) {
        citySelect.innerHTML = '<option value="">Select City/Town</option>';
        
        if (selectedDistrict && botswanaData[selectedDistrict]) {
            botswanaData[selectedDistrict].forEach(city => {
                citySelect.innerHTML += `<option value="${city}">${city}</option>`;
            });
        }
    }

    // Initialize the dropdowns
    populateProvinces();

    // Update cities when district changes
    provinceSelect.addEventListener('change', function() {
        const selectedDistrict = this.value;
        updateCities(selectedDistrict);
    });

    // Optional: If you want to pre-select values when editing, you can call updateCities with the stored value
    <?php if (isset($_POST['province'])): ?>
        const savedProvince = "<?php echo $_POST['province']; ?>";
        if (savedProvince) {
            provinceSelect.value = savedProvince;
            updateCities(savedProvince);
            <?php if (isset($_POST['city'])): ?>
                setTimeout(() => {
                    citySelect.value = "<?php echo $_POST['city']; ?>";
                }, 100);
            <?php endif; ?>
        }
    <?php endif; ?>
});
</script>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Add Employee</h4>
        </div>
        <div class="card-body">
            <a href="employee.php" type="button" class="btn btn-primary bg-gradient-primary mb-3">
                <i class="fa fa-arrow-left fa-fw"></i> Back to Employees
            </a>
            
            <div class="table-responsive">
                <form role="form" method="post" action="emp_transac.php?action=add" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="firstname" class="font-weight-bold text-primary">First Name *</label>
                        <input class="form-control" id="firstname" placeholder="Enter first name" name="firstname" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="lastname" class="font-weight-bold text-primary">Last Name *</label>
                        <input class="form-control" id="lastname" placeholder="Enter last name" name="lastname" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="font-weight-bold text-primary">Email Address *</label>
                        <input type="email" class="form-control" id="email" placeholder="Enter email address" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phonenumber" class="font-weight-bold text-primary">Phone Number *</label>
                        <input type="tel" class="form-control" id="phonenumber" placeholder="Enter phone number" name="phonenumber" 
                               pattern="[0-9+]{10,15}" title="Please enter a valid phone number" required>
                        <small class="form-text text-muted">Format: 26771234567 or +26771234567</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="jobs" class="font-weight-bold text-primary">Job Title *</label>
                        <?php echo $opt; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="hireddate" class="font-weight-bold text-primary">Hire Date *</label>
                        <input type="date" id="hireddate" name="hireddate" class="form-control" 
                               max="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="province" class="font-weight-bold text-primary">District *</label>
                        <select class="form-control" id="province" name="province" required>
                            <option value="">Select District</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="city" class="font-weight-bold text-primary">City/Town *</label>
                        <select class="form-control" id="city" name="city" required>
                            <option value="">Select City/Town</option>
                        </select>
                    </div>
                    
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fa fa-check fa-fw"></i> Save Employee
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="reset" class="btn btn-danger btn-block">
                                <i class="fa fa-times fa-fw"></i> Reset Form
                            </button>
                        </div>
                    </div>
                </form>  
            </div>
        </div>
    </div>
</center>

<script>
function validateForm() {
    // Basic form validation
    const firstName = document.getElementById('firstname').value;
    const lastName = document.getElementById('lastname').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phonenumber').value;
    const job = document.getElementById('jobs').value;
    const hireDate = document.getElementById('hireddate').value;
    const district = document.getElementById('province').value;
    const city = document.getElementById('city').value;
    
    // Check if all required fields are filled
    if (!firstName || !lastName || !email || !phone || !job || !hireDate || !district || !city) {
        alert('Please fill in all required fields.');
        return false;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        return false;
    }
    
    // Phone validation (Botswana format)
    const phoneRegex = /^(267|\+267)?[0-9]{8,9}$/;
    const cleanPhone = phone.replace(/\s+/g, '');
    if (!phoneRegex.test(cleanPhone)) {
        alert('Please enter a valid Botswana phone number (e.g., 26771234567 or +26771234567).');
        return false;
    }
    
    // Date validation - hire date shouldn't be in future
    const today = new Date();
    const selectedDate = new Date(hireDate);
    if (selectedDate > today) {
        alert('Hire date cannot be in the future.');
        return false;
    }
    
    return true;
}

// Set maximum date to today for hire date
document.getElementById('hireddate').max = new Date().toISOString().split('T')[0];
</script>

<?php
include '../includes/footer.php';
?>