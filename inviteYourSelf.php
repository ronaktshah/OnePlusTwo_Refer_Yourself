<?php

// get email and unique invitation code from one plus one
$options = getopt("e:c:");

$email = $options["e"];
$code = $options["c"];


$emails = generate_emails($email);

// loop through each email and send invite
foreach ($emails as $email) {

    if ($email[0] == '.' || $email[strlen($email)-1] == '.') {
        echo "Skipping email $email, as it is invalid \n";
    }
    else {
        echo "Sending email to $email  -- ";
        $response = send_request($code,$email."@gmail.com");

        var_dump($response);

        sleep(5);
    }
}


function send_request($code,$email) {

    $time = time()*1000;
    $request_url = "https://invites.oneplus.net/index.php?r=share/signup&success_jsonpCallback=success_jsonpCallback&email=$email&koid=$code&_=$time";

    // Get cURL resource
    $curl = curl_init();

    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $request_url,
    ));

    // Send the request & save response to $resp
    $resp = curl_exec($curl);

    // Close request to clear up some resources
    curl_close($curl);

    return $resp;

}

function generate_emails($email) {

    // no. of dots possible in gmail email
    // If email is "abc" then total dots which are possible between chars are 2 
    // (a.b.c)
    $counter = strlen($email)-1;

    // array declaration for possible combinations
    $possible_emails = array();

    $some_possible_emails = array();

    $all_possible_emails = array();

    while($counter > 0) {

        if( count($possible_emails) > 0 ) {

            // loop till we generate all possible emails for each email
            while(count($possible_emails)>0) {

                $temp_email = array_pop($possible_emails);

                $some_possible_emails = array_merge($some_possible_emails,generate_emails_helper($temp_email));

            }

            $possible_emails = array_unique($some_possible_emails);

            // add generated emails to final array of possible emails
            $all_possible_emails = array_merge($possible_emails,$all_possible_emails);

            $some_possible_emails = array();

        }
        else {
            // first call to generate_emails_helper
            $possible_emails = generate_emails_helper($email);

            // Add it to final array of possible emails
            $all_possible_emails = array_merge($possible_emails,$all_possible_emails);
        }

        $counter--;

    }


    return array_unique($all_possible_emails);
}

function generate_emails_helper($email) {

    $special_char = ".";

    $possible_emails = array();

    /*
     * If email is "abc" (without @gmail.com)
     *
     * .abc
     * a.bc
     * ab.c
     * abc.
     * --------
     * .a.bc
     * .ab.c
     * .abc.
     * etc...
     */

    $email_len = strlen($email);

    for($i=0;$i<=$email_len;$i++) {

        $string = substr($email,0,$i) . $special_char . substr($email,$i,strlen($email));
        //echo "-- $string -- \n";

        //condition if we want to remove emails contains .. OR start with . OR 
        //ends with . --> (strpos($string,'..') === false && $string[0] != '.' && $string[strlen($string)-1] != '.')
        
        if (strpos($string,'..') === false) {
            //echo "-- adding -- \n";
            array_push($possible_emails,substr($email,0,$i) . $special_char . substr($email,$i,strlen($email)));
        } 

    }

    return array_unique($possible_emails);

}
