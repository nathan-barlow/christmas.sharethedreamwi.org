<?php
get_template_part('template-parts/db-connection');
// Connect to Database Using MySQL
$conn = dbConnect('read');

function updateInventory($name, $inventory) {
    $conn = dbConnect('read');

    // Prepare the update query
    $query_updateInv = $conn->prepare("UPDATE event_settings SET inventory = ? WHERE value = ?");
    
    if (!$query_updateInv) {
        die("Error in preparing the update query: " . $conn->error);
    }

    $query_updateInv->bind_param("ss", $inventory, $name);

    if (!$query_updateInv->execute()) {
        die("Error in executing the update query: " . $query_updateInv->error);
    }
    $query_updateInv->close();
    $conn->close();
}

if(isset($_POST['save'])) {
    $gifts = $_POST['gift'];

    foreach($gifts as $name => $inventory) {
        updateInventory($name, $inventory);
    }
}

// Prepare MySQL statement
$query_allGifts = mysqli_query($conn, "SELECT family_gift, COUNT(family_gift) as count
    FROM registered_families
    GROUP BY family_gift
    ORDER BY
    CASE
        WHEN family_gift = 'No Preference' THEN 1
        ELSE 0
    END,
    family_gift;
");

$query_memberGifts = mysqli_query($conn, "SELECT
        es.value AS gift,
        es.inventory,
        COUNT(rm.gift_preference) AS requested
    FROM
        event_settings AS es
    LEFT JOIN
        registered_members AS rm
    ON
        es.value = rm.gift_preference
    WHERE
        es.name LIKE 'gifts_%' AND es.name != 'gifts_family'
    GROUP BY
        es.value, es.inventory;
");

$query_familyGifts = mysqli_query($conn, "SELECT
        es.value AS gift,
        es.inventory,
        COALESCE(rf_count.requested, 0) AS requested
    FROM
        event_settings AS es
    LEFT JOIN (
    SELECT
        family_gift,
        COUNT(*) AS requested
    FROM
        registered_families
    WHERE
        packed = false
    GROUP BY
        family_gift
    ) AS rf_count ON es.value = rf_count.family_gift
    WHERE
        es.name = 'gifts_family'
    GROUP BY
        es.value, es.inventory;
");

$query_customGifts = mysqli_query($conn, "SELECT
        rm.gift_preference AS gift,
        COUNT(*) AS count
    FROM
        registered_members AS rm
    LEFT JOIN
        event_settings AS es
    ON
        rm.gift_preference = es.value
    WHERE
        es.value IS NULL
        AND rm.gift_preference IS NOT NULL
        AND rm.gift_preference != ''
    GROUP BY
        rm.gift_preference;
");

$memberGifts = mysqli_fetch_all($query_memberGifts);
$familyGifts = mysqli_fetch_all($query_familyGifts);
$customGifts = mysqli_fetch_all($query_customGifts);

$allGifts = [];
while ($row = mysqli_fetch_assoc($query_allGifts)) {
    $allGifts[ucfirst($row['family_gift'])] = $row['count'];
}

$giftsList = $familyGifts;

$conn->close();

get_header('archive');

?>
</header>
<main class="registrations-dashboard gift-inventories wrapper">
    <nav class="nav-secondary">
        <a href="/private-registrations">Registrations Home</a>
        <a href="/private-registered-families">Families</a>
        <a class="active" href="/private-gifts">Gifts</a>
        <a href="/private-family-code">Family Codes</a>
        <a href="/private-register-family">Register New Family</a>
        <a href="/private-event-settings">Event Settings</a>
        <a href="/private-event">Event</a>
    </nav>
    <h1>Gift Inventories</h1>

    <div class="grid registerForm-admin">
        <form action="#" method="post" class="grid grid-2">
            <div class="card">
                <h2>Family Gifts</h2>
                <p>Family gifts that we have defined in <a href="/private-event-settings">Event Settings</a>.</p>
                <button class="button-main-100" type="button" onclick="copyToClipboard('gifts-table', this)"><i class="bi bi-clipboard"></i>Copy table</button>

                <table class="table-editable" id="gifts-table">
                    <thead>
                        <tr>
                            <td>Gift</td>
                            <td>Available</td>
                            <td id="on-hand">On Hand</td>
                            <td>Requested</td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($familyGifts as $gift) : ?>
                        <tr>
                            <td><?php echo $gift[0] ?></td>
                            <td class="remaining"><?php echo $gift[1] - $gift[2]; ?></td>
                            <td class="on-hand">
                                <?php echo $gift[1] ?>
                                <input type="number" name="gift[<?php echo $gift[0] ?>]" value="<?php echo $gift[1] ?>" aria-labelledby="on-hand">
                            </td>
                            <td><?php echo $gift[2] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <button class="button button-main-500" name="save" type="submit">Save</button>
            </div>

            <div class="card">
                <h2>Family Gifts</h2>
                <p>Count of all requested family gifts.</p>
                <canvas id="familyGiftGraph" width="100%"></canvas>
            </div>

            <div class="card">
                <h2>Member Gifts</h2>
                <p>Member gifts that we have defined in <a href="/private-event-settings">Event Settings</a>.</p>
                <button class="button-main-100" type="button" onclick="copyToClipboard('gifts-table', this)"><i class="bi bi-clipboard"></i>Copy table</button>

                <table class="table-editable" id="gifts-table">
                    <thead>
                        <tr>
                            <td>Gift</td>
                            <td>Available</td>
                            <td id="on-hand">On Hand</td>
                            <td>Requested</td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($memberGifts as $gift) : ?>
                        <tr>
                            <td><?php echo $gift[0] ?></td>
                            <td class="remaining"><?php echo $gift[1] - $gift[2]; ?></td>
                            <td class="on-hand">
                                <?php echo $gift[1] ?>
                                <input type="number" name="gift[<?php echo $gift[0] ?>]" value="<?php echo $gift[1] ?>" aria-labelledby="on-hand">
                            </td>
                            <td><?php echo $gift[2] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <button class="button button-main-500" name="save" type="submit">Save</button>
            </div>

            <div class="card">
                <h2>Custom Gifts</h2>
                <p>If a family member requests a gift that is not in our list of options, it will show up here. If multiple people request the gift, it will have the quantity listed.</p>

                <button class="button-main-100" type="button" onclick="copyToClipboard('custom-gifts-list', this)"><i class="bi bi-clipboard"></i>Copy list</button>
                
                <ul id="custom-gifts-list" class="box-list">
                <?php foreach($customGifts as $gift) : ?>
                        <li><?php echo $gift[0]; if($gift[1] > 1) { echo "<b>" . $gift[1] . "</b>"; }?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        </form>
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
    const needInputs = document.querySelectorAll(".remaining");

    needInputs.forEach(function(need) {
        console.log(need);
        if(parseInt(need.innerText) < 0) {
            need.dataset.validate = "invalid";
        } else {
            need.dataset.validate = "valid";
        }
    });

    const gifts = <?php echo json_encode($allGifts) ?>;
    const ctx_gifts = document.getElementById("familyGiftGraph");
    const data_gifts = {
        labels: Object.keys(gifts),
        datasets: [{
            label: "Quantity",
            data: Object.values(gifts),
            backgroundColor: [
                '#fecaca',
                '#f87171',
                '#dc2626',
                '#991b1b',
                '#450a0a'
            ],
        }]
    };
    const config_gifts = {
        type: 'bar',
        data: data_gifts,
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    min: 0,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    };

    const donutChart_gifts = new Chart(ctx_gifts, config_gifts);
</script>

<?php
get_footer();
?>
