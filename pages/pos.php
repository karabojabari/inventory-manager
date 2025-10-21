<?php
include '../includes/connection.php';
include '../includes/topp.php';

// Check if user is logged in
if (!isset($_SESSION['FIRST_NAME'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

// Initialize cart session
if (!isset($_SESSION['pointofsale'])) {
    $_SESSION['pointofsale'] = [];
}

$product_ids = array();

// Handle Add to Cart - FIXED VERSION (no header redirects)
if(isset($_POST['addpos'])){
    $product_id = $_GET['id'] ?? null;
    $product_name = $_POST['name'] ?? '';
    $product_price = $_POST['price'] ?? 0;
    $product_quantity = $_POST['quantity'] ?? 1;
    
    if($product_id && $product_name && $product_price) {
        // Check if product already exists in cart
        $found = false;
        foreach($_SESSION['pointofsale'] as $key => $item) {
            if($item['id'] == $product_id) {
                $_SESSION['pointofsale'][$key]['quantity'] += $product_quantity;
                $found = true;
                break;
            }
        }
        
        // If not found, add new product
        if(!$found) {
            $_SESSION['pointofsale'][] = [
                'id' => $product_id,
                'name' => $product_name,
                'price' => $product_price,
                'quantity' => $product_quantity
            ];
        }
        
        // Use JavaScript redirect instead of header() to avoid header errors
        echo "<script>window.location.href = 'pos.php';</script>";
        exit();
    }
}

// Handle Delete from Cart - FIXED VERSION (no header redirects)
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])){
    foreach($_SESSION['pointofsale'] as $key => $product){
        if ($product['id'] == $_GET['id']){
            unset($_SESSION['pointofsale'][$key]);
        }
    }
    $_SESSION['pointofsale'] = array_values($_SESSION['pointofsale']);
    
    // Use JavaScript redirect instead of header() to avoid header errors
    echo "<script>window.location.href = 'pos.php';</script>";
    exit();
}

// Calculate totals
$total = 0;
$vat_amount = 0;
$grand_total = 0;

if(!empty($_SESSION['pointofsale'])) {
    foreach($_SESSION['pointofsale'] as $product) {
        $total += $product['quantity'] * $product['price'];
    }
    $vat_amount = $total * 0.12; // 12% VAT
    $grand_total = $total + $vat_amount;
}

// Get customers for dropdown
$sql = "SELECT CUST_ID, FIRST_NAME, LAST_NAME FROM customer ORDER BY FIRST_NAME ASC";
$res = mysqli_query($db, $sql);
$customer_options = "";
while ($row = mysqli_fetch_assoc($res)) {
    $customer_options .= "<option value='".$row['CUST_ID']."'>".$row['FIRST_NAME'].' '.$row['LAST_NAME']."</option>";
}

$today = date("Y-m-d H:i a");

// Get all products by category for our new design
$categories = [
    0 => 'Keyboard',
    1 => 'Mouse', 
    2 => 'Monitor',
    3 => 'Motherboard',
    4 => 'Processor',
    5 => 'Power Supply',
    6 => 'Headset',
    7 => 'CPU',
    9 => 'Others'
];

$products_by_category = [];
foreach ($categories as $cat_id => $cat_name) {
    $query = "SELECT * FROM product WHERE CATEGORY_ID = $cat_id AND QTY_STOCK > 0 GROUP BY PRODUCT_CODE ORDER BY PRODUCT_CODE ASC";
    $result = mysqli_query($db, $query);
    $products_by_category[$cat_id] = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while($product = mysqli_fetch_assoc($result)) {
            $products_by_category[$cat_id][] = $product;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System • Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
            --gray: #6C757D;
            --dark-gray: #343A40;
            --green: #28A745;
            --dark-green: #1E7E34;
            --border: #DEE2E6;
            --danger: #DC3545;
        }

        body {
            background: var(--white);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--dark-gray);
            min-height: 100vh;
        }

        .header-section {
            background: linear-gradient(135deg, var(--white) 0%, var(--light-gray) 100%);
            border-bottom: 1px solid var(--border);
            border-radius: 12px;
        }

        .card-custom {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .product-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            border-color: var(--green);
        }

        .product-image {
            height: 120px;
            background: linear-gradient(135deg, var(--light-gray) 0%, var(--white) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--green);
            font-size: 2.5rem;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .category-btn {
            background: var(--white);
            border: 1px solid var(--border);
            color: var(--dark-gray);
            border-radius: 25px;
            padding: 10px 24px;
            margin: 5px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .category-btn.active,
        .category-btn:hover {
            background: var(--green);
            border-color: var(--green);
            color: var(--white);
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--green);
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--dark-green);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
        }

        .text-currency {
            color: var(--green);
            font-weight: 600;
        }

        .cart-item {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            border-color: var(--green);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .total-section {
            background: var(--light-gray);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border);
        }

        .badge-cart {
            background: var(--green);
            color: var(--white);
            border-radius: 20px;
            padding: 8px 16px;
        }

        .badge-stock-low {
            background: var(--danger);
            color: var(--white);
        }

        .badge-stock-ok {
            background: var(--green);
            color: var(--white);
        }

        .quantity-input {
            border-radius: 8px;
            border: 1px solid var(--border);
            padding: 8px 12px;
            text-align: center;
            width: 80px;
        }

        .stats-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .cart-container {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .cart-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .cart-container::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: 3px;
        }
        
        .cart-container::-webkit-scrollbar-thumb {
            background: var(--green);
            border-radius: 3px;
        }
        
        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--green);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
        }

        .error-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--danger);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
        }

        .balance-positive {
            color: var(--green);
            font-weight: bold;
        }

        .balance-negative {
            color: var(--danger);
            font-weight: bold;
        }

        .cash-input-section {
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .cash-input-section.insufficient {
            border-color: var(--danger);
            background-color: rgba(220, 53, 69, 0.05);
        }
    </style>
</head>
<body>
    <!-- Success Message -->
    <div class="success-message" id="successMessage">
        <i class="fas fa-check-circle me-2"></i> Product added to cart!
    </div>

    <!-- Error Message -->
    <div class="error-message" id="errorMessage">
        <i class="fas fa-exclamation-triangle me-2"></i> <span id="errorText"></span>
    </div>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="header-section p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h1 class="h3 mb-2 text-dark">
                                <i class="fas fa-cash-register me-3 text-success"></i>iMS-POS
                            </h1>
                            <p class="text-muted mb-0">Point of Sale System • Welcome, <?php echo $_SESSION['FIRST_NAME']; ?> (<?php echo $_SESSION['JOB_TITLE']; ?>)</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="d-flex justify-content-md-end gap-4">
                                <div class="stats-card">
                                    <div class="fs-4 fw-bold text-currency">P <?php echo number_format($grand_total, 2); ?></div>
                                    <small class="text-muted">Grand Total</small>
                                </div>
                                <div class="stats-card">
                                    <div class="fs-4 fw-bold text-dark"><?php echo count($_SESSION['pointofsale']); ?></div>
                                    <small class="text-muted">Items in Cart</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Products Section -->
            <div class="col-lg-8">
                <div class="card-custom p-4 h-100">
                    <!-- Search and Categories -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="productSearch" placeholder="Search products...">
                            </div>
                        </div>
                    </div>

                    <!-- Category Filters -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-3">PRODUCT CATEGORIES</label>
                        <div class="d-flex flex-wrap" id="categoryFilters">
                            <button class="btn category-btn active" data-category="all">All Products</button>
                            <?php foreach ($categories as $cat_id => $cat_name): ?>
                                <button class="btn category-btn" data-category="<?php echo $cat_id; ?>"><?php echo $cat_name; ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="product-grid" id="productsGrid">
                        <?php foreach ($categories as $cat_id => $cat_name): ?>
                            <?php if (!empty($products_by_category[$cat_id])): ?>
                                <?php foreach ($products_by_category[$cat_id] as $product): ?>
                                    <div class="product-card" data-category="<?php echo $cat_id; ?>">
                                        <div class="product-image">
                                            <?php 
                                            $icons = [
                                                0 => 'fa-keyboard',
                                                1 => 'fa-mouse',
                                                2 => 'fa-desktop',
                                                3 => 'fa-microchip',
                                                4 => 'fa-microchip',
                                                5 => 'fa-plug',
                                                6 => 'fa-headphones',
                                                7 => 'fa-laptop',
                                                9 => 'fa-cube'
                                            ];
                                            $icon = $icons[$cat_id] ?? 'fa-cube';
                                            ?>
                                            <i class="fas <?php echo $icon; ?>"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2"><?php echo $product['NAME']; ?></h6>
                                        <p class="text-muted small mb-2"><?php echo substr($product['DESCRIPTION'], 0, 60); ?>...</p>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="text-currency fw-bold fs-5">P <?php echo number_format($product['PRICE'], 2); ?></span>
                                            <span class="badge <?php echo $product['QTY_STOCK'] < 10 ? 'badge-stock-low' : 'badge-stock-ok'; ?>">
                                                Stock: <?php echo $product['QTY_STOCK']; ?>
                                            </span>
                                        </div>
                                        <!-- FIXED FORM - Proper action and method -->
                                        <form method="post" action="pos.php?action=add&id=<?php echo $product['PRODUCT_ID']; ?>">
                                            <div class="d-flex gap-2">
                                                <input type="number" name="quantity" class="form-control quantity-input" value="1" min="1" max="<?php echo $product['QTY_STOCK']; ?>">
                                                <input type="hidden" name="name" value="<?php echo $product['NAME']; ?>">
                                                <input type="hidden" name="price" value="<?php echo $product['PRICE']; ?>">
                                                <button type="submit" name="addpos" class="btn btn-primary flex-grow-1">
                                                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Cart Section -->
            <div class="col-lg-4">
                <div class="card-custom p-4 sticky-top" style="top: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0 text-dark">
                            <i class="fas fa-shopping-cart me-2 text-success"></i>Shopping Cart
                        </h4>
                        <span class="badge badge-cart fs-6"><?php echo count($_SESSION['pointofsale']); ?> items</span>
                    </div>

                    <!-- Cart Items -->
                    <div class="cart-container mb-4">
                        <?php if(!empty($_SESSION['pointofsale'])): ?>
                            <?php foreach($_SESSION['pointofsale'] as $key => $product): ?>
                                <div class="cart-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2 fw-bold text-dark"><?php echo $product['name']; ?></h6>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="text-muted">Qty: <?php echo $product['quantity']; ?></span>
                                                <small class="text-muted ms-3">@ P <?php echo number_format($product['price'], 2); ?></small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-currency fs-6">P <?php echo number_format($product['quantity'] * $product['price'], 2); ?></div>
                                            <a href="pos.php?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger mt-2">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-shopping-cart fa-4x mb-3 opacity-25"></i>
                                <h5>Your cart is empty</h5>
                                <p class="small">Add products to start a sale</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Checkout Form -->
                    <?php if(!empty($_SESSION['pointofsale'])): ?>
                    <form role="form" method="post" action="pos_transac.php?action=add" id="checkoutForm" onsubmit="return validatePayment()">
                        <input type="hidden" name="employee" value="<?php echo $_SESSION['FIRST_NAME']; ?>">
                        <input type="hidden" name="role" value="<?php echo $_SESSION['JOB_TITLE']; ?>">
                        <input type="hidden" name="date" value="<?php echo $today; ?>">
                        
                        <!-- Hidden product data -->
                        <?php foreach($_SESSION['pointofsale'] as $key => $product): ?>
                            <input type="hidden" name="name[]" value="<?php echo $product['name']; ?>">
                            <input type="hidden" name="quantity[]" value="<?php echo $product['quantity']; ?>">
                            <input type="hidden" name="price[]" value="<?php echo $product['price']; ?>">
                           <input type="hidden" name="product_id[]" value="<?php echo $product['id']; ?>">
                            <?php endforeach; ?>

                        <!-- Customer Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">SELECT CUSTOMER</label>
                            <select class="form-control" name="customer" required>
                                <option value="" disabled selected hidden>Select Customer</option>
                                <?php echo $customer_options; ?>
                            </select>
                        </div>

                        <!-- Totals Section -->
                        <div class="total-section mb-4">
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Subtotal:</div>
                                <div class="col-6 text-end">
                                    <input type="hidden" name="subtotal" value="<?php echo $total; ?>">
                                    <span class="fw-bold text-dark">P <?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-muted">VAT (12%):</div>
                                <div class="col-6 text-end">
                                    <input type="hidden" name="vat_amount" value="<?php echo $vat_amount; ?>">
                                    <span class="fw-bold text-dark">P <?php echo number_format($vat_amount, 2); ?></span>
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-6 fw-bold fs-5 text-dark">Grand Total:</div>
                                <div class="col-6 text-end">
                                    <input type="hidden" name="total" value="<?php echo $grand_total; ?>">
                                    <span class="fw-bold text-currency fs-4">P <?php echo number_format($grand_total, 2); ?></span>
                                </div>
                            </div>

                            <!-- Cash Input Section -->
                            <div class="cash-input-section" id="cashInputSection">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">CASH RECEIVED</label>
                                    <input type="number" class="form-control" name="cash" id="cashReceived" step="0.01" placeholder="0.00" required oninput="calculateBalance()">
                                </div>
                                
                                <!-- Balance Display -->
                                <div id="balanceSection" style="display: none;">
                                    <div class="row">
                                        <div class="col-6 text-muted">Balance:</div>
                                        <div class="col-6 text-end">
                                            <span class="fw-bold" id="balanceAmount">P 0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-credit-card me-2"></i>PROCESS PAYMENT
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Category filtering
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const category = e.target.dataset.category;
                
                // Update active state
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                
                // Show/hide products based on category
                document.querySelectorAll('.product-card').forEach(card => {
                    if (category === 'all' || card.dataset.category === category) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Search functionality
        document.getElementById('productSearch').addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                const productName = card.querySelector('h6').textContent.toLowerCase();
                if (productName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Calculate balance function
        function calculateBalance() {
            const cashReceived = parseFloat(document.getElementById('cashReceived').value) || 0;
            const grandTotal = <?php echo $grand_total; ?>;
            const balanceSection = document.getElementById('balanceSection');
            const balanceAmount = document.getElementById('balanceAmount');
            const cashInputSection = document.getElementById('cashInputSection');
            const submitBtn = document.getElementById('submitBtn');

            if (cashReceived > 0) {
                const balance = cashReceived - grandTotal;
                balanceSection.style.display = 'block';
                
                if (balance >= 0) {
                    // Sufficient cash
                    balanceAmount.textContent = 'P ' + Math.abs(balance).toFixed(2);
                    balanceAmount.className = 'fw-bold balance-positive';
                    cashInputSection.classList.remove('insufficient');
                    submitBtn.disabled = false;
                } else {
                    // Insufficient cash
                    balanceAmount.textContent = 'P ' + Math.abs(balance).toFixed(2) + ' needed';
                    balanceAmount.className = 'fw-bold balance-negative';
                    cashInputSection.classList.add('insufficient');
                    submitBtn.disabled = true;
                }
            } else {
                balanceSection.style.display = 'none';
                cashInputSection.classList.remove('insufficient');
                submitBtn.disabled = false;
            }
        }

        // Validate payment before submission
        function validatePayment() {
            const cashReceived = parseFloat(document.getElementById('cashReceived').value) || 0;
            const grandTotal = <?php echo $grand_total; ?>;
            
            if (cashReceived < grandTotal) {
                showError('Insufficient cash! Please enter more cash.');
                return false;
            }
            return true;
        }

        // Show error message
        function showError(message) {
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            errorText.textContent = message;
            errorMessage.style.display = 'block';
            setTimeout(() => {
                errorMessage.style.display = 'none';
            }, 5000);
        }

        // Show success message if product was just added
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'add') {
            setTimeout(() => {
                const message = document.getElementById('successMessage');
                message.style.display = 'block';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 3000);
            }, 500);
        }
    </script>
</body>
</html>

<?php
include '../includes/footer.php';
?>