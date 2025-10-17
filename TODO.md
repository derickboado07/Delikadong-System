# TODO List for Inventory Deduction Fix

## Current Task: Fix Inventory Deduction on Order Completion
- [x] Modify `backend/delete_order.php` to deduct inventory when marking order as completed
- [x] Add inventory_deducted flag check and logic similar to `backend/process_payment.php`
- [x] Test the fix by completing an order and verifying inventory subtraction
- [x] Ensure no double deduction if order was already processed via payment

## Next Steps
- [x] Verify that ingredients and menu inventory are correctly reduced
- [x] Check for any edge cases (e.g., orders without recipes)
- [x] Update documentation if needed
