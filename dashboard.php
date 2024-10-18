<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';


// Execute the SQL query to fetch priority counts
$sqlPriorityCounts = "SELECT 
                        COUNT(CASE WHEN priority.priority = 'High' THEN 1 END) AS high_priority_count,
                        COUNT(CASE WHEN priority.priority = 'Medium' THEN 1 END) AS medium_priority_count,
                        COUNT(CASE WHEN priority.priority = 'Low' THEN 1 END) AS low_priority_count
                    FROM tasks 
                    JOIN priority ON tasks.priority_id = priority.id";
$resultPriorityCounts = $conn->query($sqlPriorityCounts);
$rowPriorityCounts = $resultPriorityCounts->fetch_assoc();





// Fetch total task count
$sqlTotalTasks = "SELECT COUNT(*) AS total_tasks FROM tasks";
$resultTotalTasks = $conn->query($sqlTotalTasks);
$totalTasksRow = $resultTotalTasks->fetch_assoc();
$totalTasks = $totalTasksRow['total_tasks'];

// Pagination logic
$tasksPerPage = 10;
$totalPages = ceil($totalTasks / $tasksPerPage);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $tasksPerPage;

$sql = "SELECT 
            tasks.id AS task_id, 
            tasks.title AS task_name, 
            users.name AS user_name, 
            status.status AS status, 
            priority.priority AS priority, 
            tasks.due_date AS deadline 
        FROM tasks 
        JOIN users ON tasks.user_id = users.id 
        JOIN status ON tasks.status_id = status.id 
        JOIN priority ON tasks.priority_id = priority.id
        LIMIT $start, $tasksPerPage";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
	body {
            display: flex;
        }
        .sidebar {
            width: 250px;
            padding: 20px;
            background-color: #f4f4f4;
            height: 100vh;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #f7f7f7;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .pagination {
            margin: 20px 0;
        }
        .pagination a {
            margin: 0 5px;
            padding: 8px 16px;
            text-decoration: none;
            background-color: #f2f2f2;
            color: black;
            border: 1px solid #ddd;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
        }
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
        #newTaskModal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
		.search{
			
			margin:10px;
		}
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
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="sidebar bg-light p-3">
    <h2>Welcome, <?php echo $_SESSION['user_name']; ?>!</h2>
    <a href="logout.php" class="btn btn-danger">Logout</a> <br>
    <button onclick="openModal()" class="btn btn-primary mt-3">New Task</button>
    <p class="mt-3">Total Tasks: <?php echo $totalTasks; ?></p>
    <p>Priority:</p>
    <ul class="list-unstyled">
        <li>High: <?php echo $rowPriorityCounts['high_priority_count']; ?></li>
        <li>Medium: <?php echo $rowPriorityCounts['medium_priority_count']; ?></li>
        <li>Low: <?php echo $rowPriorityCounts['low_priority_count']; ?></li>
    </ul>
</div>


<div class="main-content">
    <h1>Task Dashboard</h1>
    <input class="search form-control mb-3" type="text" id="taskSearch" onkeyup="filterTasks()" placeholder="Search tasks...">

    <table id="taskTable" class="table table-striped">
        <thead>
            <tr>
                <th>Task Name</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th>Priority</th>
                <th>Deadline</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['task_name'] . "</td>";
                    echo "<td>" . $row['status'] . "</td>";
                    echo "<td>" . $row['user_name'] . "</td>";
                    echo "<td>" . $row['priority'] . "</td>";
                    echo "<td>" . $row['deadline'] . "</td>";
                    echo "<td>
                            <a href='edit_task.php?id=" . $row['task_id'] . "' class='btn btn-sm btn-primary'>Edit</a>
                            <a href='delete_task.php?id=" . $row['task_id'] . "' class='btn btn-sm btn-danger'>Delete</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No tasks found</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="pagination">
            <?php
// Connect to your database here

// Number of tasks per page
$per_page = 10;

// Get current page from URL parameter, default to 1 if not set
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Assuming you have a database connection established
$result = $conn->query("SELECT COUNT(*) AS total FROM tasks");
$row = $result->fetch_assoc();
$total_tasks = $row['total'];

// Calculate total pages
$total_pages = ceil($total_tasks / $per_page);

// Calculate start and end limits for pagination
$pagination_start = max(1, $current_page - 1);
$pagination_end = min($pagination_start + 1, $total_pages);

// Correct start if it's at the end
if ($pagination_end - $pagination_start < 1) {
    $pagination_start = max(1, $pagination_end - 1);
}

// Output pagination
echo "<div class='pagination'>";
if ($total_pages > 1) {
    // Previous page link
    if ($current_page > 1) {
        echo "<a href='?page=" . ($current_page - 1) . "'>Previous</a>";
    }

    // Page numbers
    for ($i = $pagination_start; $i <= $pagination_end; $i++) {
        echo "<a href='?page=$i' " . ($i == $current_page ? "class='active'" : "") . ">$i</a>";
    }

    // Next page link
    if ($current_page < $total_pages) {
        echo "<a href='?page=" . ($current_page + 1) . "'>Next</a>";
    }
}
echo "</div>";
?>

        </div>
</div>

<!-- Modal for New Task -->
<div id="newTaskModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Create New Task</h2>
        <form action="create_task.php" method="post">
            <div class="mb-3">
                <label for="title">Task Title:</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="user_id">Assigned To:</label>
                <select id="user_id" name="user_id" class="form-control" required>
                                        <?php
                    $result = $conn->query("SELECT id, name FROM users");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="status_id">Status:</label>
                <select id="status_id" name="status_id" class="form-control" required>
                    <?php
                    $result = $conn->query("SELECT id, status FROM status");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['status'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="priority_id">Priority:</label>
                <select id="priority_id" name="priority_id" class="form-control" required>
                    <?php
                    $result = $conn->query("SELECT id, priority FROM priority");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['priority'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="due_date">Due Date:</label>
                <input type="date" id="due_date" name="due_date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Task</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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



</body>
<script>
function filterTasks() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("taskSearch");
    filter = input.value.toUpperCase();
    table = document.getElementById("taskTable");
    tr = table.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[0]; // Assuming task name is in the first column
        if (td) {
            txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

</script>
<script>
        function openModal() {
            document.getElementById('newTaskModal').style.display = 'block';
        }
        function closeModal() {
            document.getElementById('newTaskModal').style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target == document.getElementById('newTaskModal')) {
                closeModal();
            }
        }
    </script>
</html>
