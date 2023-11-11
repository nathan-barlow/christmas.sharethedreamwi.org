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

                <div id="print-info">
                    <p>Make sure you visit each station before you leave. Volunteers will be at every station to check off.</p>
                    
                    <div class="grid grid-sm grid-3">
                        <div class="button button-gray-100 flex flex-column flex-center">
                            Family Game
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
