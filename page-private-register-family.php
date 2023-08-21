<?php
get_header('archive');
?>
</header>
<main class="registrations-dashboard wrapper">
    <nav class="nav-secondary">
        <a href="/private-registrations">Registrations Home</a>
        <a href="/private-family-code">Family Codes</a>
        <a class="active" href="/private-register-family">Register New Family</a>
        <a href="/private-event-settings">Event Settings</a>
    </nav>
    <h1>Register Family</h1>
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    
    <div>
        <section>
            <?php the_content(); ?>
        </section>
        <?php endwhile; else: endif; ?>

        <section hidden id="family-code-form" class="floating-card">
            <form class="add-edit-family" action="" method="post">
                <div class="registerForm-section family-info">
                    <h2>Family Information</h2>

                    <div class="grid grid-3">
                        <?php if ($edit_family) : ?>
                            <label for="family-id">Family Code<i>cannot be edited</i></label>
                            <input class="span-2" id="family-id" name="family-id" type="text" minlength="6" maxlength="6" value="<?php echo $family_info['family_id']; ?>" readonly required>
                            
                            <label for="last-name">Last Name</label>
                            <input class="span-2" id="last-name" name="last-name" type="text" required placeholder="Smith" autocomplete="off" value="<?php echo $family_info['family_name']; ?>">

                            <label for="email">Email</label>
                            <input class="span-2" id="email" name="email" type="email" required placeholder="name@example.com" autocomplete="off" value="<?php echo $family_info['email']; ?>">

                            <label for="phone">Phone</label>
                            <input class="span-2" id="phone" name="phone" type="tel" required placeholder="1112223333" autocomplete="off" value="<?php echo $family_info['phone']; ?>">
                        <?php else : ?>
                            <label for="family-id">Family Code</label>
                            <input class="span-2" id="family-id" name="family-id" type="text" minlength="6" maxlength="6" required onkeyup="validateCode()">
                            
                            <label for="last-name">Last Name</label>
                            <input class="span-2" id="last-name" name="last-name" type="text" required placeholder="Smith" autocomplete="off">

                            <label for="email">Email</label>
                            <input class="span-2" id="email" name="email" type="email" required placeholder="name@example.com" autocomplete="off">

                            <label for="phone">Phone</label>
                            <input class="span-2" id="phone" name="phone" type="tel" required placeholder="1112223333" autocomplete="off">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="registerForm-section">
                    <div>
                        <h2>Family Members</h2>

                        <p>Total Family Members: <span id="family-members-total"><?php
                            if($edit_family) {
                                echo count($members);
                            } else {
                                echo "1";
                            }?>
                            </span>
                        </p>

                        <span id="family-members-table">
                            <div class="form-row">
                                <p>#</p>
                                <p id="name-label">First Name</p>
                                <p id="age-label">Age (years)</p>
                                <p id="gift-label">Gift Preference</p>
                            </div>
                            <?php if ($edit_family) : ?>
                                <input name="change-family" type="hidden" value="true" required>
                                <?php $n = 1;
                                foreach($members as $member) : ?>
                                    <div class="form-row" id="member-<? echo $n ?>" data-member="<? echo $n ?>">
                                        <strong><? echo $n ?></strong>
                                        <input id="first-name-<? echo $n ?>" name="first-name[]" placeholder="First Name" type="text" value="<? echo $member['first name']?>" required aria-labelledby="name-label">
                                        <input id="age-<? echo $n ?>" name="age[]" placeholder="Age" type="number" min="0" max="120" value="<? echo $member['age']?>" required aria-labelledby="age-label">
                                        <span>
                                            <select required disabled name="gift-preference[]" id="gift-preference-<? echo $n ?>" aria-labelledby="gift-label">
                                                <option value="" disabled selected>Please specify age first</option>
                                            </select>
                                        </span>
                                    </div>
                                <?php $n++ ?>
                            <?php endforeach; else: ?>
                            <div class="form-row" id="member-1" data-member="1">
                                <strong>1</strong>
                                <input id="first-name-1" name="first-name[]" placeholder="First Name" type="text" required>
                                <input id="age-1" name="age[]" placeholder="Age" type="number" min="0" max="120" required>
                                <span>
                                    <select required disabled name="gift-preference[]" id="gift-preference-1">
                                        <option value="" disabled selected>Please specify age first</option>
                                    </select>
                                </span>
                            </div>
                            <?php endif; ?>
                        </span>

                        <div class="buttons">
                            <button type="button" onclick="addMember()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                </svg> &nbsp;
                                Add a Family Member
                            </button>

                            <button class="button-secondary" type="button" onclick="removeMember()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                                </svg> &nbsp;
                                Delete Bottom Row
                            </button>
                        </div>
                    </div>
                </div>

                <p>By submitting this form, they consent to receiving email updates about the event.</p>

                <button class="button button-primary" type="submit">Submit</button>
            </form>
        </section>

        <section class="registerForm-main registerForm-admin">
            <form action="" method="post" id="register-form">
                <div class="card" id="family-information">
                    <h2>Family Information</h2>

                    <label for="fam-code">
                        <strong>Family Code</strong>
                    </label>
                    <span id="span-fam-code" class="input-md">
                        <input id="fam-code" name="fam-id" type="text" minlength="6" maxlength="6" required data-validate="">
                        <span class="form-error"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </span>

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
                                <input class="alt-check" type="radio" name="fam-gift" id="no-preference" value="No Preference">
                                <span class="checkbox">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </span>
                                <span data-en="No Preference" data-sp="Sin Preferencia" data-hm="Tsis Txhob">No Preference</span>
                            </label>
                        </span>
                    </span>
                </div>

                <div class="card">
                    <h2>Family Members</h2>

                    <div class="members-grid">
                        <strong>#</strong>
                        <strong id="label-name">Name</strong>
                        <strong id="label-age">Age</strong>
                        <strong id="label-gift">Gift</strong>
                    </div>
                    
                    <div class="members-grid" id="member1">
                        <strong class="family-member-number">1</strong>
                        
                        <label>
                            <input class="input-lg first-name" name="members[1][name]" type="text" required autocomplete="off" minlength="2" maxlength="100" aria-labelledby="label-name">
                        </label>

                        <label>
                            <input class="input-sm age" name="members[1][age]" type="number" min="0" max="120" required autocomplete="off" aria-labelledby="label-age">
                        </label>

                        <label>
                            <select class="input-lg gift" required readonly name="members[1][gift]" aria-labelledby="label-gift">
                                <option value="" disabled selected>Please specify age first</option>
                            </select>
                        </label>
                    </div>

                    <span id="members-section"></span>
                </div>

                <span id="server-errors"></span>

                <div class="buttons">
                    <button type="button" onclick="addMember(30);" class="button-gray-150">
                        <i class="bi bi-person-add"></i>
                        <span>Add Family Member</span>
                    </button>
                    <button class="button-primary">
                        <span id="submit">Submit</span>
                    </button>
                </div>
            </form>
        </section>
    </div>
</main>

<script type="text/javascript" src="/wp-content/themes/communitychristmasfoxcities.org/js/registration.js"></script>

<script>
    const user = "registrationapi";
    const pass = "ARpw930jkN9Lldkdn23JK";
    var familyMembers = 1;
    const form = document.getElementById('register-form');
    form.addEventListener("submit", fetchSubmitFormAdmin);
    var famInfo = {
        famCode : document.getElementById('fam-code'),
        famMembers : document.getElementById('fam-members'),
        famName : document.getElementById('fam-name'),
        famEmail : document.getElementById('fam-email'),
        famPhone : document.getElementById('fam-phone')
    };
    var edit = false;
    var editFamilyNumber;

    const memberForm = function (i) {return(`<div class="members-grid" id="member` + i + `">
                        <strong class="family-member-number">` + i + `</strong>

                        <label>
                            <input class="input-lg first-name" name="members[` + i + `][name]" type="text" required autocomplete="off" minlength="2" maxlength="100" aria-labelledby="label-name">
                        </label>

                        <label>
                            <input class="input-sm age" name="members[` + i + `][age]" type="number" min="0" max="120" required autocomplete="off" aria-labelledby="label-age">
                        </label>

                        <label>
                            <select class="input-lg gift" required readonly name="members[` + i + `][gift]" aria-labelledby="label-gift">
                                <option value="" disabled selected>Please specify age first</option>
                            </select>
                        </label>

                        <button onclick="removeMember(` + i + `)" type="button" class="button-main-100"><i class="bi bi-trash"></i></button>
                    </div>`)};

    // on load:
    addFamilyValidation(famInfo);
    checkValidation(famInfo, "status-family-information");
    addGiftListener(1);
    addMemberValidation(1);

    window.addEventListener('DOMContentLoaded', () => {
        let params = new URL(document.location).searchParams;
        editFamilyNumber = params.get("edit-family");

        if(editFamilyNumber) {
            edit = true;
            getFamily(editFamilyNumber);
        }
    });

    /*window.onbeforeunload = function() {
        return "If you exit this page you may lose your data!";
    }*/

    function getFamily(id) {
        let formData = new FormData();
        formData.append('fam-id', id);

        let url = 'https://registration.communitychristmasfoxcities.org/private/fetch-family.php';
        let headers = new Headers();
        headers.set('Authorization', 'Basic ' + btoa(user + ":" + pass));

        fetch(url, { method: 'POST', body: formData, headers: headers })
            .then(function (response) {
            return response.text();
        })
            .then(function (body) {
            if(body != "[]") {
                let retrievedFamily = JSON.parse(body);
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
            document.querySelector('#member' + m + ' .age').dispatchEvent(new KeyboardEvent('keyup'));

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
                url = 'https://registration.communitychristmasfoxcities.org/fetch-edit-family.php';
            } else {
                url = 'https://registration.communitychristmasfoxcities.org/fetch-form-validation.php';
            }

            let familyForm = new FormData(form);
            if(editFamilyNumber) {
                familyForm.append('fam-number', editFamilyNumber);
            }

            let response = fetch(url, {method:'post', body: familyForm})
            .then(function (response) {
                return response.text();
            })
            .then(function (body) {
                if(body == "true") {
                    message = "<div class='message message-success'>Family successfully registered.</div>"
                    document.querySelector('main').insertAdjacentHTML("beforeend", message);
                } else if(body == "opt-out") {
                    message = "<div class='message message-success'>Family successfully registered. Remind them to add event date to calendar because they did not subscribe to receive emails.</div>"
                    document.querySelector('main').insertAdjacentHTML("beforeend", message);
                } else if(body == "email-error") {
                    message = "<div class='message message-success'>Family successfully registered. However, there was an error adding the email to Brevo. Please add their data manually if they would like to receive emails.</div>"
                    document.querySelector('main').insertAdjacentHTML("beforeend", message);
                } else if(body == "edit-true") {
                    message = "<div class='message message-success'>Edit success!</div>"
                    document.querySelector('main').insertAdjacentHTML("beforeend", message);
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
