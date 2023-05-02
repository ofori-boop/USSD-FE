<?php


$db_host = 'epussd.cankoz9ihj2m.us-west-1.rds.amazonaws.com'; // database host
$db_username = 'EpDev1'; // database username
$db_password = 'moneymoney101'; // database password
$db_name = 'ep_foundation'; // database name

$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$order_id = $_POST['order_id'];
$status_code = $_POST['status_code'];


if($status_code == 1){
    //Get the Trabsaction Record
    $sql = "SELECT * FROM payment_transactions WHERE order_id='".$order_id."' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $payment_transaction = mysqli_fetch_assoc($result);
        $transaction_id = $payment_transaction['id'];
        $transaction_type = $payment_transaction['transaction_type'];
        $amount = $payment_transaction['amount'];
        $number = $payment_transaction['resource_id'];

        if($transaction_type == "dues"){
            $sql = "UPDATE dues SET balance = balance - ".$amount." WHERE phone_number='".$number."'";
            $result = mysqli_query($conn, $sql);

            exit('Done');
        }

        
    }
}



// function interpay_callback() {
//     // Define the API key
//     $api_key = "35242564";

//     // Get the payment data from the callback parameters
//     $status = $_GET['status'];
//     $message = $_GET['message'];
//     $order_id = $_GET['order_id'];
//     $transaction_id = $_GET['transaction_id'];
//     $amount = $_GET['amount'];
//     $currency = $_GET['currency'];

//     // Check if the payment was successful
//     if ($status == 'success') {
//         // Query the Interpay API to verify the payment
//         $ch = curl_init();
//         curl_setopt($ch, CURLOPT_URL, "https://testsrv.interpayafrica.com/v7/interapi.svc/VerifyMMPayment/$transaction_id");

//         curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $api_key));
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//         // Execute the payment verification request and get the response
//         $response = curl_exec($ch);
//         curl_close($ch);

//         // Check the response status code
//         if ($response !== false) {
//             $response_data = json_decode($response, true);
//             if (isset($response_data["status"]) && $response_data["status"] == "paid") {
//                 // Payment has been successfully verified
//                 // Insert record into database or perform any other necessary action

//                 // Output a success message to the user
//                 echo "Payment of $amount $currency was successful";
//                 //TODO: Insert record into database or perform any other necessary action
//             } else {
//                 // Handle payment verification errors
//                 echo "Payment verification failed: " . $response_data["message"];
//             }
//         } else {
//             // Handle cURL errors
//             echo "cURL error: " . curl_error($ch);
//         }
//     } else {
//         // Handle payment errors
//         echo "Payment failed: $message";
//     }
// }
