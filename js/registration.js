let giftPreferences;

// add member to form
// parameters: limit = total members able to be registered using this form
// requires: span with id = "members-section"
//           input with id = "fam-members"
//           memberForm function variable (see page_register.php script)
function addMember(limit) {
    if(familyMembers < limit) {
        familyMembers++;

        membersSection = document.getElementById("members-section");
        sidebar = document.getElementById("registerForm-sidebar-links");
        famMembers = document.getElementById("fam-members");

        membersSection.insertAdjacentHTML("beforeend", memberForm(familyMembers));
        if(sidebar) {
            sidebar.insertAdjacentHTML("beforeend", memberSidebar(familyMembers));
        }

        famMembers.value = familyMembers;
        famMembers.dataset.validate = "valid";

        addMemberValidation(familyMembers);
        addGiftListener(familyMembers);

        changeLanguage();
    } else {
        alert("You may not register more than " + limit + " family members using this online form. If you have more, please close the form without submitting and call our phone number found on the bottom of our website or on your invite to make your reservation.");
    }
}

// Remove member with given ID
// requires: div with id "member + memberID"
function removeMember(memberID) {
    el = document.getElementById("member" + memberID);
    elSidebar = document.getElementById("status-member" + memberID);
    let memInfo = {
        memName : document.querySelector('#member' + memberID + ' .first-name'),
        memAge : document.querySelector('#member' + memberID + ' .age'),
        memGift : document.querySelector('#member' + memberID + ' .gift'),
        famMembers : document.getElementById("fam-members")
    }

    if (memInfo.memName.value != "") {
        var confirmMessage = "Are you sure you would like to remove " + memInfo.memName.value + "?";
    } else {
        var confirmMessage = "Are you sure you would like to remove family member " + memberID + "?";
    }
    var removeMem = false;

    if (memInfo.memName.value != "" || memInfo.memAge.value != "" || memInfo.memGift.value != "") {
        if (confirm(confirmMessage) == true) {
            removeMem = true;
        }
    } else {
        removeMem = true; 
    }

    if (removeMem == true) {
        el.remove();
        if(elSidebar) {
            elSidebar.remove();
        }
        familyMembers--;

        // update family member total
        memInfo.famMembers.value = (familyMembers);

        // reset all numbers
        let memberForms = document.querySelectorAll("#members-section .registerForm-section");
        let sidebar = document.querySelectorAll("#registerForm-sidebar-links a");

        for(let i = 2; i < memberForms.length + 2; i++) {
            let form = memberForms[i - 2];
            if(sidebar) {
                let status = sidebar[i];

                status.setAttribute("id", "status-member" + i);
                status.setAttribute("href", "#member" + i);
                status.querySelector("strong").innerText = "Family Member " + i;
            }

            let arrayName = "members[" + i + "]";
            form.setAttribute("id", "member" + i);
            form.querySelector("h2").innerText = "Family Member " + i;
            form.querySelector(".first-name").setAttribute("name", arrayName + "[name]");
            form.querySelector(".age").setAttribute("name", arrayName + "[age]");
            form.querySelector(".gift").setAttribute("name", arrayName + "[gift]");
            form.querySelector("button").setAttribute("onclick", "removeMember(" + i + ")");

            // Before cloning and replacing the form, set the cloned age input value
            let new_form = form.cloneNode(true);

            // Preserve the selected age input value
            new_form.querySelector(".age").value = form.querySelector(".age").value;

            // Preserve the selected gift select value
            let originalGiftSelect = form.querySelector(".gift");
            let clonedGiftSelect = new_form.querySelector(".gift");
            clonedGiftSelect.value = originalGiftSelect.value;

            // Ensure the selected option is visually displayed
            for (let option of clonedGiftSelect.options) {
                if (option.value === clonedGiftSelect.value) {
                    option.selected = true;
                } else {
                    option.selected = false;
                }
            }

            form.parentNode.replaceChild(new_form, form);

            addMemberValidation(i);
            addGiftListener(i);
        }

        let adminMembers = document.querySelectorAll("#members-section .members-grid");

        for(let i = 2; i < adminMembers.length + 2; i++) {
            let form = adminMembers[i - 2];

            let arrayName = "members[" + i + "]";
            form.setAttribute("id", "member" + i);
            form.querySelector(".family-member-number").innerText = i;
            form.querySelector(".first-name").setAttribute("name", arrayName + "[name]");
            form.querySelector(".age").setAttribute("name", arrayName + "[age]");
            form.querySelector(".gift").setAttribute("name", arrayName + "[gift]");
            form.querySelector("button").setAttribute("onclick", "removeMember(" + i + ")");

            // Before cloning and replacing the form, set the cloned age input value
            let new_form = form.cloneNode(true);

            // Preserve the selected age input value
            new_form.querySelector(".age").value = form.querySelector(".age").value;

            // Preserve the selected gift select value
            let originalGiftSelect = form.querySelector(".gift");
            let clonedGiftSelect = new_form.querySelector(".gift");
            clonedGiftSelect.value = originalGiftSelect.value;

            // Ensure the selected option is visually displayed
            for (let option of clonedGiftSelect.options) {
                if (option.value === clonedGiftSelect.value) {
                    option.selected = true;
                } else {
                    option.selected = false;
                }
            }

            form.parentNode.replaceChild(new_form, form);

            addMemberValidation(i);
            addGiftListener(i);
        }

    }
    changeLanguage();
}
(function fetchGifts() {
    let url = 'https://registration.communitychristmasfoxcities.org/fetch-gifts.php/';

    let response = fetch(url)
        .then(function (response) {
            return response.text();
        })
        .then(function (body) {
            giftPreferences = JSON.parse(body);
            addGiftListener(1);
            setFamilyGifts();
        });
})();

// Add gift listener to populate gift select options
// requires: memberID (id NUMBER ONLY of member container)
//           querySelector #memberNUMBER
//                         .age
//                         .gift
function addGiftListener(memberID) {
    const member = ('#member' + memberID + " .age");
    const gift = ('#member' + memberID + " .gift");

    document.querySelector(member).addEventListener("input", (event) => { 
        const giftSelect = document.querySelector(gift);
        // Set selected option as variable
        var age = parseInt(event.target.value);
        var selectValue;

        if(0 <= age && age <= 3) {
            selectValue = "Age 0-3";
        } else if(4 <= age && age <= 7) {
            selectValue = "Age 4-7";
        } else if(8 <= age && age <= 11) {
            selectValue = "Age 8-11";
        } else if(12 <= age && age <= 17) {
            selectValue = "Age 12-17";
        } else if(18 <= age && age <= 120) {
            selectValue = "Age 18+";
        } else {
            selectValue = "";
            giftSelect.setAttribute('readonly', true);
            giftSelect.innerHTML = "<option selected disabled value=''>Please specify age first</option>";
        }
        
        // For each choice in the selected option
        if(selectValue) {
            // Empty the target field
            let child = giftSelect.lastElementChild;
            while (child) {
                giftSelect.removeChild(child);
                child = giftSelect.lastElementChild;
            }

            giftSelect.innerHTML += "<option selected disabled value=''>" + selectValue + " gift choices</option>";

            for (i = 0; i < giftPreferences[selectValue].length; i++) {
                // Output choice in the target field
                giftSelect.innerHTML += ("<option value='" + giftPreferences[selectValue][i] + "'>" + giftPreferences[selectValue][i] + "</option>");
            }

            giftSelect.innerHTML += ("<option value='No Preference'>No Preference</option>");

            giftSelect.removeAttribute('readonly');
        }
    });
}

// Validate member form section. Called by addMember and removeMember.
// requires: memberID (id NUMBER ONLY of member container)
//           querySelector #memberNUMBER
//                         .first-name
//                         .age
//                         .gift
function addMemberValidation(memberID) {
    let memInfo = {
        memName : document.querySelector('#member' + memberID + ' .first-name'),
        memAge : document.querySelector('#member' + memberID + ' .age'),
        memGift : document.querySelector('#member' + memberID + ' .gift')
    }

    for(let item in memInfo) {
        memInfo[item].dataset.validate = "";
    }

    let progressID = "status-member" + memberID;

    //OPTIMIZE LATER ----------------
    let validName = memInfo.memName.checkValidity();

    if(validName && memInfo.memName.value != "") {
        memInfo.memName.dataset.validate = "valid";
    } else if(memInfo.memName.value != "") {
        memInfo.memName.dataset.validate = "invalid";
    }

    let validAge = memInfo.memAge.checkValidity();

    if(validAge && memInfo.memAge.value != "") {
        memInfo.memAge.dataset.validate = "valid";
    } else if(memInfo.memAge.value != "") {
        memInfo.memAge.dataset.validate = "invalid";
    }
    if(memInfo.memGift.value != "") {
        memInfo.memGift.dataset.validate = "valid";
    }

    
    checkValidation(memInfo, progressID);

    memInfo.memName.addEventListener("change", (event) => {
        let valid = memInfo.memName.checkValidity();

        if(valid) {
            memInfo.memName.dataset.validate = "valid";
        } else {
            memInfo.memName.dataset.validate = "invalid";
        }

        checkValidation(memInfo, progressID);
    });

    memInfo.memAge.addEventListener("change", (event) => {
        let valid = memInfo.memAge.checkValidity();

        if(valid) {
            memInfo.memAge.dataset.validate = "valid";
        } else {
            memInfo.memAge.dataset.validate = "invalid";
        }

        if(memInfo.memGift.value == "") {
            memInfo.memGift.dataset.validate = "invalid";
        } else {
            memInfo.memGift.dataset.validate = "valid";
        }

        checkValidation(memInfo, progressID);
    });

    memInfo.memGift.addEventListener("change", (event) => {
        if(memInfo.memGift.value == "") {
            memInfo.memGift.dataset.validate = "invalid";
        } else {
            memInfo.memGift.dataset.validate = "valid";
        }

        checkValidation(memInfo, progressID);
    });

}

// Submit form using fetch API
// requires: element with ID = server-errors
function fetchSubmitForm(event) {
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
        let url = 'https://registration.communitychristmasfoxcities.org/fetch-form-validation.php';

        let response = fetch(url, {method:'post', body: new FormData(form)})
        .then(function (response) {
            return response.text();
        })
        .then(function (body) {
            if(body == "true") {
                newURL = "/register-success";
                window.location.replace(newURL);
            } else if(body == "opt-out") {
                newURL = "/register-success?opt-out";
                window.location.replace(newURL);
            } else if(body == "email-error") {
                console.log(body);
                newURL = "/register-success?email-error";
                window.location.replace(newURL);
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


// Validate register form
// requires: famInfo object with famCode, famMembers, famName, famEmail, famPhone [form input nodes]
function addFamilyValidation(famInfo) {
    for(let item in famInfo) {
        famInfo[item].dataset.validate = "";
    }

    let progressID = "status-family-information";
    checkValidation(famInfo, progressID);

    famInfo.famCode.addEventListener("input", (event) => { 
        const famCodeCheck = document.querySelector("#span-fam-code svg");
        const famCodeError = document.querySelector("#error-fam-code");
        const url = "https://registration.communitychristmasfoxcities.org/fetch-code-validation.php";

        if(famInfo.famCode.value.length == 6) {
            var formData = new FormData();
            formData.append('code', famInfo.famCode.value);

            fetch(url, { method: 'POST', body: formData })
                .then(function (response) {
                return response.text();
            })
                .then(function (body) {

                if(body == "true") {
                    famCodeCheck.classList.add("valid");
                    famCodeError.innerText = "";
                    famInfo.famCode.dataset.validate = "valid";
                } else {
                    famCodeError.innerText = body;
                    famCodeCheck.classList.remove("valid");
                    famInfo.famCode.dataset.validate = "invalid";
                }
                checkValidation(famInfo, progressID);
            });

        } else {
            famCodeCheck.classList.remove("valid");
            famCodeError.innerText = "";
            famInfo.famCode.dataset.validate = "";
        }
    });

    famInfo.famMembers.addEventListener("change", (event) => {
        let max = parseInt(famInfo.famMembers.dataset.max);
        if (famInfo.famMembers.value <= max && famInfo.famMembers.value > 0) {
            famInfo.famMembers.dataset.validate = "valid";
            let errorMessageSpan = document.getElementById("error-fam-members");
            if(errorMessageSpan) {
                errorMessageSpan.classList.remove("form-error");
            }

            var difference = famInfo.famMembers.value - familyMembers;
            if (difference > 0) {
                for(let i = 0; i < difference; i++) {
                    addMember(max);
                }
            } else if (difference < 0) {
                let newValue = famInfo.famMembers.value;
                let oldValue = familyMembers;
                for(let x = oldValue; x > newValue; x--) {
                    removeMember(x);
                }
                famInfo.famMembers.value = familyMembers;
            }
        } else {
            famInfo.famMembers.dataset.validate = "invalid";

            let errorMessageSpan = document.getElementById("error-fam-members");
            if(errorMessageSpan) {
                errorMessageSpan.classList.add("form-error");
            }
        }

        checkValidation(famInfo, progressID);

    });

    famInfo.famName.addEventListener("change", (event) => {
        let valid = famInfo.famName.checkValidity();

        if(valid) {
            famInfo.famName.dataset.validate = "valid";
        } else {
            famInfo.famName.dataset.validate = "invalid";
        }

        checkValidation(famInfo, progressID);
    });

    famInfo.famEmail.addEventListener("change", (event) => {
        let valid = famInfo.famEmail.checkValidity();

        if(valid) {
            famInfo.famEmail.dataset.validate = "valid";
        } else {
            famInfo.famEmail.dataset.validate = "invalid";
        }

        checkValidation(famInfo, progressID);
    });

    famInfo.famPhone.addEventListener("change", (event) => {
        let valid = famInfo.famPhone.checkValidity();

        if(valid) {
            famInfo.famPhone.dataset.validate = "valid";
            let errorMessageSpan = document.getElementById("error-fam-phone");
            if(errorMessageSpan) {
                errorMessageSpan.innerHTML = "";
            }
        } else {
            famInfo.famPhone.dataset.validate = "invalid";
            let errorMessageSpan = document.getElementById("error-fam-phone");
            if(errorMessageSpan) {
                errorMessageSpan.innerHTML = "Please enter a valid, 10 digit phone number with area code.";
            }
        }

        checkValidation(famInfo, progressID);
    });
}

// Check if all items in section are valid and update progress to reflect
// items: object containing form elements
// containerID: id of sidebar link
function checkValidation(items, containerID) {
    let totalItems = 0;
    let totalValid = 0;
    let totalErrors = 0;

    for(let item in items) {
        totalItems++;

        if(items[item].dataset.validate == "valid") {
            totalValid++;
        } else if(items[item].dataset.validate == "invalid") {
            totalErrors++;
        }
    }

    if(containerID) {
        if (totalErrors > 0) {
            if (totalErrors == 1) {
                updateProgress(containerID, (totalErrors + " error"));
            } else {
                updateProgress(containerID, (totalErrors + " errors"));
            }
        } else if(totalItems == totalValid) {
            updateProgress(containerID, "Complete");
        } else {
            updateProgress(containerID, "Incomplete");
        }
    }
}

// Update sidebar progress using container ID and status
// Status: Complete, Incomplete, or Error
function updateProgress(id, status) {
    let icon = document.querySelector('#' + id + ' i');
    let progress = document.querySelector('#' + id + ' .registerForm-sidebar-item-status');

    if(icon) {
        if(status == "Complete") {
            icon.classList.add("form-success");
        } else if(status.includes("error")) {
            icon.classList.add("form-error");
        } else {
            icon.classList.remove("form-error");
            icon.classList.remove("form-success");
        }
    }

    if(progress) {
        progress.innerText = status;
    }
}

function setFamilyGifts() {
    let container = document.getElementById('family-gift-options');

    for (i = 0; i < giftPreferences['Family'].length; i++) {
        let giftId = giftPreferences['Family'][i].toLowerCase();
        let giftName = giftPreferences['Family'][i];
        let newOption = `
            <span class="checkboxes">
                <label for="` + giftId + `">
                    <input class="alt-check" type="radio" name="fam-gift" id="` + giftId + `" value="` + giftName + `" required>
                    <span class="checkbox">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </span>
                    <span>` + giftName + `</span>
                </label>
            </span>
        `;

        container.insertAdjacentHTML("afterbegin", newOption);
    }
}

// Language changer
const languageSelector = document.getElementById("language");
if(languageSelector) {
    languageSelector.addEventListener("change", changeLanguage);
}
function changeLanguage() {
    const languageEl = document.getElementById("language");
    if(languageEl) {
        const language = languageEl.value
        const elements = document.querySelectorAll('[data-en]');

        switch (language) {
            case "spanish":
                for(let i = 0; i < elements.length; i++) {
                    elements[i].innerText = elements[i].dataset.sp;
                }
                break;
            case "hmong":
                for(let i = 0; i < elements.length; i++) {
                    elements[i].innerText = elements[i].dataset.hm;
                }
                break;
            default:
                for(let i = 0; i < elements.length; i++) {
                    elements[i].innerText = elements[i].dataset.en;
                }
                break;
        }

        let formData = new FormData();
        formData.append('language', language);
    
        let url = 'https://registration.communitychristmasfoxcities.org/fetch-language.php';
    
        fetch(url, { method: 'POST', body: formData})
            .then(function (response) {
            return response.text();
        })
            .then(function (body) {
            if(body != "") {
                console.error(body);
            }
        });
    }
}