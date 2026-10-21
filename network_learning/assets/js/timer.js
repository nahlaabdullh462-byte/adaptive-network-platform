function startTimer(durationInMinutes, displayElementId, formId) {
    let timer = durationInMinutes * 60;
    const display = document.getElementById(displayElementId);
    const form = document.getElementById(formId);

    const interval = setInterval(function () {
        let minutes = parseInt(timer / 60, 10);
        let seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.textContent = minutes + ":" + seconds;

        if (--timer < 0) {
            clearInterval(interval);
            alert("انتهى وقت الاختبار! سيتم تسليم إجاباتك تلقائياً.");
            form.submit();
        }
    }, 1000);
}