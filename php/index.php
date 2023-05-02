<?php

function getRandomString($length = 8){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';

    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $string;
}



// SQL LOGIN

$db_host = 'epussd.cankoz9ihj2m.us-west-1.rds.amazonaws.com'; // database host
$db_username = 'EpDev1'; // database username
$db_password = 'moneymoney101'; // database password
$db_name = 'ep_foundation'; // database name

$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$post_json = file_get_contents('php://input');
$post_data = json_decode($post_json);

$number = $post_data->Mobile;

if ($number === '') {
    // handle the error condition
} else {
    // continue with the rest of the code
}

// Check if the user is allowed to use the USSD service
$sql = "SELECT COUNT(*) as mycount FROM dues WHERE phone_number = '".$number."'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_object($result);
// print_r($row) ;
// exit;

if (!$result) {
    error_log("Failed to execute SQL query: " . mysqli_error($conn));
    header('Content-type: text/plain');
    $response = [
        "Type" => "Release",
        "Message" => "Sorry, an error occurred while processing your request."
    ];
    exit(json_encode($response));
}

// Get the user's record from the database
$sql = "SELECT * FROM dues WHERE phone_number='".$number."'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    header('Content-type: text/plain');
    $response = [
        "Type" => "Release",
        "Message" => "Sorry, you are not allowed to use this service."
    ];
    exit(json_encode($response));
}


// Get the user's record from the database
$sql = "SELECT * FROM users WHERE phone_number='".$number."'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    $user_row = mysqli_fetch_assoc($result);
} else {
    $sql = "Insert into users(user_name, phone_number, previous_step) values ('".$number."','".$number."','welcome');";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("Failed to execute SQL query: " . mysqli_error($conn));
        header('Content-type: text/plain');
        $response = [
            "Type" => "Release",
            "Message" => "Sorry, unable to process request the the moment."
        ];
        exit(json_encode($response));
    }

    $sql = "SELECT * FROM users WHERE phone_number='".$number."'";
    $result = mysqli_query($conn, $sql);
    $user_row = mysqli_fetch_assoc($result);
}

// die(print_r($post_data));


$session_id = $post_data->SessionId;
$service_code = $post_data->ServiceCode;
$network = $post_data->Operator;
$text = trim($post_data->Message);

// exit($text);

// header('Content-type: text/plain');
//     $response = [
//         "Type" => "Response",
//         "Message" => $text
//     ];
//     exit(json_encode($response));


// die($text);
if($text == "*789*123*000") {
    // User is starting a new session
    $response = "Welcome to EP FOUNDATION.\n";
    $response .= "1. Dues\n";
    $response .= "2. Donation\n";
    $response .= "3. Others";


    $sql = "UPDATE users SET previous_step ='welcome' WHERE phone_number='".$number."'";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("Failed to execute SQL query: " . mysqli_error($conn));
        $response = "Sorry, an error occurred while processing your request.";
        header('Content-type: text/plain');
        echo $response;
        exit;
    }

    header('Content-type: text/plain');
    $response = [
        "Type" => "Response",
        "Message" => $response
    ];
    exit(json_encode($response));

}

if($text == "1"  && $user_row['previous_step'] == "welcome") {
    // User wants to check their dues balance
    $response = "Your outstanding balance is GHS " . $row['balance'] . ".\n";
    $response .= "1. Make payment\n";
    $response .= "2. Exit";


    $sql = "UPDATE users SET previous_step ='dues' WHERE phone_number='".$number."'";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("Failed to execute SQL query: " . mysqli_error($conn));
        $response = "Sorry, an error occurred while processing your request.";
        header('Content-type: text/plain');
        echo $response;
        exit;
    }
                          

    $response = [
        "Type" => "Response",
        "Message" => $response
    ];
    exit(json_encode($response));

}




if ($text == "1" && $user_row['previous_step'] == "dues"){
    $response_message = "Enter Amount :";

    $sql = "UPDATE users SET previous_step ='dues_enter_amount' WHERE phone_number='".$number."'";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("Failed to execute SQL query: " . mysqli_error($conn));
        $response = "Sorry, an error occurred while processing your request.";
        header('Content-type: text/plain');
        echo $response;
        exit;
    }

    $response = [
        "Type" => "Response",
        "Message" => $response_message
    ];
    exit(json_encode($response));

    
}




if ($text == "2" && $user_row['previous_step'] == "dues"){
    $response = "Thank you for using EP FOUNDATION";

    $sql = "UPDATE users SET previous_step ='welcome' WHERE phone_number='".$number."'";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("Failed to execute SQL query: " . mysqli_error($conn));
        $response = "Sorry, an error occurred while processing your request.";
        header('Content-type: text/plain');
        echo $response;
        exit;
    }
    
    $response = [
        "Type" => "Release",
        "Message" => $response
    ];
    exit(json_encode($response));

    
}




// if ($text == "1" && $user_row['previous_step'] == "dues"){

//     //$amount= readline ("Enter amount ");
//     //$response = "Enter Amount :\n";

//     if (!is_numeric($amount)) {

//         $response = "Invalid amount entered. Please try again.";

//         $sql = "UPDATE users SET previous_step ='dues' WHERE phone_number='".$number."'";
//         $result = mysqli_query($conn, $sql);
//         if (!$result) {
//             error_log("Failed to execute SQL query: " . mysqli_error($conn));
//             $response = "Sorry, an error occurred while processing your request.";
//             header('Content-type: text/plain');
//             echo $response;
//             exit;
//         }

//     } else if ($amount <= 0) {
//         $response = "Amount must be greater than zero. Please try again.";

//         $sql = "UPDATE users SET previous_step ='dues' WHERE phone_number='".$number."'";
//         $result = mysqli_query($conn, $sql);
//         if (!$result) {
//             error_log("Failed to execute SQL query: " . mysqli_error($conn));
//             $response = "Sorry, an error occurred while processing your request.";
//             header('Content-type: text/plain');
//             echo $response;
//             exit;
//         }
//     } else if ($amount > $row['balance']) {
//         $response = "Amount cannot be greater than outstanding balance. Please try again.";

//         $sql = "UPDATE users SET previous_step ='dues' WHERE phone_number='".$number."'";
//         $result = mysqli_query($conn, $sql);
//         if (!$result) {
//             error_log("Failed to execute SQL query: " . mysqli_error($conn));
//             $response = "Sorry, an error occurred while processing your request.";
//             header('Content-type: text/plain');
//             echo $response;
//             exit;
//         }
//     }

//     $sql = "UPDATE users SET previous_step ='dues_enter_amount' WHERE phone_number='".$number."'";
//     $result = mysqli_query($conn, $sql);
//     if (!$result) {
//         error_log("Failed to execute SQL query: " . mysqli_error($conn));
//         $response = "Sorry, an error occurred while processing your request.";
//         header('Content-type: text/plain');
//         echo $response;
//         exit;
//     }


//     $response = [
//         "Type" => "Response",
//         "Message" => $response
//     ];
//     exit(json_encode($response));
// }



   

if ($user_row['previous_step'] == "dues_enter_amount"){

    $amount = trim($text);



    if (!is_numeric($amount)) {

        $response = "Invalid amount entered. Please try again.";

        $sql = "UPDATE users SET previous_step ='dues_enter_amount' WHERE phone_number='".$number."'";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            error_log("Failed to execute SQL query: " . mysqli_error($conn));
            $response = "Sorry, an error occurred while processing your request.";
            header('Content-type: text/plain');
            echo $response;
            exit;
        }

        $response = [
            "Type" => "Response",
            "Message" => $response
        ];
        exit(json_encode($response));

    } else if ($amount <= 0) {
        $response = "Amount must be greater than zero. Please try again.";

        $sql = "UPDATE users SET previous_step ='dues_enter_amount' WHERE phone_number='".$number."'";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            error_log("Failed to execute SQL query: " . mysqli_error($conn));
            $response = "Sorry, an error occurred while processing your request.";
            header('Content-type: text/plain');
            echo $response;
            exit;
        }

        $response = [
            "Type" => "Response",
            "Message" => $response
        ];
        exit(json_encode($response));


    } else if ($amount > $row['balance']) {
        $response = "Amount cannot be greater than outstanding balance. Please try again.";

        $sql = "UPDATE users SET previous_step ='dues_enter_amount' WHERE phone_number='".$number."'";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            error_log("Failed to execute SQL query: " . mysqli_error($conn));
            $response = "Sorry, an error occurred while processing your request.";
            header('Content-type: text/plain');
            echo $response;
            exit;
        }

        $response = [
            "Type" => "Response",
            "Message" => $response
        ];
        exit(json_encode($response));

    }

    
    $order_id = getRandomString(12);

    //Start Payment Prompt
     $json_data = array (
        "app_id" => "8030468334", //Live
        "app_key" => "40213757", 
        "name" => "EPFOUNDATION", 
        "FeeTypeCode" => "GENERALPAYMENT", 
        "mobile" =>  $number, 
        "currency" => "GHS",
        "amount" => $amount, 
        "mobile_network" => strtoupper($network), 
        "order_id" => $order_id, 
        "order_desc" => "Payment"
     );

     


     $post_data = json_encode($json_data, JSON_UNESCAPED_SLASHES);

     

    $curl = curl_init();
     curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.interpayafrica.com/v3/interapi.svc/CreateMMPayment", //live Url
        // CURLOPT_URL => "https://testsrv.interpayafrica.com/v7/interapi.svc/CreateMMPayment", //Test Url
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $post_data,
        CURLOPT_HTTPHEADER => array(
          "Content-Type: application/json"
        ),
      ));


     $response = curl_exec($curl);
     $err = curl_error($curl);
     

     curl_close($curl);

    if ($err) {
        // dd("cURL Error #:" . $err);
        $message = "Sorry Something Went Wrong. Please Try Again Later.";

        $response = [
            "Type" => "Release",
            "Message" => $message
        ];
        exit(json_encode($response));

    } else {

         $return = json_decode($response);
         $params = json_decode($response, true);

         // die(var_dump($return));
         // dd($return);
         // $data = [
         //    "order_id" => $order_id,
         //    "amount" => $amount,
         //    "status_code" => $return->status_code,
         //    "status_message" => $return->status_message,
         //    "merchantcode" => $return->merchantcode,
         //    "transaction_no" => $return->transaction_no,
         // ];



         // Get the transaction record from the database
         $sql = "SELECT * FROM payment_transactions WHERE order_id='".$order_id."' LIMIT 1";          

          try {
              $result = mysqli_query($conn, $sql);
              if (mysqli_num_rows($result) > 0) {


                  $payment_transaction = mysqli_fetch_assoc($result);
                  $transaction_id = $payment_transaction['id'];

                  $sql = "UPDATE payment_transactions SET status_code='".$return->status_code."', amount='".$amount."',status_message='".$return->status_message."', merchantcode='".$return->merchantcode."', transaction_no='".$return->transaction_no."', resource_id='".$number."', transaction_type='dues', order_id='".$order_id."' WHERE id='".$transaction_id."'";
                  $result = mysqli_query($conn, $sql);

                 // exit("Found");
              } else {

                  $sql = "Insert into payment_transactions
                             (status_code, amount, status_message, merchantcode, transaction_no, resource_id, transaction_type,order_id) values 
                             ('".$return->status_code."','".$amount."','".$return->status_message."','".$return->merchantcode."','".$return->transaction_no."','".$number."','dues','".$order_id."');";
                  $result = mysqli_query($conn, $sql);
                  if (!$result) {
                      error_log("Failed to execute SQL query: " . mysqli_error($conn));
                      header('Content-type: text/plain');
                      $response = [
                          "Type" => "Release",
                          "Message" => "Sorry, unable to process request the the moment."
                      ];
                      exit(json_encode($response));
                  }

                  $sql = "SELECT * FROM payment_transactions WHERE order_id='".$order_id."' LIMIT 1";
                  $result = mysqli_query($conn, $sql);
                  $payment_transaction = mysqli_fetch_assoc($result);

                  // exit("NOT Found");
              }
          } catch (Exception $e) {
            // die(var_dump($e));
              $message = "Sorry Something Went Wrong. Please Try Again Later.";
              $response = [
                  "Type" => "Release",
                  "Message" => $message
              ];
              exit(json_encode($response));
          }

         


         if($return->status_code == 1){
              // $message = $return->reason;
              $sql = "UPDATE users SET previous_step ='payment' WHERE phone_number='".$number."'";
              $result = mysqli_query($conn, $sql);
              if (!$result) {
                  error_log("Failed to execute SQL query: " . mysqli_error($conn));
                  $response = "Sorry, an error occurred while processing your request.";
                  header('Content-type: text/plain');
                  echo $response;
                  exit;
              }



              $response = "You will recieve a payment prompt to complete your payment";

              $response = [
                  "Type" => "Release",
                  "Message" => $response
              ];
              exit(json_encode($response));


          }else{


              $message = "Sorry Something Went Wrong. Please Try Again Later.";
              $response = [
                  "Type" => "Release",
                  "Message" => $message
              ];
              exit(json_encode($response));
          }


    }



    
}

   




///  Kindly help input the right code to initiate the payment processing //////////////// 


// Get payment details from the user input
/*if (strpos($text, "1*1*") === 0) {
    // User entered an amount to pay
    $amount = substr($text, 4);
    if (!is_numeric($amount)) {
        $response = "Invalid amount entered. Please try again.";
    } else if ($amount <= 0) {
        $response = "Amount must be greater than zero. Please try again.";
    } else if ($amount > $row['balance']) {
        $response = "Amount cannot be greater than outstanding balance. Please try again.";
    } 
    else {

        // Payment data
        $payment_data = [
            "app_id" => "2499045588",
            "app_key" => "35242564",
            "name" => $row['name'],
            "FeeTypeCode" => "GENERALPAYMENT",
            "mobile" => $number,
            "currency" => "GHS",
            "amount" => $amount,
            "mobile_network" => "MTN",
            "email" => $row['email'],
            "order_id" => uniqid(),
            "order_desc" => "Dues Payment"
        ];

        // API endpoint URL for USSD payments
        $ussd_url = "https://testsrv.interpayafrica.com/v7/interapi.svc/CreateMMPayment";

        // Request headers
        $headers = array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $api_key
        );

        // Send the USSD payment request using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ussd_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Check the response status code
        if ($response !== false) {
            $response_data = json_decode($response, true);
            if (isset($response_data["paymentUrl"])) {
                // Get the payment URL from the response
                $payment_url = $response_data["paymentUrl"];

                // Redirect the user to the payment URL
                header("Location: $payment_url");
                exit();
            } else {
                // Handle errors
                echo "Error: " . $response_data["message"];
            }
        } else {
            // Handle cURL errors
            echo "cURL error: " . curl_error($ch);
        }

        $new_balance = $row['dues_balance'] - $amount;
        $sql = "UPDATE dues SET dues_balance='$new_balance' WHERE number='$number'";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            error_log("Failed to execute SQL query: " . mysqli_error($conn));
            $response = "Sorry, an error occurred while processing your request.";
            header('Content-type: text/plain');
            echo $response;
            exit;
        }
        $response = " Payment of GHS $amount was successful . Your new balance is GHS $new_balance.";
    }



        // User wants to exit the USSD session

}*/ 
// else if ($text == "2"  && $user_row['previous_step'] == "dues") {

//     $response = "Thank you for using EP FOUNDATION ";

//     $sql = "UPDATE users SET previous_step ='dues_exit' WHERE phone_number='".$number."'";
//     $result = mysqli_query($conn, $sql);
//     if (!$result) {
//         error_log("Failed to execute SQL query: " . mysqli_error($conn));
//         $response = "Sorry, an error occurred while processing your request.";
//         header('Content-type: text/plain');
//         echo $response;
//         exit;
//     }
    
//     $response = [
//         "Type" => "Release",
//         "Message" => $response
//     ];
//     exit(json_encode($response));


//     // User wants to make a   donation
// } else if ($text == "2" && $user_row['previous_step'] == "welcome") {

//     $response = "Enter amount to donate:";

    
//     // Check if input is a valid numeric greater than 0
//     if (!is_numeric($user_row['input']) || $user_row['input'] <= 0) {
//         $response = "Please enter a valid amount .";
//         header('Content-type: text/plain');
//         echo $response;
//         exit;
//     }
    
//     //$response = "Enter amount to donate:";

//     $sql = "UPDATE users SET previous_step ='donation_enter_amount' WHERE phone_number='".$number."'";
//     $result = mysqli_query($conn, $sql);
//     if (!$result) {
//         error_log("Failed to execute SQL query: " . mysqli_error($conn));
//         $response = "Sorry, an error occurred while processing your request.";
//         header('Content-type: text/plain');
//         echo $response;
//         exit;
//     }

//     $response = [
//         "Type" => "Response",
//         "Message" => $response
//     ];
//     exit(json_encode($response));
// }


/*else if (strpos($text, "2*") === 0) {
    // User entered an amount to donate
    $amount = substr($text, 2);
    if (!is_numeric($amount)) {
        $response = "Invalid amount entered. Please try again.";
    } else if ($amount <= 0) {
        $response = "Amount must be greater than zero. Please try again.";
    } else {

        $payment_data = [
            "app_id" => "2499045588",
            "app_key" => "35242564",
            "name" => $row['name'],
            "FeeTypeCode" => "GENERALPAYMENT",
            "mobile" => $number,
            "currency" => "GHS",
            "amount" => $amount,
            "mobile_network" => "MTN",
            "email" => $row['email'],
            "order_id" => uniqid(),
            "order_desc" => "Dues Payment"
        ];
        // Process donation and insert record into donations table
        // Interpay payment gateway provider API code here
        //  actual API ke

// API endpoint URL for USSD payments
$ussd_url = "https://testsrv.interpayafrica.com/v7/interapi.svc/CreateMMPayment";

        // Request headers
        $headers = array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $api_key
        );

// Send the USSD payment request using cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $ussd_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Check the response status code
if ($response !== false) {
    $response_data = json_decode($response, true);
    if (isset($response_data["paymentUrl"])) {
        // Get the payment URL from the response
        $payment_url = $response_data["paymentUrl"];

        // Redirect the user to the payment URL
        header("Location: $payment_url");
        exit();
    } else {
        // Handle errors
        echo "Error: " . $response_data["message"];
    }
} else {
    // Handle cURL errors
    echo "cURL error: " . curl_error($ch);
}
    //// database update for donation //////////

        $donation_date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO donations (member_id, amount, donation_date) VALUES ('".$row['id']."', '".$amount."', '".$donation_date."')";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            error_log("Failed to execute SQL query: " . mysqli_error($conn));
            $response = "Sorry, an error occurred while processing your request.";
            header('Content-type: text/plain');
            echo $response;
            exit;
        }
        $response = "Thank you for your donation of GHS ".$amount.".";
    }

    // User wants to perform other actions
} */ 
// else if ($text == "3"  && $user_row['previous_step'] == "welcome") {
    
//     $response = "Other actions not yet implemented. Please check back later.";

//     $sql = "UPDATE users SET previous_step ='others' WHERE phone_number='".$number."'";
//     $result = mysqli_query($conn, $sql);
//     if (!$result) {
//         error_log("Failed to execute SQL query: " . mysqli_error($conn));
//         $response = "Sorry, an error occurred while processing your request.";
//         header('Content-type: text/plain');
//         echo $response;
//         exit;
//     }

// } else {
//     $response = "Invalid input. Please try again.";
// }

// $response = [
//     "Type" => "Response",
//     "Message" => $response
// ];
// exit(json_encode($response));

// // Send the USSD response back to the gateway
// header('Content-type: text/plain');
// echo $response;

?>
