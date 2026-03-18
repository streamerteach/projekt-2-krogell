document.addEventListener('DOMContentLoaded', function() {
    var x;
    var savedDate = localStorage.getItem('countdownDate');

    if (savedDate) {
        startCountdown(parseInt(savedDate));
    }

    function startCountdown(countDownDate) {
        if (x) clearInterval(x);

        x = setInterval(function() {
            var now = Date.now();
            var distance = countDownDate - now;

            if (distance < 0) {
                clearInterval(x);
                document.getElementById("dateCountdown").innerHTML = "EXPIRED - Your worst date has passed! 💔";
                localStorage.removeItem('countdownDate');
                return;
            }

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("dateCountdown").innerHTML =
                days + "d " + hours + "h " + minutes + "m " + seconds + "s ";
        }, 1000);
    }

    var dateForm = document.getElementById('dateForm');
    if (dateForm) {
        dateForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var dateValue = document.getElementById('date').value;
            var timeValue = document.getElementById('time').value;

            if (!dateValue || !timeValue) {
                alert('Select both date and time!');
                return;
            }

            var countDownDate = new Date(dateValue + 'T' + timeValue).getTime();

            if (countDownDate <= Date.now()) {
                alert('Date must be in the future!');
                return;
            }

            localStorage.setItem('countdownDate', countDownDate);
            startCountdown(countDownDate);
        });
    }
});