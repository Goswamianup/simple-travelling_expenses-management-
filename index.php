<?php
// config/database.php
class Database {
    private $host = "localhost";
    private $db_name = "expense_management";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// setup.php - Run this once to create database and tables
if (isset($_GET['setup'])) {
    try {
        $pdo = new PDO("mysql:host=localhost", "root", "");
        $pdo->exec("CREATE DATABASE IF NOT EXISTS expense_management");
        $pdo->exec("USE expense_management");
        
        $createTable = "
        CREATE TABLE IF NOT EXISTS monthly_expenses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_name VARCHAR(100) NOT NULL,
            month VARCHAR(20) NOT NULL,
            date_created DATE NOT NULL,
            year INT NOT NULL,
            from_location VARCHAR(100),
            destination VARCHAR(100),
            client_name VARCHAR(100),
            purpose VARCHAR(200),
            mode_of_journey VARCHAR(50),
            fare DECIMAL(10,2),
            return_from VARCHAR(100),
            return_destination VARCHAR(100),
            return_mode VARCHAR(50),
            return_fare DECIMAL(10,2),
            total_amount DECIMAL(10,2),
            status ENUM('pending', 'verified', 'approved', 'final_approved') DEFAULT 'pending',
            signature VARCHAR(100),
            verified_by VARCHAR(100),
            approved_by VARCHAR(100),
            final_approved_by VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($createTable);
        echo "<div class='alert alert-success'>Database and table created successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// Function definitions
function showHome() {
    echo '
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-home"></i> Monthly Expense Management System</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                                    <h5>Add New Expense</h5>
                                    <p>Create a new monthly expense statement</p>
                                    <a href="?page=add" class="btn btn-primary">Add Expense</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-list fa-3x text-success mb-3"></i>
                                    <h5>View Expenses</h5>
                                    <p>View and manage all expense records</p>
                                    <a href="?page=view" class="btn btn-success">View All</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-chart-bar fa-3x text-info mb-3"></i>
                                    <h5>Statistics</h5>
                                    <p>View expense statistics and reports</p>
                                    <a href="#" class="btn btn-info">Coming Soon</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>';
}

function showAddExpense() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO monthly_expenses SET
                    employee_name=:employee_name, month=:month, date_created=:date_created, year=:year,
                    from_location=:from_location, destination=:destination, client_name=:client_name,
                    purpose=:purpose, mode_of_journey=:mode_of_journey, fare=:fare,
                    return_from=:return_from, return_destination=:return_destination,
                    return_mode=:return_mode, return_fare=:return_fare, total_amount=:total_amount";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":employee_name", $_POST['employee_name']);
        $stmt->bindParam(":month", $_POST['month']);
        $stmt->bindParam(":date_created", $_POST['date_created']);
        $stmt->bindParam(":year", $_POST['year']);
        $stmt->bindParam(":from_location", $_POST['from_location']);
        $stmt->bindParam(":destination", $_POST['destination']);
        $stmt->bindParam(":client_name", $_POST['client_name']);
        $stmt->bindParam(":purpose", $_POST['purpose']);
        $stmt->bindParam(":mode_of_journey", $_POST['mode_of_journey']);
        $stmt->bindParam(":fare", $_POST['fare']);
        $stmt->bindParam(":return_from", $_POST['return_from']);
        $stmt->bindParam(":return_destination", $_POST['return_destination']);
        $stmt->bindParam(":return_mode", $_POST['return_mode']);
        $stmt->bindParam(":return_fare", $_POST['return_fare']);
        $stmt->bindParam(":total_amount", $_POST['total_amount']);
        
        if($stmt->execute()) {
            echo '<div class="alert alert-success">Expense record created successfully!</div>';
        } else {
            echo '<div class="alert alert-danger">Unable to create expense record.</div>';
        }
    }
    
    echo '
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-plus"></i> Add New Monthly Expense</h4>
        </div>
        <div class="card-body">
            <form method="POST" class="expense-form">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Employee Name</label>
                        <input type="text" class="form-control" name="employee_name" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Month</label>
                        <select class="form-control" name="month" required>
                            <option value="">Select Month</option>
                            <option value="January">January</option>
                            <option value="February">February</option>
                            <option value="March">March</option>
                            <option value="April">April</option>
                            <option value="May">May</option>
                            <option value="June">June</option>
                            <option value="July">July</option>
                            <option value="August">August</option>
                            <option value="September">September</option>
                            <option value="October">October</option>
                            <option value="November">November</option>
                            <option value="December">December</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Year</label>
                        <input type="number" class="form-control" name="year" value="' . date('Y') . '" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date_created" value="' . date('Y-m-d') . '" required>
                    </div>
                </div>
                
                <h5 class="mt-4">Journey Details</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">From</label>
                        <input type="text" class="form-control" name="from_location">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Destination</label>
                        <input type="text" class="form-control" name="destination">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Client Name</label>
                        <input type="text" class="form-control" name="client_name">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Purpose</label>
                        <input type="text" class="form-control" name="purpose">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mode of Journey</label>
                        <select class="form-control" name="mode_of_journey">
                            <option value="">Select Mode</option>
                            <option value="Bus">Bus</option>
                            <option value="Train">Train</option>
                            <option value="Flight">Flight</option>
                            <option value="Car">Car</option>
                            <option value="Taxi">Taxi</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fare (Rs.)</label>
                        <input type="number" step="0.01" class="form-control" name="fare" id="fare" oninput="calculateTotal()">
                    </div>
                </div>
                
                <h5 class="mt-4">Return Journey</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Return From</label>
                        <input type="text" class="form-control" name="return_from">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Return Destination</label>
                        <input type="text" class="form-control" name="return_destination">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Return Mode</label>
                        <select class="form-control" name="return_mode">
                            <option value="">Select Mode</option>
                            <option value="Bus">Bus</option>
                            <option value="Train">Train</option>
                            <option value="Flight">Flight</option>
                            <option value="Car">Car</option>
                            <option value="Taxi">Taxi</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Return Fare (Rs.)</label>
                        <input type="number" step="0.01" class="form-control" name="return_fare" id="return_fare" oninput="calculateTotal()">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><strong>Total Amount (Rs.)</strong></label>
                        <input type="number" step="0.01" class="form-control" name="total_amount" id="total_amount" readonly>
                    </div>
                </div>
                
                <div class="signature-section">
                    <h6>Approval Section</h6>
                    <p class="text-muted">This will be handled in the workflow management</p>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Expense Record
                </button>
            </form>
        </div>
    </div>';
}

function showViewExpenses() {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM monthly_expenses ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    echo '
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-list"></i> All Monthly Expenses</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Month/Year</th>
                            <th>From → To</th>
                            <th>Client</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $statusClass = '';
        switch($row['status']) {
            case 'pending': $statusClass = 'badge bg-warning'; break;
            case 'verified': $statusClass = 'badge bg-info'; break;
            case 'approved': $statusClass = 'badge bg-success'; break;
            case 'final_approved': $statusClass = 'badge bg-primary'; break;
        }
        
        echo '<tr>
                <td>' . $row['id'] . '</td>
                <td>' . htmlspecialchars($row['employee_name']) . '</td>
                <td>' . $row['month'] . ' ' . $row['year'] . '</td>
                <td>' . htmlspecialchars($row['from_location']) . ' → ' . htmlspecialchars($row['destination']) . '</td>
                <td>' . htmlspecialchars($row['client_name']) . '</td>
                <td>Rs. ' . number_format($row['total_amount'], 2) . '</td>
                <td><span class="' . $statusClass . '">' . ucfirst($row['status']) . '</span></td>
                <td>
                    <a href="?page=edit&id=' . $row['id'] . '" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteExpense(' . $row['id'] . ')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>';
    }
    
    echo '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        function deleteExpense(id) {
            if(confirm("Are you sure you want to delete this expense record?")) {
                window.location.href = "?page=delete&id=" + id;
            }
        }
    </script>';
}

function showEditExpense() {
    if (!isset($_GET['id'])) {
        echo '<div class="alert alert-danger">No expense ID provided.</div>';
        return;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $query = "UPDATE monthly_expenses SET
                    employee_name=:employee_name, month=:month, date_created=:date_created, year=:year,
                    from_location=:from_location, destination=:destination, client_name=:client_name,
                    purpose=:purpose, mode_of_journey=:mode_of_journey, fare=:fare,
                    return_from=:return_from, return_destination=:return_destination,
                    return_mode=:return_mode, return_fare=:return_fare, total_amount=:total_amount
                  WHERE id=:id";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":employee_name", $_POST['employee_name']);
        $stmt->bindParam(":month", $_POST['month']);
        $stmt->bindParam(":date_created", $_POST['date_created']);
        $stmt->bindParam(":year", $_POST['year']);
        $stmt->bindParam(":from_location", $_POST['from_location']);
        $stmt->bindParam(":destination", $_POST['destination']);
        $stmt->bindParam(":client_name", $_POST['client_name']);
        $stmt->bindParam(":purpose", $_POST['purpose']);
        $stmt->bindParam(":mode_of_journey", $_POST['mode_of_journey']);
        $stmt->bindParam(":fare", $_POST['fare']);
        $stmt->bindParam(":return_from", $_POST['return_from']);
        $stmt->bindParam(":return_destination", $_POST['return_destination']);
        $stmt->bindParam(":return_mode", $_POST['return_mode']);
        $stmt->bindParam(":return_fare", $_POST['return_fare']);
        $stmt->bindParam(":total_amount", $_POST['total_amount']);
        $stmt->bindParam(":id", $_GET['id']);
        
        if($stmt->execute()) {
            echo '<div class="alert alert-success">Expense record updated successfully!</div>';
        } else {
            echo '<div class="alert alert-danger">Unable to update expense record.</div>';
        }
    }
    
    // Get expense data
    $query = "SELECT * FROM monthly_expenses WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $_GET['id']);
    $stmt->execute();
    $expense = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$expense) {
        echo '<div class="alert alert-danger">Expense record not found.</div>';
        return;
    }
    
    echo '
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-edit"></i> Edit Monthly Expense</h4>
        </div>
        <div class="card-body">
            <form method="POST" class="expense-form">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Employee Name</label>
                        <input type="text" class="form-control" name="employee_name" value="' . htmlspecialchars($expense['employee_name']) . '" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Month</label>
                        <select class="form-control" name="month" required>
                            <option value="">Select Month</option>';
    
    $months = ['January', 'February', 'March', 'April', 'May', 'June', 
               'July', 'August', 'September', 'October', 'November', 'December'];
    
    foreach ($months as $month) {
        $selected = ($expense['month'] == $month) ? 'selected' : '';
        echo '<option value="' . $month . '" ' . $selected . '>' . $month . '</option>';
    }
    
    echo '
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Year</label>
                        <input type="number" class="form-control" name="year" value="' . $expense['year'] . '" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date_created" value="' . $expense['date_created'] . '" required>
                    </div>
                </div>
                
                <h5 class="mt-4">Journey Details</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">From</label>
                        <input type="text" class="form-control" name="from_location" value="' . htmlspecialchars($expense['from_location']) . '">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Destination</label>
                        <input type="text" class="form-control" name="destination" value="' . htmlspecialchars($expense['destination']) . '">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Client Name</label>
                        <input type="text" class="form-control" name="client_name" value="' . htmlspecialchars($expense['client_name']) . '">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Purpose</label>
                        <input type="text" class="form-control" name="purpose" value="' . htmlspecialchars($expense['purpose']) . '">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mode of Journey</label>
                        <select class="form-control" name="mode_of_journey">
                            <option value="">Select Mode</option>';
    
    $modes = ['Bus', 'Train', 'Flight', 'Car', 'Taxi', 'Other'];
    foreach ($modes as $mode) {
        $selected = ($expense['mode_of_journey'] == $mode) ? 'selected' : '';
        echo '<option value="' . $mode . '" ' . $selected . '>' . $mode . '</option>';
    }
    
    echo '
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fare (Rs.)</label>
                        <input type="number" step="0.01" class="form-control" name="fare" id="fare" value="' . $expense['fare'] . '" oninput="calculateTotal()">
                    </div>
                </div>
                
                <h5 class="mt-4">Return Journey</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Return From</label>
                        <input type="text" class="form-control" name="return_from" value="' . htmlspecialchars($expense['return_from']) . '">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Return Destination</label>
                        <input type="text" class="form-control" name="return_destination" value="' . htmlspecialchars($expense['return_destination']) . '">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Return Mode</label>
                        <select class="form-control" name="return_mode">
                            <option value="">Select Mode</option>';
    
    foreach ($modes as $mode) {
        $selected = ($expense['return_mode'] == $mode) ? 'selected' : '';
        echo '<option value="' . $mode . '" ' . $selected . '>' . $mode . '</option>';
    }
    
    echo '
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Return Fare (Rs.)</label>
                        <input type="number" step="0.01" class="form-control" name="return_fare" id="return_fare" value="' . $expense['return_fare'] . '" oninput="calculateTotal()">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><strong>Total Amount (Rs.)</strong></label>
                        <input type="number" step="0.01" class="form-control" name="total_amount" id="total_amount" value="' . $expense['total_amount'] . '" readonly>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Expense Record
                </button>
                <a href="?page=view" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </form>
        </div>
    </div>';
}

// Handle delete functionality
if (isset($_GET['page']) && $_GET['page'] == 'delete' && isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "DELETE FROM monthly_expenses WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $_GET['id']);
    
    if ($stmt->execute()) {
        header("Location: ?page=view&msg=deleted");
        exit;
    } else {
        echo '<div class="alert alert-danger">Unable to delete expense record.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Expense Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .expense-form {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .signature-section {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="?page=home">
                <i class="fas fa-calculator"></i> Expense Management System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="?page=home">Home</a>
                <a class="nav-link" href="?page=add">Add Expense</a>
                <a class="nav-link" href="?page=view">View Expenses</a>
                <a class="nav-link" href="?setup=1">Setup DB</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php
        // Display success message if redirected from delete
        if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
            echo '<div class="alert alert-success">Expense record deleted successfully!</div>';
        }
        
        $page = $_GET['page'] ?? 'home';
        
        switch($page) {
            case 'add':
                showAddExpense();
                break;
            case 'view':
                showViewExpenses();
                break;
            case 'edit':
                showEditExpense();
                break;
            case 'home':
            default:
                showHome();
                break;
        }
        ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        function calculateTotal() {
            const fare = parseFloat(document.getElementById('fare').value) || 0;
            const returnFare = parseFloat(document.getElementById('return_fare').value) || 0;
            const total = fare + returnFare;
            document.getElementById('total_amount').value = total.toFixed(2);
        }
    </script>
</body>
</html>