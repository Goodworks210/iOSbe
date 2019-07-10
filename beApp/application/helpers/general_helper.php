<?php

function created() {
        //////CURRENT GMT DATE TIME(2015-09-23 14:15:07)
        return date('Y-m-d H:i:s');
}

function error_res($msg="",$statuscode="") {
    $msg = $msg == "" ? "error" : $msg;
    $statuscode = $statuscode == "" ? 200 : $statuscode;
    return array("status" => 0, "msg" => $msg,"statuscode"=>$statuscode);
}

function success_res($msg="",$statuscode="") {
    $statuscode = $statuscode == "" ? 200 : $statuscode;
    $msg = $msg == "" ? "Success" : $msg;
    return array("status" => 1, "msg" => $msg,"statuscode"=>$statuscode);
}


function generateRandomString($length = 2) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function AutorisedLogin() {
    $CI = & get_instance();
    $user_id = $CI->session->userdata('user_id');
    if ($user_id == "") {
        redirect('/');
    }
    return $user_id;
}

function push_notification_ios($arg_device_token, $message_body) {
    $deviceToken = "" . $arg_device_token . "";


    $production = 0;
    if ($production==1) {
        $gateway = 'ssl://gateway.push.apple.com:2195';
    } else {
        $gateway = 'ssl://gateway.sandbox.push.apple.com:2195';
    }

// Create a Stream
    $ctx = stream_context_create();
// Define the certificate to use
    // stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck_prod2.pem');
// Passphrase to the certificate
    // stream_context_set_option($ctx, 'ssl', 'passphrase', 'tapinpush');

    stream_context_set_option($ctx, 'ssl', 'local_cert', 'apns-dev-cert.pem');
    stream_context_set_option($ctx, 'ssl', 'passphrase', 'BeDevNotification');

// Open a connection to the APNS server
    $fp = stream_socket_client(
            $gateway, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

// Check that we've connected
    if (!$fp) {
        $error = "Failed to connect: $err $errstr" . PHP_EOL;
        return $error;
    }

    $body['aps'] = $message_body;
    // Encode the payload as JSON
    $payload = json_encode($body);
    // Build the binary notification
    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

    // Send it to the server
    $result = fwrite($fp, $msg, strlen($msg));
    fclose($fp);
    if (!$result) {
        //echo 'Error, notification not sent' . PHP_EOL;
        $return = error_res("Error, notification not sent");
        //log_message('error', "****Failed to notify $deviceToken");
        return $return;
    } else {
        $return = success_res("Success, notification sent");
        //log_message('info', "****Successfully notified $deviceToken");
        return $return;
    }
}

