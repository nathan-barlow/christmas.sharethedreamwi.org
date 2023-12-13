<?php
get_header('archive');
?>
</header>
<main class="registrations-dashboard wrapper">
    <nav class="nav-secondary">
        <a href="/private-registrations">Registrations Home</a>
        <a href="/private-registered-families">Families</a>
        <a href="/private-gifts">Gifts</a>
        <a href="/private-family-code">Family Codes</a>
        <a class="active" href="/private-register-family">Register New Family</a>
        <a href="/private-event-settings">Event Settings</a>
        <a href="/private-event">Event</a>
    </nav>
    <h1>Register Family</h1>
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    
    <div>
        <section>
            <?php the_content(); ?>
        </section>
        <?php endwhile; else: endif; ?>

        <section class="registerForm-main registerForm-admin">
            <form action="" method="post" id="register-form">
                <div class="card" id="family-information">
                    <h2>Family Information</h2>

                    <input type="hidden" name="access" id="fam-access" value="<?php echo wp_get_current_user()->user_firstname ?>">

                    <label for="fam-code">
                        <strong>Family Code</strong>
                    </label>
                    <span id="span-fam-code" class="input-md">
                        <input id="fam-code" name="fam-id" type="text" minlength="6" maxlength="6" required data-validate="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </span>
                    <div class="form-error" id="error-fam-code"></div>

                    <label for="fam-members">
                        <strong>Family Members</strong>
                        <span id="error-fam-members">
                            Families may not register more than 30 members.
                        </span>
                    </label>
                    <input class="input-sm" id="fam-members" name="fam-members" type="number" min="1" max="30" required data-max="30">

                    <label for="fam-name">
                        <strong>Last Name</strong>
                    </label>
                    <input class="input-lg" id="fam-name" name="fam-name" type="text" required placeholder="Smith" autocomplete="family-name" minlength="2" maxlength="100">

                    <label for="fam-email">
                        <strong>Email</strong>
                    </label>
                    <input class="input-lg" id="fam-email" name="fam-email" type="email" required placeholder="name@example.com" autocomplete="email">

                    <span class="checkboxes">
                        <label for="email-reminders">
                            <input class="alt-check" type="checkbox" name="email-reminders" id="email-reminders" value="true" checked>
                            <span class="toggle"></span>
                            <span>Email Reminders</span>
                        </label>
                    </span>

                    <label for="fam-phone">
                        <strong>Phone</strong>
                    </label>
                    <input class="input-lg" id="fam-phone" name="fam-phone" type="tel" required placeholder="5555555555" minlength="10" autocomplete="tel">
                    <span class="form-error" id="error-fam-phone"></span>

                    <p class="label">
                        <strong>Family Gift</strong>
                    </p>
                    <span id="family-gift-options">
                        <span class="checkboxes">
                            <label for="no-preference">
                                <input class="alt-check" type="radio" name="fam-gift" id="no-preference" value="No Preference" required>
                                <span class="checkbox">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </span>
                                <span>No Preference</span>
                            </label>
                        </span>
                    </span>

                    <label class="label-fam-reservation">
                        <strong>Reservation Time</strong>
                        You are allowed to put in a value that is not in the list. However, the value must be in the list of available times specified in <a data-type="URL" href="/private-event-settings" target="_blank">event settings</a>.
                        <input class="input-md" type="text" name="fam-reservation" list="fam-reservation" id="input-fam-reservation">
                        <datalist id="fam-reservation"></datalist>
                    </label>

                    <label for="fam-notes">
                        <strong>Notes</strong>
                        (Admin only) add any notes about the registration. Max length 255 characters.
                    </label>
                    <textarea class="input-lg" id="fam-notes" name="fam-notes" maxlength="255"></textarea>
                </div>

                <div class="card registerFormAdmin-members">
                    <h2>Family Members</h2>
                    <p style="margin-bottom: 1rem"><strong>Age and Gift:</strong> Leave BLANK for ADULTS. If editing, age will show up as 18 for all adults.</p>

                    <button class="button button-gray-150" type="button" onclick="document.getElementById('all-gift-options').showModal()"><i class="bi bi-question-circle"></i>All gift options</button>

                    <div class="members-grid">
                        <strong>#</strong>
                        <strong id="label-name">Name</strong>
                        <strong id="label-age">Age</strong>
                        <strong id="label-gift">Gift</strong>
                    </div>
                    
                    <div class="members-grid" id="member1">
                        <strong class="family-member-number">1</strong>
                        
                        <label>
                            <input class="first-name" name="members[1][name]" type="text" required autocomplete="off" minlength="2" maxlength="100" aria-labelledby="label-name">
                        </label>

                        <label>
                            <input class="age" name="members[1][age]" type="number" min="0" max="18" autocomplete="off" aria-labelledby="label-age">
                        </label>

                        <label>
                            <input class="gift" type="text" name="members[1][gift]" list="member1_gifts" aria-labelledby="label-gift">
                            <datalist id="member1_gifts"></datalist>
                            <!--<select class="gift" required readonly name="members[1][gift]" aria-labelledby="label-gift">
                                <option value="" disabled selected>Please specify age first</option>
                            </select>-->
                        </label>
                    </div>

                    <span id="members-section"></span>

                    <button type="button" onclick="addMember(30);" class="button button-gray-150">
                        <i class="bi bi-person-add"></i>
                        <span>Add Family Member</span>
                    </button>
                </div>

                <span id="server-errors"></span>

                <div class="buttons">
                    <button class="button-main-500">
                        <span id="submit">Submit</span>
                    </button>
                </div>
            </form>
        </section>
    </div>
</main>

<dialog id="family-registered-confirmation">
    <button class="close-x" onclick="document.getElementById('family-registered-confirmation').close()"><i class="bi bi-x-lg"></i></button>
    <h2>Family Registered</h2>
    <div id="registered-message" class="message message-success"></div>
    <div class="buttons">
        <a class="button button-gray-150" href="/private-register-family">Register Another Family</a>
        <a class="button button-main-500" href="/private-registered-families">Back to Families</a>
    </div>
</dialog>

<dialog id="all-gift-options">
    <button class="close-x" onclick="document.getElementById('all-gift-options').close()"><i class="bi bi-x-lg"></i></button>
    <h2>All gift options</h2>
    <p>
        If your child would like to choose a gift outside of the recommended gifts for their age group, that is perfectly acceptable. Feel free to type their request in the box.
    </p>
</dialog>

<script type="text/javascript" src="/wp-content/themes/communitychristmasfoxcities.org/js/registration.js"></script>
<script type="text/javascript" src="/wp-content/themes/communitychristmasfoxcities.org/js/private.js"></script>

<script>
    var familyMembers = 1;
    const form = document.getElementById('register-form');
    form.addEventListener("submit", fetchSubmitFormAdmin);
    var famInfo = {
        famCode : document.getElementById('fam-code'),
        famMembers : document.getElementById('fam-members'),
        famName : document.getElementById('fam-name'),
        famEmail : document.getElementById('fam-email'),
        famPhone : document.getElementById('fam-phone'),
        famReservation : document.getElementById('input-fam-reservation'),
        famNotes : document.getElementById('fam-notes'),
        famAccess : document.getElementById('fam-access')
    };
    var edit = false;
    var editFamilyNumber;

    const memberForm = function (i) {return(`<div class="members-grid" id="member${i}">
                        <strong class="family-member-number">${i}</strong>

                        <label>
                            <input class="first-name" name="members[${i}][name]" type="text" required autocomplete="off" minlength="2" maxlength="100" aria-labelledby="label-name">
                        </label>

                        <label>
                            <input class="age" name="members[${i}][age]" type="number" min="0" max="120" autocomplete="off" aria-labelledby="label-age">
                        </label>

                        <label>
                            <input class="gift" type="text" name="members[${i}][gift]" list="member${i}_gifts" aria-labelledby="label-gift">
                            <datalist id="member${i}_gifts"></datalist>
                        </label>

                        <button onclick="removeMember(${i})" type="button" class="remove-member button-main-100 button-icon-only"><i class="bi bi-trash"></i></button>
                    </div>`)};

    // on load:
    addFamilyValidation(famInfo);
    checkValidation(famInfo, "status-family-information");
    addGiftListener(1);
    addMemberValidation(1);

    window.addEventListener('DOMContentLoaded', () => {
        let params = new URL(document.location).searchParams;
        editFamilyNumber = params.get("edit-family");

        if (editFamilyNumber) {
            edit = true;

            const timeoutDuration = 2000; // 5 seconds in milliseconds
            let timeoutId;

            // Define an interval to check for giftPreferences availability
            const interval = setInterval(() => {
                if (typeof giftPreferences !== "undefined") {
                    clearInterval(interval); // Clear the interval once giftPreferences is available
                    clearTimeout(timeoutId); // Clear the timeout
                    getFamily(editFamilyNumber);
                }
            }, 100); // Check every 100 milliseconds

            // Set a timeout to run an alternative function if conditions are not met within the specified time
            timeoutId = setTimeout(() => {
                clearInterval(interval); // Clear the interval
                // Run your alternative function here
                let message = "<div class='message message-error'>Failed to load gift options. Please refresh and try again.</div>";
                document.querySelector("h1").insertAdjacentHTML("beforebegin", message);
            }, timeoutDuration);
        }
    });

    function getFamily(id) {
        let formData = new FormData();
        formData.append('fam-id', id);

        let url = 'https://registration.christmas.sharethedreamwi.org/private/fetch-family.php';
        let headers = new Headers();
        headers.set('Authorization', 'Basic ' + btoa(user + ":" + pass));

        fetch(url, { method: 'POST', body: formData, headers: headers })
            .then(function (response) {
            return response.text();
        })
            .then(function (body) {
            if(body != "[]") {
                let retrievedFamily = JSON.parse(body);
                console.log(retrievedFamily);
                populateFamily(retrievedFamily);
            } else {
                let message = "<div class='message message-error'><p>Unable to retrieve data for family number " + id + ". If you are certain that family number " + id + " exists, please contact site administrator.</p><p><a class='inline-link' href='/private-registrations'>Go back to registered families?</a></p></div>";
                document.querySelector("h1").insertAdjacentHTML("beforebegin", message);
            }
        });
    }

    function populateFamily(family) {
        famInfo.famCode.value = family.fam_code;
        famInfo.famCode.readOnly = true;

        famInfo.famMembers.value = family.members.length;
        famInfo.famName.value = family.fam_name;
        famInfo.famEmail.value = family.fam_email;
        famInfo.famPhone.value = family.fam_phone;
        famInfo.famReservation.value = family.fam_reservation;
        if (!family.access.includes("(Edited)")) {
            famInfo.famAccess.value = "(Edited) " + family.access;
        } else {
            famInfo.famAccess.value = family.access;
        }
        famInfo.famNotes.value = family.notes;

        if(family.email_reminders == true) {
            document.querySelector('#email-reminders').checked = true;
        } else {
            document.querySelector('#email-reminders').checked = false;
        }

        switch (family.fam_gift) {
            case "No Preference":
                document.querySelector('#no-preference').checked = true;
                break;
            case "Puzzle":
                document.querySelector('#puzzle').checked = true;
                break;
            case "Game":
                document.querySelector('#game').checked = true;
                break;
        }

        let m = 1;
        for(member in family.members) {
            if (m > 1) {
                addMember(30);
            }

            let memInfo = family.members[member];

            document.querySelector('#member' + m + ' .first-name').value = memInfo.name;
            document.querySelector('#member' + m + ' .age').value = memInfo.age;
            document.querySelector('#member' + m + ' .age').dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));

            // if gift option doesn't exist, add it as an option
            let giftSelect = document.querySelector("#member" + m + " .gift");
            let exists = false;
            for (i = 0; i < giftSelect.length; i++){
                if (giftSelect.options[i].value == memInfo.gift){
                    exists = true;
                }
            }

            if(!exists) {
                giftSelect.innerHTML += ("<option value='" + memInfo.gift + "'>" + memInfo.gift + "</option>");
            }

            giftSelect.value = memInfo.gift;

            m++;
        }

        document.getElementById("submit").innerText = "Save";
    }

    function fetchSubmitFormAdmin(event) {
        event.preventDefault();

        let errors = document.querySelectorAll("[data-validate='invalid'");

        if(errors.length > 0) {
            let s = "";
            if(errors.length > 1) {
                s = "s";
            }
            document.getElementById("server-errors").innerHTML = "";
            document.getElementById("server-errors").innerHTML += "<div class='message message-error'>Please correct " + errors.length + " error" + s + ". The invalid inputs are highlighted in red.</div>";
        } else {
            let url;

            if(edit) {
                url = 'https://registration.christmas.sharethedreamwi.org/fetch-edit-family.php';
            } else {
                url = 'https://registration.christmas.sharethedreamwi.org/fetch-form-validation.php';
            }

            let familyForm = new FormData(form);
            if(editFamilyNumber) {
                familyForm.append('fam-number', editFamilyNumber);
            }

        // Convert the FormData object to a plain JavaScript object
        let formObject = {};

        familyForm.forEach(function(value, key) {
            formObject[key] = value;
        });

        // Now, formObject contains all the form field values as key-value pairs

        let response = fetch(url, {method:'post', body: familyForm})
        .then(function (response) {
            return response.text();
        })
        .then(function (body) {
            if(body == "true") {
                message = "Family successfully registered."
                document.getElementById("family-registered-confirmation").showModal();
                document.querySelector('#registered-message').innerHTML = message;
            } else if(body == "opt-out") {
                message = "Family successfully registered. Remind them to add event date to calendar because they did not subscribe to receive emails."
                document.getElementById("family-registered-confirmation").showModal();
                document.querySelector('#registered-message').innerHTML = message;
            } else if(body == "email-error") {
                message = "Family successfully registered. However, there was an error adding the email to Brevo. Please add their data manually if they would like to receive emails."
                document.getElementById("family-registered-confirmation").showModal();
                document.querySelector('#registered-message').innerHTML = message;
            } else if(body == "edit-true") {
                message = "Edit success!"
                document.getElementById("family-registered-confirmation").showModal();
                document.querySelector('#registered-message').innerHTML = message;
            } else {
                console.log(body);
                errors = JSON.parse(body);
                document.getElementById("server-errors").innerHTML = "";
                document.getElementById("server-errors").innerHTML += "<div class='message message-error'><ul id='error-list'></ul></div>";
                for(let error in errors) {
                    document.getElementById("error-list").innerHTML += "<li>" + errors[error] + "</li>";
                }
            }
        });
    }
    }

</script>

<?php
get_footer();
?>
