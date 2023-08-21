<?php
require('includes/db-connection.php');

function checkCode($family_id) {
    $conn = dbConnect('read');
    $problems = array();
    // CHECK IF CODE IS VALID ---------------------------------------------------------------------------------
    // Prepare MySQL statements
    $query_valid_code = $conn->prepare("SELECT * FROM family_id_list WHERE family_code = ?");
    $query_valid_code->bind_param("s", $family_id);

    $query_used_code = $conn->prepare("SELECT * FROM registered_families WHERE family_id = ?");
    $query_used_code->bind_param("s", $family_id);
    
    $query_valid_code->execute(); // should return 1 row if code is in database
    $result_valid_code = $query_valid_code->get_result();
    $row_count_valid_code = mysqli_num_rows($result_valid_code);

    $query_used_code->execute(); // should return 0 rows if code hasn't been registered
    $result_used_code = $query_used_code->get_result();
    $row_count_used_code = mysqli_num_rows($result_used_code);

    if($row_count_valid_code == 0) {
        // code not found in database
        array_push($problems, "Family code is invalid.");
    } else if ($row_count_used_code >= 1) {
        // code already registered
        array_push($problems, "Family code has already been registered. Please use your own, unique code.");
    }
    
    $query_valid_code->close();
    $query_used_code->close();

    return $problems;
}

function validateRegistration($edit = false) {
    $conn = dbConnect('read');
    $problems = array();

    $fam_id = strtoupper(trim($_POST['fam-id']));
    $fam_members = intval($_POST['fam-members']);
    $fam_name = ucfirst(trim($_POST['fam-name']));
    $fam_email = trim($_POST['fam-email']);
    $fam_phone = preg_replace("/[^0-9]/", '', trim($_POST['fam-phone']));
    $fam_gift = trim($_POST['fam-gift']);
    $limit_members = true;

    $members = $_POST['members'];

    foreach ($members as $member) {
        $member['name'] = ucfirst(trim($member['name']));
        $member['age'] = intval($member['age']);
        $member['gift'] = trim($member['gift']);
    }

    if(!$edit) {
        $checkCode = checkCode($fam_id);
    }

    if($checkCode) {
        array_push($problems, $checkCode[0]);
    }
    if(($fam_members != count($members))) {
        array_push($problems, "Number of members and members entered do not match. Please contact site administrator.");
    }
    if ((limit_members && count($members) > 30) || count($members) < 1) {
        array_push($problems, "You can only register between 1 and 8 family members using the online form.");
    }
    if (!filter_var($fam_email, FILTER_VALIDATE_EMAIL)) {
        array_push($problems, "Email is invalid.");
    } else if (strlen($fam_email) > 255) {
        array_push($problems, "Email is too long. Please try another email.");
    }
    if (strlen($fam_name) > 255) {
        array_push($problems, "Last name entered is too long.");
    }
    if (strlen($fam_phone) > 15) {
        array_push($problems, "Phone number entered is too long.");
    }
    if (strlen($fam_gift) > 255) {
        array_push($problems, "Family gift is too long. Please contact site administrator.");
    }
    if (!$fam_id || !$fam_name || !$fam_phone || !$fam_email) {
        array_push($problems, "Please fill out all fields.");
    }
    foreach ($members as $member) {
        if (strlen($member["name" ]) > 255) {
            array_push($problems, htmlentities($member['name']) . "'s name is too long.");
        }
        if ($member["age"] > 130) {
            array_push($problems, htmlentities($member['name']) . "'s age is too large.");
        }
        if (strlen($member["gift"]) > 255) {
            array_push($problems, htmlentities($member['name']) . "'s gift preference is too long. Please contact site administrator.");
        }
    }
    return $problems;
}

function insertFamily() {
    $conn = dbConnect('read');

    $fam_number = intval($_POST['fam-number']);
    $fam_id = strtoupper(trim($_POST['fam-id']));
    $fam_name = ucfirst(trim($_POST['fam-name']));
    $fam_email = trim($_POST['fam-email']);
    $fam_phone = preg_replace("/[^0-9]/", '', trim($_POST['fam-phone']));
    $fam_gift = $_POST['fam-gift'];

    if($_POST['email-reminders']) {
        $email_reminders = 1;
    } else {
        $email_reminders = 0;
    }

    $members = $_POST['members'];

    foreach ($members as $member) {
        $member['name'] = ucfirst(trim($member['name']));
        $member['age'] = intval($member['age']);
        $member['gift'] = trim($member['gift']);
    }

    // If there are no problems, add family to database
    if($fam_number) {
        $add_family = $conn->prepare("INSERT INTO registered_families 
        (family_number, family_id, phone, email, family_name, family_gift, email_reminders)
        VALUES (?, ?, ?, ?, ?, ?, ?)");

        $add_family->bind_param("isssssi", $fam_number, $fam_id, $fam_phone, $fam_email, $fam_name, $fam_gift, $email_reminders);
    } else {
        $add_family = $conn->prepare("INSERT INTO registered_families 
        (family_id, phone, email, family_name, family_gift, email_reminders)
        VALUES (?, ?, ?, ?, ?, ?)");

        $add_family->bind_param("sssssi", $fam_id, $fam_phone, $fam_email, $fam_name, $fam_gift, $email_reminders);
    }

    $add_family->execute();
    $add_family->close();


    // If there are no problems, add family members to database
    $add_member = $conn->prepare("INSERT INTO registered_members (first_name, age, gift_preference, family_id)
                                    VALUES (?, ?, ?, ?)");
    $add_member->bind_param("siss", $mem_name, $mem_age, $mem_gift, $fam_id);

    foreach ($members as $member) {
        $mem_name = $member['name'];
        $mem_age = $member['age'];
        $mem_gift = $member['gift'];
        $add_member->execute();
    }

    $add_member->close();
    $conn->close();
}

function backupFamily() {
    $conn = dbConnect('read');

    $fam_id = strtoupper(trim($_POST['fam-id']));

    // If there are no problems, insert family into backup database
    $remove_old = $conn->prepare("DELETE FROM deleted_families
        WHERE family_id = ?");
    $remove_old->bind_param("s", $fam_id);
    $remove_old->execute();

    $backup_family = $conn->prepare("INSERT INTO deleted_families
        SELECT * FROM registered_families
        WHERE family_id = ?");
    $backup_family->bind_param("s", $fam_id);
    $backup_family->execute();

    if($backup_family->affected_rows == 1) {
        $delete_members = $conn->prepare("DELETE FROM registered_members WHERE family_id = ?");
        $delete_members->bind_param("s", $fam_id);
        $delete_members->execute();

        $delete_family = $conn->prepare("DELETE FROM registered_families WHERE family_id = ?");
        $delete_family->bind_param("s", $fam_id);
        $delete_family->execute();

        if($delete_members->affected_rows >= 1 && $delete_family->affected_rows == 1) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }

    $backup_family->close();
    $delete_members->close();
    $delete_family->close();
    $conn->close();
}

function registerEmail() {
    $email_problem = false;

    $fam_email = trim($_POST['fam-email']);
    if($_POST['email-reminders']) {
        $email_opt = true;
    } else {
        $email_opt = false;
    }

    $fam_id = strtoupper(trim($_POST['fam-id']));
    $fam_name = ucfirst(trim($_POST['fam-name']));
    $members = $_POST['members'];
    $first_name = ucfirst(trim($members[1]['name']));
    $total_members = count($members);

    $data = '{"email": "' . $fam_email . '", 
        "listIds" : [11],
        "attributes": {
            "FIRSTNAME" : "' . $first_name . '", 
            "LASTNAME" : "' . $fam_name . '", 
            "FAMILYCODE" : "' . $fam_id . '", 
            "FAMILYMEMBERS" : "'. $total_members . '"}}';


    if($email_opt && $fam_email) {
        $ch = curl_init();
        $api_key = "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";

        curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/contacts');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $headers = array();
        $headers[] = 'Api-Key: ' . $api_key;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $email_problem = 'Error:' . curl_error($ch);
        } else if(!strpos($result, "id")) {
            $email_problem = $result;
        }
        curl_close($ch);

        return $email_problem;
    } else {
        return "opt-out";
    }
}

?>