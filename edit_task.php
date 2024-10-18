<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';


// Fetch total task count
$sqlTotalTasks = "SELECT COUNT(*) AS total_tasks FROM tasks";
$resultTotalTasks = $conn->query($sqlTotalTasks);
$totalTasksRow = $resultTotalTasks->fetch_assoc();
$totalTasks = $totalTasksRow['total_tasks'];

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $task_id = $_GET['id'];
    $sql = "SELECT * FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();

    $stmt->close();
	
	
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $task_id = $_POST['id'];
    $title = $_POST['title'];
    $user_id = $_POST['user_id'];
    $status_id = $_POST['status_id'];
    $priority_id = $_POST['priority_id'];
    $due_date = $_POST['due_date'];

    $sql = "UPDATE tasks SET title = ?, user_id = ?, status_id = ?, priority_id = ?, due_date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiisi", $title, $user_id, $status_id, $priority_id, $due_date, $task_id);

    if ($stmt->execute()) {
        echo "Task updated successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Task Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" >
	<style>
	.sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
	</style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 sidebar bg-light">
                <h2>Welcome, <?php echo $_SESSION['user_name']; ?>!</h2>
                
                <button class="btn btn-danger" style="color:white">
				
				<a class="badge badge-info" href="dashboard.php">Back To Dashboard </a>
				
				
				</button>
				
               
            </div>
            <div class="col-md-9 main-content">
                <h1>Edit Task</h1>
                <form action="edit_task.php" method="post">
                    <input type="hidden" name="id" value="<?php echo $task['id']; ?>">

                    <label for="title" class="form-label">Task Title:</label>
                    <input type="text" id="title" name="title" value="<?php echo $task['title']; ?>" class="form-control" required><br>

                    <label for="user_id" class="form-label">Assigned To:</label>
                    <select id="user_id" name="user_id" class="form-select" required>
                        <?php
                        $result = $conn->query("SELECT id, name FROM users");
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($task['user_id'] == $row['id']) ? "selected" : "";
                            echo "<option value='" . $row['id'] . "' $selected>" . $row['name'] . "</option>";
                        }
                        ?>
                    </select><br>

                    <label for="status_id" class="form-label">Status:</label>
                    <select id="status_id" name="status_id" class="form-select" required>
                        <?php
                        $result = $conn->query("SELECT id, status FROM status");
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($task['status_id'] == $row['id']) ? "selected" : "";
                            echo "<option value='" . $row['id'] . "' $selected>" . $row['status'] . "</option>";
                        }
                        ?>
                    </select><br>

                    <label for="priority_id" class="form-label">Priority:</label>
                    <select id="priority_id" name="priority_id" class="form-select" required>
                        <?php
                        $result = $conn->query("SELECT id, priority FROM priority");
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($task['priority_id'] == $row['id']) ? "selected" : "";
                            echo "<option value='" . $row['id'] . "' $selected>" . $row['priority'] . "</option>";
                        }
                        ?>
                    </select><br>

                    <label for="due_date" class="form-label">Due Date:</label>
                    <input type="date" id="due_date" name="due_date" value="<?php echo $task['due_date']; ?>" class="form-control" required><br>

                    <button type="submit" class="btn btn-primary">Update Task</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" ></script>

    <script>
        // Function to open the modal
        function openModal() {
            document.getElementById('newTaskModal').style.display = 'block';
        }

        // Function to close the modal
        function closeModal() {
            document.getElementById('newTaskModal').style.display = 'none';
        }
    </script>
</body>
</html>
