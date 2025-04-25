<?php
// Session protection
require_once "check_session.php";

// DB connection
require_once "db_connect.php";

// Init vars
$student_id = $student_name = "";
$student_id_err = $student_name_err = "";
$action_status = "";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $action_status = "Invalid request";
    } else {
        $student_id = trim($_POST["student_id"]);
        $student_name = trim($_POST["student_name"]);

        // Create record
        if (isset($_POST["create"])) {
            if (empty($student_id)) {
                $student_id_err = "Please enter a student ID";
            } else {
                $sql = "SELECT id FROM test WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $student_id);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $student_id_err = "This student ID already exists";
                }
                $stmt->close();
            }

            if (empty($student_name)) {
                $student_name_err = "Please enter a name";
            }

            if (empty($student_id_err) && empty($student_name_err)) {
                $sql = "INSERT INTO test (id, name) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $student_id, $student_name);
                if ($stmt->execute()) {
                    $action_status = "Record created successfully";
                    $student_id = $student_name = "";
                } else {
                    $action_status = "Error creating record";
                }
                $stmt->close();
            }
        }

        // Update record
        if (isset($_POST["update"])) {
            if (empty($student_id)) $student_id_err = "Please enter student ID";
            if (empty($student_name)) $student_name_err = "Please enter student name";

            if (empty($student_id_err) && empty($student_name_err)) {
                $sql = "UPDATE test SET name = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $student_name, $student_id);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $action_status = "Record updated successfully";
                } else {
                    $action_status = "No matching ID found";
                }
                $stmt->close();
            }
        }

        // Delete record
        if (isset($_POST["delete"])) {
            if (empty($student_id)) {
                $student_id_err = "Please enter student ID";
            } else {
                $sql = "DELETE FROM test WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $student_id);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $action_status = "Record deleted successfully";
                    $student_id = $student_name = "";
                } else {
                    $action_status = "No matching ID found";
                }
                $stmt->close();
            }
        }

        // Search record
        if (isset($_POST["search"])) {
            if (empty($student_id)) {
                $student_id_err = "Please enter student ID";
            } else {
                $sql = "SELECT name FROM test WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $student_id);
                $stmt->execute();
                $stmt->bind_result($student_name);
                if ($stmt->fetch()) {
                    $action_status = "Record found";
                } else {
                    $action_status = "No record found";
                    $student_name = "";
                }
                $stmt->close();
            }
        }
    }
}

// Generate new CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$all_records = [];
$result = $conn->query("SELECT id, name FROM test ORDER BY id");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $all_records[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student Records</title>
    <link href="management-style.css" rel="stylesheet">
</head>
<body>
    <div class="management-container">
        <header class="management-header">
            <h1>Student Records</h1>
            <div class="user-info">
                <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong></span>
                <a href="logout.php" class="logout-button">Logout</a>
            </div>
        </header>

        <?php if (!empty($action_status)): ?>
            <div class="status-message <?php echo strpos($action_status, 'successfully') !== false ? 'success' : 'info'; ?>">
                <?php echo $action_status; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="record-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id" class="<?php echo !empty($student_id_err) ? 'input-error' : ''; ?>" value="<?php echo htmlspecialchars($student_id); ?>" maxlength="15">
                    <?php if (!empty($student_id_err)): ?>
                        <span class="error-message"><?php echo $student_id_err; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="student_name">Student Name</label>
                    <input type="text" id="student_name" name="student_name" class="<?php echo !empty($student_name_err) ? 'input-error' : ''; ?>" value="<?php echo htmlspecialchars($student_name); ?>" maxlength="50">
                    <?php if (!empty($student_name_err)): ?>
                        <span class="error-message"><?php echo $student_name_err; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="action-buttons">
                <button type="submit" name="create" class="action-button create">Create</button>
                <button type="submit" name="update" class="action-button update">Update</button>
                <button type="submit" name="search" class="action-button search">Search</button>
                <button type="submit" name="delete" class="action-button delete">Delete</button>
            </div>
        </form>

        <section class="records-section">
            <h2>All Records</h2>
            <div class="records-table-container">
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($all_records)): ?>
                            <?php foreach ($all_records as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['id']); ?></td>
                                    <td><?php echo htmlspecialchars($record['name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="no-records">No records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>
</html>