<?php
include 'db.php';

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];

    $sql = "DELETE FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $task_id);

    if ($stmt->execute()) {
        echo "Task deleted successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
    header("Location: dashboard.php");
    exit;
} else {
    echo "Invalid request.";
}
?>
