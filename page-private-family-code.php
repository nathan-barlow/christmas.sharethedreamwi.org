
<?php
get_header('archive');
get_template_part('template-parts/db-connection');

// Connect to Database Using MySQL
$conn = dbConnect('read');

$query_ids = mysqli_query($conn, "SELECT * FROM family_id_list");

$generated_codes = [];

while($row = mysqli_fetch_array($query_ids)){
    array_push($generated_codes, $row["family_code"]);
}

function generateCode() {
    global $generated_codes;
    $permitted_chars = 'ABCDEFGHJKMNPQRSTUVWXYZ234567892345678923456789';
    $unique = false;

    while(!$unique) {
        $random_code = substr(str_shuffle($permitted_chars), 0, 6);

        if(!in_array($random_code, $generated_codes)) {
            array_push($generated_codes, $random_code);
            $unique = true;
            return $random_code;
        } else {
            $unique = false;
        }
    }
}

if (isset($_POST['organizations'])) {
    $orgs = $_POST['organization'];
    $quantities = $_POST['quantity'];

    $codes = array();

    $organizations = array();
    for($i = 0; $i < count($orgs); $i++) {
        $organizations[$i] = array(
            "organization"=>$orgs[$i],
            "quantity"=>$quantities[$i]
        );
    }

    foreach($organizations as $org) {
        $total_codes = intval($org['quantity']);
        if($total_codes == 0) {
            echo "<div class='wrapper'><div class='message message-error'>Error adding " . htmlentities($org["organization"]) . ". Quantity must be an integer greater than 0.</div></div>";
        } else {
            for($a = 0; $a < $total_codes; $a++) {
                array_push($codes, array(
                    "organization"=>$org["organization"],
                    "code"=>generateCode()
                ));
            }
        }
    }

    $add_code = $conn->prepare("INSERT INTO family_id_list (family_code, organization)
                                  VALUES (?, ?)");
    $add_code->bind_param("ss", $ADDfamilyID, $ADDorganization);

    foreach ($codes as $code) {
        $ADDfamilyID = $code['code'];
        $ADDorganization = $code['organization'];

        $add_code->execute();
    }

    $add_code->close();
}

?>
</header>
<main class="registrations-dashboard wrapper wrapper-wide">
    <nav class="nav-secondary">
        <a href="/private-registrations">Registrations Home</a>
        <a href="/private-registered-families">Families</a>
        <a href="/private-gifts">Gifts</a>
        <a class="active" href="/private-family-code">Family Codes</a>
        <a href="/private-register-family">Register New Family</a>
        <a href="/private-event-settings">Event Settings</a>
        <a href="/private-event">Event</a>
    </nav>
    <h1>Family Codes</h1>
    <div class="grid grid-3">
        <div class="span-2 grid">
            <form action="#" method="post" class="code-generator card" id="code-generator">
                <label for="organizations"><strong>Organizations</strong></label>
                <p>Enter a list of organizations, each separated by a new line (by typing the enter/return key). After you've typed all organizations you would like to add, click "Enter Quantities" and select how many invite codes you need for each organization (max 200 per form submit). The new codes will populate into a new table on the right, which you can copy and paste into a spreadsheet.</p>
                <p>
                    <i>If you would like to generate more invites for the same organization, just type the exact same organization name (by referencing the table at the right).</i>
                </p>
                <textarea name="organizations" id="organizations" cols="30" rows="10"></textarea>
                <div id="code-generator-fields"></div>
                
                <button id="enter-quantities" class="button-main-100" type="button" onclick='generateOrganizations()'>Enter Quantities</button>

                <div class="buttons hide" id="hidden-buttons">
                    <button id="goback-button" class="button button-gray-150" type='button' onclick='goBack()'>Go Back</button>
                    <button id="submit-button" class="button" type='submit'>Submit</button>
                </div>
            </form>

            <div class="card">
                <?php
                    $query_organizations = mysqli_query($conn, "SELECT organization as ORGANIZATION,
                        COUNT(organization) AS INVITES
                        FROM
                            family_id_list
                        GROUP BY
                            organization
                    ");

                    $organizations = mysqli_fetch_all($query_organizations, MYSQLI_ASSOC);?>
                <table>
                    <thead>
                        <tr>
                            <td>Organization</td>
                            <td>Number of Invites</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach($organizations as $org){
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars(stripslashes($org["ORGANIZATION"])) . "</td>";
                                echo "<td>" . htmlspecialchars($org["INVITES"]). "</td>";
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <?php if($codes) : ?>
                <div>
                    <button class="button-main-100" onclick="copyToClipboard('family-codes-new', this)"><i class="bi bi-clipboard"></i>Copy new codes</button>
    
                    <div class="container-max-height">
                        <table class="family-codes-table">
                            <thead>
                                <tr>
                                    <td>Code</td>
                                    <td>Organization</td>
                                </tr>
                            </thead>
                            <tbody id="family-codes-new">
                                <?php
                                    foreach($codes as $code){
                                        echo "<tr>";
                                        echo "<td>" . $code["code"] . "</td>";
                                        echo "<td>" . htmlspecialchars(stripslashes($code["organization"])). "</td>";
                                        echo "</tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div>
            <?php
                $family_codes = mysqli_query($conn, "SELECT * FROM family_id_list ORDER BY organization, family_code");?>
                <button class="button-main-100" onclick="copyToClipboard('family-codes-table', this)"><i class="bi bi-clipboard"></i>Copy <? echo mysqli_num_rows($family_codes) ?> codes</button>

                <div class="container-max-height">
                    <table class="family-codes-table">
                        <thead>
                            <tr>
                                <td>Code</td>
                                <td>Organization</td>
                            </tr>
                        </thead>
                        <tbody id="family-codes-table">
                            
                            <?php
                                while($row = mysqli_fetch_array($family_codes)){
                                    echo "<tr>";
                                    echo "<td>" . $row["family_code"] . "</td>";
                                    echo "<td>" . htmlspecialchars(stripslashes($row["organization"])) . "</td>";
                                    echo "</tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
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
    
<script>
    var organizationTotal = 1;

    function goBack() {
        document.getElementById('organizations').style.display = "block";
        document.querySelector('[for="organizations"').hidden = false;
        document.querySelector('#enter-quantities').hidden = false;
        document.querySelector('#hidden-buttons').classList.add('hide');

        document.getElementById("code-generator-fields").innerHTML = "";
    }
    
    function generateOrganizations() {
        var text = document.getElementById('organizations').value.trim();
        var lines = text.split("\n");
        var count = lines.length;

        if (text != "") {
            document.getElementById('organizations').style.display = "none";
            document.querySelector('[for="organizations"').hidden = true;
            document.querySelector('#enter-quantities').hidden = true;
            document.querySelector('#hidden-buttons').classList.remove('hide');

            for (let i = 1; i < (count + 1); i++) {
                line = lines[i - 1];
                organization = `
                    <span class="organization-quantities grid grid-2 grid-sm">
                    <label for="organization${i}">Organization Name</label>
                    <label for="quantity${i}">Quantity</label>

                    <input type="text" name="organization[]" id="organization${i}" value="` + line + `" tabindex="-1" required>
                    <input type="number" name="quantity[]" id="quantity${i}" min="1" max="200" required>
                    </span>`

                document.getElementById("code-generator-fields").innerHTML += organization;
            }
        } 
    }

    const characters ='ABCDEFGHJKMNPQRSTUVWXYZ234567892345678923456789';
    function generateString() {
        var length = 6;
        let result = '';
        const charactersLength = characters.length;
        for ( let i = 0; i < length; i++ ) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }

        return result;
    }
</script>


<?php
get_footer();

$conn->close();
?>
