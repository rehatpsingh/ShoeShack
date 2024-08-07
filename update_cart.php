<?php
// include_once 'Database.php';

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {

//     if (isset($_POST['action'])) {
//         $action = $_POST['action'];

//         if ($action === 'update') {
//             $shoeId = isset($_POST['shoe_id']) ? intval($_POST['shoe_id']) : 0;
//             $shoeSizeId = isset($_POST['shoe_size_id']) ? intval($_POST['shoe_size_id']) : 0;
//             $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

//             // Check if quantity is valid
//             if ($quantity <= 0) {
//                 echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
//                 exit;
//             }

//             if (isset($_COOKIE['cart'])) {
//                 $cart = json_decode($_COOKIE['cart'], true);
//                 $itemFound = false;

//                 foreach ($cart as &$item) {
//                     if ($item['shoe_id'] == $shoeId && $item['shoe_size_id'] == $shoeSizeId) {
//                         $productObj = new ProductDetail();
//                         $shoe = $productObj->getShoeById($item['shoe_id']);
//                         if ($shoe) {
//                             $item['shoe_qty'] = $quantity;
//                             $item['subtotal_bill'] = floatval($shoe[0]['shoe_srp']) * $quantity;
//                             $shoe_tax_rate = 0.13;
//                             $shoe_tax = $item['subtotal_bill'] * $shoe_tax_rate;
//                             $item['shoe_tax'] = $shoe_tax;
//                             $item['final_amt'] = $item['subtotal_bill'] + $shoe_tax;
//                             $item['final_amt'] = number_format($item['final_amt'], 2, '.', '');
//                             $itemFound = true;
//                         } else {
//                             echo json_encode(['success' => false, 'message' => 'Product not found']);
//                             exit;
//                         }
//                         break;
//                     }
//                 }

//                 if ($itemFound) {
//                     if (setcookie('cart', json_encode($cart), time() + 86400, '/')) {
//                         echo json_encode(['success' => true]);
//                     } else {
//                         echo json_encode(['success' => false, 'message' => 'Failed to set cookie']);
//                     }
//                 } else {
//                     echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
//                 }
//             } else {
//                 echo json_encode(['success' => false, 'message' => 'Cart is empty']);
//             }
//         } elseif ($action === 'delete') {
//             $shoe_id = isset($_POST['shoe_id']) ? intval($_POST['shoe_id']) : 0;
//             $shoe_size_id = isset($_POST['shoe_size_id']) ? intval($_POST['shoe_size_id']) : 0;

//             $cart = new Cart();
//             $cart->removeFromCookies($shoe_id, $shoe_size_id);

//             echo json_encode(['success' => true]);
//         } else {
//             echo json_encode(['success' => false, 'message' => 'Invalid action']);
//         }
//     } else {
//         echo json_encode(['success' => false, 'message' => 'No action specified']);
//     }
// } else {
//     echo json_encode(['success' => false, 'message' => 'Invalid request method']);
// }

session_start();
include_once 'Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];

    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $shoeId = isset($_POST['shoe_id']) ? intval($_POST['shoe_id']) : 0;
        $shoeSizeId = isset($_POST['shoe_size_id']) ? intval($_POST['shoe_size_id']) : 0;

        $cartObj = new Cart();
        $isLoggedIn = isset($_SESSION['user_details']);

        if ($action === 'update') {
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

            if ($quantity <= 0) {
                $response['message'] = 'Invalid quantity';
            } else {
                if ($isLoggedIn) {
                    $userDetails = json_decode($_SESSION['user_details'], true);
                    $customerId = $userDetails['customer_id'];
                    $success = $cartObj->updateCartItem($customerId, $shoeId, $shoeSizeId, $quantity);
                    if ($success) {
                        $response['success'] = true;
                        $response['message'] = 'Cart updated successfully';
                    } else {
                        $response['message'] = 'Failed to update cart in database';
                    }
                } else {
                    // Update cookie-based cart
                    $cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
                    $itemFound = false;

                    foreach ($cart as &$item) {
                        if ($item['shoe_id'] == $shoeId && $item['shoe_size_id'] == $shoeSizeId) {
                            $productObj = new ProductDetail();
                            $shoe = $productObj->getShoeById($item['shoe_id']);
                            if ($shoe) {
                                $item['shoe_qty'] = $quantity;
                                $item['subtotal_bill'] = floatval($shoe[0]['shoe_srp']) * $quantity;
                                $shoe_tax_rate = 0.13;
                                $item['shoe_tax'] = $item['subtotal_bill'] * $shoe_tax_rate;
                                $item['final_amt'] = $item['subtotal_bill'] + $item['shoe_tax'];
                                $item['final_amt'] = number_format($item['final_amt'], 2, '.', '');
                                $itemFound = true;
                            } else {
                                $response['message'] = 'Product not found';
                            }
                            break;
                        }
                    }

                    if ($itemFound) {
                        if (setcookie('cart', json_encode($cart), time() + 86400, '/')) {
                            $response['success'] = true;
                            $response['message'] = 'Cart updated successfully';
                        } else {
                            $response['message'] = 'Failed to set cookie';
                        }
                    } else {
                        $response['message'] = 'Item not found in cart';
                    }
                }
            }
        } elseif ($action === 'delete') {
            if ($isLoggedIn) {
                $userDetails = json_decode($_SESSION['user_details'], true);
                $customerId = $userDetails['customer_id'];
                $success = $cartObj->removeFromCart($customerId, $shoeId, $shoeSizeId);
                if ($success) {
                    $response['success'] = true;
                    $response['message'] = 'Item removed from cart successfully';
                } else {
                    $response['message'] = 'Failed to remove item from database';
                }
            } else {
                // Remove from cookie-based cart
                $cartObj->removeFromCookies($shoeId, $shoeSizeId);
                $response['success'] = true;
                $response['message'] = 'Item removed from cart successfully';
            }
        } else {
            $response['message'] = 'Invalid action';
        }
    } else {
        $response['message'] = 'No action specified';
    }
} else {
    $response['message'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);
