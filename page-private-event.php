<?php get_header('archive'); ?>

    <?php if ($family_id_delete): ?>
    <div class='wrapper'>
        <div class='message message-success'>
            Family <strong><?php echo htmlspecialchars($family_id_delete); ?></strong> has been deleted.
        </div>
    </div>
    <?php endif; ?>
</header>
<main class="registrations-dashboard wrapper wrapper-wide">
    <nav class="nav-secondary">
        <a href="/private-registrations">Registrations Home</a>
        <a href="/private-registered-families">Families</a>
        <a href="/private-gifts">Gifts</a>
        <a href="/private-family-code">Family Codes</a>
        <a href="/private-register-family">Register New Family</a>
        <a href="/private-event-settings">Event Settings</a>
        <a class="active" href="/private-event">Event</a>
    </nav>
    <div class="grid grid-4 margin-top">
        <div class="card totals-section">
            <p>People Here <i class="bi bi-people"></i></p>
            <h2 id="total-people"></h2>
        </div>
        <div class="card totals-section">
            <p>People Served <i class="bi bi-people-fill"></i></p>
            <h2 id="total-people-served"></h2>
        </div>
        <div class="card totals-section">
            <p>Families Served <i class="bi bi-house-fill"></i></p>
            <h2 id="total-families-served"></h2>
        </div>
        <div class="card totals-section">
            <p>Checked In Online <i class="bi bi-globe"></i></p>
            <h2 id="total-online"></h2>
        </div>
    </div>
    <div id="registrations" class="card registrations">
        <div class="options-container" id="search-options">
            <button class="edit-family button button-gray-150" onclick="toggleMenu('search')" title="search options">
                <i class="bi bi-search"></i> &nbsp; Search Options
            </button>
            <div id="search-options-menu" class="options-menu">
                <button class="flex flex-xs flex-center options-menu-item" data-query=".fam-number" data-selected="true">
                    <i class="bi bi-check2 opacity-0"></i>
                    Family Number
                </button>
                <button class="flex flex-xs flex-center options-menu-item" data-query=".fam-name" data-selected="true">
                    <i class="bi bi-check2 opacity-0"></i>
                    Last Name
                </button>
                <button class="flex flex-xs flex-center options-menu-item" data-query=".fam-code" data-selected="true">
                    <i class="bi bi-check2 opacity-0"></i>
                    Family Code
                </button>
                <button class="flex flex-xs flex-center options-menu-item" data-query=".fam-email" data-selected="false">
                    <i class="bi bi-check2 opacity-0"></i>
                    Family Email
                </button>
                <button class="flex flex-xs flex-center options-menu-item" data-query=".fam-phone" data-selected="false">
                    <i class="bi bi-check2 opacity-0"></i>
                    Family Phone
                </button>
                <button class="flex flex-xs flex-center options-menu-item" data-query=".fam-gift" data-selected="false">
                    <i class="bi bi-check2 opacity-0"></i>
                    Family Gift
                </button>
            </div>
        </div>

        <h2>Event App</h2>

        <div class="registration-filters flex flex-sm flex-center flex-wrap">
            <input type="search" id="searchFamilies" oninput="filterFamilies('search')" placeholder="Search for family, name, or family code">

            <div class="filter-options">
                <button id="not-attended" onclick="filterFamilies('not-attended')">
                    Not Attended<strong class="bg-gray100">0</strong>
                </button>
                <button id="here" onclick="filterFamilies('here')">
                    Here<strong class="bg-warning100">0</strong>
                </button>
                <button id="left" onclick="filterFamilies('left')">
                    Left<strong class="bg-success100">0</strong>
                </button>
            </div>

            <a id="recent-action" class="button button-gray-150 hidden" href=""></a>
        </div>

        <div class="table-families-container">
            <table class="table-families table-editable">
                <thead>
                    <tr>
                        <td class=""></td>
                        <td class="fam-number">#</td>
                        <td class="fam-reservation">Time</td>
                        <td class="fam-code">Code &nbsp;<i class="bi bi-pencil c-gray400"></i></td>
                        <td class="fam-name">First Name</td>
                        <td class="fam-name">Last Name</td>
                        <td class="fam-kids">Kids</td>
                        <td class="fam-kids">Adults</td>
                        <td class="fam-gift">Gift</td>
                        <td class="fam-here">Here</td>
                        <td class="fam-left">Card</td>
                    </tr>
                </thead>
                <tbody id="table-families"></tbody>
            </table>
        </div>

    </div>

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    
    <div class="wrapper">
        <section>
            <?php the_content(); ?>
        </section>
        <?php endwhile; else: endif; ?>
    </div>

    <dialog id="edit-family">
        <h2><span id="fam-name"></span></h2>
        <div class="grid grid-2 grid-xs">
            <div class="flex flex-xs">
                <i class="c-main500 bi bi-hash"></i>
                <span id="fam-number"></span>
            </div>
            <div class="flex flex-xs">
                <i class="c-main500 bi bi-upc"></i>
                <span id="fam-code"></span>
            </div>
            <div class="flex flex-xs">
                <i class="c-main500 bi bi-telephone"></i>
                <span id="fam-phone"></span>
            </div>
            <div class="flex flex-xs">
                <i class="c-main500 bi bi-envelope"></i>
                <span id="fam-email"></span>
            </div>
            <div class="flex flex-xs">
                <i class="c-main500 bi bi-gift"></i>
                <span id="fam-gift"></span>
            </div>
            <div class="flex flex-xs">
                <i class="c-main500 bi bi-clock"></i>
                <span id="fam-reservation"></span>
            </div>
            <div class="flex flex-xs">
                <i class="c-main500 bi bi-calendar"></i>
                <span id="fam-registerdate"></span>
            </div>
            <div class="flex flex-xs">
                <i class="c-main500 bi bi-info"></i>
                <span id="fam-access"></span>
            </div>
            <div class="span-2 flex flex-xs">
                <i class="c-main500 bi bi-people"></i>
                <span id="fam-members" class="grid grid-2 grid-columns"></span>
            </div>
        </div>

        <label for="fam-notes">Notes</label>
        <textarea class="vertical-resize" name="fam-notes" id="fam-notes" type="text" required></textarea>

        <div class="flex flex-sm justify-right">
            <button class="button-gray-100" type="button" onclick="document.getElementById('edit-family').close();">Cancel</button>
            <button class="button-gray-150" onclick="submitNotes()">Save</button>
            <button class="button button-main-500" id="save-and-mark-here" onclick="submitNotes(andMarkHere = true)">Save and Mark Here</button>
        </div>
        </form>
    </dialog>
</main>

<script type="text/javascript" src="/wp-content/themes/communitychristmasfoxcities.org/js/private.js"></script>

<script>
    var oldFamilies;
    var allFamilies;
    var currentFilter;
    let intervalId = null;
    const searchVariables = localStorage.getItem('std_searchVariables_EVENT') ? JSON.parse(localStorage.getItem('std_searchVariables_EVENT')) : [".fam-code", ".fam-name", ".fam-number"];
    
    document.addEventListener('keydown', evt => {
        if (evt.key === 'Escape') {
            closeMenus();
        }
    });

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
            localStorage.setItem("std_searchVariables_EVENT", JSON.stringify(searchVariables));
            searchFamilies();
        });
    });

    function searchFamilies() {
        stopFetching();
        // Declare variables
        var input, familiesContainer, families, dataSet, data, i, txtValue, match;
        input = document.getElementById("searchFamilies").value.toUpperCase();
        familiesContainer = document.getElementById("table-families");
        families = familiesContainer.getElementsByClassName("family-row");

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

        if(input != "") {
            currentFilter = "search";
        }
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

        if(menu) {
            if(menu.classList.contains('open')) {
                closeMenus();
            } else {
                closeMenus();
                menu.classList.add('open');
            }
        }
    }

    function fetchFamilies() {
        let url = 'https://registration.christmas.sharethedreamwi.org/private/fetch-families-event.php/';

        let headers = new Headers();
        headers.set('Authorization', 'Basic ' + btoa(user + ":" + pass));

        let response = fetch(url, {headers: headers})
            .then(function (response) {
                return response.text();
            })
            .then(function (body) {
                if(body == "Authorization required") {
                    document.querySelector(".family-member-table").innerHTML += "<div class='message message-error'>Authorization failed</div>";
                } else {
                    let result = JSON.parse(body);
                    let allFamilies = result.families;

                    if(JSON.stringify(allFamilies) != JSON.stringify(oldFamilies)) {
                        document.getElementById("table-families").innerHTML = "";

                        if(currentFilter == "here") {
                            allFamilies.sort((a, b) => {
                                const attendedA = new Date(a.attended);
                                const attendedB = new Date(b.attended);
                                return attendedA - attendedB;
                            });
                        }

                        for(family in allFamilies) {
                            createCard(allFamilies[family]);
                        }
                    }

                    oldFamilies = allFamilies;

                    filterFamilies(null, true);
                    generateTotals(result.people_here, result.people_served);
                }
            });
    }

    function startFetching() {
        clearInterval(intervalId);
        intervalId = setInterval(fetchFamilies, 5000);
    }

    function stopFetching() {
        clearInterval(intervalId);
    }

    fetchFamilies();

    function createCard(family) {
        let container = document.getElementById("table-families");

        let newCard = `
            <tr id="family-${family['fam_number']}" class="family-row" data-here="${family['attended']}" data-left="${family['picked_up']}" data-checked-in-online="${family['checked_in_online']}">
                <td>
                    <button class="edit-family button-toggle button button-transparent" onclick="getFamily(${family['fam_number']})" title="Add Notes">
                        <i class="bi bi-arrows-angle-expand"></i>
                    </button>
                </td>
                <td class="fam-number">
                    <a title="View ${family['fam_name']} Family" href="/private-registered-families#family-${family['fam_number']}">
                        ${family['fam_number']}
                    </a>
                </td>
                <td class="fam-reservation">${family['fam_reservation']}</td>
                <td class="fam-code">
                    <a title="Edit ${family['fam_name']} Family" href="/private-register-family/?edit-family=${family['fam_number']}">
                        ${family['fam_code']}
                    </a>
                </td>
                <td class="fam-name">
                    ${family['first_name']}
                </td>
                <td class="fam-name">
                    ${family['fam_name']}
                    <button hidden data-text="${family['notes']}" class="button-mini button-icon-only tooltip button-gray-100"><i class="bi bi-chat-text"></i></button>
                </td>
                <td class="fam-kids">${family['fam_kids']}</td>
                <td class="fam-kids">${family['fam_adults']}</td>
                <td class="fam-gift">${family['fam_gift']}</td>
                <td class="fam-here">
                    <button class="edit-family button-toggle button button-transparent button-here" onclick="toggleFamily(${family['fam_number']}, 'here')" title="mark here">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </button>
                </td>
                <td class="fam-left">
                    <button class="edit-family button-toggle button button-transparent button-left" onclick="toggleFamily(${family['fam_number']}, 'left')" title="mark card found">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </button>
                </td>
            </tr>`;

        container.insertAdjacentHTML("beforeend", newCard);
    }

    function closeMenus() {
        let menus = document.querySelectorAll(".options-menu");

        menus.forEach(function(menu) {
            menu.classList.remove("open");
        });
    }

    function filterFamilies(filter = null, maintain = false) {
        let button_notAttended = document.querySelector('#not-attended');
        let button_here = document.querySelector('#here');
        let button_left = document.querySelector('#left');

        let here = document.querySelectorAll(".family-row:not([data-here=''])[data-left='']");
        let notAttended = document.querySelectorAll(".family-row[data-here=''][data-left='']");
        let left = document.querySelectorAll(".family-row:not([data-left=''])");

        let previousFilter = currentFilter;

        if(filter) {
            currentFilter = filter;
            fetchFamilies();
        }
        
        button_notAttended.classList.remove('active');
        button_here.classList.remove('active');
        button_left.classList.remove('active');

        if(previousFilter != "search") {
            // Clear everything, start from scratch
            notAttended.forEach(function(el) {
                el.classList.remove('hide');
            });
            here.forEach(function(el) {
                el.classList.remove('hide');
            });
            left.forEach(function(el) {
                el.classList.remove('hide');
            });
        }

        switch(currentFilter) {
            case 'not-attended':
                if(previousFilter != 'not-attended' || maintain) {
                    button_notAttended.classList.add('active');
                    here.forEach(function(el) {
                        el.classList.add('hide');
                    });
                    left.forEach(function(el) {
                        el.classList.add('hide');
                    });
                } else {
                    currentFilter = null;
                }

                break;
            case 'here':
                if(previousFilter != 'here' || maintain) {
                    button_here.classList.add('active');
                    notAttended.forEach(function(el) {
                        el.classList.add('hide');
                    });
                    left.forEach(function(el) {
                        el.classList.add('hide');
                    });

                    startFetching();
                } else {
                    currentFilter = null;
                    stopFetching();
                }

                break;
            case 'left':
                if(previousFilter != 'left' || maintain) {
                    button_left.classList.add('active');
                    here.forEach(function(el) {
                        el.classList.add('hide');
                    });
                    notAttended.forEach(function(el) {
                        el.classList.add('hide');
                    });
                } else {
                    currentFilter = null;
                }

                break;
            case 'search':
                searchFamilies();
        }
    }

    function toggleFamily(number, action) {
        let formData = new FormData();
        formData.append('number', number);
        formData.append('action', action);

        let url = 'https://registration.christmas.sharethedreamwi.org/private/toggle-family.php';
        let headers = new Headers();
        headers.set('Authorization', 'Basic ' + btoa(user + ":" + pass));

        fetch(url, { method: 'POST', body: formData, headers: headers })
            .then(function (response) {
            return response.text();
        })
            .then(function (body) {
            if(body == "true") {
                let recentAction = document.getElementById("recent-action");
                recentAction.innerHTML = ("<i class='bi bi-clock-history'></i>Toggled <b>#" + number + "</b> " + action);
                recentAction.classList.remove("hidden");
                recentAction.setAttribute("href", "#family-" + number);

                //document.querySelector("#family-" + number).dataset[action] = document.querySelector("#family-" + number).dataset[action] ? "" : "thisisonlyheretomakethecheckanimate";

                fetchFamilies();
            } else {
                console.error(body);
            }
        });
    }

    function generateTotals(numPeople, numPeopleServed) {
        // FILTER BUTTONS
        let here = document.querySelector('#here');
        let notHere = document.querySelector('#not-attended');
        let left = document.querySelector('#left');

        let numHere = document.querySelectorAll(".family-row:not([data-here=''])[data-left='']").length;
        let numNotHere = document.querySelectorAll(".family-row[data-here=''][data-left='']").length;
        let numLeft = document.querySelectorAll(".family-row:not([data-left=''])").length;

        here.querySelector("strong").innerText = numHere;
        notHere.querySelector("strong").innerText = numNotHere;
        left.querySelector("strong").innerText = numLeft;

        // TOTALS AT TOP
        let totalPeopleServed = document.querySelector('#total-people-served');
        let totalPeople = document.querySelector('#total-people');
        let totalOnline = document.querySelector('#total-online');
        let totalFamiliesServed = document.querySelector('#total-families-served');

        let numOnline = document.querySelectorAll(".family-row[data-checked-in-online='1']").length;
        let numServed = document.querySelectorAll(".family-row:not([data-here=''])").length;

        totalPeopleServed.innerText = numPeopleServed;
        totalPeople.innerText = numPeople;
        totalOnline.innerText = numOnline;
        totalFamiliesServed.innerText = numServed;
    };

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
                openFamily(retrievedFamily);
            } else {
                let message = "<div class='message message-error'><p>Unable to retrieve data for family number " + id + ". If you are certain that family number " + id + " exists, please contact site administrator.</p><p><a class='inline-link' href='/private-registrations'>Go back to registered families?</a></p></div>";
                document.querySelector("h1").insertAdjacentHTML("beforebegin", message);
            }
        });
    }

    function openFamily(fam) {
        let familyInfo = {
            fam_name : document.getElementById('fam-name'),
            fam_number : document.getElementById('fam-number'),
            fam_code : document.getElementById('fam-code'),
            fam_phone : document.getElementById('fam-phone'),
            fam_email : document.getElementById('fam-email'),
            fam_gift : document.getElementById('fam-gift'),
            fam_reservation : document.getElementById('fam-reservation'),
            fam_registerdate : document.getElementById('fam-registerdate'),
            fam_access : document.getElementById('fam-access'),
            fam_notes : document.getElementById('fam-notes'),
            fam_members : document.getElementById('fam-members'),
        }

        let members = "";

        fam.members.forEach(person => {
            members += `<span>${person.name} (${person.age})</span>`;
        });

        familyInfo.fam_name.innerText = fam.fam_name;
        familyInfo.fam_number.innerText = fam.fam_number;
        familyInfo.fam_code.innerText = fam.fam_code;
        familyInfo.fam_phone.innerText = fam.fam_phone;
        familyInfo.fam_email.innerText = fam.fam_email;
        familyInfo.fam_gift.innerText = fam.fam_gift;
        familyInfo.fam_reservation.innerText = fam.fam_reservation;
        familyInfo.fam_registerdate.innerText = fam.register_date;
        familyInfo.fam_access.innerText = fam.access;
        familyInfo.fam_members.innerHTML = members;
        familyInfo.fam_notes.value = fam.notes;

        if(fam.attended != "") {
            document.getElementById("save-and-mark-here").style.display = "none";
        } else {
            document.getElementById("save-and-mark-here").style.display = "block";
        }

        document.getElementById('edit-family').showModal();
    }

    function submitNotes(andMarkHere = false) {
        let familyInfo = {
            fam_number : document.getElementById('fam-number').innerText,
            fam_notes : document.getElementById('fam-notes').value
        }

        let formData = new FormData();
        formData.append('number', familyInfo.fam_number);
        formData.append('notes', familyInfo.fam_notes);

        let url = 'https://registration.christmas.sharethedreamwi.org/private/update-family-event.php';
        let headers = new Headers();
        headers.set('Authorization', 'Basic ' + btoa(user + ":" + pass));

        fetch(url, { method: 'POST', body: formData, headers: headers })
        .then(function (response) {
            return response.text();
        })
        .then(function (body) {
            if(body == "true") {
                let recentAction = document.getElementById("recent-action");
                recentAction.innerHTML = ("<i class='bi bi-clock-history'></i><b>#" + familyInfo.fam_number + "</b> " + "notes updated");
                recentAction.classList.remove("hidden");
                recentAction.setAttribute("href", "#family-" + familyInfo.fam_number);

                fetchFamilies();

                if(andMarkHere) {
                    toggleFamily(familyInfo.fam_number, "here");
                }

                document.getElementById('edit-family').close();
            } else {
                console.error(body);
            }
        });
    }
</script>


<?php
get_footer();
?>
