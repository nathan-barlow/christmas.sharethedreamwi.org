<?php
get_template_part('template-parts/db-connection');
// Connect to Database Using MySQL
$conn = dbConnect('read');

function insertGifts($ageGroup, $giftList) {
    $conn = dbConnect('read');
    $giftsArray = explode("\n", $giftList);
    $giftsArray = array_map('trim', $giftsArray);
    $giftsCommaList = "'" . implode("','", $giftsArray) . "'";

    $query_delete = $conn->prepare("DELETE FROM event_settings WHERE name = ? AND value NOT IN ($giftsCommaList)");
    $query_delete->bind_param("s", $ageGroup);
    $query_delete->execute();
    $query_delete->close();

    if($giftList != "") {
        $query_insert = $conn->prepare("INSERT IGNORE INTO event_settings (name, value) VALUES (?, ?)");
        $query_insert->bind_param("ss", $ageGroup, $gift);
    
        foreach ($giftsArray as $gift) {
            $query_insert->execute();
        }
    
        $query_insert->close();
    }
    
    $conn->close();
}

if(isset($_POST['save'])) {
    $newGifts = [];

    $newGifts['gifts_family'] = trim($_POST['familyGifts']);
    $newGifts['gifts_0-3'] = trim($_POST['0-3Gifts']);
    $newGifts['gifts_4-7'] = trim($_POST['4-7Gifts']);
    $newGifts['gifts_8-11'] = trim($_POST['8-11Gifts']);
    $newGifts['gifts_12-17'] = trim($_POST['12-17Gifts']);
    $newGifts['timeframe'] = trim($_POST['timeframes']);
    $newGifts['timeframe_limit'] = trim($_POST['timeframe_limit']);
    //$newGifts['gifts_18+'] = trim($_POST['18Gifts']);

    foreach($newGifts as $ageGroup => $giftList) {
        insertGifts($ageGroup, $giftList);
    }
}

// Prepare MySQL statement
$query_familyGifts = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_family'");
$query_0x3 = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_0-3'");
$query_4x7 = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_4-7'");
$query_8x11 = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_8-11'");
$query_12x17 = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_12-17'");
$query_18x = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_18+'");
$query_timeframes = mysqli_query($conn, "SELECT value as timeframe FROM event_settings WHERE name = 'timeframe'");
$query_timeframe_limit = mysqli_query($conn, "SELECT value as timeframe_limit FROM event_settings WHERE name = 'timeframe_limit'");

$gifts = [];
$gifts['Family'] = [];
$gifts['Age 0-3'] = [];
$gifts['Age 4-7'] = [];
$gifts['Age 8-11'] = [];
$gifts['Age 12-17'] = [];
$gifts['Age 18+'] = [];
$timeframes = [];
$timeframe_limit = mysqli_fetch_all($query_timeframe_limit)[0][0];
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
while($row = mysqli_fetch_array($query_timeframes)) {
    array_push($timeframes, $row["timeframe"]);
}

$conn->close();

get_header('archive');

?>
</header>
<main class="registrations-dashboard event-settings wrapper">
    <nav class="nav-secondary">
        <a href="/private-registrations">Registrations Home</a>
        <a href="/private-registered-families">Families</a>
        <a href="/private-gifts">Gifts</a>
        <a href="/private-family-code">Family Codes</a>
        <a href="/private-register-family">Register New Family</a>
        <a class="active" href="/private-event-settings">Event Settings</a>
        <a href="/private-event">Event</a>
    </nav>
    <h1>Event Settings</h1>

    <div class="grid registerForm-admin">
        <form action="#" method="post" class="grid">
            <div class="card">
                <label for="familyGifts" style="margin-top: 0px">
                    <h2>Family Gift Options</h2>
                    <p>
                        List of family gift options. Shows up on registration form under “which would you prefer?” Separate each option with a new line.
                    </p>
                </label>

                <textarea class="vertical-resize" name="familyGifts" id="familyGifts" cols="30" rows="5"><? echo implode("\n", $gifts['Family']) ?></textarea>

                <button class="button button-main-500" name="save" type="submit">Save</button>
            </div>

            <div class="card">
                <h2>Individual Gift Options</h2>
                <p>
                    List of gift options for each age group. Registration form will auto populate with these options depending on the age entered. Separate each option with a new line. If two options have identical names, inventories will be combined and managed as a single inventory.
                </p>

                <div class="grid grid-4">
                    <span>
                        <label for="0-3Gifts">Age 0-3</label>
                        <textarea class="vertical-resize" name="0-3Gifts" id="0-3Gifts" cols="30" rows="5"><? echo implode("\n", $gifts['Age 0-3']) ?></textarea>
                    </span>
                    <span>
                        <label for="4-7Gifts">Age 4-7</label>
                        <textarea class="vertical-resize" name="4-7Gifts" id="4-7Gifts" cols="30" rows="5"><? echo implode("\n", $gifts['Age 4-7']) ?></textarea>
                    </span>
                    <span>
                        <label for="8-11Gifts">Age 8-11</label>
                        <textarea class="vertical-resize" name="8-11Gifts" id="8-11Gifts" cols="30" rows="5"><? echo implode("\n", $gifts['Age 8-11']) ?></textarea>
                    </span>
                    <span>
                        <label for="12-17Gifts">Age 12-17</label>
                        <textarea class="vertical-resize" name="12-17Gifts" id="12-17Gifts" cols="30" rows="5"><? echo implode("\n", $gifts['Age 12-17']) ?></textarea>
                    </span>
                </div>

                <button class="button button-main-500" name="save" type="submit">Save</button>
            </div>

            <div class="card">
                <label for="timeframes" style="margin-top: 0px">
                    <h2>Timeframes</h2>
                    <p>
                        List of timeframes for the event. Users will choose between them when registering for the event. The form will use an automatic algorithm to ensure each attendee has at least two options to choose from and reservations are distributed.<br><em>If no options are entered, this functionality will be disabled on the registration form.</em>
                    </p>
                </label>

                <textarea class="vertical-resize" placeholder="2:00pm-3:00pm" name="timeframes" id="timeframes" cols="30" rows="5"><? echo implode("\n", $timeframes) ?></textarea>

                <label for="timeframe-limit">
                    <h2>Timeframe Limit</h2>
                    <p>
                        After one of the timeframes reaches this value, the algorithm will kick in. When the algorithm kicks in, users will be able to select from any option that has less than this value. If there is only one or zero options less than this value, they will be able to pick between the two least popular timeframes.
                    </p>
                </label>

                <input type="number" class="input-sm" placeholder="75" name="timeframe_limit" id="timeframe-limit" value="<? echo $timeframe_limit; ?>" required min="1">

                <button class="button button-main-500" name="save" type="submit">Save</button>
            </div>
        </form>

        <div class="card danger">
            <h2>Danger Zone</h2>
            <p>
                Restart event and add new one. This will delete all current registrants and start from scratch. Should only be done once per year.
            </p>
            <button id="button-confirm-restart" class="button button-main-500">
                Restart Event
            </button>
        </div>
    </div>

    <dialog id="restart-confirmation">
        <h2>Reset Event</h2>
        <p>
            Are you sure you want to restart the event? This will delete all current registrants and store this year's event data in a backup database. The active database will be cleared. This cannot be easily undone.
        </p>
        <label for="reset-text">Type "<?php echo date('Y') ?>" to confirm</label>
        <input type="text" id="reset-text" name="reset-text" pattern="<?php echo date('Y') ?>" required placeholder="<?php echo date('Y') ?>">

        <div class="buttons">
            <button onclick="closeReset()" class="button button-gray-150">
                Cancel
            </button>
            <button class="button button-main-500" id="button-restart">
                Restart Event
            </button>
        </div>
    </dialog>

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    
    <div class="wrapper">
        <section>
            <?php the_content(); ?>
        </section>
        <?php endwhile; else: endif; ?>
    </div>
</main>

<script type="text/javascript" src="/wp-content/themes/communitychristmasfoxcities.org/js/private.js"></script>

<script>
    const button_confirm = document.getElementById("button-confirm-restart");
    const dialog_box = document.getElementById("restart-confirmation");
    const button_restart = document.getElementById("button-restart");
    const reset_text = document.getElementById("reset-text");

    button_confirm.addEventListener("click", confirmReset);
    button_restart.addEventListener("click", resetEvent);

    function confirmReset() {
        dialog_box.showModal();
    }

    function closeReset() {
        dialog_box.close();
    }

    function resetEvent() {
        let existingMessage = document.querySelector(".message");
        if(existingMessage) {
            existingMessage.remove();
        }
            
        if(reset_text.checkValidity()) {
            let url = 'https://registration.christmas.sharethedreamwi.org/private/reset-event.php/';

            let headers = new Headers();
            headers.set('Authorization', 'Basic ' + btoa(user + ":" + pass));

            let response = fetch(url, {headers: headers})
                .then(function (response) {
                    return response.text();
                })
                .then(function (body) {
                    if(body == "Authorization required") {
                        reset_text.insertAdjacentHTML("afterend", "<div class='message message-error'>Authorization failed.</div>");
                    } else if(body == "success") {
                        dialog_box.close();
                        document.querySelector(".danger").insertAdjacentHTML("afterend", "<div class='message message-success'>Success! Event has been reset.</div>");
                        reset_text.value = "";
                    } else {
                        let message = "<div class='message message-error'>Error: " + body + "</div>";
                        reset_text.insertAdjacentHTML("afterend", message);
                    }
                });
        } else {
            let message = "<div class='message message-error'>Make sure to type in the correct value.</div>";
            reset_text.insertAdjacentHTML("afterend", message);
        }
    }
</script>


<?php
get_footer();
?>