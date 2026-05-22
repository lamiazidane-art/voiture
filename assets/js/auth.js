document.addEventListener("DOMContentLoaded", () => {

    const tabs = document.querySelectorAll(".tab");
    const forms = document.querySelectorAll(".form-box");

    const activateTab = (target) => {
        tabs.forEach(t => t.classList.toggle("active", t.dataset.tab === target));

        forms.forEach(f => {
            f.classList.toggle("active", f.classList.contains(target));
        });
    };

    tabs.forEach(tab => {
        tab.setAttribute("type", "button");
        tab.addEventListener("click", (event) => {
            event.preventDefault();
            activateTab(tab.dataset.tab);
        });
    });

    const activeTab = document.querySelector(".tab.active");
    if (activeTab && activeTab.dataset.tab) {
        activateTab(activeTab.dataset.tab);
    }

});