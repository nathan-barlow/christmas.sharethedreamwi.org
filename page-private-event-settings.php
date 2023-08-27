
<?php
get_template_part('template-parts/db-connection');
// Connect to Database Using MySQL
$conn = dbConnect('read');

// Prepare MySQL statement

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

$giftsJSON = json_encode($gifts);

echo "<pre>";
print_r($gifts['Family']);
echo "</pre>";

$conn->close();

get_header('archive');

?>
</header>
<main class="registrations-dashboard event-settings wrapper">
    <nav class="nav-secondary">
        <a href="/private-registrations">Registrations Home</a>
        <a href="/private-family-code">Family Codes</a>
        <a href="/private-register-family">Register New Family</a>
        <a class="active" href="/private-event-settings">Event Settings</a>
    </nav>
    <h1>Event Settings</h1>

    <div class="grid registerForm-admin">
        <form action="#" class="grid">
            <div class="card">
                <label for="familyGifts">
                    <h2>Family Gift Options</h2>
                    <p>
                        List of family gift options. Shows up on registration form under “which would you prefer?”
                    </p>
                </label>

                <textarea name="familyGifts" id="familyGifts" cols="30" rows="5"><? echo implode("\n", $gifts['Family']) ?></textarea>
            </div>

            <div class="card">
                <h2>Individual Gift Options</h2>
                <p>
                    List of gift options for each age group. Registration form will auto populate with these options depending on the age entered. Click to remove.
                </p>
            </div>
        </form>

        <div class="card danger">
            <h2>Danger Zone</h2>
            <p>
                Restart event and add new one. This will delete all current registrants and start from scratch. Should only be done once per year.
            </p>
            <button id="button-confirm-restart" class="button button-primary">
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
            <button class="button button-primary" id="button-restart">
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

<script>
    const user = "registrationapi";
    const pass = "ARpw930jkN9Lldkdn23JK";

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
            let url = 'https://registration.communitychristmasfoxcities.org/private/reset-event.php/';

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
