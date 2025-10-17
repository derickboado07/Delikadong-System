<?php
header('Content-Type: application/json');
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    $order_id = $data['order_id'] ?? null;

    if ($order_id) {
        // Start transaction for safety
        $conn->begin_transaction();

        try {
            // Update order status to completed (keep for dashboard)
            $update_order = $conn->prepare("UPDATE orders SET order_status = 'completed' WHERE id = ?");
            $update_order->bind_param("i", $order_id);

            if ($update_order->execute()) {
                // After marking as completed, deduct inventory for the order if not already deducted
                try {
                    // Ensure the orders table has an inventory_deducted flag
                    $dbNameResult = $conn->query("SELECT DATABASE() AS dbname");
                    $dbName = $dbNameResult->fetch_assoc()['dbname'];
                    $colCheckSql = "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'inventory_deducted'";
                    $colCheckStmt = $conn->prepare($colCheckSql);
                    $colCheckStmt->bind_param("s", $dbName);
                    $colCheckStmt->execute();
                    $colCheckRes = $colCheckStmt->get_result()->fetch_assoc();
                    $colCheckStmt->close();

                    if (intval($colCheckRes['cnt']) === 0) {
                        // Add the column
                        $conn->query("ALTER TABLE orders ADD COLUMN inventory_deducted TINYINT(1) NOT NULL DEFAULT 0");
                        error_log("Added inventory_deducted column to orders table");
                    }

                    // Check if inventory already deducted for this order
                    $dedCheckStmt = $conn->prepare("SELECT inventory_deducted FROM orders WHERE id = ? LIMIT 1");
                    $dedCheckStmt->bind_param("i", $order_id);
                    $dedCheckStmt->execute();
                    $dedRow = $dedCheckStmt->get_result()->fetch_assoc();
                    $dedCheckStmt->close();

                    if (empty($dedRow) || intval($dedRow['inventory_deducted']) === 1) {
                        error_log("Inventory already deducted or order not found for order_id={$order_id}");
                    } else {
                        // Fetch order items. If order_items has menu_id column use it, otherwise fallback to name lookup.
                        $itemsStmt = $conn->prepare("SELECT COALESCE(menu_id, NULL) AS menu_id, item_name, quantity FROM order_items WHERE order_id = ?");
                        $itemsStmt->bind_param("i", $order_id);
                        $itemsStmt->execute();
                        $itemsResult = $itemsStmt->get_result();
                        // Begin a transaction to make deductions atomic
                        $conn->begin_transaction();
                        try {
                            // Prepare statements
                            $menuLookup = $conn->prepare("SELECT id FROM menu WHERE LOWER(name) = LOWER(?) LIMIT 1");
                            $invUpdate = $conn->prepare("UPDATE menu_inventory SET stock_quantity = GREATEST(stock_quantity - ?, 0), last_updated = CURRENT_TIMESTAMP WHERE menu_id = ?");
                            $invInsert = $conn->prepare("INSERT INTO menu_inventory (menu_id, stock_quantity) VALUES (?, 0)");

                            // ingredient update and safe insert-if-missing (only insert if not exists)
                            $ingredientUpdate = $conn->prepare("UPDATE ingredients SET stock_quantity = GREATEST(stock_quantity - ?, 0), last_updated = CURRENT_TIMESTAMP WHERE id = ?");
                            $ingredientInsert = $conn->prepare("INSERT INTO ingredients (id, name, unit, stock_quantity) SELECT ?, 'Unknown', 'unit', 0 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM ingredients WHERE id = ?)");

                            $recipeStmt = $conn->prepare("SELECT ingredient_id, quantity_required FROM menu_recipes WHERE menu_id = ?");

                            while ($row = $itemsResult->fetch_assoc()) {
                                $iqty = floatval($row['quantity']);

                                // prefer explicit menu_id if present
                                $mid = intval($row['menu_id'] ?? 0);
                                if ($mid <= 0) {
                                    // fallback to lookup by name
                                    $iname = $row['item_name'];
                                    $menuLookup->bind_param("s", $iname);
                                    $menuLookup->execute();
                                    $mres = $menuLookup->get_result();
                                    if ($mrow = $mres->fetch_assoc()) {
                                        $mid = intval($mrow['id']);
                                    } else {
                                        error_log("Menu lookup: no menu entry found for '{$iname}' during completion deduction");
                                        continue;
                                    }
                                }

                                // Deduct menu-level inventory (if exists)
                                $invUpdate->bind_param("di", $iqty, $mid);
                                $invUpdate->execute();
                                if ($invUpdate->affected_rows === 0) {
                                    // Insert inventory row if missing
                                    $invInsert->bind_param("i", $mid);
                                    $invInsert->execute();
                                    error_log("Inventory row created for menu_id={$mid} (missing) during completion deduction");
                                } else {
                                    error_log("Deducted {$iqty} units from menu_id={$mid} for order {$order_id}");
                                }

                                // Deduct ingredients per recipe
                                $recipeStmt->bind_param('i', $mid);
                                $recipeStmt->execute();
                                $rres = $recipeStmt->get_result();
                                while ($rrow = $rres->fetch_assoc()) {
                                    $ingId = intval($rrow['ingredient_id']);
                                    $reqQty = floatval($rrow['quantity_required']);
                                    $totalDeduct = $reqQty * $iqty; // per menu qty

                                    $ingredientUpdate->bind_param('di', $totalDeduct, $ingId);
                                    $ingredientUpdate->execute();
                                    if ($ingredientUpdate->affected_rows === 0) {
                                        // insert placeholder ingredient row if missing
                                        $ingredientInsert->bind_param('ii', $ingId, $ingId);
                                        $ingredientInsert->execute();
                                        error_log("Inserted missing ingredient id={$ingId} while deducting for menu_id={$mid}");
                                    } else {
                                        error_log("Deducted {$totalDeduct} units from ingredient_id={$ingId} for order {$order_id}");
                                    }
                                }
                            }

                            // Mark as deducted and commit
                            $markStmt = $conn->prepare("UPDATE orders SET inventory_deducted = 1 WHERE id = ?");
                            $markStmt->bind_param("i", $order_id);
                            $markStmt->execute();
                            $markStmt->close();

                            $conn->commit();

                            // close prepared statements
                            $menuLookup->close();
                            $invUpdate->close();
                            $invInsert->close();
                            $ingredientUpdate->close();
                            $ingredientInsert->close();
                            $recipeStmt->close();
                        } catch (Exception $e) {
                            $conn->rollback();
                            error_log('Ingredient/menu deduction transaction failed: '.$e->getMessage());
                        }

                        $itemsStmt->close();
                    }
                } catch (Exception $invExc) {
                    error_log("Error during inventory deduction on completion: " . $invExc->getMessage());
                }

                $conn->commit();
                echo json_encode(["status" => "success", "message" => "Order marked as completed successfully"]);
            } else {
                throw new Exception("Failed to update order: " . $update_order->error);
            }

            $update_order->close();

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No order ID provided"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

$conn->close();
?>
