<?php
get_header('archive');
?>
</header>
<main class="main-register">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    
    <div class="wrapper wrapper-narrow">
        <div>
            <h1><?php the_title(); ?></h1>
            <?php the_content(); ?>
        </div>
            
        <form action="" method="post" id="check-in-form">
            <label for="fam-code">
                <strong>Family Code</strong>
                <span>Enter the "family code" from your invite.</span>
            </label>
            <span id="span-fam-code" class="input-md">
                <input id="fam-code" name="code" type="text" minlength="6" maxlength="6" required data-validate="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </span>
            <div class="form-error" id="error-fam-code"></div>

            <button id="submit-button" class="button-main-500" hidden>Check in <span id="fam-name"></span>family</button>
        </form>
    </div>
    <?php endwhile; else: endif; ?>
</main>

<script>
    const familyCode = document.getElementById("fam-code");
    const errorFamilyCode = document.getElementById("error-fam-code");
    const buttonSubmit = document.getElementById("submit-button");
    const famName = document.getElementById("fam-name");
    const form = document.getElementById('check-in-form');
    form.addEventListener("submit", fetchSubmitForm);

    familyCode.addEventListener("input", (event) => { 
        const famCodeCheck = document.querySelector("#span-fam-code svg");
        const famCodeError = document.querySelector("#error-fam-code");
        const url = "https://registration.christmas.sharethedreamwi.org/fetch-check-in-validation.php";

        if(familyCode.value.length == 6) {
            let formData = new FormData();
            formData.append('code', familyCode.value);

            fetch(url, { method: 'POST', body: formData })
            .then(function (response) {
                return response.text();
            })
            .then(function (body) {
                if(body == "invalid") {
                    errorFamilyCode.style.display = "block";
                    errorFamilyCode.innerText = "Invalid code. Please check in at the registration table.";
                } else if (body == "blocked") {
                    console.log(body);
                    errorFamilyCode.style.display = "block";
                    errorFamilyCode.innerText = "Too many failed attempts. Please check in at the registration table.";
                } else {
                    errorFamilyCode.style.display = "none";
                    famName.innerText = body + " ";
                    buttonSubmit.style.display = "block";
                }
            });
        } else {
            errorFamilyCode.innerText = "";
            buttonSubmit.style.display = "none";
        }
    });

    function fetchSubmitForm(event) {
        event.preventDefault();

        let url = 'https://registration.christmas.sharethedreamwi.org/fetch-check-in-validation.php';
        let formData = new FormData();
        formData.append('code', familyCode.value);
        formData.append('submitted', 'true');

        fetch(url, {method:'post', body: formData})
        .then(function (response) {
            return response.text();
        })
        .then(function (body) {
            if(body == "error") {
                errorFamilyCode.style.display = "block";
                errorFamilyCode.innerText = "Error marking family here. Please check in at the registration table.";
            } else {
                let familyData = JSON.parse(body);
                form.style.display = "none";
                form.insertAdjacentHTML("beforebegin", `
                    <div class="message message-success">
                        <h2>${familyData.number}</h2>
                        ${familyData.name} family successfully checked in. Please bypass the registration table and show your phone to the next volunteer to collect your bag.<br><br>
                        <strong>Family Code: </strong>${familyData.code}
                    </div>`)
            }
        });
    }

</script>

<?php
get_footer();
?>
