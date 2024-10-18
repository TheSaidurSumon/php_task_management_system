<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $user_id = $_POST['user_id'];
    $status_id = $_POST['status_id'];
    $priority_id = $_POST['priority_id'];
    $due_date = $_POST['due_date'];

    $sql = "INSERT INTO tasks (title, user_id, status_id, priority_id, due_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiis", $title, $user_id, $status_id, $priority_id, $due_date);

    if ($stmt->execute()) {
        echo "Task created successfully!";
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
