const reducedMotion1 = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

// BUTTONS ------------------------------------------------------------------------------
function createRipple(event) {
    const button = event.currentTarget;
    const btnRect = button.getBoundingClientRect();

    const circle = document.createElement("span");
    const diameter = Math.max(btnRect.width, btnRect.height);
    const radius = diameter / 2;

    circle.style.width = circle.style.height = `${diameter}px`;
    circle.style.left = `${event.clientX - (btnRect.left + radius)}px`;
    circle.style.top = `${event.clientY - (btnRect.top + radius)}px`;
    circle.classList.add("ripple");

    const ripple = button.getElementsByClassName("ripple")[0];

    if (ripple) {
        ripple.remove();
    }

    button.appendChild(circle);
}

const buttons = document.querySelectorAll("button:not(.tooltip), .button, .wp-block-button__link, .menu > .menu-item:last-of-type a, .menu > .menu-item:nth-last-of-type(2) a, .nav-secondary a, .satisfaction, .event-button");
for (const button of buttons) {
    button.addEventListener("mousedown", createRipple);
}

// COUNTERS -----------------------------------------------------------------------------
const numbers = document.getElementsByClassName("scroll-counter");
const counterContainer = document.getElementById("scroll-counter-container");
var counted = false;

if (counterContainer && !reducedMotion1) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!counted && entry.isIntersecting) {
                for (entry of numbers) {
                    const target = parseInt(entry.innerText.replace('$', ''));
                    var dollarSign = entry.innerText.includes("$");
                    
                    startCounter(target, entry, dollarSign);
                }
                counted = true;
            }
        });
    });

    observer.observe(counterContainer);
}

// start counter for numbers. takes three inputs
//  target: INT - target number to iterate to
//  counter: element to update
//  dollarSign: BOOL - true if should append dollar sign to number
function startCounter(target, counter, dollarSign) {
    // current count
    var count = 0;

    const updateCount = () => {
        // what to add to current count
        var increment = 1;
        
        // number of ms between numbers
        var speed = 1;

        if (target > 20000) {
            increment = 200;
        } else if (target > 10000) {
            increment = 100;
        } else if (target > 1000) {
            increment = 10;
        }
        
        if (count > (target - increment)) {
            speed = 50;
            increment = 1;
        } else if (count > (target - target / 10)) {
            speed = 10;
        }

        if (count < target) {
            if (dollarSign) {
                counter.innerText = "$" + (count + increment);
            } else {
                counter.innerText = count + increment;
            }
            count += increment;
            setTimeout(updateCount, speed);
        } else {
            if (dollarSign) {
                counter.innerText = "$" + target;
            } else {
                count.innerText = target;
            }
        }
    }
    updateCount();
}
  
// COPY TO CLIPBOARD --------------------------------------------------------------------
async function copyToClipboard(id, button) {
    copyText = document.getElementById(id).innerText;

    try {
        await navigator.clipboard.writeText(copyText);

        if(button) {
            button.innerHTML = "<i class='bi bi-clipboard-check'></i>Copied!";
        }
    } catch (err) {
        if(button) {
            button.innerHTML = "<i class='bi bi-clipboard-x'></i>Failed to copy";
        }
        console.error('Failed to copy: ', err);
    }
}