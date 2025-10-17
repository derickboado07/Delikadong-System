<?php
session_start();
include '../connectdb/connect.php'; // ✅ Include the database connection

// ✅ Step 1: Make sure only admin can refresh the dashboard
if (!isset($_SESSION['admin_username'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit();
}

/*
✅ Step 2: What happens during refresh:
- All users will be marked as offline (is_online = 0).
- Clear their previous login and logout times (time_in and time_out).
- This way, the next time they log in, they’ll have a new time_in and appear online again.
*/
$update = $conn->query("
    UPDATE users
    SET 
        is_online = 0,      -- Set all users to OFFLINE
        time_in = NULL,     -- Remove old login time
        time_out = NULL     -- Remove old logout time
");

// ✅ Step 3: Send a JSON response to the front-end
if ($update) {
    echo json_encode([
        "success" => true,
        "message" => "Dashboard refreshed successfully. All users are now offline. New time_in will be recorded on next login."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to refresh dashboard. Please try again."
    ]);
}
?>
