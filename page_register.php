<?php
/* Template Name: Registration OPEN */

get_header('archive');
?>
</header>
<main class="main-register">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    
    <div class="wrapper">
        <div class="registerForm-container grid grid-3">
            <aside class="registerForm-sidebar">
                <div class="registerForm-sidebar-container">
                    <h2>Form Sections</h2>
                    <div id="registerForm-sidebar-links">
                        <a class="registerForm-sidebar-item" id="status-family-information" href="#family-information">
                            <i class="bi bi-check-circle"></i>
                            <p>
                                <strong data-en="Family Information" data-sp="Información de Família" data-hm="Cov Ntaub Ntawv Tsev Neeg">Family Information</strong> <br>
                                <span class="registerForm-sidebar-item-status">Incomplete</span>
                            </p>
                        </a>
                        <a class="registerForm-sidebar-item" id="status-member1" href="#member1">
                            <i class="bi bi-check-circle"></i>
                            <p>
                                <strong data-en="About You" data-sp="Sobre Ud." data-hm="Peb Koj">About You</strong> <br>
                                <span class="registerForm-sidebar-item-status">Incomplete</span>
                            </p>
                        </a>
                    </div>
                    <button onclick="addMember(8);" class="button-main-100 button-gray-150">
                        <i class="bi bi-person-add"></i>
                        <span data-en="Add Family Member" data-sp="Agregar Miembro de la Familia" data-hm="Nce Koj Tsev Neeg">Add Family Member</span>
                    </button>
                </div>
            </aside>

            <section class="registerForm-main span-2">

                <div>
                    <?php the_content(); ?>
                </div>

                <div class="registerForm-section language-selector">
                    <select class="input-sm" aria-label="form language" name="language" id="language">
                        <option value="english">English</option>
                        <option value="spanish">Español</option>
                        <option value="hmong">Hmong</option>
                    </select>
                </div>

                <form action="" method="post" id="register-form">
                    <div class="registerForm-section" id="family-information">
                        <h2 data-en="Family Information" data-sp="Información de Família" data-hm="Cov Ntaub Ntawv Tsev Neeg">Family Information</h2>

                        <input type="hidden" name="access" id="fam-access" value="Direct">

                        <label for="fam-code">
                            <strong data-en="Family Code" data-sp="Código de Familia" data-hm="Kode Hluas Tsev Neeg">Family Code</strong>
                            <span data-en="Enter the &quot;family code&quot; from your invite. Each family code may only be registered once."
                                data-sp="Ingrese el &quot;código de familia&quot; de su invitación. Cada código de familia solo se puede registrar una vez."
                                data-hm="Cov ntawd rau tsev neeg Tsev neeg li code muaj peb tsaw leej nyob haub Koj tsev neeg">Enter the "family code" from your invite. Each family code may only be registered once.</span>
                        </label>
                        <span id="span-fam-code" class="input-md">
                            <input id="fam-code" name="fam-id" type="text" minlength="6" maxlength="6" required data-validate="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </span>
                        <div class="form-error" id="error-fam-code"></div>

                        <label for="fam-members">
                            <strong data-en="Family Members" data-sp="Miembros de la Familia" data-hm="Tsev Neeg">Family Members</strong>
                            <span id="error-fam-members" data-en="You may not register more than 8 family members using this online form. If your household is larger than 8 members, please call us at (920) 574-2199 to make your reservation."
                                data-sp="No puede registrar más de 8 miembros de la familia utilizando este formulario en línea. Si su hogar tiene más de 8 miembros, llámenos para hacer su reserva."
                                data-hm="yog Koj muaj dhau 8 leej nyob haub Koj tsev neej ces thov hu tus xom tooj (920-574-2199)." >
                                You may not register more than 8 family members using this online form. If your household is larger than 8 members, please call us at (920) 574-2199 to make your reservation.
                            </span>
                        </label>
                        <input class="input-sm" id="fam-members" name="fam-members" type="number" min="0" max="8" required data-max="8">

                        <label for="fam-name">
                            <strong data-en="Last Name" data-sp="Apellido" data-hm="Ntawv Qhia">Last Name</strong>
                        </label>
                        <input class="input-lg" id="fam-name" name="fam-name" type="text" required placeholder="Smith" autocomplete="family-name" minlength="2" maxlength="100">

                        <label for="fam-email">
                            <strong data-en="Email" data-sp="Correo Electrónico" data-hm="Kev Ua Si Email">Email</strong>
                        </label>
                        <input class="input-lg" id="fam-email" name="fam-email" type="email" required placeholder="name@example.com" autocomplete="email">

                        <span class="checkboxes">
                            <label for="email-reminders">
                                <input class="alt-check" type="checkbox" name="email-reminders" id="email-reminders" value="true" checked>
                                <span class="toggle"></span>
                                <span data-en="I would like to receive email reminders about the event"
                                    data-sp="Me gustaría recibir recordatorios por correo electrónico sobre el evento"
                                    data-hm="Kuv xav tias kuv yuav ua haujlwm ntawv ua haujlwm ntawv rau hauv tsev neeg">I would like to receive email reminders about the event</span>
                            </label>
                        </span>

                        <label for="fam-phone">
                            <strong data-en="Phone" data-sp="Teléfono" data-hm="Cov Lus">Phone</strong>
                        </label>
                        <input class="input-lg" id="fam-phone" name="fam-phone" type="tel" required placeholder="5555555555" pattern=".*[0-9].*[0-9].*[0-9].*[0-9].*[0-9].*[0-9].*[0-9].*[0-9].*[0-9].*[0-9].*" autocomplete="tel">
                        <span class="form-error" id="error-fam-phone"></span>

                        <p class="label">
                            <strong data-en="Family Gift" data-sp="Regalo Familiar" data-hm="Ntsuas Cai Tsev Neeg">Family Gift</strong>
                            <span data-en="Please select whichever you would prefer. We will do our best to accommodate all preferences, but there is a chance this gift may be substituted with another gift depending on inventory levels."
                                data-sp="Por favor, seleccione el que prefiera. Haremos todo lo posible por satisfacer todas las preferencias, pero existe la posibilidad de que este regalo pueda ser reemplazado por otro regalo dependiendo de los niveles de inventario."
                                data-hm="pliag rau tsev neej thov xaiv qhov khoom plig ua Koj tsev neeg xav tau. tab si yeej yuav muaj ib qhov kev hloov Ua yog qhov khoom plig Ua Koj xaiv ntawd nws Tsis muaj lawv ces, lawv yuav muaj ib yam khoom txawv los rau koj thiab Koj tsev neeg. thov txoj xav li cas. " >
                                Please select whichever you would prefer. We will do our best to accommodate all preferences, but there is a chance this gift may be substituted with another gift depending on inventory levels.
                            </span>
                        </p>
                        <span id="family-gift-options">
                            <span class="checkboxes">
                                <label for="no-preference">
                                    <input class="alt-check" type="radio" name="fam-gift" id="no-preference" value="No Preference" required>
                                    <span class="checkbox">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    </span>
                                    <span data-en="No Preference" data-sp="Sin Preferencia" data-hm="dab tsis los tau ">No Preference</span>
                                </label>
                            </span>
                        </span>

                        <label id="label-fam-reservation" for="fam-reservation">
                            <strong>
                                <span data-en="Reservation Time" data-sp="Hora de Reserva" data-hm="Hnub Txog Txhua">Reservation Time</span>
                            </strong>
                            <span data-en="Please let us know when you plan on attending. If none of these times work for you, please call us to see if we can fit you in for an unavailable time."
                                data-sp="Por favor, avísenos cuándo planea asistir. Si ninguno de estos horarios le funciona, llámenos para ver si podemos acomodarlo en un horario no disponible."
                                data-hm="thov xaiv ib lub si hawm - yog cov sij hawm no Koj tsis khoom thov hu peb." >
                                Please let us know when you plan on attending. If none of these times work for you, please call us to see if we can fit you in for an unavailable time.
                            </span>
                        </label>
                        <select class="input-md" required id="fam-reservation" name="fam-reservation">
                            <option value="" data-en="Please select a time" data-sp="Por favor, seleccione una hora" data-hm="Thov xa mus rau txhua hnub">Please select a time</option>
                        </select>
                    </div>

                    <div class="registerForm-section" id="member1">
                        <h2 data-en="About You" data-sp="Sobre Ud." data-hm="information txog koj">About You</h2>
                        
                        <label>
                            <strong data-en="First Name" data-sp="Primer Nombre" data-hm="Koj lub npe">First Name</strong>
                            <input class="input-lg first-name" name="members[1][name]" type="text" required autocomplete="off" minlength="2" maxlength="100">
                        </label>

                        <div class="age-category flex flex-sm flex-wrap">
                            <label>
                                <input class="alt-check adult-CHILD" type="radio" name="adult-child-1" value="child" aria-label="child" title="Child" required>
                                <div class="button button-gray-100 age-category-button">
                                    <span data-en="Child (under 18)" data-sp="Niño (menos de 18)" data-hm="menyuam yaum 18 xyoo">Child (under 18)</span>
                                </div>
                            </label>
                            <label>
                                <input class="alt-check ADULT-child" type="radio" name="adult-child-1" value="adult" aria-label="adult" title="Adult" required>
                                <div class="button button-gray-100 age-category-button">
                                    <span data-en="Adult (18+)" data-sp="Adulto (18+)" data-hm="neej laus hlob 18 xyoo">Adult (18+)</span>
                                </div>
                            </label>
                        </div>

                        <span class="age-gift" id="age-gift-1">
                            <label>
                                <strong data-en="Age (years)" data-sp="Edad (años)" data-hm="Hnub nyoog">Age (years)</strong>
                                <div>
                                    <span data-en="Enter an age between 0-17." data-sp="Ingrese una edad entre 0 y 17." data-hm="Haiv neeg dav 0-17.">Enter an age between 0-17.</span>
                                </div>
                                <input class="input-sm age" name="members[1][age]" type="number" min="0" max="17" autocomplete="off">
                            </label>

                            <!--Disallow users to select a gift preference
                            <label>
                                <strong data-en="Gift Preference" data-sp="Preferencia de Regalo" data-hm="Ntsuas cai tshuaj">Gift Preference</strong>
                                <div data-en="Please enter the gift that this family member would like to receive. We will do our best to accommodate all preferences, but there is a chance this gift may be substituted with another age-appropriate gift."
                                    data-sp="Por favor ingrese el regalo que le gustaría recibir a este miembro de la familia. Haremos nuestro mejor esfuerzo para satisfacer todas las preferencias, pero existe la posibilidad de que este regalo pueda ser reemplazado por otro regalo adecuado para la edad."
                                    data-hm="Thov sau ntsuas cai tshuaj uas no tsev neeg xav tau tshwm sim. Peb yuav ua hauj lwm ntawm txhua tshuaj tias yuav ua hluas tsev neeg txoj kev tshuaj, tab sis yuav tsum tau ua tshwm sim lwm tus lub xya hli.">
                                    Please enter the gift that this family member would like to receive. We will do our best to accommodate all preferences, but there is a chance this gift may be substituted with another age-appropriate gift.
                                </div>
                                <button class="button button-gray-150" type="button" onclick="document.getElementById('all-gift-options').showModal()"><i class="bi bi-question-circle"></i>I don't see a list when I click in the textbox.</button>
                                <input class="input-lg gift" placeholder="Selecting your age will populate a list with popular gifts" type="text" name="members[1][gift]" list="member1_gifts">
                                <datalist id="member1_gifts"></datalist>
                            </label> -->
                        </span>
                    </div>

                    <span id="members-section"></span>

                    <span id="server-errors"></span>

                    <div class="buttons registerForm-section">
                        <button type="button" onclick="addMember(8);" class="button-gray-150">
                            <i class="bi bi-person-add"></i>
                            <span data-en="Add Family Member" data-sp="Agregar Miembro de la Familia" data-hm="Nce Koj Tsev Neeg">Add Family Member</span>
                        </button>
                        <button class="button-main-500">
                            <span data-en="Submit" data-sp="Enviar" data-hm="Ntawv">Submit</span>
                        </button>
                    </div>
                </form>

                <div class="message">
                    <span data-en="Having issues with the registration form? Call us at" data-sp="¿Tiene problemas con el formulario de registro? Llámenos al" data-hm="Muaj lus nug ntxiv rau koj qhov nyiaj thiab cawm peb nyob rau hauv" >
                        Having issues with the registration form? Call us at
                    </span>
                    <a href="tel:+19205742199">(920) 574-2199</a>
                    <span data-en="to make your reservation." data-sp="para hacer su reserva." data-hm="rau hauv koj txoj kev pom thiab.">to make your reservation.</span>
                </div>
            </section>
        </div>
        <?php endwhile; else: endif; ?>
    </div>
</main>

<dialog id="all-gift-options">
    <button class="close-x" onclick="document.getElementById('all-gift-options').close()"><i class="bi bi-x-lg"></i></button>
    <h2>All gift options</h2>
    <p>
        If your child would like to choose a gift outside of the recommended gifts for their age group, that is perfectly acceptable. Feel free to type their request in the box.
    </p>
</dialog>

<script type="text/javascript" src="/wp-content/themes/communitychristmasfoxcities.org/js/registration.js"></script>

<script>
    var familyMembers = 1;
    const form = document.getElementById('register-form');
    form.addEventListener("submit", fetchSubmitForm);
    var famInfo = {
        famCode : document.getElementById('fam-code'),
        famMembers : document.getElementById('fam-members'),
        famName : document.getElementById('fam-name'),
        famEmail : document.getElementById('fam-email'),
        famPhone : document.getElementById('fam-phone'),
        famReservation : document.getElementById('fam-reservation')
    };

    const memberForm = function (i) {return(`<div class="registerForm-section" id="member` + i + `">
                        <h2>
                            <span data-en="Family Member" data-sp="Miembro de la Familia" data-hm="Tsev Neeg Ntaub Ntawv">Family Member</span>
                        ` + i + `</h2>

                        <label>
                            <strong data-en="First Name" data-sp="Primer Nombre" data-hm="Ib Npawg">First Name</strong>
                            <input class="input-lg first-name" name="members[` + i + `][name]" type="text" required autocomplete="off" minlength="2" maxlength="100">
                        </label>

                        <div class="age-category flex flex-sm flex-wrap">
                            <label>
                                <input class="alt-check adult-CHILD" type="radio" name="adult-child-${i}" value="child" aria-label="child" title="Child" required>
                                <div class="button button-gray-100 age-category-button">
                                    <span data-en="Child (under 18)" data-sp="Niño (menos de 18)" data-hm="menyuam yaum 18 xyoo">Child (under 18)</span>
                                </div>
                            </label>
                            <label>
                                <input class="alt-check ADULT-child" type="radio" name="adult-child-${i}" value="adult" aria-label="adult" title="Adult" required>
                                <div class="button button-gray-100 age-category-button">
                                    <span data-en="Adult (18+)" data-sp="Adulto (18+)" data-hm="neej laus hlob 18 xyoo">Adult (18+)</span>
                                </div>
                            </label>
                        </div>

                        <span class="age-gift" id="age-gift-${i}">
                            <label>
                                <strong data-en="Age (years)" data-sp="Edad (años)" data-hm="Hnub nyoog">Age (years)</strong>
                                <div>
                                    <span data-en="Enter an age between 0-17." data-sp="Ingrese una edad entre 0 y 17." data-hm="Haiv neeg dav 0-17.">Enter an age between 0-17.</span>
                                </div>
                                <input class="input-sm age" name="members[` + i + `][age]" type="number" min="0" max="17" required autocomplete="off">
                            </label>
                        </span>

                        <button onclick="removeMember(` + i + `)" type="button" class="button-main-100 remove-member"><i class="bi bi-trash"></i>
                            <span data-en="Remove Member" data-sp="Eliminar Miembro" data-hm="Thawj Coj Ntaub Ntawv">Remove Member</span>
                        </button>
                    </div>`)};
    const memberSidebar = function(i) {return(`<a class="registerForm-sidebar-item" id="status-member` + i + `" href="#member` + i + `">
                            <i class="bi bi-check-circle"></i>
                            <p>
                                <strong><span data-en="Family Member" data-sp="Miembro de la Familia" data-hm="Tsev Neeg Ntaub Ntawv">Family Member</span> ` + i + `</strong> <br>
                                <span class="registerForm-sidebar-item-status">Incomplete</span>
                            </p>
                        </a>`
                    )};
    
    // If we want to include gift options, this will be the form to use.
    const DEPRECATED_memberForm = function (i) {return(`<div class="registerForm-section" id="member` + i + `">
        <h2>
            <span data-en="Family Member" data-sp="Miembro de la Familia" data-hm="Tsev Neeg Ntaub Ntawv">Family Member</span>
        ` + i + `</h2>

        <label>
            <strong data-en="First Name" data-sp="Primer Nombre" data-hm="Ib Npawg">First Name</strong>
            <input class="input-lg first-name" name="members[` + i + `][name]" type="text" required autocomplete="off" minlength="2" maxlength="100">
        </label>

        <div class="age-category flex flex-sm flex-wrap">
            <label>
                <input class="alt-check adult-CHILD" type="radio" name="adult-child-${i}" value="child" aria-label="child" title="Child" required>
                <div class="button button-gray-100 age-category-button">
                    <span data-en="Child (under 18)" data-sp="Niño (menos de 18)" data-hm="Tsis Tso (thiab dav 18-)">Child (under 18)</span>
                </div>
            </label>
            <label>
                <input class="alt-check ADULT-child" type="radio" name="adult-child-${i}" value="adult" aria-label="adult" title="Adult" required>
                <div class="button button-gray-100 age-category-button">
                    <span data-en="Adult (18+)" data-sp="Adulto (18+)" data-hm="Tsis Tso (dav 18+)">Adult (18+)</span>
                </div>
            </label>
        </div>

        <span class="age-gift" id="age-gift-${i}">
            <label>
                <strong data-en="Age (years)" data-sp="Edad (años)" data-hm="Lub Xya Hli (xya hli)">Age (years)</strong>
                <div>
                    <span data-en="Enter an age between 0-17." data-sp="Ingrese una edad entre 0 y 17." data-hm="Haiv neeg dav 0-17.">Enter an age between 0-17.</span>
                </div>
                <input class="input-sm age" name="members[` + i + `][age]" type="number" min="0" max="17" required autocomplete="off">
            </label>

            <label>
                <strong data-en="Gift Preference" data-sp="Preferencia de Regalo" data-hm="Ntsuas cai tshuaj">Gift Preference</strong>
                <div data-en="Please enter the gift that this family member would like to receive. We will do our best to accommodate all preferences, but there is a chance this gift may be substituted with another age-appropriate gift."
                    data-sp="Por favor ingrese el regalo que le gustaría recibir a este miembro de la familia. Haremos nuestro mejor esfuerzo para satisfacer todas las preferencias, pero existe la posibilidad de que este regalo pueda ser reemplazado por otro regalo adecuado para la edad."
                    data-hm="Thov sau ntsuas cai tshuaj uas no tsev neeg xav tau tshwm sim. Peb yuav ua hauj lwm ntawm txhua tshuaj tias yuav ua hluas tsev neeg txoj kev tshuaj, tab sis yuav tsum tau ua tshwm sim lwm tus lub xya hli.">
                    Please enter the gift that this family member would like to receive. We will do our best to accommodate all preferences, but there is a chance this gift may be substituted with another age-appropriate gift.
                </div>
                <button class="button button-gray-150" type="button" onclick="document.getElementById('all-gift-options').showModal()"><i class="bi bi-question-circle"></i>I don't see a list when I click in the textbox.</button>
                <input class="input-lg gift" placeholder="Selecting your age will populate a list with popular gifts" type="text" required name="members[${i}][gift]" list="member${i}_gifts">
                <datalist id="member${i}_gifts"></datalist>
            </label>
        </span>

        <button onclick="removeMember(` + i + `)" type="button" class="button-main-100 remove-member"><i class="bi bi-trash"></i>
            <span data-en="Remove Member" data-sp="Eliminar Miembro" data-hm="Thawj Coj Ntaub Ntawv">Remove Member</span>
        </button>
    </div>`)};

    function getSource() {
        let params = new URL(document.location).searchParams;
        let source = params.get("source");

        if(source) {
            document.getElementById("fam-access").value = source;
        }
    }
    

    window.onload = function() {
        // on load:
        addFamilyValidation(famInfo);
        checkValidation(famInfo, "status-family-information");
        addMemberValidation(1);
        getSource();
    }

</script>

<?php
get_footer();
?>
