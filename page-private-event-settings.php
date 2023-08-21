
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
            <button class="button-primary">
                Restart Event
            </button>
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


<?php
get_footer();
?>
