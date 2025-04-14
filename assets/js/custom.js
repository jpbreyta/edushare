document.addEventListener("DOMContentLoaded", function() {
    let rememberCheckbox = document.getElementById("remember");

    rememberCheckbox.addEventListener("change", function() {
        if (this.checked) {
            console.log("Remember me checked");
        } else {
            console.log("Remember me unchecked");
        }
    });
});
