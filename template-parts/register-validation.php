<?php

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

?>