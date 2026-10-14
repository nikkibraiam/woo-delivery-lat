const modal = document.querySelector(".modal");
const trigger = document.querySelector(".trigger");
const closeButton = document.querySelector(".close-button");

function toggleModal() {
    modal.classList.toggle("show-modal");
}

function windowOnClick(event) {
    if (event.target === modal) {
        toggleModal();
    }
}

if (trigger) {
    trigger.addEventListener("click", toggleModal);
}
if (closeButton) {
    closeButton.addEventListener("click", toggleModal);
}
window.addEventListener("click", windowOnClick);