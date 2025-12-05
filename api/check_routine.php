<?php


if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $today = date('Y-m-d');

    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? AND is_routine = 1");
    $stmt->execute([$uid]);
    $routines = $stmt->fetchAll();

    foreach ($routines as $task) {

        $last_gen = $task['last_generated'] ?? date('Y-m-d', strtotime($task['created_at']));
        $interval = $task['routine_interval'];

        $next_due_date = date('Y-m-d', strtotime($last_gen . " + $interval days"));

        if ($today >= $next_due_date) {
            $new_deadline = date('Y-m-d', strtotime($today . " + $interval days"));

            $insert = $pdo->prepare("INSERT INTO tasks (user_id, task_name, status, urgency, deadline, is_routine, routine_interval, last_generated) VALUES (?, ?, 'ongoing', ?, ?, 0, 0, NULL)");

            $insert->execute([$uid, $task['task_name'] . " (Rutin)", $task['urgency'], $new_deadline]);

            $update = $pdo->prepare("UPDATE tasks SET last_generated = ? WHERE id = ?");
            $update->execute([$today, $task['id']]);
        }
    }
}
?>