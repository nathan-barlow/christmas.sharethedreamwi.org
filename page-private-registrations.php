
<?php
get_template_part('template-parts/db-connection');
// Connect to Database Using MySQL
$conn = dbConnect('read');

// Prepare MySQL statement
$query_families = mysqli_query($conn, "SELECT * FROM registered_families");
$query_adults = mysqli_query($conn, "SELECT * FROM registered_members WHERE age >= 18");
$query_children = mysqli_query($conn, "SELECT * FROM registered_members WHERE age < 18");

$query_organizations = mysqli_query($conn, "SELECT organization,
        COUNT(organization) as registered,
        (SELECT COUNT(organization) FROM family_id_list WHERE family_id_list.organization = f.organization) as total
    FROM
        registered_families,
        family_id_list f
    WHERE registered_families.family_id = f.family_code
    GROUP BY organization
    ORDER BY registered DESC, organization
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
        registered_members.age as AGE,
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
    $data[$row["FAMILY_NUMBER"]]["register_date"] = ($row["DATE_REGISTERED"]);
    $data[$row["FAMILY_NUMBER"]]["members"][$i] = array(
        "name"=>htmlspecialchars($row["FIRST_NAME"]),
        "age"=>htmlspecialchars($row["AGE"]),
        "gift"=>htmlspecialchars($row["GIFT"]),
    );
    $i++;
}

// SATISFACTION QUERY
$query_feedback_satisfaction = mysqli_query($conn, "SELECT s.satisfaction, COALESCE(f.count, 0) AS count
FROM (
    SELECT 'good' AS satisfaction
    UNION ALL
    SELECT 'neutral'
    UNION ALL
    SELECT 'bad'
) AS s
LEFT JOIN (
    SELECT satisfaction, COUNT(*) AS count
    FROM feedback
    WHERE satisfaction IN ('good', 'neutral', 'bad')
    GROUP BY satisfaction
) AS f ON s.satisfaction = f.satisfaction;");

$satisfaction = [];
while ($row = mysqli_fetch_assoc($query_feedback_satisfaction)) {
    $satisfaction[ucfirst($row['satisfaction'])] = $row['count'];
}

// FEEDBACK MESSAGES QUERY
$query_feedback_messages = mysqli_query($conn, "SELECT message FROM feedback WHERE message != '' ORDER BY LENGTH(message);");
$feedback_messages = mysqli_fetch_all($query_feedback_messages);

// LANGUAGE CHANGES QUERY
$query_language_changes = mysqli_query($conn, "SELECT language, COUNT(*) AS count FROM language_changes GROUP BY language;");

$language_changes = [];
while ($row = mysqli_fetch_assoc($query_language_changes)) {
    $language_changes[ucfirst($row['language'])] = $row['count'];
}

// RESERVATIONS QUERY
$query_reservations = mysqli_query($conn, "SELECT es.value AS timeframe, COUNT(rf.reservation) AS count
    FROM event_settings es
    LEFT JOIN registered_families rf ON es.value = rf.reservation
    WHERE es.name = 'timeframe'
    GROUP BY es.value, rf.reservation");

$reservations = array();
while($row = mysqli_fetch_array($query_reservations)){
    $reservations[$row["timeframe"]] = $row["count"];
}

// BLOCKED IPs QUERY
$query_blocked = mysqli_query($conn, "SELECT ip_address, last_attempt FROM failed_attempts WHERE attempts >= 20;");
$blocked_ips = mysqli_fetch_all($query_blocked);

// ERROR MESSAGES QUERY
$query_errors = mysqli_query($conn, "SELECT error_type, error_message, error_timestamp FROM error_log ORDER BY error_timestamp DESC;");
$error_messages = mysqli_fetch_all($query_errors);

// FAMILY NOTES QUERY
$query_notes = mysqli_query($conn, "SELECT family_number, family_name, notes FROM registered_families WHERE notes <> '';");
$family_notes = mysqli_fetch_all($query_notes);

// DATE GRAPH QUERY
$query_dates = mysqli_query($conn, "SELECT DATE_FORMAT(DATE_SUB(date_registered, INTERVAL 5 HOUR), '%m-%d') AS the_date, COUNT(*) AS count
    FROM registered_families
    GROUP BY the_date
    ORDER BY the_date");

$registration_dates = [];
while ($row = mysqli_fetch_assoc($query_dates)) {
    $registration_dates[ucfirst($row['the_date'])] = $row['count'];
}

// ACCESS SOURCE QUERY
$query_sources = mysqli_query($conn, "SELECT access, COUNT(access) as count FROM registered_families GROUP BY access");

$family_sources = [];
while ($row = mysqli_fetch_assoc($query_sources)) {
    $family_sources[ucfirst($row['access'])] = $row['count'];
}

// SUCCESS ATTEMPTS QUERY
$query_total_started = mysqli_query($conn, "SELECT COUNT(attempts) as VALID_CODES FROM failed_attempts WHERE attempts = 0;");
$total_started_form = mysqli_fetch_all($query_total_started)[0][0];
$total_families = mysqli_num_rows($query_families);
$percent_completed_form = ($total_started_form != 0) ? number_format((($total_families / $total_started_form) * 100), 2) : 0;
$abandoned_forms = $total_started_form - $total_families;

$old_members = isset($_COOKIE["old-members"]) ? htmlspecialchars($_COOKIE["old-members"]) : '';
$old_families = isset($_COOKIE["old-families"]) ? htmlspecialchars($_COOKIE["old-families"]) : '';

$new_members = mysqli_num_rows($query_members);

if($old_families || $old_members) {
    if($new_members - $old_members > 0) {
        $difference_members = $new_members - $old_members;
    }
    if($total_families - $old_families > 0) {
        $difference_families = $total_families - $old_families;
    }
}

setcookie("old-members", $new_members, time()+(86400 * 30), "/");
setcookie("old-families", $total_families, time()+(86400 * 30), "/");

$conn->close();

get_header('archive');
?>
</header>
<main class="registrations-dashboard wrapper">
    <nav class="nav-secondary">
        <a class="active" href="/private-registrations">Registrations Home</a>
        <a href="/private-registered-families">Families</a>
        <a href="/private-gifts">Gifts</a>
        <a href="/private-family-code">Family Codes</a>
        <a href="/private-register-family">Register New Family</a>
        <a href="/private-event-settings">Event Settings</a>
        <a href="/private-event">Event</a>
    </nav>
    <button class="button-gray-150 float-right margin-top" onclick="window.print()"><i class="bi bi-download"></i>Download PDF</button>
    <h1>Registrations Home</h1>
    <div class="grid" id="main-container">
        <div class="grid grid-2" id="container-1">
            <div class="grid grid-2">
                <div class="card totals-section">
                    <p>Families <i class="bi bi-house"></i></p>
                    <h2>
                        <?php echo $total_families; ?>
                        <?php if($difference_families) : ?>
                            <span class="new-people">
                                <?php echo $difference_families ?> new
                            </span>
                        <?php endif; ?>
                    </h2>
                    <div data-current="<?php echo $total_families; ?>" data-goal="300" class="progress-bar"></div>
                </div>
                <div class="card totals-section">
                    <p>Individuals <i class="bi bi-people"></i></p>
                    <h2>
                        <?php echo $new_members; ?>
                        <?php if($difference_members) : ?>
                            <span class="new-people">
                                <?php echo $difference_members ?> new
                            </span>
                        <?php endif; ?>
                    </h2>
                    <div data-current="<?php echo $new_members; ?>" data-goal="1100" class="progress-bar"></div>
                </div>

                <div class="card span-2">
                    <h2>Family Makeup</h2>
                    <p>Adults: <strong><?php echo mysqli_num_rows($query_adults); ?></strong></p>
                    <p>Children: <strong><?php echo mysqli_num_rows($query_children); ?></strong></p>
                    <canvas id="familyMakeupGraph" width="100%"></canvas>
                </div>

                <div class="card totals-section">
                    <p>Completed Forms</p>
                    <h2>
                        <?php echo $percent_completed_form; ?>%
                    </h2>
                    <div data-current="<?php echo $total_families ?>" data-goal="<?php echo $total_started_form; ?>" class="progress-bar"></div>
                </div>
                <div class="card totals-section">
                    <p>Abandoned Forms</p>
                    <h2>
                        <?php echo $abandoned_forms; ?>
                    </h2>
                    <div data-current="<?php echo $abandoned_forms ?>" data-goal="<?php echo $total_started_form; ?>" class="progress-bar"></div>
                </div>
            </div>
            
            <div class="card">
                <h2>Organizations</h2>

                <?php foreach($organization_info as $org) : ?>

                <div class="card-organization">
                    <p><?php echo htmlspecialchars(stripslashes($org['organization'])) ?></p>
                    <div data-current="<?php echo $org['registered'] ?>" data-goal="<?php echo $org['total'] ?>" class="progress-bar"></div>
                </div>

                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="grid" id="container-2">
            <div class="grid grid-2">
                <div class="grid grid-2">
                    <div class="card totals-section">
                        <p>Form Satisfaction</p>
                        <canvas id="satisfactionGraph" width="100%"></canvas>
                    </div>
                    <div class="card totals-section">
                        <p>Language</p>
                        <canvas id="languageGraph" height="134px" width="100%"></canvas>
                    </div>
                </div>
                <div class="card totals-section">
                    <p>Reservation Times</p>
                    <canvas id="reservationsGraph" width="100%"></canvas>
                </div>
            </div>
            
            <div class="grid grid-2">
                <div class="card totals-section">
                    <p>Date Registered</p>
                    <canvas id="dateGraph" width="100%"></canvas>
                </div>
                <div class="card totals-section">
                    <p>Source</p>
                    <canvas id="sourceGraph" width="100%"></canvas>
                </div>
            </div>
        </div>

        <div class="card" id="family-notes">
            <h2>Family Notes</h2>
            <p>This includes any and all family notes made by registration admin.</p>

            <table class="table-padded">
                <thead>
                    <tr>
                        <td>#</td>
                        <td>Name</td>
                        <td class="width-100">Note</td>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($family_notes as $note) : ?>
                    <tr>
                        <td>
                            <a title="View Family" href="/private-registered-families#family-<?php echo htmlspecialchars($note[0]) ?>"><?php echo htmlspecialchars($note[0]) ?></a>
                        </td>
                        <td><?php echo htmlspecialchars($note[1]) ?></td>
                        <td><?php echo htmlspecialchars($note[2]) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card" id="feedback-messages">
            <h2>Feedback Messages</h2>

            <div class="messages">
                <?php foreach($feedback_messages as $msg) : ?>

                <div class="card-message card-message-inline">
                    <p><?php echo htmlspecialchars($msg[0]) ?></p>
                </div>

                <?php endforeach; ?>
            </div>
        </div>

        <div class="card" id="error-log">
            <h2>Error Log</h2>
            <p>This includes all error messages generated in the application. Admin uses this to squash bugs.</p>

            <?php foreach($error_messages as $msg) : 
                $datetime = new DateTime($msg[2]);
                $datetime->modify('-5 hours');
                ?>

            <div class="message card-message">
                <p><strong><?php echo strtoupper($msg[0]) ?></strong> <?php echo $datetime->format("M j, Y g:ia"); ?></p>
                <p style="margin-top: .25rem"><?php echo htmlspecialchars($msg[1]) ?></p>
            </div>

            <?php endforeach; ?>
        </div>

        <div class="card" id="blocked-ip">
            <h2>Blocked IPs</h2>
            <p>If a user makes too many failed attempts to register their invite code, their IP address will appear below. This is to protect against bots and people who don't actually have an invite. Please contact site administrator to help unblock them if they call, have a valid code, and are a human.</p>

            <?php foreach($blocked_ips as $ip) : 
                $datetime = new DateTime($ip[1]);
                $datetime->modify('-5 hours'); ?>

            <ul>
                <li><?php echo "<strong>" . htmlspecialchars($ip[0]) . "</strong> on " . $datetime->format("M j, Y g:ia") . "</i>" ?></li>
            </ul>

            <?php endforeach; ?>
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
    
    for(let i = 0; i < progressBars.length; i++) {
        const bar = progressBars[i];

        bar.innerHTML += '<div class="progress"></div>';
        const progress = bar.querySelector(".progress");

        const goal = parseInt(bar.dataset.goal);
        const currentProgress = parseInt(bar.dataset.current);
        const percentProgress = (currentProgress / goal) * 100;
        
        progress.style.width = percentProgress + "%";

        switch (true) {
            case (percentProgress >= 0 && percentProgress <= 10):
                progress.style.background = "#fee2e2";
                break;
            case (percentProgress > 10 && percentProgress <= 20):
                progress.style.background = "#ffedd5";
                break;
            case (percentProgress > 20 && percentProgress <= 30):
                progress.style.background = "#fef3c7";
                break;
            case (percentProgress > 30 && percentProgress <= 40):
                progress.style.background = "#fef9c3";
                break;
            case (percentProgress > 40 && percentProgress <= 50):
                progress.style.background = "#d9f99d";
                break;
            case (percentProgress > 50 && percentProgress <= 100):
                progress.style.background = "#d9f99d";
                break;
            default:
                console.log("Out of range");
        }
    }

    const organizations = document.querySelectorAll(".card-organization");
    for(let i = 0; i < organizations.length; i++) {
        const organization = organizations[i];
        const progressBar = organization.querySelector(".progress-bar")
        const progressCurrent = parseInt(progressBar.dataset.current);
        const progressGoal = parseInt(progressBar.dataset.goal);


            progressBar.innerHTML += ("<p class='goal'>" + progressGoal + "</p>");
            progressBar.querySelector(".progress").innerHTML += ("<p class='current'>" + progressCurrent + "</p>");
        
        // if (progressCurrent <= (progressGoal * .9)) {
        //     progressBar.innerHTML += ("<p class='goal'>" + progressGoal + "</p>");
        // }
        // if (progressCurrent >= (progressGoal) * .1) {
        //     progressBar.querySelector(".progress").innerHTML += ("<p class='current'>" + progressCurrent + "</p>");
        // }
    }

    const familyMakeup = <?php echo json_encode($family_makeup_info[0]) ?>;
    const ctx_familyMakeup = document.getElementById("familyMakeupGraph");

    var makeupLabels = [];
    var makeupNumbers = [];

    for(var ageGroup in familyMakeup) {
        makeupNumbers.push(parseInt(familyMakeup[ageGroup]));
        makeupLabels.push(ageGroup);
        ctx_familyMakeup.innerHTML += ("<p>" + ageGroup + ": " + familyMakeup[ageGroup] + "</p>");
    }

    const data_familyMakeup = {
        labels: makeupLabels,
        datasets: [{
            label: "Registered Members",
            data: makeupNumbers,
            backgroundColor: [
                '#fecaca',
                '#f87171',
                '#dc2626',
                '#991b1b',
                '#450a0a'
            ],
            hoverOffset: 5,
            cutout: '66%',
        }]
    };
    const config_familyMakeup = {
        type: 'doughnut',
        data: data_familyMakeup,
        options: {
            plugins: {
                legend: {
                    position: 'left',
                    align: 'center',
                    labels: {
                        boxWidth: 30,
                        boxHeight: 30,
                    }
                }
            }
        }
    };


    const satisfaction = <?php echo json_encode($satisfaction) ?>;
    const ctx_satisfaction = document.getElementById("satisfactionGraph");
    const data_satisfaction = {
        labels: Object.keys(satisfaction),
        datasets: [{
            label: "Satisfaction",
            data: Object.values(satisfaction),
            backgroundColor: [
                '#bbf7cd',
                '#fef08a',
                '#fecaca'
            ],
            hoverOffset: 5,
            cutout: '0%',
        }]
    };
    const config_satisfaction = {
        type: 'doughnut',
        data: data_satisfaction,
        options: {
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    };


    const language = <?php echo json_encode($language_changes) ?>;
    const ctx_language = document.getElementById("languageGraph");
    const data_language = {
        labels: Object.keys(language),
        datasets: [{
            label: "Language Changes",
            data: Object.values(language),
            backgroundColor: [
                '#f87171',
                '#991b1b'
            ],
            hoverOffset: 5,
            cutout: '0%',
        }]
    };
    const config_language = {
        type: 'bar',
        data: data_language,
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    };

    const reservations = <?php echo json_encode($reservations) ?>;
    const ctx_reservations = document.getElementById("reservationsGraph");
    const data_reservations = {
        labels: Object.keys(reservations),
        datasets: [{
            label: "Reservations",
            data: Object.values(reservations),
            backgroundColor: [
                '#fecaca',
                '#f87171',
                '#dc2626',
                '#991b1b',
                '#450a0a'
            ],
            hoverOffset: 5,
            cutout: '0%',
        }]
    };
    const config_reservations = {
        type: 'bar',
        data: data_reservations,
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    ticks: {
                        stepSize: 10
                    }
                }
            }
        }
    };

    const timestamps = <?php echo json_encode($registration_dates) ?>;
    const ctx_timestamps = document.getElementById("dateGraph");
    const data_timestamps = {
        labels: Object.keys(timestamps),
        datasets: [{
            label: "Total registered on this date",
            data: Object.values(timestamps),
            backgroundColor: [
                '#f87171',
            ],
            fill: true,
            backgroundColor: '#fecaca',
            borderColor: '#f87171',
            pointRadius: 1,
            borderWidth: 2,
        }]
    };
    const config_timestamps = {
        type: 'line',
        data: data_timestamps,
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 10
                    }
                }
            }
        }
    };

    const sources = <?php echo json_encode($family_sources) ?>;
    const ctx_sources = document.getElementById("sourceGraph");
    const data_sources = {
        labels: Object.keys(sources),
        datasets: [{
            label: "Access Source",
            data: Object.values(sources),
            backgroundColor: [
                '#fecaca',
                '#f87171',
                '#dc2626',
                '#991b1b',
                '#450a0a'
            ],
            hoverOffset: 5,
            cutout: '0%',
        }]
    };
    const config_sources = {
        type: 'bar',
        data: data_sources,
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 10
                    }
                }
            }
        }
    };

    const donutChart_familyMakeup = new Chart(ctx_familyMakeup, config_familyMakeup);
    const donutChart_satisfaction = new Chart(ctx_satisfaction, config_satisfaction);
    const donutChart_language = new Chart(ctx_language, config_language);
    const donutChart_reservations = new Chart(ctx_reservations, config_reservations);
    const donutChart_timestamps = new Chart(ctx_timestamps, config_timestamps);
    const donutChart_sources = new Chart(ctx_sources, config_sources);

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
                    console.error("Database authorization failed.")
                } else {
                    allFamilies = JSON.parse(body);
                }
            });
    })();
</script>


<?php
get_footer();
?>
