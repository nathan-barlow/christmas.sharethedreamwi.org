<?php
get_template_part('template-parts/db-connection');
// Connect to Database Using MySQL
$conn = dbConnect('read');

if ($_POST['confirm'] === "DELETE" && isset($_POST['family-id-delete'])) {
    $family_id_delete = $_POST['family-id-delete'];

    $create_backup = $conn->prepare("INSERT INTO deleted_families
        SELECT * FROM registered_families
        WHERE family_id = ?");
    $delete_members = $conn->prepare("DELETE FROM registered_members WHERE family_id = ?");
    $delete_family = $conn->prepare("DELETE FROM registered_families WHERE family_id = ?");

    $create_backup->bind_param("s", $family_id_delete);
    $delete_family->bind_param("s", $family_id_delete);
    $delete_members->bind_param("s", $family_id_delete);

    $create_backup->execute();
    $create_backup->close();
    $delete_members->execute();
    $delete_members->close();
    $delete_family->execute();
    $delete_family->close();
}

// Prepare MySQL statement
$query_families = mysqli_query($conn, "SELECT * FROM registered_families");

$query_organizations = mysqli_query($conn, "SELECT organization,
        COUNT(organization) as registered,
        (SELECT COUNT(organization) FROM family_id_list WHERE family_id_list.organization = f.organization) as total
    FROM
        registered_families,
        family_id_list f
    WHERE registered_families.family_id = f.family_code
    GROUP BY organization
    ORDER BY registered DESC
");

$query_family_makeup = mysqli_query($conn, "SELECT
        COUNT(case when age BETWEEN 0 and 3 then 1 else null end) as INFANT,
        COUNT(case when age BETWEEN 4 and 7 then 1 else null end) as TODDLER,
        COUNT(case when age BETWEEN 8 and 11 then 1 else null end) as CHILD,
        COUNT(case when age BETWEEN 12 and 17 then 1 else null end) as TEEN,
        COUNT(case when age >= 18 then 1 else null end) as ADULT
    FROM registered_members
");

$organization_info = mysqli_fetch_all($query_organizations, MYSQLI_ASSOC);
$family_makeup_info = mysqli_fetch_all($query_family_makeup, MYSQLI_ASSOC);

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
        registered_members.member_id as MEMBER_ID,
        registered_members.first_name as FIRST_NAME,
            CASE
                WHEN registered_members.age = 18 THEN 'adult'
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
    $data[$row["FAMILY_NUMBER"]]["register_date"] = ($row["DATE_REGISTERED"]);
    $data[$row["FAMILY_NUMBER"]]["members"][$i] = array(
        "name"=>htmlspecialchars($row["FIRST_NAME"]),
        "age"=>htmlspecialchars($row["AGE"]),
        "gift"=>htmlspecialchars($row["GIFT"]),
    );
    $i++;
}

function writeToCSV($family_data) {
    $filename = 'registered-members.csv';
    ob_clean();
    $fp = fopen($filename, 'w');

    $csvHeader = ["Date", "Number", "Code", "Name", "Email", "Phone", "Family Gift", "Reservation Time", "Name", "Age", "Gift", "Packed", "Attended", "Picked Up"];
    
    fputcsv($fp, $csvHeader);
    
    foreach($family_data as $family_number => $family) {
        $register_date = $family["register_date"];
        $fam_number = $family["fam_number"];
        $fam_code = $family["fam_code"];
        $fam_name = $family["fam_name"];
        $fam_email = $family["fam_email"];
        $fam_phone = $family["fam_phone"];
        $fam_gift = $family["fam_gift"];
        $fam_reservation = $family["fam_reservation"];
        $packed = $family["packed"];
        $attended = $family["attended"];
        $picked_up = $family["picked_up"];
    
        foreach($family["members"] as $member) {
            $newRow = [$register_date, $fam_number, $fam_code, $fam_name, $fam_email, $fam_phone, $fam_gift, $fam_reservation, $member["name"], $member["age"], $member["gift"], $packed, $attended, $picked_up];
            fputcsv($fp, $newRow);
        }
    }
    header('Content-type: text/csv');
    header('Content-disposition:attachment; filename="'.$filename.'"');
    readfile($filename);
    
    fclose($fp);

    unlink($filename);

    exit;
}

if(isset($_POST['download-csv'])) {
    writeToCSV($data);
}

$old_members = isset($_COOKIE["old-members"]) ? htmlspecialchars($_COOKIE["old-members"]) : '';
$old_families = isset($_COOKIE["old-families"]) ? htmlspecialchars($_COOKIE["old-families"]) : '';

$new_members = mysqli_num_rows($query_members);
$new_families = mysqli_num_rows($query_families);

if($old_families || $old_members) {
    if($new_members - $old_members > 0) {
        $difference_members = $new_members - $old_members;
    }
    if($new_families - $old_families > 0) {
        $difference_families = $new_families - $old_families;
    }
}

setcookie("old-members", $new_members, time()+(86400 * 30), "/");
setcookie("old-families", $new_families, time()+(86400 * 30), "/");

$conn->close();

get_header('archive');

?>

    <?php if ($family_id_delete): ?>
    <div class='wrapper'>
        <div class='message message-success'>
            Family <strong><?php echo htmlspecialchars($family_id_delete); ?></strong> has been deleted.
        </div>
    </div>
    <?php endif; ?>
</header>
<main class="registrations-dashboard wrapper">
    <nav class="nav-secondary">
        <a href="/private-registrations">Registrations Home</a>
        <a class="active" href="/private-registered-families">Families</a>
        <a href="/private-gifts">Gifts</a>
        <a href="/private-family-code">Family Codes</a>
        <a href="/private-register-family">Register New Family</a>
        <a href="/private-event-settings">Event Settings</a>
        <a href="/private-event">Event</a>
    </nav>
    <div class="grid">
        <div id="registrations" class="card registrations">
            <div class="options-container" id="registered-families-options">
                <button class="edit-family button button-gray-150" onclick="toggleMenu('registered')" title="registration options">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <div class="options-menu">
                    <a class="options-menu-item button" href="/private-register-family">
                        <i class="bi bi-person-plus"></i>
                        <p>Add Family</p>
                    </a>
                    <button class="options-menu-item" onclick="window.print()">
                        <i class="bi bi-printer"></i>
                        <p>Print Labels</p>
                    </button>
                    <form method="post" hidden id="download-csv"></form>
                    <button class="options-menu-item" form="download-csv" name="download-csv" value="true">
                        <i class="bi bi-download"></i>
                        <p>Download CSV</p>
                    </button>
                    <button class="options-menu-item" id="expand-contract" onclick="toggleAllTables()">
                        <i class="bi bi-arrows-angle-expand"></i>
                        <p>Expand All</p>
                    </button>
                    <button class="options-menu-item" onclick="resetPrinted()">
                        <i class="bi bi-arrow-clockwise"></i>
                        <p>Reset Printed</p>
                    </button>
                    <button class="options-menu-item" onclick="markAllPacked()">
                        <i class="bi bi-check2"></i>
                        <p>Mark Visible as Packed</p>
                    </button>
                    <button class="options-menu-item" onclick="markAllNotPacked()">
                        <i class="bi bi-square"></i>
                        <p>Mark Visible as Not Packed</p>
                    </button>
                    <button class="options-menu-item" onclick="toggleGiftColumn()">
                        <i class="bi bi-gift"></i>
                        <p>Toggle Gift Column</p>
                    </button>
                </div>
            </div>

            <div class="options-container" id="search-options">
                <button class="edit-family button button-gray-150" onclick="toggleMenu('search')" title="search options">
                    <i class="bi bi-search"></i>
                </button>
                <div id="search-options-menu" class="options-menu">
                    <strong>Search Options</strong>
                    <button class="flex flex-xs flex-center options-menu-item" data-query=".fam-number" data-selected="true">
                        <i class="bi bi-check2 opacity-0"></i>
                        <p>Family Number</p>
                    </button>
                    <button class="flex flex-xs flex-center options-menu-item" data-query=".fam-name" data-selected="true">
                        <i class="bi bi-check2 opacity-0"></i>
                        <p>Last Name</p>
                    </button>
                    <button class="flex flex-xs flex-center options-menu-item" data-query=".fam-code" data-selected="true">
                        <i class="bi bi-check2 opacity-0"></i>
                        <p>Family Code</p>
                    </button>
                    <button class="flex flex-xs flex-center options-menu-item" data-query=".family-gift" data-selected="false">
                        <i class="bi bi-check2 opacity-0"></i>
                        <p>Family Gift</p>
                    </button>
                    <button class="flex flex-xs flex-center options-menu-item" data-query=".fam-email" data-selected="false">
                        <i class="bi bi-check2 opacity-0"></i>
                        <p>Family Email</p>
                    </button>
                    <button class="flex flex-xs flex-center options-menu-item" data-query=".fam-phone" data-selected="false">
                        <i class="bi bi-check2 opacity-0"></i>
                        <p>Family Phone</p>
                    </button>
                    <button class="flex flex-xs flex-center options-menu-item" data-query="td:nth-of-type(1)" data-selected="false">
                        <i class="bi bi-check2 opacity-0"></i>
                        <p>Member Name</p>
                    </button>
                    <button class="flex flex-xs flex-center options-menu-item" data-query="td:nth-of-type(2)" data-selected="false">
                        <i class="bi bi-check2 opacity-0"></i>
                        <p>Member Age</p>
                    </button>
                    <button class="flex flex-xs flex-center options-menu-item" data-query="td:nth-of-type(3)" data-selected="true">
                        <i class="bi bi-check2 opacity-0"></i>
                        <p>Member Gift</p>
                    </button>
                </div>
            </div>

            <h2>Registered Families</h2>

            <div class="registration-filters flex flex-sm flex-center flex-wrap">
                <input type="search" id="searchFamilies" oninput="searchFamilies()" placeholder="Search for family, name, or family code">

                <div class="filter-options">
                    <button id="not-packed" onclick="filterFamilies('not-packed')">
                        Unpacked<strong class="bg-error100">0</strong>
                    </button>
                    <button id="packed" onclick="filterFamilies('packed')">
                        Packed<strong class="bg-success100">0</strong>
                    </button>
                    <button id="not-printed" onclick="filterFamilies('not-printed')">
                        Unprinted<strong class="bg-error100">0</strong>
                    </button>
                    <button id="printed" onclick="filterFamilies('printed')">
                        Printed<strong class="bg-success100">0</strong>
                    </button>
                </div>
            </div>

            <div id="family-member-cards" class="family-member-cards grid grid-2">
                <div class="message">
                    Loading families...
                </div>
            </div>

            <dialog id="delete-confirmation">
                <h2>Delete <span id="lastname"></span> family</h2>
                <form action="" method="post" class="register-form">
                    <label for="family-id-delete">This family code will be unregistered</label>
                    <input name="family-id-delete" id="family-id-delete" type="text" readonly required>

                    <label for="confirm">To confirm, type DELETE</label>
                    <input name="confirm" id="confirm" type="text" pattern="DELETE" placeholder="DELETE" required>

                    <div class="buttons justify-right">
                        <button class="button-gray-100" type="button" onclick="document.getElementById('delete-confirmation').close();">Cancel</button>
                        <button class="button button-main-500" type="submit">Delete</button>
                    </div>
                </form>
            </dialog>
        </div>
    </div>

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    
    <div class="wrapper">
        <section>
            <?php the_content(); ?>
        </section>
        <?php endwhile; else: endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
<script type="text/javascript" src="/wp-content/themes/communitychristmasfoxcities.org/js/private.js"></script>

<script>
    var allFamilies
    var currentFilter;
    var row = "";
    const progressBars = document.querySelectorAll(".progress-bar");
    const searchVariables = localStorage.getItem('std_searchVariables') ? JSON.parse(localStorage.getItem('std_searchVariables')) : [".fam-code", ".fam-name", ".fam-number"];
    
    for(let i = 0; i < progressBars.length; i++) {
        const bar = progressBars[i];

        bar.innerHTML += '<div class="progress"></div>';
        const progress = bar.querySelector(".progress");

        const goal = parseInt(bar.dataset.goal);
        const currentProgress = parseInt(bar.dataset.current);
        
        progress.style.width = (currentProgress / goal) * 100 + "%";
    }

    const organizations = document.querySelectorAll(".card-organization");
    for(let i = 0; i < organizations.length; i++) {
        const organization = organizations[i];
        const progressBar = organization.querySelector(".progress-bar")
        const progressCurrent = parseInt(progressBar.dataset.current);
        const progressGoal = parseInt(progressBar.dataset.goal);
        
        if (progressCurrent <= (progressGoal * .9)) {
            progressBar.innerHTML += ("<p class='goal'>" + progressGoal + "</p>");
        }
        if (progressCurrent >= (progressGoal) * .03) {
            progressBar.querySelector(".progress").innerHTML += ("<p class='current'>" + progressCurrent + "</p>");
        }
    }

    document.addEventListener('keydown', evt => {
        if (evt.key === 'Escape') {
            closeMenus();
        }
    });

    document.addEventListener("click", closeMenus, true);

    searchVariables.forEach(function (query) {
        // Find elements with the matching data-query attribute
        let elements = document.querySelectorAll(`[data-query="${query}"]`);

        // Loop through the matching elements and add data-selected="selected"
        elements.forEach(function (element) {
            element.dataset.selected = "true";
            element.querySelector("i").classList.remove("opacity-0");
        });
    });

    const searchOptions = document.querySelectorAll("#search-options-menu .options-menu-item");

    searchOptions.forEach(function (option) {
        option.addEventListener("click", function () {
            let query = this.dataset.query;
            let selected = this.dataset.selected;

            if(selected === "true") {
                // remove from array
                let indexToRemove = searchVariables.indexOf(query);
                if (indexToRemove !== -1) {
                    searchVariables.splice(indexToRemove, 1);
                }
                this.querySelector("i").classList.add("opacity-0");
                this.dataset.selected = "false";
            } else {
                // add to array
                searchVariables.push(query);
                this.querySelector("i").classList.remove("opacity-0");
                this.dataset.selected = "true";
            }
            searchFamilies();
            localStorage.setItem("std_searchVariables", JSON.stringify(searchVariables));
        });
    });

    function searchFamilies() {
        // Declare variables
        var input, familiesContainer, families, dataSet, data, i, txtValue, match;
        input = document.getElementById("searchFamilies").value.toUpperCase();
        familiesContainer = document.getElementById("family-member-cards");
        families = familiesContainer.getElementsByClassName("family-card");

        for (i = 0; i < families.length; i++) {
            const dataSet = searchVariables.flatMap(selector => [...families[i].querySelectorAll(selector)]);

            match = false;

            for(a = 0; a < dataSet.length; a++) {
                data = dataSet[a];

                if (data) {
                    txtValue = data.textContent || data.innerText;
                    if (txtValue.toUpperCase().indexOf(input) > -1) {
                        match = true;
                        if(input != "") {
                            data.style.background = "var(--color-success-100)";
                        } else {
                            data.style.background = "";
                        }
                    } else {
                        data.style.background = "";
                    }
                }
            }

            if (match) {
                families[i].classList.remove("hide");
            } else {
                families[i].classList.add("hide");
            }
            
        }
    }

    function editFamily(id) {
        var data = new URLSearchParams();
        data.append("edit-family", id);
        
        var url = "/private-register-family?" + data.toString();
        location.href = url;
    }

    function deleteFamily(id, lastname) {
        let confirmationForm = document.getElementById("delete-confirmation");
        let lastNameText = document.getElementById("lastname");
        let idText = document.getElementById("family-id-delete");
        let confirmationText = document.getElementById("confirm");

        confirmationForm.showModal();
        idText.value = id;
        lastNameText.innerHTML = lastname;
        confirmationText.value = "";
        confirmationText.focus();
    }

    // toggle "family options" menu
    function toggleMenu(id) {
        let menu;

        if(id == "registered") {
            menu = document.querySelector("#registered-families-options .options-menu");
        } else if (id == "search") {
            menu = document.querySelector("#search-options-menu");
        } else {
            menu = document.querySelector("#family-" + id + " .options-menu");
        }

        menu.classList.add('open');
    }

    // toggle table show
    function toggleTable(id) {
        let table = document.querySelector("#family-" + id + " .all-info");
        let arrow_icon = document.querySelector("#family-" + id + " button.family-members .bi-chevron-down");

        table.classList.toggle('open');
        arrow_icon.classList.toggle('open');

        closeMenus();
    }

    function toggleAllTables() {
        let tables = document.querySelectorAll('.all-info');
        let arrows = document.querySelectorAll(".bi-chevron-down");
        let expand_contract = document.getElementById('expand-contract');
        let icon = expand_contract.querySelector('.bi');
        let icon_label = expand_contract.querySelector('p');

        if(icon_label.innerText == "Expand All") {
            icon.classList.remove('bi-arrows-angle-expand');
            icon.classList.add('bi-arrows-angle-contract');
            icon_label.innerText = "Contract All";

            for (i = 0; i < tables.length; i++) {
                tables[i].classList.add('open');
            }

            for (i = 0; i < arrows.length; i++) {
                arrows[i].classList.add('open');
            }
        } else {
            icon.classList.remove('bi-arrows-angle-contract');
            icon.classList.add('bi-arrows-angle-expand');
            icon_label.innerText = "Expand All";

            for (i = 0; i < tables.length; i++) {
                tables[i].classList.remove('open');
            }

            for (i = 0; i < arrows.length; i++) {
                arrows[i].classList.remove('open');
            }
        }

        toggleMenu("registered");
    }

    (function fetchFamilies() {
        let url = 'https://registration.christmas.sharethedreamwi.org/private/fetch-families.php/';

        let headers = new Headers();
        headers.set('Authorization', 'Basic ' + btoa(user + ":" + pass));

        let response = fetch(url, {headers: headers})
            .then(function (response) {
                return response.text();
            })
            .then(function (body) {
                if(body == "Authorization required") {
                    document.getElementById("family-member-cards").innerHTML = "<div class='message message-error'>Authorization failed</div>";
                } else {
                    allFamilies = JSON.parse(body);

                    if(allFamilies.length == 0) {
                        document.getElementById("family-member-cards").innerHTML = "<div class='message message-error'>No Registered Families</div>";
                    } else {
                        document.getElementById("family-member-cards").innerHTML = "";

                        let i = 0;
                        for(family in allFamilies) {
                            createCard(allFamilies[family], i, allFamilies.length);
                            i++;
                        }

                        for(family in allFamilies) {
                            theFamily = allFamilies[family];
                            for(mem in theFamily['members']) {
                                member = theFamily['members'][mem];
                                createMemberTables(member, theFamily['fam_number']);
                            }
                        }

                        generatePacked();
                        setPrinted();

                        // disable gift column by default
                        toggleGiftColumn();

                        function scrollToElement(elementId) {
                            const element = document.getElementById(elementId);
                            if (element) {
                                element.scrollIntoView({ behavior: 'smooth' }); // Scroll to the element smoothly
                            }
                        }

                        const urlHash = window.location.hash; // Get the anchor from the URL
                        if (urlHash) {
                            // Remove the "#" character from the URL anchor
                            const elementId = urlHash.substring(1);
                            scrollToElement(elementId); // Scroll to the element with the specified ID
                        }
                    }
                }
            });
    })();

    function createCard(family, i, totalFamilies) {
        let container = document.getElementById("family-member-cards");
        let s = '';

        if(family['members'].length != 1) {
            s = 's';
        }

        let adults = 0;
        let children = 0;

        for (let person of family.members) {
            if (person.age === "adult") {
                adults++;
            } else {
                children ++;
            }
        }

        if (adults == 1) {
            adults = adults + " adult";
        } else {
            adults = adults + " adults";
        }

        if (children == 1) {
            children = children + " child";
        } else {
            children = children + " children";
        }

        let newCard = `
            <div class='family-card' id="family-${family['fam_number']}" data-packed="${family['packed']}" data-printed="false" data-note="${family['notes']}">
                <div class="options-container">
                    <button class="edit-family button button-gray-100" onclick="toggleMenu(${family['fam_number']})" title="family options">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <div class="options-menu">
                        <a class="options-menu-item button" href="mailto:${family['fam_email']}">
                            <i class="bi bi-envelope"></i>
                            <p class="fam-email">${family['fam_email']}</p>
                        </a>
                        <a class="options-menu-item button" href="tel:${family['fam_phone']}">
                            <i class="bi bi-telephone"></i>
                            <p class="fam-phone">${family['fam_phone']}</p>
                        </a>
                        <button class="options-menu-item" onclick="editFamily(${family['fam_number']})">
                            <i class="bi bi-pencil-square"></i>
                            <p>Edit Familiy</p>
                        </button>
                        <button class="options-menu-item delete" onclick="deleteFamily('${family['fam_code']}', '${family['fam_name']}')">
                            <i class="bi bi-trash"></i>
                            <p>Delete Family</p>
                        </button>
                    </div>
                </div>
                
                <button class="edit-family button-toggle button button-gray-100" onclick="togglePacked(${family['fam_number']})" title="mark packed">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>

                <div class="family-info flex flex-sm flex-bottom flex-wrap">
                    <span class="fam-number">${family['fam_number']}</span>
                    <h3 class="fam-name">${family['fam_name']}</h3>
                    <p class="fam-code">${family['fam_code']}</p>
                </div>

                <span class="family-reservation margin-top-sm" data-time="${family['fam_reservation']}">
                    ${family['fam_reservation']}
                </span>

                <div class="buttons">
                    <span class="family-gift button button-gray-150" data-gift="${family['fam_gift']}">${family['fam_gift']}</span>

                    <span id="children" class="hide button button-gray-100">${children}</span>
                    <span id="adults" class="hide button button-gray-100">${adults}</span>

                    <button class="family-members button-gray-100 flex flex-xs flex-center flex-wrap" onclick="toggleTable(${family['fam_number']})">
                        <i class="bi bi-people"></i>
                        <p>
                            <span class="fam-members">${family['members'].length}</span>
                            family member${s}
                            <i class="bi bi-chevron-down"></i>
                        </p>
                    </button>
                </div>

                <span class="all-info">
                    <table class='family-members'>
                        <thead>
                            <tr>
                                <td>Name</td>
                                <td>Age</td>
                                <td>Gift</td>
                            </tr>
                        </thead>

                        <tbody id="family-members-table-${family['fam_number']}"></tbody>
                    </table>

                    <div class="message">
                        <strong>Source: </strong>${family['access']} <br>
                        <strong>Last Update: </strong>${family['register_date']} <br>
                        ${family['notes']}
                    </div>
                </span>

                <span class='data-icons'>
                    <i title="This family has a note" class="bi bi-chat-text"></i>
                    <i title="This label has been printed" class='bi bi-printer-fill'></i>
                </span>

                <div id="print-info" class="hide">
                    <p>Make sure you visit each station before you leave. Volunteers will be at every station to check off.</p>
                    
                    <div class="grid grid-sm grid-3">
                        <div class="button button-gray-100 flex flex-column flex-center">
                            <svg fill="#000000" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 496 496" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <g> <path d="M429.192,264c1.744-3.648,2.808-7.688,2.808-12c0-15.44-12.56-28-28-28h-3.72c-0.096,0-0.184,0.024-0.28,0.024V168h16V72 h-74.088C348.208,64.384,352,54.624,352,44c0-24.256-19.744-44-44-44h-6.184c-10.888,0-21.344,4.016-29.432,11.296L208,69.24 l-64.384-57.944C135.528,4.016,125.08,0,114.184,0H108C83.744,0,64,19.744,64,44c0,10.624,3.792,20.384,10.088,28H0v96h16v240 h16.408c-0.264,2.632-0.408,5.296-0.408,8c0,44.112,35.888,80,80,80s80-35.888,80-80c0-2.704-0.144-5.368-0.408-8H224v88h256V344 h16v-80H429.192z M400,88v64H256V88h32h20H400z M274.808,264H256v-96h128v61.312c-1.224,0.88-2.432,1.8-3.512,2.888L352,260.688 l-28.488-28.48c-5.288-5.296-12.32-8.208-19.792-8.208H300c-15.44,0-28,12.56-28,28C272,256.312,273.064,260.352,274.808,264z M283.088,23.184C288.232,18.552,294.88,16,301.816,16H308c15.44,0,28,12.56,28,28s-12.56,28-28,28h-20h-59.152L283.088,23.184z M240,88v64h-64V88H240z M108,16h6.184c6.928,0,13.576,2.552,18.728,7.184L187.152,72H128h-20c-15.44,0-28-12.56-28-28 S92.56,16,108,16z M16,88h92h20h32v64H16V88z M32,392V168h128v184.152c-4.936-3.72-10.272-6.92-16-9.424V320 c0-8.824-7.176-16-16-16h-8v-72h-16v72h-8c-8.816,0-16,7.176-16,16v22.728C58.992,351.944,42.656,369.896,35.688,392H32z M128,320v17.616c-5.168-1.056-10.52-1.616-16-1.616s-10.832,0.56-16,1.616V320H128z M112,480c-35.288,0-64-28.712-64-64 c0-35.288,28.712-64,64-64s64,28.712,64,64C176,451.288,147.288,480,112,480z M224,392h-35.688 c-2.76-8.76-7.056-16.824-12.464-24H176V168h64v96h-32v80h16V392z M320,480h-80V344h80V480z M320,328h-96v-48h76h12h8V328z M312,264h-12c-6.616,0-12-5.384-12-12c0-6.616,5.384-12,12-12h3.72c3.2,0,6.216,1.248,8.488,3.52l20.48,20.48H312z M368,480h-32 V344h32V480z M368,328h-32v-48h32V328z M391.8,243.52c2.264-2.264,5.28-3.52,8.488-3.52H404c6.616,0,12,5.384,12,12 c0,6.616-5.384,12-12,12h-12h-20.688L391.8,243.52z M464,480h-80V344h80V480z M480,328h-96v-48h8h12h76V328z"></path> <path d="M64,416h16c0-17.648,14.352-32,32-32v-16C85.528,368,64,389.528,64,416z"></path> <rect x="432" y="360" width="16" height="72"></rect> <rect x="432" y="448" width="16" height="16"></rect> </g> </g> </g> </g></svg>

                            <p>Family Game</p>
                        </div>
                        <div class="button button-gray-100 flex flex-column flex-center">
                            <svg fill="#000000" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 496 496" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <g> <path d="M176.632,257.672c1.16-5.936,3.928-11.472,8.096-16l77.656-84.128c6.2-6.72,9.616-15.456,9.616-25.264 C272,112.272,255.728,96,235.72,96c-9.696,0-18.808,3.776-25.656,10.624L192,124.688V96c0-52.936-43.064-96-96-96 C43.064,0,0,43.064,0,96v84.92c0,26.608,4.544,52.68,13.432,77.64C5.512,262.48,0,270.576,0,280v32c0,13.232,10.768,24,24,24h144 c13.232,0,24-10.768,24-24v-32C192,269.816,185.6,261.152,176.632,257.672z M16,96c0-44.112,35.888-80,80-80s80,35.888,80,80v48 h19.312l26.064-26.064c3.824-3.824,8.92-5.936,14.344-5.936c11.176,0,20.28,9.104,20.28,20.936c0,5.12-1.912,10-5.376,13.76 l-77.656,84.128c-6.528,7.072-10.672,15.824-12.224,25.176H29.552C20.592,231.912,16,206.688,16,180.92V96z M176,312 c0,4.416-3.584,8-8,8H24c-4.416,0-8-3.584-8-8v-32c0-4.416,3.584-8,8-8h144c4.416,0,8,3.584,8,8V312z"></path> <path d="M96,48V32c-35.288,0-64,28.712-64,64h16C48,69.528,69.528,48,96,48z"></path> <rect x="32" y="112" width="16" height="16"></rect> <rect x="32" y="288" width="96" height="16"></rect> <rect x="144" y="288" width="16" height="16"></rect> <path d="M496,256c0-52.936-43.064-96-96-96c-52.936,0-96,43.064-96,96v28.688l-18.064-18.064 C279.088,259.776,269.976,256,260.28,256c-20.008,0-36.28,16.272-36.28,36.936c0,9.152,3.416,17.88,9.624,24.616l77.64,84.112 c4.176,4.528,6.944,10.064,8.104,16.008C310.4,421.152,304,429.816,304,440v32c0,13.232,10.768,24,24,24h144 c13.232,0,24-10.768,24-24v-32c0-9.424-5.512-17.52-13.432-21.44C491.456,393.6,496,367.528,496,340.92V256z M480,472 c0,4.416-3.584,8-8,8H328c-4.416,0-8-3.584-8-8v-32c0-4.416,3.584-8,8-8h144c4.416,0,8,3.584,8,8V472z M480,340.92 c0,25.768-4.592,50.992-13.552,75.08H335.256c-1.544-9.352-5.696-18.112-12.232-25.184l-77.64-84.112 c-3.472-3.768-5.384-8.648-5.384-14.424c0-11.176,9.104-20.28,20.28-20.28c5.424,0,10.512,2.112,14.344,5.936L300.688,304H320 v-48c0-44.112,35.888-80,80-80s80,35.888,80,80V340.92z"></path> <path d="M336,256h16c0-26.472,21.528-48,48-48v-16C364.712,192,336,220.712,336,256z"></path> <rect x="336" y="272" width="16" height="16"></rect> <rect x="336" y="448" width="96" height="16"></rect> <rect x="448" y="448" width="16" height="16"></rect> </g> </g> </g> </g></svg>
                            
                            <p>Hat/Gloves</p>
                        </div>
                        <div class="button button-gray-100 flex flex-column flex-center">
                            <svg fill="#000000" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 496 496" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <g> <path d="M160.968,37.656C157.376,34.064,152.4,32,147.312,32C136.664,32,128,40.664,128,51.312 c0,5.088,2.064,10.064,5.656,13.656l4.688,4.688l11.312-11.312l-4.688-4.688C144.352,53.04,144,52.184,144,51.312 c0-2.704,3.744-4.24,5.656-2.344l4.688,4.688l11.312-11.312L160.968,37.656z"></path> <path d="M346.344,48.968c1.896-1.896,5.656-0.36,5.656,2.344c0,0.872-0.352,1.728-0.968,2.344l-4.688,4.688l11.312,11.312 l4.688-4.688C365.936,61.376,368,56.4,368,51.312C368,40.664,359.336,32,348.688,32c-5.088,0-10.064,2.064-13.656,5.656 l-4.688,4.688l11.312,11.312L346.344,48.968z"></path> <path d="M184,136c13.232,0,24-10.768,24-24s-10.768-24-24-24s-24,10.768-24,24S170.768,136,184,136z M184,104 c4.416,0,8,3.584,8,8s-3.584,8-8,8s-8-3.584-8-8S179.584,104,184,104z"></path> <path d="M312,136c13.232,0,24-10.768,24-24s-10.768-24-24-24s-24,10.768-24,24S298.768,136,312,136z M312,104 c4.416,0,8,3.584,8,8s-3.584,8-8,8c-4.416,0-8-3.584-8-8S307.584,104,312,104z"></path> <path d="M278.016,218.064c-2.56,5.144-9.2,7.4-14.416,4.792l-1.68-0.84c-3.648-1.824-5.92-5.496-5.92-9.592v-13.608 c9.384-2.696,16-9.96,16-18.816c0-11.216-10.536-20-24-20s-24,8.784-24,20c0,8.856,6.608,16.12,16,18.816v13.608 c0,4.096-2.272,7.776-5.912,9.592l-1.704,0.848c-5.184,2.576-11.832,0.336-14.408-4.792l-2.824-5.648l-14.312,7.144l2.824,5.656 c4.552,9.112,13.72,14.776,23.912,14.776c4.12,0,8.248-0.976,11.936-2.816l1.712-0.848c2.56-1.28,4.808-2.952,6.776-4.88 c1.968,1.928,4.224,3.608,6.792,4.888l1.688,0.832c3.688,1.848,7.824,2.824,11.944,2.824c10.192,0,19.36-5.664,23.912-14.784 l2.816-5.648l-14.312-7.144L278.016,218.064z M248,184c-4.96,0-8-2.584-8-4s3.04-4,8-4s8,2.584,8,4S252.96,184,248,184z"></path> <path d="M374.064,352c-9.48,0-18.368,3.28-25.712,9.04L336,348.688V328h44c24.256,0,44-19.744,44-44 c0-16.72-9.752-31.832-24.432-39.16c0.256-1.632,0.432-3.272,0.432-4.936c0-16.888-13.232-30.616-29.856-31.696 C381.472,188,388,164.76,388,140c0-17.76-3.352-35.104-9.984-51.672C391.584,79.592,400,64.488,400,48c0-26.472-21.528-48-48-48 c-14.208,0-27.688,6.504-36.736,17.352c-41.312-22.752-93.216-22.752-134.528,0C171.688,6.504,158.208,0,144,0 c-26.472,0-48,21.528-48,48c0,16.488,8.416,31.592,21.984,40.328C111.352,104.896,108,122.24,108,140 c0,24.76,6.528,48,17.856,68.208C109.232,209.288,96,223.016,96,239.904c0,1.656,0.176,3.304,0.432,4.928 C81.744,252.168,72,267.28,72,284c0,24.256,19.744,44,44,44h44v20.688l-12.352,12.352c-7.344-5.76-16.232-9.04-25.712-9.04 C98.816,352,80,370.816,80,393.936c0,11.2,4.36,21.736,12.28,29.656l60.128,60.128c7.92,7.92,18.456,12.28,29.656,12.28 c22.464,0,40.704-17.792,41.744-40H224c0-13.232,10.768-24,24-24s24,10.768,24,24h0.192c1.04,22.208,19.28,40,41.744,40 c11.2,0,21.736-4.36,29.656-12.28l60.128-60.128c7.92-7.92,12.28-18.456,12.28-29.656C416,370.816,397.184,352,374.064,352z M392.608,260.344c0.208-0.248,0.352-0.544,0.552-0.8C402.096,264.344,408,273.68,408,284c0,15.44-12.56,28-28,28h-55.856 C359.256,296.352,382.04,273.08,392.608,260.344z M124,140c0-17.92,3.864-35.384,11.496-51.896l3.448-7.464l-7.552-3.24 C119.608,72.344,112,60.808,112,48c0-17.648,14.352-32,32-32c11.392,0,21.68,5.984,27.528,16l4.152,7.112l6.984-4.352 C202.384,22.48,224.976,16,248,16s45.616,6.48,65.336,18.76l6.984,4.352L324.472,32C330.32,21.984,340.608,16,352,16 c17.648,0,32,14.352,32,32c0,12.808-7.608,24.344-19.392,29.4l-7.552,3.24l3.448,7.464C368.136,104.616,372,122.08,372,140 c0,46.976-26.256,87.928-64.864,108.96C315.224,237.328,320,223.216,320,208c0-39.704-32.296-72-72-72c-39.704,0-72,32.296-72,72 c0,15.216,4.776,29.328,12.864,40.96C150.256,227.928,124,186.976,124,140z M304,208c0,28.944-22.08,52.84-50.28,55.712 C251.808,263.8,249.928,264,248,264c-1.928,0-3.808-0.2-5.72-0.288C214.08,260.84,192,236.944,192,208c0-30.872,25.128-56,56-56 S304,177.128,304,208z M127.904,224H136v-0.36C161.552,257.776,202.176,280,248,280c45.816,0,86.44-22.224,112-56.36V224h8.096 c8.768,0,15.904,7.136,15.904,15.904c0,3.712-1.312,7.344-3.704,10.216c-15.44,18.6-59.208,61.864-132.52,61.864 c-0.008,0-0.016,0-0.024,0h-5.048l-2.168,4.544c-2.624,5.488-4.576,10.744-5.976,16.064c-1.76,6.72-7.84,11.408-14.784,11.408 c-5.208,0-10.064-2.616-12.976-7c-2.792-4.192-3.304-9.2-1.408-13.728c0.864-2.04,1.784-3.872,2.728-5.592l4.936-8.936 l-9.856-2.656c-47.72-12.832-77.088-43.408-87.496-55.96c-2.392-2.88-3.704-6.512-3.704-10.224 C112,231.136,119.136,224,127.904,224z M116,312c-15.44,0-28-12.56-28-28c0-10.32,5.904-19.656,14.84-24.456 c0.2,0.256,0.344,0.544,0.552,0.8c9.28,11.192,32.392,35.464,68.856,51.656H116z M182.064,480 c-6.928,0-13.448-2.696-18.344-7.592l-60.128-60.128C98.696,407.384,96,400.864,96,393.936C96,379.632,107.632,368,121.936,368 c6.928,0,13.448,2.696,18.344,7.592l60.128,60.128c4.896,4.896,7.592,11.416,7.592,18.344C208,468.368,196.368,480,182.064,480z M284.28,424.416c-2.024,2.024-3.8,4.224-5.344,6.552C271.6,421.912,260.528,416,248,416s-23.6,5.912-30.936,14.96 c-1.544-2.328-3.32-4.528-5.344-6.552L159.312,372L176,355.312v-41.776c4.504,1.88,9.08,3.72,13.968,5.304 c-3.128,8.976-1.864,18.936,3.504,27.016c5.88,8.856,15.712,14.144,26.304,14.144c14.216,0,26.664-9.6,30.256-23.344 c0.76-2.88,1.736-5.768,2.936-8.72c12.512-0.312,24.136-1.872,35.032-4.208V360h16v-40.376c5.6-1.744,10.896-3.728,16-5.856 v41.552l16.688,16.688L284.28,424.416z M392.408,412.28l-60.128,60.128c-4.896,4.896-11.416,7.592-18.344,7.592 C299.632,480,288,468.368,288,454.064c0-6.928,2.696-13.448,7.592-18.344l60.128-60.128c4.896-4.896,11.416-7.592,18.344-7.592 C388.368,368,400,379.632,400,393.936C400,400.864,397.304,407.384,392.408,412.28z"></path> <rect x="288" y="376" width="16" height="16"></rect> </g> </g> </g> </g></svg>
                            
                            <p>Kid's Gifts</p>
                        </div>
                        <div class="button button-gray-100 flex flex-column flex-center">
                            <svg fill="#000000" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g id="Solid"> <path d="M40,2H10A6.00475,6.00475,0,0,0,4,8V21.58594L8.58594,17,7.293,15.707A.99989.99989,0,0,1,8.707,14.293L10,15.58594,11.58594,14l-1.293-1.293A.99989.99989,0,0,1,11.707,11.293L13,12.58594,14.58594,11,13.293,9.707A.99989.99989,0,0,1,14.707,8.293L16,9.58594l1.293-1.293A.99989.99989,0,0,1,18.707,9.707L17.41406,11l1.293,1.293a.99962.99962,0,0,1,0,1.41406,1.01152,1.01152,0,0,1-1.41406,0L16,12.41406,14.41406,14l1.293,1.293A.99989.99989,0,1,1,14.293,16.707L13,15.41406,11.41406,17l1.293,1.293a.99963.99963,0,0,1,0,1.414,1.01174,1.01174,0,0,1-1.41407,0L10,18.41406l-6,6V47a1,1,0,0,0,2,0V44H8.28564v3a1,1,0,0,0,2,0V44h2.28565v3a1,1,0,0,0,2,0V44h2.28564v3a1,1,0,0,0,2,0V44h2.28614v3a1,1,0,0,0,2,0V44h2.28564v3a1,1,0,0,0,2,0V44h2.28565v3a1,1,0,0,0,2,0V44H34v3a1,1,0,0,0,2,0V36h7a3.00883,3.00883,0,0,0,3-3V8A6.00475,6.00475,0,0,0,40,2ZM24.293,23.707A.99989.99989,0,0,1,25.707,22.293l1.293,1.29291L28.293,22.293A.99989.99989,0,0,1,29.707,23.707l-1.29291,1.29291,1.586,1.58588L31.293,25.293A.99989.99989,0,0,1,32.707,26.707l-1.29278,1.29279L33.6665,30.252a.99963.99963,0,0,1,0,1.41406,1.01135,1.01135,0,0,1-1.41407,0l-2.25226-2.25214L28.707,30.707a1.01137,1.01137,0,0,1-1.41406,0,.99962.99962,0,0,1,0-1.41406l1.29309-1.29309-1.586-1.58588-1.293,1.293a1.01146,1.01146,0,0,1-1.41406,0,.99962.99962,0,0,1,0-1.41406l1.293-1.293ZM34,42H6V39H34Zm10-9a1.003,1.003,0,0,1-1,1H41V9a1,1,0,0,0-2,0V34H36V8a4,4,0,0,1,8,0Z"></path> </g> </g></svg>
                            
                            <p>Blanket</p>
                        </div>
                        <div class="button button-gray-100 flex flex-column flex-center">
                            <svg fill="#000000" height="200px" width="200px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 470 470" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M462.5,270.58c-4.142,0-7.5,3.358-7.5,7.5v22.5H15v-22.5c0-4.142-3.358-7.5-7.5-7.5s-7.5,3.358-7.5,7.5v183.137 c0,4.142,3.358,7.5,7.5,7.5h455c4.142,0,7.5-3.358,7.5-7.5V278.08C470,273.938,466.642,270.58,462.5,270.58z M455,330.58H67.5 c-4.142,0-7.5,3.358-7.5,7.5s3.358,7.5,7.5,7.5H455v108.137H15V345.58h22.5c4.142,0,7.5-3.358,7.5-7.5s-3.358-7.5-7.5-7.5H15v-15 h440V330.58z"></path> <path d="M189.28,283.891c1.393,1.137,3.07,1.69,4.738,1.69c2.175,0,4.332-0.941,5.814-2.757l83.154-101.865 c2.619-3.208,2.142-7.933-1.067-10.553c-3.208-2.619-7.933-2.142-10.553,1.067l-83.154,101.865 C185.594,276.546,186.071,281.271,189.28,283.891z"></path> <path d="M226.213,273.338c-2.619,3.208-2.142,7.933,1.067,10.553c1.393,1.137,3.07,1.69,4.738,1.69 c2.175,0,4.332-0.941,5.814-2.757c0,0,81.987-100.437,83.139-101.847c0.004-0.006,0.009-0.011,0.014-0.017 c0.462-0.566,0.958-1.114,1.472-1.629c8.355-8.354,21.95-8.354,30.305,0l20.679,20.679c8.355,8.355,8.355,21.95,0,30.306 c-0.515,0.515-1.063,1.01-1.627,1.471l-49.594,40.484c-3.209,2.62-3.687,7.344-1.067,10.553c1.483,1.816,3.64,2.757,5.814,2.757 c1.667,0,3.346-0.553,4.738-1.69l49.595-40.485c0.954-0.779,1.878-1.615,2.747-2.484c12.334-12.335,13.951-31.384,4.863-45.486 c20.311-18.05,46.189-27.922,73.589-27.922c4.142,0,7.5-3.358,7.5-7.5s-3.358-7.5-7.5-7.5c-30.352,0-59.063,10.668-81.858,30.223 l63.241-63.24c2.929-2.929,2.929-7.677,0-10.606c-2.929-2.929-7.678-2.929-10.606,0l-63.236,63.236 c19.553-22.795,30.219-51.503,30.219-81.853c0-4.142-3.358-7.5-7.5-7.5s-7.5,3.358-7.5,7.5c0,27.399-9.872,53.278-27.922,73.589 c-14.102-9.089-33.152-7.472-45.486,4.862c-0.869,0.869-1.705,1.793-2.484,2.747l0.019,0.015c-0.335,0.374-0.648,0.757-0.953,1.13 L226.213,273.338z"></path> <path d="M37.477,285.58c4.142,0,7.5-3.358,7.5-7.5V96.334h106.47V278.08c0,4.142,3.358,7.5,7.5,7.5s7.5-3.358,7.5-7.5V91.235 l28.212-39.54l21.148,29.638H195.8c-4.142,0-7.5,3.358-7.5,7.5s3.358,7.5,7.5,7.5h27.071v89.779c0,4.142,3.358,7.5,7.5,7.5 s7.5-3.358,7.5-7.5V88.834c0-1.562-0.488-3.085-1.395-4.356l-34.317-48.096V8.783c0-4.142-3.358-7.5-7.5-7.5H73.189 c-4.142,0-7.5,3.358-7.5,7.5v27.598L31.371,84.478c-0.907,1.271-1.395,2.794-1.395,4.356V278.08 C29.977,282.222,33.334,285.58,37.477,285.58z M80.689,16.283h106.47v15H80.689V16.283z M77.051,46.283h103.043l-25.01,35.051 H52.041L77.051,46.283z"></path> <path d="M432.5,285.581c4.142,0,7.5-3.358,7.5-7.5v-12.462c0-15.596-9.857-29.732-24.528-35.176c-3.885-1.44-8.2,0.539-9.641,4.422 c-1.441,3.884,0.539,8.2,4.422,9.641c8.82,3.273,14.747,11.757,14.747,21.113v12.462C425,282.223,428.358,285.581,432.5,285.581z"></path> </g> </g></svg>
                            
                            <p>Food (at exit)</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML("beforeend", newCard);
    }

    function createMemberTables(member, family_number) {
        let table = document.getElementById("family-members-table-" + family_number);
        let newRow = `
            <tr>
                <td>` + member['name'] + `</td>
                <td>` + member['age'] + `</td>
                <td>` + member['gift'] + `</td>
            </tr>`; 
        table.insertAdjacentHTML("beforeend", newRow);
    }

    function toggleGiftColumn() {
        let giftColumn = document.querySelectorAll(".family-members td:nth-of-type(3)");

        giftColumn.forEach(function(column) {
            column.classList.toggle("hidden");
        });
    }

    function closeMenus() {
        let menus = document.querySelectorAll(".options-menu");

        menus.forEach(function(menu) {
            menu.classList.remove("open");
        });
    }

    function filterFamilies(filter = null, maintain = false) {
        let button_packed = document.querySelector('#packed');
        let button_notPacked = document.querySelector('#not-packed');
        let button_printed = document.querySelector('#printed');
        let button_notPrinted = document.querySelector('#not-printed');

        let packed = document.querySelectorAll("[data-packed='1']");
        let notPacked = document.querySelectorAll("[data-packed='0']");
        let printed = document.querySelectorAll("[data-printed='false']");
        let notPrinted = document.querySelectorAll("[data-printed='true']");

        let previousFilter = currentFilter;

        if(filter) {
            currentFilter = filter;
        }

        button_packed.classList.remove('active');
        packed.forEach(function(el) {
            el.classList.remove('hide');
        });
        button_notPacked.classList.remove('active');
        notPacked.forEach(function(el) {
            el.classList.remove('hide');
        });
        button_printed.classList.remove('active');
        printed.forEach(function(el) {
            el.classList.remove('hide');
        });
        button_notPrinted.classList.remove('active');
        notPrinted.forEach(function(el) {
            el.classList.remove('hide');
        });

        switch(currentFilter) {
            case 'packed':
                if(previousFilter != 'packed' || maintain) {
                    button_packed.classList.add('active');
                    notPacked.forEach(function(el) {
                        el.classList.add('hide');
                    });
                } else {
                    currentFilter = null;
                }

                break;
            case 'not-packed':
                if(previousFilter != 'not-packed' || maintain) {
                    button_notPacked.classList.add('active');
                    packed.forEach(function(el) {
                        el.classList.add('hide');
                    });
                } else {
                    currentFilter = null;
                }

                break;
            case 'printed':
                if(previousFilter != 'printed' || maintain) {
                    button_printed.classList.add('active');
                    printed.forEach(function(el) {
                        el.classList.add('hide');
                    });
                } else {
                    currentFilter = null;
                }

                break;
            case 'not-printed':
                if(previousFilter != 'not-printed' || maintain) {
                    button_notPrinted.classList.add('active');
                    notPrinted.forEach(function(el) {
                        el.classList.add('hide');
                    });
                } else {
                    currentFilter = null;
                }

                break;
        }
    }

    function togglePacked(number, pack=null) {
        let family = document.querySelector('#family-' + number);
        let formData = new FormData();
        formData.append('number', number);
        if(!pack) {
            formData.append('pack', family.dataset.packed);
        } else if(pack == "yes") {
            formData.append('pack', 0);
        } else if(pack == "no") {
            formData.append('pack', 1);
        }

        let url = 'https://registration.christmas.sharethedreamwi.org/private/toggle-packed.php';
        let headers = new Headers();
        headers.set('Authorization', 'Basic ' + btoa(user + ":" + pass));

        fetch(url, { method: 'POST', body: formData, headers: headers })
            .then(function (response) {
            return response.text();
        })
            .then(function (body) {
            if(body == "true") {
                if(family.dataset.packed == '1') {
                    family.dataset.packed = '0';
                } else {
                    family.dataset.packed = '1';
                }
                generatePacked();
                setTimeout(function() {
                    filterFamilies(null, true);
                }, 3000);
            } else {
                console.error(body);
            }
        });
    }

    function markAllPacked() {
        let visibleFamilies = document.querySelectorAll("#family-member-cards .family-card:not(.hide)");

        for(let i = 0; i < visibleFamilies.length; i++) {
            let number = parseInt(visibleFamilies[i].querySelector(".fam-number").innerText);
            togglePacked(number, "yes");
        }
    }

    function markAllNotPacked() {
        let visibleFamilies = document.querySelectorAll("#family-member-cards .family-card:not(.hide)");

        for(let i = 0; i < visibleFamilies.length; i++) {
            let number = parseInt(visibleFamilies[i].querySelector(".fam-number").innerText);
            togglePacked(number, "no");
        }
    }

    function generatePacked() {
        let packed = document.querySelector('#packed');
        let notPacked = document.querySelector('#not-packed');

        let numPacked = document.querySelectorAll("#family-member-cards [data-packed='1']").length;
        let numNotPacked = document.querySelectorAll("#family-member-cards [data-packed='0']").length;

        packed.querySelector("strong").innerText = numPacked;
        notPacked.querySelector("strong").innerText = numNotPacked;
    };

    window.onload = function() {
        window.addEventListener('beforeprint', formatPrint);
        window.addEventListener('afterprint', undoFormatPrint);
    };

    function formatPrint() {
        let cards = document.querySelectorAll("#family-member-cards .family-card:not(.hide)");
        document.getElementById("family-member-cards").classList.add("hide");

        let fragment = document.createDocumentFragment();
        let printedFamilies = document.createElement("span");
        printedFamilies.id = "print";
        printedFamilies.classList.add("family-member-cards", "grid");

        for (let i = 0; i < cards.length; i += 2) {
            let pageSpan = document.createElement("span");
            pageSpan.classList.add("page-break", "grid", "grid-2");

            if (i + 1 < cards.length) {
                pageSpan.appendChild(cards[i].cloneNode(true));
                pageSpan.appendChild(cards[i + 1].cloneNode(true));
            } else {
                pageSpan.appendChild(cards[i].cloneNode(true));
            }

            fragment.appendChild(pageSpan);
        }

        printedFamilies.appendChild(fragment);
        
        let referenceNode = document.getElementById("family-member-cards");
        referenceNode.parentNode.insertBefore(printedFamilies, referenceNode.nextSibling);
    }

    function undoFormatPrint() {
        document.getElementById("family-member-cards").classList.remove("hide");

        if(document.getElementById("print")) {
            document.getElementById("print").remove();
            printLabels();
        }
    }

    function setPrinted() {
        let printedNumbers = localStorage.getItem('printedNumbers') ? JSON.parse(localStorage.getItem('printedNumbers')) : [];

        if(printedNumbers) {
            for(x of printedNumbers) {
                let familyCard = document.getElementById("family-" + x);

                if(familyCard) {
                    familyCard.dataset.printed = "true";
                }
            }
        }

        let printed = document.querySelector('#printed');
        let notPrinted = document.querySelector('#not-printed');

        let numPrinted = document.querySelectorAll("#family-member-cards [data-printed='true']").length;
        let numNotPrinted = document.querySelectorAll("#family-member-cards [data-printed='false']").length;

        printed.querySelector("strong").innerText = numPrinted;
        notPrinted.querySelector("strong").innerText = numNotPrinted;
    }

    function printLabels() {
        let printed = document.querySelectorAll("#family-member-cards .family-card:not(.hide)");
        let printedNumbers = localStorage.getItem('printedNumbers') ? JSON.parse(localStorage.getItem('printedNumbers')) : [];

        if (confirm("Mark " + printed.length + " family labels as printed?") == true) {
            for(let i = 0; i < printed.length; i++) {
                let number = parseInt(printed[i].querySelector(".fam-number").innerText);

                if(printedNumbers.indexOf(number) === -1) {
                    printedNumbers.push(number);
                }
            }

            localStorage.setItem('printedNumbers', JSON.stringify(printedNumbers));

            setPrinted();
        }
    }

    function resetPrinted() {
        localStorage.removeItem('printedNumbers');

        let printed = document.querySelectorAll("[data-printed='true']");
        for(let i = 0; i < printed.length; i++) {
            printed[i].dataset.printed = 'false';
        }

        setPrinted();
        filterFamilies(null, true);
        closeMenus();
    }
</script>


<?php
get_footer();
?>
