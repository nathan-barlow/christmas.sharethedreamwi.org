<?php
require_once('../includes/db-connection.php');
require_once('../includes/log-error.php');

function getFamilies() {
    $conn = dbConnect('read');

    $query_members = mysqli_query($conn,
        "SELECT
            registered_families.family_id as FAMILY_CODE,
            registered_families.family_name as LAST_NAME,
            registered_families.phone as PHONE,
            registered_families.email as EMAIL,
            registered_families.family_number as FAMILY_NUMBER,
            registered_families.date_registered as DATE_REGISTERED,
            registered_families.family_gift as FAMILY_GIFT,
            registered_families.packed as PACKED,
            registered_families.attended as ATTENDED,
            registered_families.picked_up as PICKED_UP,
            registered_families.reservation as RESERVATION,
            registered_families.access as ACCESS,
            registered_families.notes as NOTES,
            registered_members.member_id as MEMBER_ID,
            registered_members.first_name as FIRST_NAME,
            CASE
                WHEN registered_members.age >= 18 THEN 'adult'
                ELSE registered_members.age
            END as AGE,
            registered_members.gift_preference as GIFT
        FROM registered_families
        JOIN registered_members ON (registered_families.family_id = registered_members.family_id)
        ORDER BY family_number, registered_members.age DESC");

    $data = array();
    $i = 0;
    while($row = mysqli_fetch_array($query_members)){
        $data[$row["FAMILY_NUMBER"]]["fam_number"] = $row["FAMILY_NUMBER"];
        $data[$row["FAMILY_NUMBER"]]["fam_code"] = htmlspecialchars($row["FAMILY_CODE"]);
        $data[$row["FAMILY_NUMBER"]]["fam_name"] = htmlspecialchars($row["LAST_NAME"]);
        $data[$row["FAMILY_NUMBER"]]["fam_phone"] = htmlspecialchars($row["PHONE"]);
        $data[$row["FAMILY_NUMBER"]]["fam_email"] = htmlspecialchars($row["EMAIL"]);
        $data[$row["FAMILY_NUMBER"]]["fam_gift"] = htmlspecialchars($row["FAMILY_GIFT"]);
        $data[$row["FAMILY_NUMBER"]]["fam_reservation"] = htmlspecialchars($row["RESERVATION"]);
        $data[$row["FAMILY_NUMBER"]]["packed"] = htmlspecialchars($row["PACKED"]);
        $data[$row["FAMILY_NUMBER"]]["attended"] = htmlspecialchars($row["ATTENDED"]);
        $data[$row["FAMILY_NUMBER"]]["picked_up"] = htmlspecialchars($row["PICKED_UP"]);
        $data[$row["FAMILY_NUMBER"]]["access"] = htmlspecialchars($row["ACCESS"]);
        $data[$row["FAMILY_NUMBER"]]["notes"] = htmlspecialchars($row["NOTES"]);
        $data[$row["FAMILY_NUMBER"]]["register_date"] = date("M j, Y g:ia", strtotime($row["DATE_REGISTERED"]) - 5 * 3600);
        $data[$row["FAMILY_NUMBER"]]["members"][$i] = array(
            "name"=>htmlspecialchars($row["FIRST_NAME"]),
            "age"=>htmlspecialchars($row["AGE"]),
            "gift"=>htmlspecialchars($row["GIFT"]),
        );
        $i++;
    }

    $data = array_values($data);

    for($i = 0; $i < count($data); $i++) {
        $data[$i]['members'] = array_values($data[$i]['members']);
    }

    $conn->close();

    return $data;
}

function getFamily($number) {
    $conn = dbConnect('read');

    $query = ("SELECT
            registered_families.family_id as FAMILY_CODE,
            registered_families.family_name as LAST_NAME,
            registered_families.phone as PHONE,
            registered_families.email as EMAIL,
            registered_families.family_number as FAMILY_NUMBER,
            registered_families.date_registered as DATE_REGISTERED,
            registered_families.family_gift as FAMILY_GIFT,
            registered_families.packed as PACKED,
            registered_families.attended as ATTENDED,
            registered_families.picked_up as PICKED_UP,
            registered_families.reservation as RESERVATION,
            registered_families.email_reminders as EMAIL_REMINDERS,
            registered_families.access as ACCESS,
            registered_families.notes as NOTES,
            registered_members.member_id as MEMBER_ID,
            registered_members.first_name as FIRST_NAME,
            registered_members.age as AGE,
            registered_members.gift_preference as GIFT
        FROM registered_families
        JOIN registered_members ON (registered_families.family_id = registered_members.family_id)
        WHERE family_number = ?");

    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $number);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $family = array();
        $i = 0;
        while($row = mysqli_fetch_array($result)){
            $family["fam_code"] = htmlspecialchars($row["FAMILY_CODE"]);
            $family["fam_number"] = $row["FAMILY_NUMBER"];
            $family["fam_name"] = htmlspecialchars($row["LAST_NAME"]);
            $family["fam_phone"] = htmlspecialchars($row["PHONE"]);
            $family["fam_email"] = htmlspecialchars($row["EMAIL"]);
            $family["fam_gift"] = htmlspecialchars($row["FAMILY_GIFT"]);
            $family["fam_reservation"] = htmlspecialchars($row["RESERVATION"]);
            $family["packed"] = htmlspecialchars($row["PACKED"]);
            $family["attended"] = htmlspecialchars($row["ATTENDED"]);
            $family["picked_up"] = htmlspecialchars($row["PICKED_UP"]);
            $family["email_reminders"] = htmlspecialchars($row["EMAIL_REMINDERS"]);
            $family["register_date"] =  date("M j, Y g:ia", strtotime($row["DATE_REGISTERED"]) - 5 * 3600);
            $family["access"] = htmlspecialchars($row["ACCESS"]);
            $family["notes"] = htmlspecialchars($row["NOTES"]);
            $family["members"][$i] = array(
                "name"=>htmlspecialchars($row["FIRST_NAME"]),
                "age"=>htmlspecialchars($row["AGE"]),
                "gift"=>htmlspecialchars($row["GIFT"]),
            );
            $i++;
        }

        mysqli_stmt_close($stmt);

        $conn->close();
    
        return $family;
    } else {
        return "Invalid family number.";
    }
}

function getFamiliesEvent() {
    $conn = dbConnect('read');

    $query_families = mysqli_query($conn,
        "SELECT
            rf.family_id as FAMILY_CODE,
            rm.first_name as FIRST_NAME,
            rf.family_name as LAST_NAME,
            rf.phone as PHONE,
            rf.email as EMAIL,
            rf.family_number as FAMILY_NUMBER,
            rf.family_gift as GIFT,
            rf.attended as ATTENDED,
            rf.picked_up as PICKED_UP,
            rf.reservation as RESERVATION,
            rf.notes as NOTES,
            rf.checked_in_online as CHECKED_IN_ONLINE,
            COUNT(CASE WHEN rm.age < 18 THEN 1 END) as CHILDREN,
            COUNT(CASE WHEN rm.age >= 18 THEN 1 END) as ADULTS
        FROM registered_families rf
        LEFT JOIN registered_members rm ON rf.family_id = rm.family_id
        GROUP BY rf.family_id
        ORDER BY rf.picked_up, rf.attended, rf.reservation, rf.family_name;");

    $query_people = mysqli_query($conn,
        "SELECT COUNT(*) AS member_count
         FROM registered_members
         JOIN registered_families ON registered_members.family_id = registered_families.family_id
         WHERE registered_families.attended IS NOT NULL AND registered_families.picked_up IS NULL;");

    $query_people_served = mysqli_query($conn,
        "SELECT COUNT(*) AS member_count
         FROM registered_members
         JOIN registered_families ON registered_members.family_id = registered_families.family_id
         WHERE registered_families.attended IS NOT NULL;");

    $data = array();
    $i = 0;
    while($row = mysqli_fetch_array($query_families)){
        $data[$row["FAMILY_NUMBER"]]["fam_code"] = htmlspecialchars($row["FAMILY_CODE"]);
        $data[$row["FAMILY_NUMBER"]]["first_name"] = htmlspecialchars($row["FIRST_NAME"]);
        $data[$row["FAMILY_NUMBER"]]["fam_name"] = htmlspecialchars($row["LAST_NAME"]);
        $data[$row["FAMILY_NUMBER"]]["fam_phone"] = htmlspecialchars($row["PHONE"]);
        $data[$row["FAMILY_NUMBER"]]["fam_number"] = $row["FAMILY_NUMBER"];
        $data[$row["FAMILY_NUMBER"]]["fam_email"] = htmlspecialchars($row["EMAIL"]);
        $data[$row["FAMILY_NUMBER"]]["fam_adults"] = htmlspecialchars($row["ADULTS"]);
        $data[$row["FAMILY_NUMBER"]]["fam_kids"] = htmlspecialchars($row["CHILDREN"]);
        $data[$row["FAMILY_NUMBER"]]["fam_gift"] = htmlspecialchars($row["GIFT"]);
        $data[$row["FAMILY_NUMBER"]]["fam_reservation"] = htmlspecialchars($row["RESERVATION"]);
        $data[$row["FAMILY_NUMBER"]]["attended"] = htmlspecialchars($row["ATTENDED"]);
        $data[$row["FAMILY_NUMBER"]]["picked_up"] = htmlspecialchars($row["PICKED_UP"]);
        $data[$row["FAMILY_NUMBER"]]["notes"] = htmlspecialchars($row["NOTES"]);
        $data[$row["FAMILY_NUMBER"]]["checked_in_online"] = htmlspecialchars($row["CHECKED_IN_ONLINE"]);
        $i++;
    }

    $people_here = mysqli_fetch_assoc($query_people)["member_count"];
    $people_served = mysqli_fetch_assoc($query_people_served)["member_count"];

    $result = array(
        "people_here" => $people_here,
        "people_served" => $people_served,
        "families" => array_values($data)
    );

    $conn->close();

    return $result;
}

// code: family number
// packed: true if doesn't need to be packed, false if needs to be packed
function togglePacked($number, $pack) {
    $conn = dbConnect('read');

    $code_query = $conn->prepare("SELECT family_id
        FROM registered_families
        WHERE family_number = ?");

    $code_query->bind_param("s", $number);
    $code_query->execute();
    $code_query->bind_result($code);    
    $code_query->fetch();
    $code_query->close();

    if($pack == '0') {
        $togglePacked = $conn->prepare("UPDATE registered_families
            SET packed = 1
            WHERE family_number = ?");

        $updateMemberInv = $conn->prepare("UPDATE event_settings AS es
            JOIN (
                SELECT rm.gift_preference, COUNT(*) AS member_count
                FROM registered_members AS rm
                WHERE rm.family_id = ?
                GROUP BY rm.gift_preference
            ) AS member_counts ON es.value = member_counts.gift_preference
            SET es.inventory = es.inventory - member_counts.member_count");

        $updateFamilyInv = $conn->prepare("UPDATE event_settings AS es
            JOIN registered_families AS rf ON es.value = rf.family_gift
            SET es.inventory = es.inventory - 1
            WHERE es.name = 'gifts_family' AND rf.family_id = ?;");
    } else {
        $togglePacked = $conn->prepare("UPDATE registered_families
            SET packed = 0
            WHERE family_number = ?");

        $updateMemberInv = $conn->prepare("UPDATE event_settings AS es
            JOIN (
                SELECT rm.gift_preference, COUNT(*) AS member_count
                FROM registered_members AS rm
                WHERE rm.family_id = ?
                GROUP BY rm.gift_preference
            ) AS member_counts ON es.value = member_counts.gift_preference
            SET es.inventory = es.inventory + member_counts.member_count");

        $updateFamilyInv = $conn->prepare("UPDATE event_settings AS es
            JOIN registered_families AS rf ON es.value = rf.family_gift
            SET es.inventory = es.inventory + 1
            WHERE es.name = 'gifts_family' AND rf.family_id = ?;");
    }

    $togglePacked->bind_param("s", $number);
    $togglePacked->execute();

    $updateMemberInv->bind_param("s", $code);
    $updateMemberInv->execute();

    $updateFamilyInv->bind_param("s", $code);
    $updateFamilyInv->execute();

    if($togglePacked->affected_rows == 1) {
        echo "true";
    }
    $togglePacked->close();
    $updateMemberInv->close();
    $updateFamilyInv->close();
    $conn->close();
}

function toggleFamily($number, $action) {
    $conn = dbConnect('read');

    if($action == 'here') {
        $toggle_query = $conn->prepare("UPDATE registered_families
            SET attended = CASE
                WHEN attended IS NULL THEN NOW()
                ELSE NULL
            END, checked_in_online = 0
            WHERE family_number = ?");
    } else if ($action == 'left') {
        $toggle_query = $conn->prepare("UPDATE registered_families
            SET picked_up = CASE
                WHEN picked_up IS NULL THEN NOW()
                ELSE NULL
            END
            WHERE family_number = ?");
    } else {
        echo "Invalid parameters.";
        logError("ADMIN", ("Invalid parameters on toggleFamily() function private-functions.php. ACTION = " . htmlspecialchars($action)));
        exit;
    }

    $toggle_query->bind_param("s", $number);
    $toggle_query->execute();

    if($toggle_query->affected_rows == 1) {
        echo "true";
    }
    $toggle_query->close();
    $conn->close();
}

function updateNotes($number, $notes) {
    $conn = dbConnect('read');

    $updateNotes_query = $conn->prepare("UPDATE registered_families
        SET notes = ?
        WHERE family_number = ?");

    $updateNotes_query->bind_param("ss", $notes, $number);
    $updateNotes_query->execute();

    if($updateNotes_query->error == "") {
        echo "true";
    } else {
        echo "failed to update: " . $updateNotes_query->error;
    }
    $updateNotes_query->close();
    $conn->close();
}

function getGifts() {
    $conn = dbConnect('read');

    $query_familyGifts = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_family'");
    $query_0x3 = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_0-3'");
    $query_4x7 = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_4-7'");
    $query_8x11 = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_8-11'");
    $query_12x17 = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_12-17'");
    $query_18x = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_18+'");

    $gifts = [];
    $gifts['Family'] = [];
    $gifts['Age 0-3'] = [];
    $gifts['Age 4-7'] = [];
    $gifts['Age 8-11'] = [];
    $gifts['Age 12-17'] = [];
    $gifts['Age 18+'] = [];
    while($row = mysqli_fetch_array($query_familyGifts)) {
        array_push($gifts['Family'], $row["gift"]);
    }
    while($row = mysqli_fetch_array($query_0x3)) {
        array_push($gifts['Age 0-3'], $row["gift"]);
    }
    while($row = mysqli_fetch_array($query_4x7)) {
        array_push($gifts['Age 4-7'], $row["gift"]);
    }
    while($row = mysqli_fetch_array($query_8x11)) {
        array_push($gifts['Age 8-11'], $row["gift"]);
    }
    while($row = mysqli_fetch_array($query_12x17)) {
        array_push($gifts['Age 12-17'], $row["gift"]);
    }
    while($row = mysqli_fetch_array($query_18x)) {
        array_push($gifts['Age 18+'], $row["gift"]);
    }

    return json_encode($gifts);

    $conn->close();
}

function resetEvent() {
    $conn = dbConnect('read');

    // Start a transaction
    mysqli_begin_transaction($conn);

    $tables = ['deleted_families', 'registered_members', 'registered_families', 'family_id_list', 'feedback', 'language_changes'];
    $backupYear = date('Y');

    // Disable foreign key checks
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

    foreach ($tables as $table) {
        // Create backup table name
        $backupTableName = $backupYear . '_' . $table;

        // Check if the backup table name already exists
        $counter = 2;
        while (tableExists($backupTableName, $conn)) {
            $backupTableName = $backupYear . '_' . $counter . '_' . $table;
            $counter++;
        }

        // Backup table
        $backupQuery = "CREATE TABLE $backupTableName AS SELECT * FROM $table";
        $backupResult = mysqli_query($conn, $backupQuery);

        if (!$backupResult) {
            // Rollback the transaction on failure
            mysqli_rollback($conn);
            return "Backup of table $table failed. " . mysqli_error($conn);
        }

        // Clear original table
        $clearQuery = "TRUNCATE TABLE $table";
        $clearResult = mysqli_query($conn, $clearQuery);

        if (!$clearResult) {
            // Rollback the transaction on failure
            mysqli_rollback($conn);
            return "Clearing of table $table failed. " . mysqli_error($conn);
        }

        // Restart counter
        $resetAutoIncrementQuery = "ALTER TABLE $table AUTO_INCREMENT = 1";
        $resetAutoIncrementResult = mysqli_query($conn, $resetAutoIncrementQuery);
    
        if (!$resetAutoIncrementResult) {
            // Rollback the transaction on failure
            mysqli_rollback($conn);
            return "Resetting auto-increment for table $table failed. " . mysqli_error($conn);
        }
    }

    $clearFailedAttemptsQuery = "DELETE FROM failed_attempts WHERE attempts = 0";
    $clearFailedAttemptsResult = mysqli_query($conn, $clearFailedAttemptsQuery);

    if (!$clearFailedAttemptsResult) {
        // Rollback the transaction on failure to clear failed attempts
        mysqli_rollback($conn);
        return "Clearing of failed attempts failed. " . mysqli_error($conn);
    }

    // Enable foreign key checks
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

    // Commit the transaction if everything is successful
    mysqli_commit($conn);

    $conn->close();

    return "success";
}

function tableExists($tableName, $conn) {
    $checkQuery = "SHOW TABLES LIKE '$tableName'";
    $result = mysqli_query($conn, $checkQuery);
    return $result && $result->num_rows > 0;
}

?>