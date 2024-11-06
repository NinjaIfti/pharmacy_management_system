<?php
require_once '../includes/db_connect.php';
session_start();

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: " . $base_url . "login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: " . $base_url . "invoices/invoices.php");
    exit();
}

$invoice_id = $_GET['id'];

// Fetch suppliers for dropdown
$suppliers = $pdo->query("SELECT supplier_id, name FROM SUPPLIER ORDER BY name")->fetchAll();

// Fetch current invoice data
$stmt = $pdo->prepare("SELECT * FROM INVOICE WHERE invoice_id = ?");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    header("Location: " . $base_url . "invoices/invoices.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplier_id = $_POST['supplier_id'];
    $invoice_date = $_POST['invoice_date'];
    $total_amount = $_POST['total_amount'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("
            UPDATE INVOICE 
            SET supplier_id = ?, invoice_date = ?, total_amount = ?, status = ? 
            WHERE invoice_id = ?
        ");
        
        $stmt->execute([
            $supplier_id,
            $invoice_date,
            $total_amount,
            $status,
            $invoice_id
        ]);
        
        $_SESSION['success'] = "Invoice updated successfully";
        header("Location: " . $base_url . "invoices/invoices.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating invoice: " . $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-lg mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Edit Invoice</h1>
            <a href="<?php echo $base_url; ?>invoices/invoices.php" class="text-blue-500 hover:text-blue-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Invoices
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <form action="" method="POST">
                <div class="mb-4">
                    <label for="supplier_id" class="block text-gray-700 text-sm font-bold mb-2">Supplier *</label>
                    <select name="supplier_id" id="supplier_id" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Select Supplier</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo $supplier['supplier_id']; ?>"
                                    <?php echo $supplier['supplier_id'] == $invoice['supplier_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($supplier['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="invoice_date" class="block text-gray-700 text-sm font-bold mb-2">Invoice Date *</label>
                    <input type="date" name="invoice_date" id="invoice_date" required
                           value="<?php echo $invoice['invoice_date']; ?>"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label for="total_amount" class="block text-gray-700 text-sm font-bold mb-2">Total Amount (৳) *</label>
                    <input type="number" step="0.01" name="total_amount" id="total_amount" required
                           value="<?php echo $invoice['total_amount']; ?>"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-6">
                    <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status *</label>
                    <select name="status" id="status" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="Pending" <?php echo $invoice['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Paid" <?php echo $invoice['status'] == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="Cancelled" <?php echo $invoice['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Update Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 