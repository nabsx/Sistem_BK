import "./bootstrap";

document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.querySelector("[data-menu-toggle]");
    const sidebar = document.querySelector("#sidebar");
    const passwordToggle = document.querySelector("[data-password-toggle]");
    const password = document.querySelector("#password");

    toggle?.addEventListener("click", () => sidebar?.classList.toggle("open"));
    document.querySelectorAll(".nav-item").forEach((link) =>
        link.addEventListener("click", () => sidebar?.classList.remove("open")),
    );

    const profileMenu = document.querySelector("[data-profile-menu]");
    const profileToggle = document.querySelector("[data-profile-toggle]");
    const profileDropdown = document.querySelector("[data-profile-dropdown]");
    const closeProfileMenu = () => {
        if (!profileDropdown || !profileToggle) return;
        profileDropdown.hidden = true;
        profileToggle.setAttribute("aria-expanded", "false");
        profileMenu?.classList.remove("is-open");
    };
    profileToggle?.addEventListener("click", () => {
        if (!profileDropdown) return;
        const isOpen = !profileDropdown.hidden;
        profileDropdown.hidden = isOpen;
        profileToggle.setAttribute("aria-expanded", String(!isOpen));
        profileMenu?.classList.toggle("is-open", !isOpen);
    });
    document.addEventListener("click", (event) => {
        if (profileMenu && !profileMenu.contains(event.target))
            closeProfileMenu();
    });
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") closeProfileMenu();
    });

    passwordToggle?.addEventListener("click", () => {
        if (!password) return;
        const visible = password.type === "text";
        password.type = visible ? "password" : "text";
        passwordToggle.setAttribute(
            "aria-label",
            visible ? "Tampilkan kata sandi" : "Sembunyikan kata sandi",
        );
    });

    const directory = document.querySelector("[data-student-directory]");
    if (directory) {
        const search = directory.querySelector("[data-student-search]");
        const rows = [...directory.querySelectorAll("[data-student-row]")];
        const filters = [
            ...directory.querySelectorAll("[data-student-filter]"),
        ];
        const applyFilters = () => {
            const term = search?.value.trim().toLowerCase() ?? "";
            rows.forEach((row) => {
                const matchesTerm =
                    !term ||
                    `${row.dataset.name} ${row.dataset.nis}`.includes(term);
                const matchesFilters = filters.every(
                    (filter) =>
                        !filter.value ||
                        row.dataset[filter.dataset.studentFilter] ===
                            filter.value.toLowerCase(),
                );
                row.hidden = !(matchesTerm && matchesFilters);
            });
        };
        search?.addEventListener("input", applyFilters);
        filters.forEach((filter) =>
            filter.addEventListener("change", applyFilters),
        );
        directory
            .querySelector("[data-sort-points]")
            ?.addEventListener("click", () => {
                const table = directory.querySelector("[data-student-table]");
                rows.sort(
                    (a, b) =>
                        Number(b.dataset.points) - Number(a.dataset.points),
                ).forEach((row) => table?.appendChild(row));
            });
        directory.querySelectorAll("[data-view]").forEach((button) =>
            button.addEventListener("click", () => {
                directory
                    .querySelectorAll("[data-view]")
                    .forEach((item) =>
                        item.classList.toggle("is-active", item === button),
                    );
                directory.classList.toggle(
                    "is-grid-view",
                    button.dataset.view === "grid",
                );
            }),
        );
        document.addEventListener("keydown", (event) => {
            if (
                (event.ctrlKey || event.metaKey) &&
                event.key.toLowerCase() === "k"
            ) {
                event.preventDefault();
                search?.focus();
            }
        });
    }
});
