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
                <div id="registerForm-sidebar-links">
                    <a class="registerForm-sidebar-item" id="status-family-information" href="#family-information">
                        <i class="bi bi-check-circle"></i>
                        <p>
                            <strong data-en="Family Information" data-sp="Información de Família" data-hm="Cov Ntaub Ntawv Tsev Neeg">Family Information</strong> <br>
                            <span class="registerForm-sidebar-item-status" data-en="Incomplete" data-sp="Incompleto" data-hm="Tsom Cov Tsev Neeg">Incomplete</span>
                        </p>
                    </a>
                    <a class="registerForm-sidebar-item" id="status-member1" href="#member1">
                        <i class="bi bi-check-circle"></i>
                        <p>
                            <strong data-en="About You" data-sp="Sobre Ud." data-hm="Peb Koj">About You</strong> <br>
                            <span class="registerForm-sidebar-item-status" data-en="Incomplete" data-sp="Incompleto" data-hm="Tsom">Incomplete</span>
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

                        <label for="fam-code">
                            <strong data-en="Family Code" data-sp="Código de Familia" data-hm="Kode Hluas Tsev Neeg">Family Code</strong>
                            <span data-en="Enter the &quot;family code&quot; from your invite. Each family code may only be registered once."
                                data-sp="Ingrese el &quot;código de familia&quot; de su invitación. Cada código de familia solo se puede registrar una vez."
                                data-hm="Sau ib daim &quot;kode hluas tsev neeg&quot; ntawm koj lub caij nyoog. Tsis tas li ntawd yuav ua hluas tsev neeg ib zaug.">Enter the "family code" from your invite. Each family code may only be registered once.</span>
                        </label>
                        <span id="span-fam-code" class="input-md">
                            <input id="fam-code" name="fam-id" type="text" minlength="6" maxlength="6" required data-validate="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </span>
                        <div class="form-error" id="error-fam-code"></div>

                        <label for="fam-members">
                            <strong data-en="Family Members" data-sp="Miembros de la Familia" data-hm="Tsev Neeg">Family Members</strong>
                            <span id="error-fam-members" data-en="You may not register more than 8 family members using this online form. If your household is larger than 8 members, please call us to make your reservation."
                                data-sp="No puede registrar más de 8 miembros de la familia utilizando este formulario en línea. Si su hogar tiene más de 8 miembros, llámenos para hacer su reserva."
                                data-hm="Koj tsis muaj txoj kev hloov tuaj ntxiv rau tus neeg hluas tsev neeg uas siv qhov kev ua haujlwm ntawm txhua lub caij nyoog. Yog koj lub neej muaj ntau tus hluas tsev neeg hauv 8 neeg, thov hu rau peb rau hais tias koj xav tau tus neeg hluas tsev neeg uas pab tsev." >
                                You may not register more than 8 family members using this online form. If your household is larger than 8 members, please call us to make your reservation.
                            </span>
                        </label>
                        <input class="input-sm" id="fam-members" name="fam-members" type="number" min="1" max="8" required data-max="8">

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
                        <input class="input-lg" id="fam-phone" name="fam-phone" type="tel" required placeholder="5555555555" minlength="10" autocomplete="tel">
                        <span class="form-error" id="error-fam-phone"></span>

                        <p class="label">
                            <strong data-en="Family Gift" data-sp="Regalo Familiar" data-hm="Ntsuas Cai Tsev Neeg">Family Gift</strong>
                            <span data-en="Please select whichever you would prefer. We will do our best to accommodate all preferences, but there is a chance this gift may be substituted with another gift depending on inventory levels."
                                data-sp="Por favor, seleccione el que prefiera. Haremos todo lo posible por satisfacer todas las preferencias, pero existe la posibilidad de que este regalo pueda ser reemplazado por otro regalo dependiendo de los niveles de inventario."
                                data-hm="Thov xaiv tus nqi uas koj xav kom. Peb yuav ua haujlwm tau txais tus nqi uas hluas tsev neeg xav tau tshwm sim, tab sis yuav tsum tau ua haujlwm tsis tshuaj tshuaj ntawv lawm txog qhov tsim nyog." >
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
                                    <span data-en="No Preference" data-sp="Sin Preferencia" data-hm="Tsis Txhob">No Preference</span>
                                </label>
                            </span>
                        </span>
                    </div>

                    <div class="registerForm-section" id="member1">
                    <h2 data-en="About You" data-sp="Sobre Ud." data-hm="Peb Koj">About You</h2>
                    
                    <label>
                        <strong data-en="First Name" data-sp="Primer Nombre" data-hm="Ib Npawg">First Name</strong>
                        <input class="input-lg first-name" name="members[1][name]" type="text" required autocomplete="off" minlength="2" maxlength="100">
                    </label>

                    <label>
                        <strong data-en="Age (years)" data-sp="Edad (años)" data-hm="Lub Xya Hli (xya hli)">Age (years)</strong>
                        <input class="input-sm age" name="members[1][age]" type="number" min="0" max="120" required autocomplete="off">
                    </label>

                    <label>
                        <strong data-en="Gift Preference" data-sp="Preferencia de Regalo" data-hm="Ntsuas cai tshuaj">Gift Preference</strong>
                        <div data-en="Please enter the gift that this family member would like to receive. We will do our best to accommodate all preferences, but there is a chance this gift may be substituted with another age-appropriate gift."
                            data-sp="Por favor ingrese el regalo que le gustaría recibir a este miembro de la familia. Haremos nuestro mejor esfuerzo para satisfacer todas las preferencias, pero existe la posibilidad de que este regalo pueda ser reemplazado por otro regalo adecuado para la edad."
                            data-hm="Thov sau ntsuas cai tshuaj uas no tsev neeg xav tau tshwm sim. Peb yuav ua hauj lwm ntawm txhua tshuaj tias yuav ua hluas tsev neeg txoj kev tshuaj, tab sis yuav tsum tau ua tshwm sim lwm tus lub xya hli.">
                            Please enter the gift that this family member would like to receive. We will do our best to accommodate all preferences, but there is a chance this gift may be substituted with another age-appropriate gift.
                        </div>
                        <select class="input-lg gift" required readonly name="members[1][gift]">
                            <option value="" disabled selected>Please specify age first</option>
                        </select>
                    </label>
                </div>

                    <span id="members-section"></span>

                    <span id="server-errors"></span>

                    <div class="buttons">
                        <button type="button" onclick="addMember(8);" class="button-gray-150">
                            <i class="bi bi-person-add"></i>
                            <span data-en="Add Family Member" data-sp="Agregar Miembro de la Familia" data-hm="Nce Koj Tsev Neeg">Add Family Member</span>
                        </button>
                        <button class="button-primary">
                            <span data-en="Submit" data-sp="Enviar" data-hm="Ntawv">Submit</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
        <?php endwhile; else: endif; ?>
    </div>
</main>

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
        famPhone : document.getElementById('fam-phone')
    };

    const memberForm = function (i) {return(`<div class="registerForm-section" id="member` + i + `">
                        <h2>
                            <span data-en="Family Member" data-sp="Miembro de la Familia" data-hm="Tsev Neeg Ntaub Ntawv">Family Member</span>
                        ` + i + `</h2>

                        <label>
                            <strong data-en="First Name" data-sp="Primer Nombre" data-hm="Ib Npawg">First Name</strong>
                            <input class="input-lg first-name" name="members[` + i + `][name]" type="text" required autocomplete="off" minlength="2" maxlength="100">
                        </label>

                        <label>
                            <strong data-en="Age (years)" data-sp="Edad (años)" data-hm="Lub Xya Hli (xya hli)">Age (years)</strong>
                            <input class="input-sm age" name="members[` + i + `][age]" type="number" min="0" max="120" required autocomplete="off">
                        </label>

                        <label>
                            <strong data-en="Gift Preference" data-sp="Preferencia de Regalo" data-hm="Ntsuas cai tshuaj">Gift Preference</strong>
                            <div data-en="Please enter the gift that this family member would like to receive. We will do our best to accommodate all preferences, but there is a chance this gift may be substituted with another age-appropriate gift."
                                data-sp="Por favor ingrese el regalo que le gustaría recibir a este miembro de la familia. Haremos nuestro mejor esfuerzo para satisfacer todas las preferencias, pero existe la posibilidad de que este regalo pueda ser reemplazado por otro regalo adecuado para la edad."
                                data-hm="Thov sau ntsuas cai tshuaj uas no tsev neeg xav tau tshwm sim. Peb yuav ua hauj lwm ntawm txhua tshuaj tias yuav ua hluas tsev neeg txoj kev tshuaj, tab sis yuav tsum tau ua tshwm sim lwm tus lub xya hli.">
                                Please enter the gift that this family member would like to receive. We will do our best to accommodate all preferences, but there is a chance this gift may be substituted with another age-appropriate gift.
                            </div>
                            <select class="input-lg gift" required readonly name="members[` + i + `][gift]">
                                <option value="" disabled selected>Please specify age first</option>
                            </select>
                        </label>

                        <button onclick="removeMember(` + i + `)" type="button" class="button-main-100"><i class="bi bi-trash"></i>
                            <span data-en="Remove Member" data-sp="Eliminar Miembro" data-hm="Thawj Coj Ntaub Ntawv">Remove Member</span>
                        </button>
                    </div>`)};
    const memberSidebar = function(i) {return(`<a class="registerForm-sidebar-item" id="status-member` + i + `" href="#member` + i + `">
                            <i class="bi bi-check-circle"></i>
                            <p>
                                <strong><span data-en="Family Member" data-sp="Miembro de la Familia" data-hm="Tsev Neeg Ntaub Ntawv">Family Member</span> ` + i + `</strong> <br>
                                <span class="registerForm-sidebar-item-status"
                                    data-en="Incomplete" data-sp="Incompleto" data-hm="Tsis Muaj Ntxiv">Incomplete</span>
                            </p>
                        </a>`
                    )};

    // on load:
    addFamilyValidation(famInfo);
    checkValidation(famInfo, "status-family-information");
    addMemberValidation(1);

</script>

<?php
get_footer();
?>
