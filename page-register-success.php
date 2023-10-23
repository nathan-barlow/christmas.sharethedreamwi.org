<?php
get_header('archive');
?>
</header>
<main class="register-success wrapper">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <section class="floating-card" id="success-container">
        <div class="check-container">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
        <h1><?php the_title(); ?></h1>
        <?php the_content(); ?>

        <form action="#" method="post" id="satisfaction-survey" class="satisfaction-survey">
            <div id="survey-1">
                <h2>How was your registration experience?</h2>
                <p>This is a completely anonymous survey. The responses you submit are not associated with your registration.</p>
                <div class="satisfaction-container grid grid-3">
                    <label>
                        <input class="alt-check" type="radio" name="satisfaction" value="good" aria-label="good" title="Good">
                        <div class="satisfaction good">
                            <i class="bi bi-emoji-smile-fill"></i>
                        </div>
                    </label>

                    <label>
                        <input class="alt-check" type="radio" name="satisfaction" value="neutral" aria-label="neutral" title="Neutral">
                        <div class="satisfaction neutral">
                            <i class="bi bi-emoji-neutral-fill"></i>
                        </div>
                    </label>

                    <label>
                        <input class="alt-check" type="radio" name="satisfaction" value="bad" aria-label="bad" title="Bad">
                        <div class="satisfaction bad">
                            <i class="bi bi-emoji-frown-fill"></i>
                        </div>
                    </label>
                </div>
            </div>

            <div id="survey-2">
                <label for="message"><strong>Leave a comment? (optional)</strong></label>
                <p>Please let us know if you had any issues registering or have any ideas for the event. We love hearing from you!</p>
                <textarea name="message" id="message" cols="30" rows="10" maxlength="1000"></textarea>
            </div>

            <button class="button-main-500" type="submit" id="submit-button">
                Submit
            </button>

            <span id="satisfaction-errors"></span>
        </form>
    </section>
    <?php endwhile; else: endif; ?>
</main>

<script>
    const form = document.getElementById('satisfaction-survey');
    form.addEventListener("submit", fetchSubmitFeedback);
    var url = new URL(
        window.location.href
    );

    window.addEventListener("beforeunload", function(e){
        const satisfactionSelected = !!document.querySelector('input[name="satisfaction"]:checked');

        if(satisfactionSelected) {
            event.preventDefault();
            event.returnValue = "All fields except satisfaction are optional. Please submit form to let us know how we did."
            document.getElementById("submit-button").scrollIntoView();
        }
    });

    const container = document.getElementById('success-container');
    if(url.search == "?email-error") {
        const message = "<div class='message message-error'>There was an error when we attempted to add your email to our email reminder service. You have successfully registered, but if you would like to receive email reminders, <strong>please contact us so we can add you manually</strong>.</div>";

        container.insertAdjacentHTML("afterbegin", message);
        document.querySelector("p").innerText += " Please make sure to add the event date to your calendar so you don't miss out on your meals and gifts!";
    } else if(url.search == "?opt-out") {
        document.querySelector("p").innerText += " Please make sure to add the event date to your calendar so you don't miss out on your meals and gifts!";
    } else {
        document.querySelector("p").innerText += " You will receive an email confirmation shortly.";
    }

    if(localStorage.getItem('std-feedback-2023') == "true") {
        form.remove();
    }

    function fetchSubmitFeedback(event) {
        if(event) {
            event.preventDefault();
        }
        
        let url = 'https://registration.christmas.sharethedreamwi.org/fetch-feedback.php';

        let response = fetch(url, {method:'post', body: new FormData(form), keepalive:'true'})
        .then(function (response) {
            return response.text();
        })
        .then(function (body) {
            if(body == "error") {
                let message = "<div class='message message-error'>Error submitting feedback. Please make sure your message is less than 1000 characters and that you have selected your satisfaction level.</div>";
                document.getElementById("satisfaction-errors").innerHTML = message;
            } else if(body == "fail") {
                let message = "<div class='message message-error'>Error submitting feedback. Please try again.</div>";
                document.getElementById("satisfaction-errors").innerHTML = message;
            } else if(body == "success") {
                form.remove();
                localStorage.setItem('std-feedback-2023', 'true');
                let message = "<div class='message message-success'>Your response has been submitted. Thank you for helping us provide a great registration experience!</div>";
                document.getElementById("success-container").insertAdjacentHTML("beforeend", message);
            } else {
                console.log(body);
                let message = "<div class='message message-error'>Error submitting feedback. Please try again.</div>";
                document.getElementById("satisfaction-errors").innerHTML = message;
            }
        });
    }



</script>

<?php
get_footer();
?>
